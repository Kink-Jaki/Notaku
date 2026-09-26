<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class KasirDashboardController extends Controller
{
    /**
     * Shell halaman: hanya merender skeleton.
     * Data diambil oleh JS melalui /api/kasir/dashboard.
     */
    public function index(): View
    {
        return view('kasir.dashboard');
    }
}
