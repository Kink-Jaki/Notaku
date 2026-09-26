<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\View\View;

class KasirStrukController extends Controller
{
    public function show(string $trx): View
    {
        $transaction = Transaction::where('transaction_number', $trx)
            ->orWhere('id', (int) $trx)
            ->with(['items', 'order', 'promoCode', 'user'])
            ->first();

        if (! $transaction) {
            return view('kasir.struk', ['notFound' => true, 'id' => $trx]);
        }

        $isOnline = $transaction->order_id !== null;
        $rp = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');

        $items = $transaction->items->map(fn ($item) => [
            'name' => $item->product_name,
            'qty' => $item->qty,
            'price' => (int) $item->price,
        ]);

        $items = collect($items)
            ->map(fn ($item) => $item + ['subtotal' => $item['qty'] * $item['price']])
            ->values()
            ->all();

        $subtotal = (int) $transaction->subtotal;
        $discount = (int) $transaction->discount;
        $promo = $transaction->promoCode?->code;
        $total = (int) $transaction->total;
        $metodeRaw = $transaction->payment_method;
        $kasir = $transaction->user?->name ?? '-';

        if ($isOnline) {
            $metode = 'Online → QRIS';
            $metodeBadge = 'info';
        } else {
            $metode = 'Tunai';
            $metodeBadge = 'neutral';
        }

        $stats = [
            ['label' => 'Subtotal', 'value' => $rp($subtotal), 'icon' => 'bi-receipt', 'modifier' => 'primary'],
            ['label' => 'Diskon', 'value' => $discount > 0 ? '−'.$rp($discount) : $rp($discount), 'icon' => 'bi-tag', 'modifier' => 'success'],
            ['label' => 'Total Bayar', 'value' => $rp($total), 'icon' => 'bi-cash-stack', 'modifier' => ''],
            ['label' => 'Metode', 'value' => $metode, 'icon' => 'bi-credit-card', 'modifier' => ''],
        ];

        $buyer = [
            ['label' => 'Pelanggan', 'value' => $transaction->order?->customer_name],
            ['label' => 'Telepon', 'value' => $transaction->order?->note],
            ['label' => 'No. Pesanan', 'value' => $transaction->order?->order_number ?? '-', 'mono' => true],
            ['label' => 'Catatan', 'value' => $transaction->order?->note],
        ];

        $viewData = compact(
            'transaction', 'isOnline', 'items', 'subtotal', 'discount', 'total',
            'kasir', 'metode', 'metodeBadge', 'stats', 'buyer', 'promo'
        );

        $viewData['id'] = $trx;

        return view('kasir.struk', $viewData);
    }
}
