<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;

class PelangganRiwayatController extends Controller
{
    public function index(Request $request)
    {
        $statusMap = [
            'belum-bayar' => Order::STATUS_UNPAID,
            'kadaluarsa' => Order::STATUS_EXPIRED,
            'menunggu' => Order::STATUS_PENDING,
            'diproses' => Order::STATUS_PROCESSING,
            'selesai' => Order::STATUS_COMPLETED,
            'ditolak' => Order::STATUS_REJECTED,
        ];

        $orders = Order::query()->with(['items', 'promoCode'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $hitungPesanan = fn () => Order::query()->where('user_id', auth()->id());
        $counts = ['semua' => $hitungPesanan()->count()];

        foreach ($statusMap as $slug => $dbStatus) {
            $counts[$slug] = $hitungPesanan()->where('status', $dbStatus)->count();
        }

        return $this->viewOrFragment($request, 'pelanggan.riwayat', 'pelanggan.riwayat-results', [
            'orders' => $orders,
            'statusMap' => $statusMap,
            'counts' => $counts,
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'promoCode']);

        return view('pelanggan.riwayat', ['order' => $order, 'orders' => collect([$order])]);
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return back()->with('error', 'Tidak diizinkan.');
        }

        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_REJECTED])) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        $order->update(['status' => Order::STATUS_REJECTED]);

        $order->user?->notify(new OrderStatusUpdated($order->refresh()));

        return redirect()->route('pelanggan.pesanan-saya')->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function bayar(Order $order, PaymentGateway $gateway)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== Order::STATUS_UNPAID) {
            return redirect()->route('pelanggan.pesanan-saya')->with('error', 'Pesanan ini tidak dapat dibayar.');
        }

        if (! $order->xendit_invoice_id) {
            return redirect()->route('pelanggan.pesanan-saya')->with('error', 'Data pembayaran tidak lengkap.');
        }

        // Regenerate invoice if needed (webhook might not have fired yet)
        // For now, redirect to the payment status page which will handle polling
        return redirect()->route('pelanggan.pembayaran.status', $order);
    }

    public function pembayaranStatus(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($order->status, [Order::STATUS_UNPAID, Order::STATUS_EXPIRED])) {
            return redirect()->route('pelanggan.pesanan-saya');
        }

        if (request()->wantsJson()) {
            return response()->json(['status' => $order->status]);
        }

        return view('pelanggan.pembayaran', compact('order'));
    }

    public function pesanLagi(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($order->status, [Order::STATUS_EXPIRED, Order::STATUS_REJECTED])) {
            return redirect()->route('pelanggan.pesanan-saya')->with('error', 'Pesanan ini tidak dapat dipesan ulang.');
        }

        // Re-populate cart from order items
        $cart = [];
        foreach ($order->items as $item) {
            $cart[$item->product_id] = [
                'id' => $item->product_id,
                'name' => $item->product_name,
                'price' => $item->price,
                'stock' => 999, // akan divalidasi saat add to cart
                'qty' => $item->qty,
            ];
        }

        session(['pelanggan_cart' => $cart]);

        return redirect()->route('pelanggan.checkout')->with('success', 'Item dipindahkan ke keranjang. Silakan lanjutkan checkout.');
    }
}
