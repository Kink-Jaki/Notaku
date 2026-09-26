<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHarianController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal', now()->format('Y-m-d'));
        $kasirId = $request->query('kasir_id');
        $fmt = fn ($v) => number_format($v, 0, ',', '.');
        $rp = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');

        $baseQuery = Transaction::where('status', 'selesai')
            ->whereDate('transactions.created_at', $tanggal);

        if ($kasirId) {
            $baseQuery->where('user_id', $kasirId);
        }

        $totalPenjualan = (int) (clone $baseQuery)->sum('total');
        $totalTransaksi = (int) (clone $baseQuery)->count();
        $totalItem = (int) (clone $baseQuery)
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereDate('transactions.created_at', $tanggal)
            ->sum('transaction_items.qty');
        $totalDiskon = (int) (clone $baseQuery)->sum('discount');

        $metodePembayaran = (clone $baseQuery)
            ->select('payment_method', DB::raw('count(*) as trx'), DB::raw('sum(total) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($m) => [
                'nama' => ucfirst(str_replace('_', ' ', $m->payment_method)),
                'trx' => $m->trx,
                'total' => $m->total,
                'icon' => match ($m->payment_method) {
                    'tunai' => 'bi-cash-coin',
                    'qris' => 'bi-qr-code',
                    'ewallet' => 'bi-wallet2',
                    'debit' => 'bi-credit-card',
                    'transfer' => 'bi-bank',
                    default => 'bi-box',
                },
                'badge' => match ($m->payment_method) {
                    'tunai' => 'neutral',
                    'qris' => 'info',
                    'ewallet' => 'success',
                    'debit' => 'neutral',
                    'transfer' => 'warning',
                    default => 'neutral',
                },
            ]);

        $totalMetode = $metodePembayaran->sum('total');

        $rincian = (clone $baseQuery)
            ->latest('transactions.created_at')
            ->take(8)
            ->get()
            ->map(fn ($trx) => [
                'jam' => $trx->created_at->format('H:i'),
                'id' => $trx->transaction_number,
                'jenis' => $trx->order_id ? 'Online' : 'Kasir',
                'metode' => ucfirst(str_replace('_', ' ', $trx->payment_method)),
                'total' => (int) $trx->total,
                'kasir' => $trx->user?->name,
            ]);

        $totalRincian = (int) $rincian->sum('total');

        $jenisBadge = ['Kasir' => 'success', 'Online' => 'info'];

        $ringkasan = [
            ['label' => 'Kasir Manual', 'value' => $rp((clone $baseQuery)->whereNull('order_id')->sum('total')), 'icon' => 'bi-cash-stack', 'modifier' => 'success'],
            ['label' => 'Online', 'value' => $rp((clone $baseQuery)->whereNotNull('order_id')->sum('total')), 'icon' => 'bi-cloud-arrow-up', 'modifier' => 'info'],
            ['label' => 'Rata-rata/Hari', 'value' => $rp($totalTransaksi > 0 ? round($totalPenjualan / $totalTransaksi) : 0), 'icon' => 'bi-graph-up-arrow', 'modifier' => ''],
        ];

        $statCards = [
            ['label' => 'Total Penjualan', 'value' => $rp($totalPenjualan), 'icon' => 'bi-cash-stack', 'modifier' => 'primary'],
            ['label' => 'Transaksi', 'value' => number_format($totalTransaksi, 0, ',', '.'), 'icon' => 'bi-receipt', 'modifier' => 'success'],
            ['label' => 'Item Terjual', 'value' => number_format($totalItem, 0, ',', '.'), 'icon' => 'bi-box-seam', 'modifier' => 'info'],
            ['label' => 'Total Diskon', 'value' => $rp($totalDiskon), 'icon' => 'bi-tag', 'modifier' => 'warning'],
        ];

        $kasirList = User::where('role', 'kasir')->orderBy('name')->get(['id', 'name']);

        return view('admin.laporan-harian', [
            'tanggal' => $tanggal,
            'kasirId' => $kasirId,
            'kasirList' => $kasirList,
            'totalPenjualan' => $totalPenjualan,
            'totalTransaksi' => $totalTransaksi,
            'totalItem' => $totalItem,
            'totalDiskon' => $totalDiskon,
            'metodePembayaran' => $metodePembayaran,
            'totalMetode' => $totalMetode,
            'rincian' => $rincian,
            'totalRincian' => $totalRincian,
            'ringkasan' => $ringkasan,
            'statCards' => $statCards,
            'fmt' => $fmt,
            'rp' => $rp,
            'jenisBadge' => $jenisBadge,
        ]);
    }
}
