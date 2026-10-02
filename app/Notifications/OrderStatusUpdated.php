<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [DatabaseChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $orderNumber = (string) $this->order->order_number;
        $rejectedReason = trim((string) $this->order->rejected_reason);

        [$title, $message] = match ($this->order->status) {
            Order::STATUS_PENDING => [
                'Status Pesanan Diperbarui',
                "Pesanan {$orderNumber} masih menunggu persetujuan kasir.",
            ],
            Order::STATUS_PROCESSING => [
                'Pesanan Disetujui',
                "Pesanan {$orderNumber} disetujui kasir dan sedang diproses.",
            ],
            Order::STATUS_COMPLETED => [
                'Status Pesanan Diperbarui',
                "Pesanan {$orderNumber} sudah selesai diproses.",
            ],
            Order::STATUS_REJECTED => [
                'Pesanan Ditolak',
                $rejectedReason === ''
                    ? "Pesanan {$orderNumber} ditolak."
                    : "Pesanan {$orderNumber} ditolak: {$rejectedReason}.",
            ],
            default => [
                'Status Pesanan Diperbarui',
                "Status pesanan {$orderNumber} diperbarui menjadi {$this->order->status}.",
            ],
        };

        return [
            'order_number' => $orderNumber,
            'status' => (string) $this->order->status,
            'title' => $title,
            'message' => $message,
            'url' => $this->destinationUrl(),
            'rejected_reason' => $this->order->rejected_reason,
        ];
    }

    private function destinationUrl(): string
    {
        $slug = match ($this->order->status) {
            Order::STATUS_PENDING => 'menunggu',
            Order::STATUS_PROCESSING => 'diproses',
            Order::STATUS_COMPLETED => 'selesai',
            Order::STATUS_REJECTED => 'ditolak',
            default => null,
        };

        return route('pelanggan.pesanan-saya', $slug === null ? [] : ['status' => $slug]);
    }
}
