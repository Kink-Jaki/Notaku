<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->translatedFormat('l, d F Y');

        $stats = [
            [
                'label' => 'Total Penjualan Hari Ini',
                'value' => 'Rp '.number_format(Transaction::where('status', 'selesai')
                    ->whereDate('created_at', today())
                    ->sum('total'), 0, ',', '.'),
                'icon' => 'bi-cash-stack',
                'modifier' => 'primary',
                'delta' => '+12% vs kemarin',
                'delta_modifier' => 'stat-card__delta--up',
            ],
            [
                'label' => 'Transaksi Hari Ini',
                'value' => Transaction::where('status', 'selesai')
                    ->whereDate('created_at', today())
                    ->count(),
                'icon' => 'bi-receipt',
                'modifier' => 'success',
            ],
            [
                'label' => 'Item Terjual',
                'value' => TransactionItem::whereHas('transaction', function ($q) {
                    $q->where('status', 'selesai')->whereDate('created_at', today());
                })
                    ->sum('qty'),
                'icon' => 'bi-box-seam',
                'modifier' => 'info',
            ],
            [
                'label' => 'Pesanan Menunggu Konfirmasi',
                'value' => Order::where('status', Order::STATUS_PENDING)->count(),
                'icon' => 'bi-hourglass-split',
                'modifier' => 'warning',
                'delta' => 'perlu dicek',
                'delta_modifier' => 'text-warning',
                'link' => route('kasir.antrian'),
            ],
        ];

        $statusBadge = [
            'menunggu' => 'warning',
            'diproses' => 'info',
            'selesai' => 'success',
            'ditolak' => 'danger',
        ];

        $transactions = Transaction::where('status', 'selesai')
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->take(8)
            ->get();

        $pendingOrders = Order::where('status', Order::STATUS_PENDING)
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('kasir.dashboard', compact(
            'today', 'stats', 'statusBadge', 'transactions', 'pendingOrders'
        ));
    }
}
