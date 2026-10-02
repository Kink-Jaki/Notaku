<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KasirRiwayatApiController extends Controller
{
    /**
     * Data riwayat transaksi (filter tanggal) untuk halaman /kasir/riwayat.
     * Filter jenis, status, dan pencarian diterapkan di sisi klien.
     */
    public function index(Request $request): JsonResponse
    {
        $dari = $request->query('dari', now()->subDays(6)->format('Y-m-d'));
        $sampai = $request->query('sampai', now()->format('Y-m-d'));

        $totals = $this->filteredQuery($dari, $sampai)
            ->selectRaw('count(*) as total_transaksi, coalesce(sum(total), 0) as total_penjualan')
            ->toBase()
            ->first();

        $totalTransaksi = (int) ($totals->total_transaksi ?? 0);
        $totalPenjualan = (int) ($totals->total_penjualan ?? 0);
        $rataRata = $totalTransaksi > 0 ? (int) round($totalPenjualan / $totalTransaksi) : 0;

        $riwayat = $this->filteredQuery($dari, $sampai)
            ->with(['items', 'user', 'order'])
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString()
            ->setPath(route('kasir.riwayat'));

        $rows = $riwayat->getCollection()
            ->map(fn (Transaction $trx) => [
                'transaction_number' => $trx->transaction_number,
                'detail_url' => route('kasir.transaksi', $trx->transaction_number),
                'tanggal' => $trx->created_at->format('d/m/Y'),
                'jam' => $trx->created_at->format('H:i'),
                'kasir' => $trx->user?->name,
                'jenis' => $trx->order_id ? 'Online' : 'Kasir',
                'jenis_badge' => $trx->order_id ? 'info' : 'success',
                'metode' => ucfirst(str_replace('_', ' ', $trx->payment_method)),
                'item' => (int) $trx->items->sum('qty'),
                'total' => (int) $trx->total,
                'status' => $trx->status ?? 'selesai',
                'status_badge' => $trx->status === 'selesai' ? 'success' : 'danger',
                'order_number' => $trx->order?->order_number,
            ])
            ->values()
            ->all();

        $statCards = [
            [
                'label' => 'Total Transaksi',
                'value' => number_format($totalTransaksi, 0, ',', '.'),
                'icon' => 'bi-receipt',
                'modifier' => 'primary',
            ],
            [
                'label' => 'Total Penjualan',
                'value' => 'Rp '.number_format($totalPenjualan, 0, ',', '.'),
                'icon' => 'bi-cash-stack',
                'modifier' => 'success',
            ],
            [
                'label' => 'Rata-rata Transaksi',
                'value' => 'Rp '.number_format($rataRata, 0, ',', '.'),
                'icon' => 'bi-graph-up',
                'modifier' => 'info',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'badges' => [
                    'total_transaksi' => number_format($totalTransaksi, 0, ',', '.'),
                    'total_penjualan' => 'Rp '.number_format($totalPenjualan, 0, ',', '.'),
                ],
                'stat_cards' => $statCards,
                'rows' => $rows,
                'summary' => [
                    'first' => $riwayat->firstItem() ?? 0,
                    'last' => $riwayat->lastItem() ?? 0,
                    'total' => $riwayat->total(),
                ],
                'pagination' => (string) $riwayat->links(),
            ],
        ]);
    }

    private function filteredQuery(string $dari, string $sampai): Builder
    {
        $query = Transaction::query()->where('status', 'selesai');

        $query->whereBetween('created_at', [$dari, $sampai.' 23:59:59']);

        return $query;
    }
}
