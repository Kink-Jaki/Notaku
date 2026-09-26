<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PelangganRiwayatController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['items', 'promoCode'])
            ->where('user_id', auth()->id())
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('pelanggan.riwayat', compact('orders'));
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

        $order->update(['status' => 'ditolak']);

        return redirect()->route('pelanggan.pesanan-saya')->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
