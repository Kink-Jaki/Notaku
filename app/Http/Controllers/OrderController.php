<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Support\NumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
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

    public function checkout(Request $request)
    {
        $cart = $this->cart();

        if (empty($cart)) {
            return redirect()->route('marketplace')->with('error', 'Keranjang kosong.');
        }

        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);
        $jumlahItem = collect($cart)->sum('qty');

        $promoSession = session($this->promoKey());
        $diskon = $promoSession['discount'] ?? 0;
        $total = $subtotal - $diskon;

        return view('pelanggan.checkout', compact('cart', 'subtotal', 'diskon', 'total', 'jumlahItem', 'promoSession'));
    }

    public function store(Request $request)
    {
        $cart = $this->cart();

        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'delivery_type' => ['required', Rule::in(['delivery', 'pickup'])],
            'address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in(['tunai', 'qris', 'ewallet', 'transfer'])],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cart = $this->cart();
        $promoSession = session($this->promoKey());

        try {
            $orderNumber = DB::transaction(function () use ($cart, $promoSession, $request) {
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

                $order = Order::create([
                    'order_number' => NumberGenerator::next('ORD', Order::class),
                    'user_id' => Auth::id(),
                    'customer_name' => $request->customer_name,
                    'note' => $request->note,
                    'status' => Order::STATUS_PENDING,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'promo_code_id' => $promoCodeId,
                    'total' => $total,
                    'payment_method' => $request->payment_method,
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

                if ($promoCodeId !== null) {
                    PromoCode::query()->whereKey($promoCodeId)->increment('used_count');
                }

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
                        'payment_method' => $request->payment_method,
                        'total' => $total,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return $order->order_number;
            });
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        session()->forget(['pelanggan_cart', 'pelanggan_promo']);

        return redirect()->route('marketplace')->with('success', 'Pesanan Terkirim, menunggu konfirmasi kasir. Nomor pesanan: '.$orderNumber);
    }
}
