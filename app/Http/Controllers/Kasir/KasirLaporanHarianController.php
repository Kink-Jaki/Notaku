<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KasirLaporanHarianController extends Controller
{
    /**
     * Shell halaman: hanya merender skeleton + filter tanggal/kasir.
     * Data laporan diambil oleh JS melalui /api/kasir/laporan-harian.
     */
    public function index(Request $request): View
    {
        return view('kasir.laporan-harian', [
            'tanggal' => $this->resolveTanggal($request),
            'kasirId' => $request->query('kasir_id'),
            'kasirList' => User::where('role', 'kasir')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Ambil tanggal filter yang valid (Y-m-d), fallback ke hari ini.
     */
    private function resolveTanggal(Request $request): string
    {
        $tanggal = (string) $request->query('tanggal', now()->format('Y-m-d'));

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $parts) !== 1
            || ! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return now()->format('Y-m-d');
        }

        return $tanggal;
    }
}
