<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanBulananController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);
        $kasirId = $request->query('kasir_id');
        $fmt = fn ($value) => number_format($value, 0, ',', '.');
        $rp = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
        $shortBulan = Carbon::createFromFormat('!m', $bulan)->translatedFormat('M');
        $labelBulan = Carbon::createFromFormat('!m', $bulan)->format('F Y');

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $grandTrx = $grandItem = $grandManual = $grandOnline = $grandDiskon = $grandTotal = 0;
        $rekap = [];

        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
            $createdAt = Carbon::create($tahun, $bulan, $hari);
            if (! $createdAt->isValid()) {
                continue;
            }

            $baseQuery = Transaction::where('status', 'selesai')
                ->whereDate('transactions.created_at', $createdAt->format('Y-m-d'));

            if ($kasirId) {
                $baseQuery->where('user_id', $kasirId);
            }

            $trx = (clone $baseQuery)->count();
            $item = (int) (clone $baseQuery)
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->sum('transaction_items.qty');

            $manual = (clone $baseQuery)->whereNull('order_id')->sum('total');
            $online = (clone $baseQuery)->whereNotNull('order_id')->sum('total');
            $diskon = (clone $baseQuery)->sum('discount');
            $total = $manual + $online;

            $grandTrx += $trx;
            $grandItem += $item;
            $grandManual += $manual;
            $grandOnline += $online;
            $grandDiskon += $diskon;
            $grandTotal += $total;

            $rekap[] = [
                'hari' => $hari,
                'trx' => $trx,
                'item' => $item,
                'manual' => $manual,
                'online' => $online,
                'diskon' => $diskon,
                'total' => $total,
            ];
        }
        $rataHari = intdiv($grandTotal, $jumlahHari);

        $topKategoriQuery = Product::selectRaw('categories.name, sum(transaction_items.price * transaction_items.qty) as revenue')
            ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'selesai')
            ->whereMonth('transactions.created_at', $bulan)
            ->whereYear('transactions.created_at', $tahun);

        if ($kasirId) {
            $topKategoriQuery->where('transactions.user_id', $kasirId);
        }

        $topKategori = $topKategoriQuery
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('revenue', 'desc')
            ->take(4)
            ->get()
            ->map(fn ($k) => [
                'nama' => $k->name,
                'total' => (int) $k->revenue,
                'icon' => match ($k->name) {
                    'Makanan' => 'bi-basket2',
                    'Minuman' => 'bi-cup-straw',
                    'Snack' => 'bi-cookie',
                    'Sembako' => 'bi-box-seam',
                    default => 'bi-box',
                },
                'badge' => match ($k->name) {
                    'Makanan' => 'neutral',
                    'Minuman' => 'info',
                    'Snack' => 'success',
                    'Sembako' => 'warning',
                    default => 'neutral',
                },
            ]);

        $statCards = [
            ['label' => 'Total Penjualan Bulan Ini', 'value' => number_format($grandTotal, 0, ',', '.'), 'icon' => 'bi-cash-stack', 'modifier' => 'primary'],
            ['label' => 'Transaksi', 'value' => number_format($grandTrx, 0, ',', '.'), 'icon' => 'bi-receipt', 'modifier' => 'success'],
            ['label' => 'Item Terjual', 'value' => number_format($grandItem, 0, ',', '.'), 'icon' => 'bi-box-seam', 'modifier' => 'info'],
            ['label' => 'Rata-rata/Hari', 'value' => number_format($rataHari, 0, ',', '.'), 'icon' => 'bi-graph-up-arrow', 'modifier' => ''],
        ];

        $kasirList = User::where('role', 'kasir')->orderBy('name')->get(['id', 'name']);

        $daftarBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $daftarTahun = array_merge(
            array_reverse(range(2023, $tahun + 1)),
            [2024, 2025, 2026]
        );

        $tglHariIni = now()->day;

        return $this->viewOrFragment($request, 'admin.laporan-bulanan', 'admin.laporan-bulanan-results', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kasirId' => $kasirId,
            'kasirList' => $kasirList,
            'shortBulan' => $shortBulan,
            'labelBulan' => $labelBulan,
            'grandTrx' => $grandTrx,
            'grandItem' => $grandItem,
            'grandManual' => $grandManual,
            'grandOnline' => $grandOnline,
            'grandDiskon' => $grandDiskon,
            'grandTotal' => $grandTotal,
            'rataHari' => $rataHari,
            'rekap' => $rekap,
            'topKategori' => $topKategori,
            'statCards' => $statCards,
            'daftarBulan' => $daftarBulan,
            'daftarTahun' => $daftarTahun,
            'tglHariIni' => $tglHariIni,
            'fmt' => $fmt,
            'rp' => $rp,
        ]);
    }
}
