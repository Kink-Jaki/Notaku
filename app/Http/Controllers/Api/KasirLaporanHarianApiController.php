<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirLaporanHarianApiController extends Controller
{
    /**
     * Data laporan harian: ringkasan penjualan, metode bayar, rincian
     * transaksi, dan ringkasan kesimpulan sesuai filter tanggal/kasir.
     * Dipakai shell skeleton di /kasir/laporan-harian.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'kasir_id' => ['nullable', 'integer'],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $kasirId = $validated['kasir_id'] ?? null;

        $fmt = fn (int $value): string => number_format($value, 0, ',', '.');
        $rp = fn (int $value): string => 'Rp '.number_format($value, 0, ',', '.');

        $baseQuery = Transaction::where('status', 'selesai')
            ->whereDate('transactions.created_at', $tanggal);

        if ($kasirId) {
            $baseQuery->where('user_id', $kasirId);
        }

        $totalPenjualan = (int) (clone $baseQuery)->sum('total');
        $totalTransaksi = (int) (clone $baseQuery)->count();
        $totalItem = (int) (clone $baseQuery)
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->sum('transaction_items.qty');
        $totalDiskon = (int) (clone $baseQuery)->sum('discount');

        $metodePembayaran = (clone $baseQuery)
            ->select('payment_method', DB::raw('count(*) as trx'), DB::raw('sum(total) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($m) => [
                'nama' => ucfirst(str_replace('_', ' ', $m->payment_method)),
                'trx' => (int) $m->trx,
                'total' => (int) $m->total,
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

        $totalMetode = (int) $metodePembayaran->sum('total');

        $metodePembayaran = $metodePembayaran->map(fn ($m) => $m + [
            'total_formatted' => $rp($m['total']),
            'persen' => $totalMetode > 0 ? (int) round(($m['total'] / $totalMetode) * 100) : 0,
        ])->values();

        $rincian = (clone $baseQuery)
            ->with('user:id,name')
            ->latest('transactions.created_at')
            ->take(8)
            ->get()
            ->map(fn ($trx) => [
                'jam' => $trx->created_at->format('H:i'),
                'id' => $trx->transaction_number,
                'jenis' => $trx->order_id ? 'Online' : 'Kasir',
                'metode' => ucfirst(str_replace('_', ' ', $trx->payment_method)),
                'total' => (int) $trx->total,
                'total_formatted' => $rp((int) $trx->total),
                'kasir' => $trx->user?->name,
            ])->values();

        $totalRincian = (int) $rincian->sum('total');

        $statCards = [
            ['label' => 'Total Penjualan', 'value' => $rp($totalPenjualan), 'icon' => 'bi-cash-stack', 'modifier' => 'primary'],
            ['label' => 'Transaksi', 'value' => $fmt($totalTransaksi), 'icon' => 'bi-receipt', 'modifier' => 'success'],
            ['label' => 'Item Terjual', 'value' => $fmt($totalItem), 'icon' => 'bi-box-seam', 'modifier' => 'info'],
            ['label' => 'Total Diskon', 'value' => $rp($totalDiskon), 'icon' => 'bi-tag', 'modifier' => 'warning'],
        ];

        $ringkasan = [
            ['label' => 'Kasir Manual', 'value' => $rp((int) (clone $baseQuery)->whereNull('order_id')->sum('total')), 'icon' => 'bi-cash-stack'],
            ['label' => 'Online', 'value' => $rp((int) (clone $baseQuery)->whereNotNull('order_id')->sum('total')), 'icon' => 'bi-cloud-arrow-up'],
            ['label' => 'Rata-rata/Hari', 'value' => $rp($totalTransaksi > 0 ? (int) round($totalPenjualan / $totalTransaksi) : 0), 'icon' => 'bi-graph-up-arrow'],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $tanggal,
                'tanggal_label' => Carbon::parse($tanggal)->translatedFormat('l, d F Y'),
                'penjualan_harian' => $fmt($totalPenjualan),
                'stat_cards' => $statCards,
                'metode' => [
                    'count' => $metodePembayaran->count(),
                    'total' => $rp($totalMetode),
                    'rows' => $metodePembayaran,
                ],
                'rincian' => [
                    'count' => $rincian->count(),
                    'total' => $rp($totalRincian),
                    'rows' => $rincian,
                ],
                'ringkasan' => [
                    'tanggal' => $tanggal,
                    'items' => $ringkasan,
                ],
            ],
        ]);
    }
}
