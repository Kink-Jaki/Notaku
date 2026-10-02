<?php

namespace App\Services\Payments;

use App\Models\Order;
use Xendit\Exceptions\ApiException;
use Xendit\Invoice;
use Xendit\Xendit;

class XenditGateway implements PaymentGateway
{
    public function __construct()
    {
        Xendit::setApiKey(config('services.xendit.secret_key'));
    }

    public function createInvoice(Order $order): array
    {
        $params = [
            'external_id' => $order->order_number,
            'amount' => (int) $order->total,
            'payer_email' => $order->user?->email ?? 'customer@example.com',
            'description' => 'Pembayaran pesanan '.$order->order_number,
            'invoice_duration' => config('services.xendit.invoice_duration', 3600),
            'currency' => 'IDR',
            'success_redirect_url' => route('pelanggan.pembayaran.status', $order),
            'failure_redirect_url' => route('pelanggan.pembayaran.status', $order),
            'payment_methods' => ['QRIS', 'VIRTUAL_ACCOUNT', 'EWALLET'],
        ];

        try {
            $invoice = Invoice::create($params);

            return [
                'invoice_url' => $invoice['invoice_url'] ?? ($invoice->invoice_url ?? ''),
                'invoice_id' => $invoice['id'] ?? ($invoice->id ?? ''),
            ];
        } catch (ApiException $e) {
            throw new \RuntimeException('Gagal membuat invoice Xendit: '.$e->getMessage());
        }
    }

    public function expireInvoice(string $xenditInvoiceId): bool
    {
        try {
            Invoice::expireInvoice($xenditInvoiceId);

            return true;
        } catch (ApiException $e) {
            return false;
        }
    }
}
