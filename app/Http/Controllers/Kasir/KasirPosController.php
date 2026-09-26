<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Support\NumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KasirPosController extends Controller
{
    private const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet',
        'debit' => 'Debit',
        'transfer' => 'Transfer',
    ];

    public function index()
    {
        $produk = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'nama' => $product->name,
                'kategori' => $product->category?->name ?? 'Lainnya',
                'harga' => (int) $product->price,
                'stok' => (int) $product->stock,
            ]);

        $kategoriList = collect([['label' => 'Semua', 'count' => $produk->count(), 'active' => true]])
            ->merge(
                $produk->groupBy('kategori')
                    ->map(fn ($items, $kategori) => [
                        'label' => $kategori,
                        'count' => $items->count(),
                        'active' => false,
                    ])
                    ->values()
            );

        return view('kasir.pos', [
            'produk' => $produk,
            'kategoriList' => $kategoriList,
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    /**
     * Validate a promo code against the current (client side) subtotal.
     */
    public function validatePromo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:255'],
            'subtotal' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $subtotal = $request->integer('subtotal');
        $promo = $this->findActivePromo($request->input('code'));

        if ($promo === null) {
            return $this->promoError('Kode promo tidak valid atau sudah tidak aktif.');
        }

        if ($subtotal < (int) $promo->min_order) {
            return $this->promoError('Subtotal belum memenuhi minimum pesanan promo ini.');
        }

        return response()->json([
            'valid' => true,
            'code' => $promo->code,
            'type' => $promo->type,
            'value' => (int) $promo->value,
            'min_order' => (int) $promo->min_order,
            'discount' => $promo->discountFor($subtotal),
        ]);
    }

    /**
     * Persist a finished order. The cart is assembled in the browser and only
     * sent once, when the cashier presses "Order".
     */
    public function order(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => ['required', Rule::in(array_keys(self::PAYMENT_METHODS))],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'promo_code' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validated = $validator->validated();
        $quantities = [];

        foreach ($validated['items'] as $item) {
            $productId = (int) $item['product_id'];
            $quantities[$productId] = ($quantities[$productId] ?? 0) + (int) $item['qty'];
        }

        $productIds = array_keys($quantities);
        $promoCode = trim((string) ($validated['promo_code'] ?? ''));

        if ($promoCode !== '' && $this->findActivePromo($promoCode) === null) {
            return $this->promoError('Kode promo tidak valid atau sudah tidak aktif.', $productIds);
        }

        try {
            $result = DB::transaction(function () use ($quantities, $promoCode, $validated, $request) {
                $lockedProducts = Product::query()
                    ->whereIn('id', array_keys($quantities))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $subtotal = 0;
                $rows = [];

                foreach ($quantities as $productId => $qty) {
                    $product = $lockedProducts->get($productId);

                    if ($product === null || ! $product->is_active) {
                        throw new \RuntimeException('Produk di keranjang sudah tidak tersedia.');
                    }

                    if ($qty > (int) $product->stock) {
                        throw new \RuntimeException("Stok tidak mencukupi untuk {$product->name}.");
                    }

                    $subtotal += (int) $product->price * $qty;
                    $rows[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => (int) $product->price,
                        'qty' => $qty,
                    ];
                }

                $discount = 0;
                $promoCodeId = null;

                if ($promoCode !== '') {
                    $promo = $this->findActivePromo($promoCode);

                    if ($promo === null) {
                        throw new \RuntimeException('Kode promo tidak valid atau sudah tidak aktif.');
                    }

                    if ($subtotal < (int) $promo->min_order) {
                        throw new \RuntimeException('Subtotal belum memenuhi minimum pesanan promo ini.');
                    }

                    $discount = $promo->discountFor($subtotal);
                    $promoCodeId = $promo->id;
                }

                $transaction = Transaction::create([
                    'transaction_number' => NumberGenerator::next('TRX', Transaction::class),
                    'order_id' => null,
                    'user_id' => $request->user()->id,
                    'customer_name' => 'Kasir',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'promo_code_id' => $promoCodeId,
                    'total' => $subtotal - $discount,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'selesai',
                ]);

                foreach ($rows as $row) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $row['product_id'],
                        'product_name' => $row['product_name'],
                        'price' => $row['price'],
                        'qty' => $row['qty'],
                    ]);

                    $lockedProducts->get($row['product_id'])->decrement('stock', $row['qty']);
                }

                if ($promoCodeId !== null) {
                    PromoCode::query()->whereKey($promoCodeId)->increment('used_count');
                }

                // Audit log
                $itemDetails = collect($rows)->map(fn ($r) => "{$r['product_name']} x{$r['qty']}")->implode(', ');
                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'pos_transaction',
                    'model_type' => Transaction::class,
                    'model_id' => $transaction->id,
                    'description' => "Transaksi POS {$transaction->transaction_number}: {$itemDetails} (Total: Rp ".number_format($subtotal - $discount, 0, ',', '.').')',
                    'old_values' => null,
                    'new_values' => [
                        'transaction_number' => $transaction->transaction_number,
                        'items' => $rows,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'total' => $subtotal - $discount,
                        'payment_method' => $validated['payment_method'],
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return [
                    'success' => true,
                    'transaction_number' => $transaction->transaction_number,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $subtotal - $discount,
                    'items_count' => array_sum($quantities),
                    'stocks' => $this->stockMap(array_keys($quantities)),
                ];
            });
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Terjadi kesalahan saat memproses pesanan.',
                'stocks' => $this->stockMap($productIds),
            ], 422);
        }

        return response()->json($result);
    }

    private function findActivePromo(mixed $code): ?PromoCode
    {
        $promo = PromoCode::query()
            ->whereRaw('LOWER(code) = ?', [strtolower(trim((string) $code))])
            ->first();

        return ($promo !== null && $promo->isCurrentlyActive()) ? $promo : null;
    }

    private function promoError(string $message, array $productIds = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'code' => 'promo_invalid',
            'stocks' => $this->stockMap($productIds),
        ], 422);
    }

    /**
     * @param  array<int>  $productIds
     * @return array<int, array{ id: int, stock: int }>
     */
    private function stockMap(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'stock'])
            ->map(fn (Product $product) => ['id' => (int) $product->id, 'stock' => (int) $product->stock])
            ->values()
            ->all();
    }
}
