<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpCenterController extends Controller
{
    /**
     * Halaman Pusat Bantuan (accordion FAQ), bisa diakses tanpa login.
     */
    public function index(): View
    {
        return view('pusat-bantuan');
    }
}
