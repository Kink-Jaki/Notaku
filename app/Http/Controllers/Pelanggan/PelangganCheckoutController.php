<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Services\Payments\PaymentGateway;
use App\Support\NumberGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PelangganCheckoutController extends Controller
{
    private function cartKey(): string
    {
        return 'pelanggan_cart';
    }

    private function promoKey(): string
    {
        return 'pelanggan_promo';
    }

    private function cart(): array
    {
        return session($this->cartKey(), []);
    }

    /**
     * Sertakan gambar produk pada item keranjang agar thumbnail checkout
     * bisa menampilkan gambar asli, bukan selalu placeholder.
     *
     * @param  array<int|string, array>  $cart
     * @return array<int|string, array>
     */
    private function attachProductImages(array $cart): array
    {
        $images = Product::query()->whereIn('id', array_keys($cart))->pluck('image', 'id');

        foreach ($cart as $id => $item) {
            $cart[$id]['image'] = $images->get($id);
        }

        return $cart;
    }

    /**
     * Mode beli-sekarang: item dibangun langsung dari produk terpilih sehingga
     * checkout bisa jalan tanpa melalui keranjang.
     *
     * @return array{items?: array<int|string, array>, error?: string}
     */
    private function buyNowItems(Request $request): array
    {
        $product = Product::query()
            ->whereKey($request->integer('product_id'))
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return ['error' => 'Produk tidak ditemukan atau sudah tidak tersedia'];
        }

        $qty = $request->filled('qty') ? $request->integer('qty') : 1;

        if ($qty < 1) {
            return ['error' => 'Jumlah produk tidak valid'];
        }

        if ($qty > $product->stock) {
            return ['error' => 'Stok tidak mencukupi untuk jumlah yang diminta'];
        }

        return ['items' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'qty' => $qty,
            ],
        ]];
    }

    private function promoQualifies(?array $promoSession, int|float $subtotal): bool
    {
        if (! $promoSession) {
            return false;
        }

        $promo = PromoCode::query()->find($promoSession['promo_code_id'] ?? null);

        return (bool) $promo
            && $promo->isCurrentlyActive()
            && $subtotal >= (int) $promo->min_order;
    }

    /**
     * Siapkan data bersama untuk halaman checkout (mode keranjang & beli-sekarang).
     */
    private function prepare(Request $request, string $view): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $buyNow = $request->filled('product_id');

        if ($buyNow) {
            $result = $this->buyNowItems($request);

            if (isset($result['error'])) {
                return redirect()->route('marketplace')->with('swal_warning', $result['error']);
            }

            $cart = $this->attachProductImages($result['items']);
        } else {
            $cart = $this->cart();

            if (empty($cart)) {
                return redirect()->route('marketplace')->with('swal_warning', 'Keranjang masih kosong');
            }

            $cart = $this->attachProductImages($cart);
        }

        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);
        $jumlahItem = collect($cart)->sum('qty');

        $promoSession = session($this->promoKey());
        $diskon = $promoSession['discount'] ?? 0;

        if ($buyNow && ! $this->promoQualifies($promoSession, $subtotal)) {
            $promoSession = null;
            $diskon = 0;
        }

        $total = max(0, $subtotal - $diskon);

        return view($view, compact('cart', 'subtotal', 'diskon', 'total', 'jumlahItem', 'promoSession'));
    }

    public function index(Request $request)
    {
        return $this->prepare($request, 'pelanggan.checkout');
    }

    public function indexMobile(Request $request)
    {
        return $this->prepare($request, 'pelanggan.checkout-mobile');
    }

    public function store(Request $request, PaymentGateway $gateway)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $buyNow = $request->filled('product_id');

        if ($buyNow) {
            $result = $this->buyNowItems($request);

            if (isset($result['error'])) {
                return back()->with('swal_warning', $result['error']);
            }

            $cart = $result['items'];
        } else {
            $cart = $this->cart();

            if (empty($cart)) {
                return back()->with('swal_warning', 'Keranjang masih kosong');
            }
        }

        $paymentMethod = $request->input('payment_method');

        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'delivery_type' => ['required', Rule::in(['delivery', 'pickup'])],
            'address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in(['tunai', 'transfer', 'online'])],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validasi: tunai hanya untuk pickup
        if ($paymentMethod === 'tunai' && $request->input('delivery_type') === 'delivery') {
            return back()->withErrors(['payment_method' => 'Pembayaran tunai hanya tersedia untuk Ambil di Tempat (pickup).'])->withInput();
        }

        $promoSession = session($this->promoKey());

        try {
            $orderNumber = DB::transaction(function () use ($cart, $promoSession, $request, $gateway, $paymentMethod) {
                $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);

                $discount = 0;
                $promoCodeId = null;

                if ($promoSession) {
                    $promo = PromoCode::query()->find($promoSession['promo_code_id']);

                    if ($promo && $promo->isCurrentlyActive() && $subtotal >= (int) $promo->min_order) {
                        $discount = $promo->discountFor($subtotal);
                        $promoCodeId = $promo->id;
                    }
                }

                $total = $subtotal - $discount;

                $isOnline = $paymentMethod === 'online';
                $status = $isOnline ? Order::STATUS_UNPAID : Order::STATUS_PENDING;
                $expiresAt = $isOnline ? now()->addSeconds(config('services.xendit.invoice_duration', 3600)) : null;
                $paymentChannel = null;
                $xenditInvoiceId = null;

                $order = Order::create([
                    'order_number' => NumberGenerator::next('ORD', Order::class),
                    'user_id' => Auth::id(),
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->input('customer_phone'),
                    'delivery_type' => $request->input('delivery_type'),
                    'address' => $request->input('delivery_type') === 'delivery' ? $request->input('address') : null,
                    'note' => $request->note,
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'promo_code_id' => $promoCodeId,
                    'total' => $total,
                    'payment_method' => $isOnline ? 'online' : $paymentMethod,
                    'expires_at' => $expiresAt,
                ]);

                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'price' => $item['price'],
                        'qty' => $item['qty'],
                    ]);
                }

                // Audit log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'create_order',
                    'model_type' => Order::class,
                    'model_id' => $order->id,
                    'description' => "Pelanggan memesan {$order->order_number} ({$request->customer_name})",
                    'old_values' => null,
                    'new_values' => [
                        'order_number' => $order->order_number,
                        'customer_name' => $request->customer_name,
                        'delivery_type' => $request->delivery_type,
                        'payment_method' => $isOnline ? 'online' : $paymentMethod,
                        'total' => $total,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                if ($isOnline) {
                    $result = $gateway->createInvoice($order);
                    $order->update([
                        'xendit_invoice_id' => $result['invoice_id'],
                        'payment_channel' => null, // diisi saat webhook PAID
                    ]);

                    return ['order' => $order, 'invoice_url' => $result['invoice_url']];
                }

                // Untuk manual (tunai/transfer), increment promo di sini
                if ($promoCodeId !== null) {
                    PromoCode::query()->whereKey($promoCodeId)->increment('used_count');
                }

                return ['order' => $order, 'invoice_url' => null];
            });

            // $orderNumber adalah array atau string tergantung branch
            if (is_array($orderNumber)) {
                $order = $orderNumber['order'];
                if ($orderNumber['invoice_url']) {
                    return redirect($orderNumber['invoice_url']);
                }
                $orderNumber = $order->order_number;
            }

            if (! $buyNow) {
                session()->forget(['pelanggan_cart', 'pelanggan_promo']);
            }

            $message = $order->status === Order::STATUS_UNPAID
                ? 'Pesanan dibuat, silakan selesaikan pembayaran dalam 60 menit.'
                : 'Pesanan terkirim, menunggu konfirmasi kasir.';

            return redirect()->route('marketplace')->with('swal_success', $message.' Nomor pesanan: '.$orderNumber);
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
