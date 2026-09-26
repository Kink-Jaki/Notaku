<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardApiController extends Controller
{
    /**
     * Seluruh data dashboard admin (stat card, chart 7 hari, stok menipis,
     * transaksi terbaru, ringkasan) dihitung di sini supaya shell halaman
     * cukup merender skeleton tanpa query.
     */
    public function index(): JsonResponse
    {
        $today = now();
        $chartStart = $today->copy()->startOfDay()->subDays(6);
        $monthlyTransactions = Transaction::query()
            ->where('status', 'selesai')
            ->whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month);

        $dailySales = Transaction::query()
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as total_sales')
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$chartStart, $today->copy()->endOfDay()])
            ->groupBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $chartDays = collect(range(6, 0))->map(function (int $daysAgo) use ($today, $dailySales): array {
            $date = $today->copy()->subDays($daysAgo);
            $summary = $dailySales->get($date->toDateString());

            return [
                'label' => $date->translatedFormat('D d/m'),
                'transactions' => (int) ($summary?->transaction_count ?? 0),
                'sales' => (int) ($summary?->total_sales ?? 0),
            ];
        });

        $totalOmzet = (int) (clone $monthlyTransactions)->sum('total');
        $totalTransactions = (int) (clone $monthlyTransactions)->count();
        $pendingOrders = Order::query()->where('status', Order::STATUS_PENDING)->count();
        $totalOutOfStock = Product::query()->where('stock', 0)->count();
        $totalLowStock = Product::query()->whereBetween('stock', [1, 10])->count();
        $totalStockIssues = $totalOutOfStock + $totalLowStock;

        $stats = [
            [
                'label' => 'Total Omzet',
                'hint' => '(bulan ini)',
                'value' => 'Rp '.number_format($totalOmzet, 0, ',', '.'),
                'icon' => 'bi-cash-stack',
                'modifier' => 'primary',
                'badge' => null,
            ],
            [
                'label' => 'Total Transaksi',
                'hint' => null,
                'value' => number_format($totalTransactions, 0, ',', '.'),
                'icon' => 'bi-receipt',
                'modifier' => 'success',
                'badge' => null,
            ],
            [
                'label' => 'Pesanan Menunggu',
                'hint' => null,
                'value' => number_format($pendingOrders, 0, ',', '.'),
                'icon' => 'bi-hourglass-split',
                'modifier' => 'warning',
                'badge' => $pendingOrders > 0 ? 'warning' : null,
            ],
            [
                'label' => 'Stok Menipis / Habis',
                'hint' => null,
                'value' => number_format($totalStockIssues, 0, ',', '.'),
                'icon' => 'bi-box-seam',
                'modifier' => 'danger',
                'badge' => $totalStockIssues > 0 ? 'danger' : null,
            ],
        ];

        $stocks = Product::query()
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'stock' => $product->stock,
                'modifier' => $product->stock <= 0 ? 'danger' : 'warning',
            ])
            ->values();

        $recentTransactions = Transaction::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'number' => $transaction->transaction_number,
                'time' => $transaction->created_at->format('H:i'),
                'cashier' => $transaction->user?->name ?? '-',
                'type' => $transaction->order_id === null ? 'Kasir' : 'Online',
                'type_modifier' => $transaction->order_id === null ? 'success' : 'info',
                'total' => (int) $transaction->total,
                'status' => $transaction->status,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'chart' => [
                    'labels' => $chartDays->pluck('label')->values()->all(),
                    'transactions' => $chartDays->pluck('transactions')->values()->all(),
                    'omzet' => $chartDays->pluck('sales')->values()->all(),
                ],
                'stocks' => $stocks,
                'recentTransactions' => $recentTransactions,
                'summary' => [
                    'products' => Product::query()->count(),
                    'users' => User::query()->count(),
                    'promos' => PromoCode::query()->where('is_active', true)->count(),
                ],
            ],
        ]);
    }
}
