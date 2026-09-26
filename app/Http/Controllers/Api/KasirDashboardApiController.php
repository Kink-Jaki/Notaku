<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;

class KasirDashboardApiController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now();

        $totalPenjualan = (int) Transaction::query()
            ->whereDate('created_at', $today->toDateString())
            ->where('status', 'selesai')
            ->sum('total');

        $totalTransaksi = Transaction::query()
            ->whereDate('created_at', $today->toDateString())
            ->where('status', 'selesai')
            ->count();

        $totalItem = (int) TransactionItem::query()
            ->whereHas('transaction', fn ($query) => $query
                ->where('status', 'selesai')->whereDate('created_at', $today->toDateString()))
            ->sum('qty');

        $pendingOrdersCount = Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->count();

        $stats = [
            [
                'label' => 'Total Penjualan Hari Ini',
                'value' => 'Rp '.number_format($totalPenjualan, 0, ',', '.'),
                'icon' => 'bi-cash-stack',
                'modifier' => 'primary',
            ],
            [
                'label' => 'Transaksi Hari Ini',
                'value' => number_format($totalTransaksi, 0, ',', '.'),
                'icon' => 'bi-receipt',
                'modifier' => 'success',
            ],
            [
                'label' => 'Item Terjual',
                'value' => number_format($totalItem, 0, ',', '.'),
                'icon' => 'bi-box-seam',
                'modifier' => 'info',
            ],
            [
                'label' => 'Pesanan Menunggu Konfirmasi',
                'value' => number_format($pendingOrdersCount, 0, ',', '.'),
                'icon' => 'bi-hourglass-split',
                'modifier' => 'warning',
                'delta' => 'perlu dicek',
                'delta_modifier' => 'text-warning',
                'link' => route('kasir.antrian'),
                'link_label' => 'Buka Antrian →',
            ],
        ];

        $transactions = Transaction::query()
            ->with(['user', 'order'])
            ->whereDate('created_at', $today->toDateString())
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'id' => $transaction->transaction_number,
                'url' => route('kasir.transaksi', $transaction->transaction_number),
                'jam' => $transaction->created_at->format('H:i'),
                'kasir' => $transaction->user?->name,
                'jenis' => $transaction->order_id ? 'Online' : 'Kasir',
                'jenis_badge' => $transaction->order_id ? 'info' : 'success',
                'total' => (int) $transaction->total,
                'status' => $transaction->status ?? 'selesai',
                'no_pesanan' => $transaction->order?->order_number,
            ])
            ->values();

        $pendingOrders = Order::query()
            ->withCount('items')
            ->where('status', Order::STATUS_PENDING)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $order) => [
                'no' => $order->order_number,
                'customer' => $order->customer_name,
                'items' => (int) $order->items_count,
                'total' => (int) $order->total,
                'time' => $order->created_at->format('H:i'),
                'status_badge' => 'warning',
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $today->translatedFormat('l, d F Y'),
                'stats' => $stats,
                'transactions' => $transactions,
                'pendingOrders' => $pendingOrders,
            ],
        ]);
    }
}
