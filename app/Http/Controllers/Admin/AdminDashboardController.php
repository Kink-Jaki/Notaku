<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Shell halaman: hanya merender skeleton.
     * Data diambil oleh JS melalui /api/admin/dashboard.
     */
    public function index(): View
    {
        return view('admin.dashboard');
    }
}
