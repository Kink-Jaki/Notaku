<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KasirRiwayatController extends Controller
{
    /**
     * Shell halaman: hanya merender skeleton.
     * Data diambil oleh JS melalui /api/kasir/riwayat.
     */
    public function index(Request $request): View
    {
        $dari = $request->query('dari', now()->subDays(6)->format('Y-m-d'));
        $sampai = $request->query('sampai', now()->format('Y-m-d'));
        $jenis = $request->query('jenis', 'Semua');
        $status = $request->query('status', 'Semua');
        $q = $request->query('q', '');

        return view('kasir.riwayat', compact('dari', 'sampai', 'jenis', 'status', 'q'));
    }
}
