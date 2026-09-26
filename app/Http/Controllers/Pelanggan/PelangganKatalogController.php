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

    public function show(Product $produk)
    {
        $kategori = Category::orderBy('name')->get();

        return view('pelanggan.produk', compact('produk', 'kategori'));
    }
}
