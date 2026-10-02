<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\Payments\PaymentGateway;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('orders:expire', ['expire:orders'])]
#[Description('Mencari order status unpaid yang sudah lewat expires_at, update ke expired + panggil gateway->expireInvoice()')]
class OrdersExpire extends Command
{
    public function __construct(
        private PaymentGateway $gateway
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $expiredOrders = Order::where('status', Order::STATUS_UNPAID)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            if ($order->xendit_invoice_id) {
                $this->gateway->expireInvoice($order->xendit_invoice_id);
            }

            $order->forceFill([
                'status' => Order::STATUS_EXPIRED,
            ])->save();

            $order->user?->notify(new OrderStatusUpdated($order->refresh()));

            $count++;
            Log::info('Order expired', ['order_number' => $order->order_number]);
        }

        $this->info("Expired {$count} order(s).");

        return Command::SUCCESS;
    }
}
