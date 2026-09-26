<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with('category')
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $produk = $query->orderBy('name')->paginate(12)->withQueryString();
        $kategori = Category::orderBy('name')->get();
        $kategoriCounts = ['semua' => $produk->total()];
        foreach ($kategori as $k) {
            $kategoriCounts[$k->slug] = Product::where('category_id', $k->id)->where('is_active', true)->count();
        }

        return view('pelanggan.katalog', compact('produk', 'kategori', 'kategoriCounts'));
    }

    public function show(Product $produk)
    {
        $kategori = Category::orderBy('name')->get();

        return view('pelanggan.produk', compact('produk', 'kategori'));
    }

    public function addToCart(Request $request)
    {
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

        return response()->json(['success' => true, 'cart_count' => collect($cart)->sum('qty')]);
    }
}
