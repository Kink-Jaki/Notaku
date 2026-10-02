<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Buat invoice pembayaran.
     *
     * @return array{invoice_url: string, invoice_id: string}
     */
    public function createInvoice(Order $order): array;

    /**
     * Expire invoice manual (fallback/jaring pengaman).
     */
    public function expireInvoice(string $xenditInvoiceId): bool;
}
