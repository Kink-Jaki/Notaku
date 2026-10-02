<?php

namespace App\Http\Controllers\Webhook;

use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __construct(
        private PaymentGateway $gateway
    ) {}

    public function __invoke(Request $request): Response
    {
        $callbackToken = $request->header('x-callback-token');
        $expectedToken = config('services.xendit.callback_token');

        if (! $callbackToken || ! Hash::check($callbackToken, $expectedToken)) {
            Log::warning('Xendit webhook: token tidak valid', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $payload = $request->all();
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;
        $paymentChannel = $payload['payment_channel'] ?? null;

        if (! $externalId || ! in_array($status, ['PAID', 'EXPIRED'], true)) {
            return response('Bad Request', 400);
        }

        $order = Order::where('order_number', $externalId)->first();

        if (! $order) {
            Log::warning('Xendit webhook: order tidak ditemukan', ['external_id' => $externalId]);

            return response('Order not found', 404);
        }

        if ($status === 'PAID') {
            if ($order->status !== Order::STATUS_UNPAID) {
                return response('OK');
            }

            $order->forceFill([
                'status' => Order::STATUS_PENDING,
                'paid_at' => now(),
                'payment_channel' => $paymentChannel,
            ])->save();

            $order->user?->notify(new OrderStatusUpdated($order->refresh()));
        } elseif ($status === 'EXPIRED') {
            if ($order->status !== Order::STATUS_UNPAID) {
                return response('OK');
            }

            $order->forceFill([
                'status' => Order::STATUS_EXPIRED,
            ])->save();

            $order->user?->notify(new OrderStatusUpdated($order->refresh()));
        }

        return response('OK');
    }
}
