<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KasirAntrianApiController extends Controller
{
    /**
     * Data antrian pesanan (pesanan menunggu + riwayat penanganan) untuk halaman /kasir/antrian.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'semua');

        $pending = Order::query()
            ->with(['items', 'promoCode'])
            ->withCount('items')
            ->where('status', Order::STATUS_PENDING)
            ->latest()
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'payment_method' => $order->payment_method,
                'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
                'waktu' => $order->created_at->format('H:i'),
                'item' => (int) $order->items_count,
                'total' => (int) $order->total,
                'subtotal' => (int) $order->subtotal,
                'discount' => (int) $order->discount,
                'note' => $order->note,
                'promo_code' => $order->promoCode?->code,
                'items' => $order->items
                    ->map(fn ($item) => [
                        'product_name' => $item->product_name,
                        'qty' => (int) $item->qty,
                        'price' => (int) $item->price,
                    ])
                    ->values()
                    ->all(),
                'approve_url' => route('kasir.antrian.approve', $order),
                'reject_url' => route('kasir.antrian.reject', $order),
            ])
            ->values()
            ->all();

        $handledQuery = Order::query()
            ->withCount('items')
            ->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_REJECTED]);

        if ($status === 'diproses') {
            $handledQuery->where('status', Order::STATUS_PROCESSING);
        } elseif ($status === 'ditolak') {
            $handledQuery->where('status', Order::STATUS_REJECTED);
        }

        $handledOrders = $handledQuery->latest('updated_at')->get();

        $transactionMap = Transaction::query()
            ->whereIn('order_id', $handledOrders->pluck('id'))
            ->whereNotNull('order_id')
            ->get()
            ->pluck('transaction_number', 'order_id');

        $handled = $handledOrders->map(fn (Order $order) => [
            'no' => $order->order_number,
            'trx' => $transactionMap->get($order->id),
            'trx_url' => $transactionMap->has($order->id)
                ? route('kasir.transaksi', $transactionMap->get($order->id))
                : null,
            'pelanggan' => $order->customer_name,
            'waktu' => $order->updated_at->format('H:i'),
            'item' => (int) $order->items_count,
            'total' => (int) $order->total,
            'keputusan' => $order->status === Order::STATUS_PROCESSING ? 'disetujui' : 'ditolak',
            'alasan' => $order->rejected_reason,
        ])->values()->all();

        $counts = [
            'menunggu' => Order::query()->where('status', Order::STATUS_PENDING)->count(),
            'diproses' => Order::query()->where('status', Order::STATUS_PROCESSING)->count(),
            'ditolak' => Order::query()->where('status', Order::STATUS_REJECTED)->count(),
        ];
        $counts['semua'] = $counts['menunggu'] + $counts['diproses'] + $counts['ditolak'];

        $tabs = [
            ['key' => 'semua', 'label' => 'Semua', 'count' => $counts['semua'], 'url' => route('kasir.antrian', ['status' => 'semua'])],
            ['key' => 'menunggu', 'label' => 'Menunggu', 'count' => $counts['menunggu'], 'url' => route('kasir.antrian', ['status' => 'menunggu'])],
            ['key' => 'diproses', 'label' => 'Diproses', 'count' => $counts['diproses'], 'url' => route('kasir.antrian', ['status' => 'diproses'])],
            ['key' => 'ditolak', 'label' => 'Ditolak', 'count' => $counts['ditolak'], 'url' => route('kasir.antrian', ['status' => 'ditolak'])],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'counts' => $counts,
                'tabs' => $tabs,
                'pending' => $pending,
                'handled' => $handled,
            ],
        ]);
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
            'tunai' => 'Tunai',
            default => ucfirst((string) $method),
        };
    }
}
