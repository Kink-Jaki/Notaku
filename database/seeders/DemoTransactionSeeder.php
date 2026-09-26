<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashier = User::query()->where('email', 'kasir@posapp.test')->firstOrFail();
        $customer = User::query()->where('email', 'pelanggan@posapp.test')->firstOrFail();
        $promo = PromoCode::query()->where('code', 'RAMDISC10')->firstOrFail();
        $products = Product::query()->get()->keyBy('name');

        $transactions = [
            ['number' => 'TRX-DEMO-001', 'days_ago' => 0, 'hour' => 9, 'payment' => 'tunai', 'items' => [['Nasi Goreng Spesial', 2], ['Es Teh Manis', 2]]],
            ['number' => 'TRX-DEMO-002', 'days_ago' => 1, 'hour' => 11, 'payment' => 'qris', 'items' => [['Ayam Geprek', 1], ['Es Jeruk', 1]]],
            ['number' => 'TRX-DEMO-003', 'days_ago' => 3, 'hour' => 14, 'payment' => 'ewallet', 'items' => [['Kopi Susu Gula Aren', 2], ['Pisang Goreng', 2]]],
        ];

        foreach ($transactions as $index => $definition) {
            $items = collect($definition['items'])->map(function (array $item) use ($products): array {
                $product = $products->get($item[0]);

                if ($product === null) {
                    throw new \RuntimeException("Produk seed {$item[0]} tidak ditemukan.");
                }

                return ['product' => $product, 'qty' => $item[1]];
            });
            $subtotal = $items->sum(fn (array $item): int => $item['product']->price * $item['qty']);
            $isOnline = $index === 1;
            $discount = $isOnline ? $promo->discountFor($subtotal) : 0;
            $occurredAt = now()->subDays($definition['days_ago'])->setTime($definition['hour'], 15);

            $order = null;
            if ($isOnline) {
                $order = Order::updateOrCreate(
                    ['order_number' => 'ORD-DEMO-001'],
                    [
                        'user_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'note' => 'Pesanan demo online',
                        'status' => Order::STATUS_PROCESSING,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'promo_code_id' => $promo->id,
                        'total' => $subtotal - $discount,
                        'payment_method' => $definition['payment'],
                        'approved_by' => $cashier->id,
                        'approved_at' => $occurredAt,
                    ],
                );
                $order->forceFill(['created_at' => $occurredAt, 'updated_at' => $occurredAt])->save();
            }

            $transaction = Transaction::updateOrCreate(
                ['transaction_number' => $definition['number']],
                [
                    'order_id' => $order?->id,
                    'user_id' => $cashier->id,
                    'customer_name' => $isOnline ? $customer->name : 'Pembeli langsung',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'promo_code_id' => $isOnline ? $promo->id : null,
                    'total' => $subtotal - $discount,
                    'payment_method' => $definition['payment'],
                    'status' => 'selesai',
                ],
            );
            $transaction->forceFill(['created_at' => $occurredAt, 'updated_at' => $occurredAt])->save();

            foreach ($items as $item) {
                $product = $item['product'];
                $attributes = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'qty' => $item['qty'],
                ];

                TransactionItem::updateOrCreate(['transaction_id' => $transaction->id, 'product_id' => $product->id], $attributes);

                if ($order !== null) {
                    OrderItem::updateOrCreate(['order_id' => $order->id, 'product_id' => $product->id], $attributes);
                }
            }
        }

        $pendingOrder = Order::updateOrCreate(
            ['order_number' => 'ORD-DEMO-002'],
            [
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'note' => 'Mohon tanpa sambal',
                'status' => Order::STATUS_PENDING,
                'subtotal' => $products->get('Mie Goreng Jawa')->price,
                'discount' => 0,
                'total' => $products->get('Mie Goreng Jawa')->price,
                'payment_method' => 'qris',
            ],
        );

        $pendingProduct = $products->get('Mie Goreng Jawa');
        OrderItem::updateOrCreate(
            ['order_id' => $pendingOrder->id, 'product_id' => $pendingProduct->id],
            [
                'product_name' => $pendingProduct->name,
                'price' => $pendingProduct->price,
                'qty' => 1,
            ],
        );
    }
}
