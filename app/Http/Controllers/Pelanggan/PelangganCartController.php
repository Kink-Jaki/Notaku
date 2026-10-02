<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PelangganCartController extends Controller
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
     * State keranjang terkini untuk di-sync ke client (localStorage) supaya
     * badge & daftar item bisa ter-update tanpa reload.
     *
     * @param  array<int, array<string, mixed>>  $cart
     * @return array{cart: array<int, array<string, mixed>>, cart_count: int}
     */
    private function cartState(array $cart): array
    {
        return [
            'cart' => $cart,
            'cart_count' => collect($cart)->sum('qty'),
        ];
    }

    public function index(Request $request)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $cart = $this->cart();

        $subtotal = 0;
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product && $product->is_active && $product->stock > 0) {
                $item['product'] = $product;
                $item['line_total'] = $item['price'] * $item['qty'];
                $subtotal += $item['line_total'];
                $items[$productId] = $item;
            }
        }

        $promoSession = session($this->promoKey());
        $diskon = $promoSession['discount'] ?? 0;
        $total = $subtotal - $diskon;
        $jumlahItem = collect($items)->sum('qty');

        return view('pelanggan.cart', compact('items', 'subtotal', 'diskon', 'total', 'jumlahItem', 'promoSession'));
    }

    public function indexMobile(Request $request)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $cart = $this->cart();

        $subtotal = 0;
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product && $product->is_active && $product->stock > 0) {
                $item['product'] = $product;
                $item['line_total'] = $item['price'] * $item['qty'];
                $subtotal += $item['line_total'];
                $items[$productId] = $item;
            }
        }

        $promoSession = session($this->promoKey());
        $diskon = $promoSession['discount'] ?? 0;
        $total = $subtotal - $diskon;
        $jumlahItem = collect($items)->sum('qty');

        return view('pelanggan.cart-mobile', compact('items', 'subtotal', 'diskon', 'total', 'jumlahItem', 'promoSession'));
    }

    public function add(Request $request)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Data tidak valid'], 422);
        }

        $product = Product::whereKey($request->integer('product_id'))
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        $qty = $request->integer('qty');

        if ($qty > $product->stock) {
            return response()->json(['error' => 'Stok tidak mencukupi'], 400);
        }

        $cart = session('pelanggan_cart', []);
        $productId = $product->id;

        if (isset($cart[$productId])) {
            $newQty = $cart[$productId]['qty'] + $qty;
            if ($newQty > $product->stock) {
                return response()->json(['error' => 'Stok tidak mencukupi'], 400);
            }
            $cart[$productId]['qty'] = $newQty;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'qty' => $qty,
            ];
        }

        session(['pelanggan_cart' => $cart]);

        return response()->json(['success' => true, ...$this->cartState($cart)]);
    }

    public function update(Request $request)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Data tidak valid'], 422);
        }

        $productId = $request->integer('product_id');
        $qty = $request->integer('qty');
        $cart = session('pelanggan_cart', []);

        if (! isset($cart[$productId])) {
            return response()->json(['error' => 'Item tidak ada di keranjang'], 404);
        }

        $product = Product::find($productId);

        if ($product && $qty > $product->stock) {
            return response()->json(['error' => 'Stok tidak mencukupi'], 400);
        }

        if ($qty === 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['qty'] = $qty;
        }

        session(['pelanggan_cart' => $cart]);

        return response()->json(['success' => true, ...$this->cartState($cart)]);
    }

    public function remove(Request $request)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Data tidak valid'], 422);
        }

        $cart = session('pelanggan_cart', []);
        unset($cart[$request->integer('product_id')]);
        session(['pelanggan_cart' => $cart]);

        return response()->json(['success' => true, ...$this->cartState($cart)]);
    }

    public function clear(Request $request)
    {
        if (! auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Silakan login untuk memesan'], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login untuk memesan');
        }

        session()->forget(['pelanggan_cart', 'pelanggan_promo']);

        return response()->json(['success' => true, 'promo' => null, ...$this->cartState([])]);
    }

    public function applyPromo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Kode promo wajib diisi'], 422);
            }

            return back()->withErrors(['code' => 'Kode promo wajib diisi'])->withInput();
        }

        $promo = PromoCode::query()
            ->whereRaw('LOWER(code) = ?', [strtolower(trim($request->input('code')))])
            ->first();

        if (! $promo || ! $promo->isCurrentlyActive()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Kode promo tidak valid atau sudah tidak aktif'], 400);
            }

            return back()->withErrors(['code' => 'Kode promo tidak valid atau sudah tidak aktif'])->withInput();
        }

        $cart = session('pelanggan_cart', []);
        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);

        if ($subtotal < $promo->min_order) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Subtotal belum memenuhi minimum pesanan promo ini'], 400);
            }

            return back()->withErrors(['code' => 'Subtotal belum memenuhi minimum pesanan promo ini'])->withInput();
        }

        $discount = $promo->discountFor($subtotal);

        session(['pelanggan_promo' => [
            'promo_code_id' => $promo->id,
            'code' => $promo->code,
            'discount' => $discount,
        ]]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'code' => $promo->code,
                'discount' => $discount,
                'promo' => ['code' => $promo->code, 'discount' => $discount],
            ]);
        }

        return back()->with('success', 'Promo diterapkan! Diskon: '.number_format($discount, 0, ',', '.'));
    }

    public function removePromo(Request $request)
    {
        session()->forget('pelanggan_promo');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'promo' => null]);
        }

        return back()->with('success', 'Promo dihapus.');
    }
}
