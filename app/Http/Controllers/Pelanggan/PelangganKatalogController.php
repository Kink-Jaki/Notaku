<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class PelangganKatalogController extends Controller
{
    /**
     * Halaman marketplace dirender tanpa query produk/kategori supaya
     * navigasi terasa instan; grid produk di-load lewat ProductApiController.
     */
    public function index()
    {
        return view('pelanggan.marketplace');
    }

    public function indexMobile(Request $request)
    {
        $produk = Product::with('category')
            ->where('is_active', true)
            ->orderByOutOfStockLast()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $kategori = Category::orderBy('name')->get();

        return $this->viewOrFragment($request, 'pelanggan.katalog-mobile', 'pelanggan.katalog-mobile-results', [
            'produk' => $produk,
            'kategori' => $kategori,
        ]);
    }

    public function show(Product $produk)
    {
        $kategori = Category::orderBy('name')->get();

        return view('pelanggan.produk', compact('produk', 'kategori'));
    }
}
