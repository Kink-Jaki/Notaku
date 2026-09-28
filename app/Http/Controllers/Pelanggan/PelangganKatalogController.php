<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

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

    public function indexMobile()
    {
        $produk = Product::with('category')
            ->where('is_active', true)
            ->when(request('category_id'), fn ($q) => $q->where('category_id', request('category_id')))
            ->when(request('search'), fn ($q) => $q->where('name', 'like', '%' . request('search') . '%'))
            ->latest()
            ->paginate(20);

        $kategori = Category::orderBy('name')->get();
        $kategoriCounts = $kategori->pluck('id')->mapWithKeys(fn ($id) => [$id => Product::where('category_id', $id)->where('is_active', true)->count()])->toArray();

        return view('pelanggan.katalog-mobile', compact('produk', 'kategori', 'kategoriCounts'));
    }

    public function show(Product $produk)
    {
        $kategori = Category::orderBy('name')->get();

        return view('pelanggan.produk', compact('produk', 'kategori'));
    }
}
