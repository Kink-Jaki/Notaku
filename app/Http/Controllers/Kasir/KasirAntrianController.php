<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Support\NumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KasirAntrianController extends Controller
{
    /**
     * Shell halaman: hanya merender skeleton.
     * Data diambil oleh JS melalui /api/kasir/antrian.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'semua');

        return view('kasir.antrian', compact('status'));
    }

    public function approve(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => ['required', Rule::in(['tunai', 'qris', 'ewallet'])],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (! $order->isPending()) {
            return back()->with('error', 'Pesanan ini sudah diputuskan sebelumnya.');
        }

        try {
            $transactionNumber = DB::transaction(function () use ($order, $request) {
                $order->load('items');

                $lockedProducts = Product::query()
                    ->whereIn('id', $order->items->pluck('product_id')->filter())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($order->items as $item) {
                    if ($item->product_id === null) {
                        continue;
                    }

                    $product = $lockedProducts->get($item->product_id);

                    if ($product === null || $item->qty > $product->stock) {
                        throw new \RuntimeException("Stok tidak mencukupi untuk produk {$item->product_name}.");
                    }
                }

                $order->forceFill([
                    'status' => Order::STATUS_PROCESSING,
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ])->save();

                $transaction = Transaction::create([
                    'transaction_number' => NumberGenerator::next('TRX', Transaction::class),
                    'order_id' => $order->id,
                    'user_id' => $request->user()->id,
                    'customer_name' => $order->customer_name,
                    'subtotal' => (int) $order->subtotal,
                    'discount' => (int) $order->discount,
                    'promo_code_id' => $order->promo_code_id,
                    'total' => (int) $order->total,
                    'payment_method' => $request->input('payment_method'),
                    'status' => 'selesai',
                ]);

                foreach ($order->items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'price' => (int) $item->price,
                        'qty' => (int) $item->qty,
                    ]);

                    if ($item->product_id !== null) {
                        $lockedProducts->get($item->product_id)->decrement('stock', $item->qty);
                    }
                }

                if ($order->promo_code_id !== null) {
                    PromoCode::query()->whereKey($order->promo_code_id)->increment('used_count');
                }

                // Audit log
                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'approve_order',
                    'model_type' => Order::class,
                    'model_id' => $order->id,
                    'description' => "Menyetujui pesanan {$order->order_number} ({$order->customer_name}) dengan metode {$request->input('payment_method')}",
                    'old_values' => ['status' => 'pending'],
                    'new_values' => ['status' => 'processing', 'payment_method' => $request->input('payment_method'), 'transaction_number' => $transaction->transaction_number],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $transaction->transaction_number;
            });
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('swal_success', "Pesanan disetujui — {$order->order_number} (Transaksi {$transactionNumber})");
    }

    public function reject(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'min:3'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (! $order->isPending()) {
            return back()->with('error', 'Pesanan ini sudah diputuskan sebelumnya.');
        }

        $order->forceFill([
            'status' => Order::STATUS_REJECTED,
            'rejected_reason' => $request->input('reason'),
        ])->save();

        // Audit log
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'reject_order',
            'model_type' => Order::class,
            'model_id' => $order->id,
            'description' => "Menolak pesanan {$order->order_number} ({$order->customer_name}): {$request->input('reason')}",
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'rejected', 'rejected_reason' => $request->input('reason')],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('swal_success', "Pesanan ditolak — {$order->order_number}.");
    }

    public function checkNewOrders(Request $request)
    {
        $request->session()->reflash();

        $count = Order::where('status', Order::STATUS_PENDING)->count();

        return response()->json(['pending_count' => $count]);
    }
}
