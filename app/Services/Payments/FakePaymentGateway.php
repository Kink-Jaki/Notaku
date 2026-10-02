<?php

namespace App\Services\Payments;

use App\Models\Order;

class FakePaymentGateway implements PaymentGateway
{
    public array $createdInvoices = [];

    public array $expiredInvoices = [];

    public function createInvoice(Order $order): array
    {
        $invoiceId = 'fake-invoice-'.$order->id;
        $invoiceUrl = route('pelanggan.pembayaran.status', $order).'?fake_pay=1';

        $this->createdInvoices[$order->order_number] = [
            'invoice_url' => $invoiceUrl,
            'invoice_id' => $invoiceId,
        ];

        return $this->createdInvoices[$order->order_number];
    }

    public function expireInvoice(string $xenditInvoiceId): bool
    {
        $this->expiredInvoices[] = $xenditInvoiceId;

        return true;
    }

    public function reset(): void
    {
        $this->createdInvoices = [];
        $this->expiredInvoices = [];
    }
}
