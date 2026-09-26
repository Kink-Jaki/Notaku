<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * Data katalog untuk marketplace: produk terpaginasi + kategori
     * beserta jumlah produknya, dipakai grid yang di-load via fetch.
     */
    public function index(Request $request): JsonResponse
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

        $jumlahPerKategori = Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $kategori->pluck('id'))
            ->groupBy('category_id')
            ->selectRaw('category_id, count(*) as jumlah')
            ->pluck('jumlah', 'category_id');

        $kategoriCounts = ['semua' => $produk->total()];
        foreach ($kategori as $k) {
            $kategoriCounts[$k->slug] = (int) ($jumlahPerKategori[$k->id] ?? 0);
        }

        return response()->json([
            'produk' => $produk,
            'kategori' => $kategori,
            'kategori_counts' => $kategoriCounts,
            'is_logged_in' => auth()->check(),
            'csrf' => csrf_token(),
            'urls' => [
                'produk' => url('/produk'),
                'storage' => asset('storage'),
                'cart_add' => route('pelanggan.cart.add'),
                'login' => route('login'),
                'marketplace' => route('marketplace'),
            ],
        ]);
    }
}
