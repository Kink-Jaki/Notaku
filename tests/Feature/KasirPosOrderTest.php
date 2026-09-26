<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirPosOrderTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        $kasir = User::factory()->kasir()->create();
        $kasir->email_verified_at = now();
        $kasir->save();

        return $kasir;
    }

    public function test_order_dari_frontend_disimpan_ke_database(): void
    {
        $kasir = $this->kasir();
        $product = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $response = $this->actingAs($kasir)->postJson(route('kasir.pos.order'), [
            'payment_method' => 'tunai',
            'items' => [
                ['product_id' => $product->id, 'qty' => 2],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('subtotal', 30000)
            ->assertJsonPath('total', 30000)
            ->assertJsonPath('items_count', 2);

        $transaction = Transaction::query()->latest('id')->firstOrFail();

        $this->assertSame(30000, (int) $transaction->subtotal);
        $this->assertSame('tunai', $transaction->payment_method);
        $this->assertSame('selesai', $transaction->status);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 15000,
        ]);

        $this->assertSame(8, (int) $product->fresh()->stock);
        $this->assertSame(
            ['id' => $product->id, 'stock' => 8],
            $response->json('stocks.0')
        );
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $kasir->id,
            'action' => 'pos_transaction',
        ]);
    }

    public function test_order_gagal_ketika_stok_tidak_mencukupi(): void
    {
        $kasir = $this->kasir();
        $product = Product::factory()->create(['stock' => 1]);
        $transactionCount = Transaction::query()->count();

        $response = $this->actingAs($kasir)->postJson(route('kasir.pos.order'), [
            'payment_method' => 'tunai',
            'items' => [
                ['product_id' => $product->id, 'qty' => 5],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('stocks.0.stock', 1);

        $this->assertStringContainsString('Stok tidak mencukupi', $response->json('message'));
        $this->assertSame($transactionCount, Transaction::query()->count());
        $this->assertSame(1, (int) $product->fresh()->stock);
    }

    public function test_order_gagal_ketika_promo_tidak_valid(): void
    {
        $kasir = $this->kasir();
        $product = Product::factory()->create(['price' => 20000, 'stock' => 5]);
        $transactionCount = Transaction::query()->count();

        $response = $this->actingAs($kasir)->postJson(route('kasir.pos.order'), [
            'payment_method' => 'qris',
            'promo_code' => 'TIDAK-ADA',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'promo_invalid');

        $this->assertSame($transactionCount, Transaction::query()->count());
    }

    public function test_order_menerapkan_promo_dari_frontend(): void
    {
        $kasir = $this->kasir();
        $product = Product::factory()->create(['price' => 20000, 'stock' => 5]);
        $promo = PromoCode::factory()->create([
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 10,
            'min_order' => 0,
        ]);

        $response = $this->actingAs($kasir)->postJson(route('kasir.pos.order'), [
            'payment_method' => 'debit',
            'promo_code' => $promo->code,
            'items' => [
                ['product_id' => $product->id, 'qty' => 2],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('subtotal', 40000)
            ->assertJsonPath('discount', 4000)
            ->assertJsonPath('total', 36000);

        $transaction = Transaction::query()->latest('id')->firstOrFail();

        $this->assertSame($promo->id, (int) $transaction->promo_code_id);
        $this->assertSame(1, (int) $promo->fresh()->used_count);
    }

    public function test_validate_promo_mengembalikan_diskon_tanpa_menyimpan_keranjang(): void
    {
        $kasir = $this->kasir();
        $promo = PromoCode::factory()->create([
            'type' => PromoCode::TYPE_FIXED,
            'value' => 5000,
            'min_order' => 10000,
        ]);

        $this->actingAs($kasir)
            ->postJson(route('kasir.pos.promoValidate'), ['code' => $promo->code, 'subtotal' => 20000])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('code', $promo->code)
            ->assertJsonPath('discount', 5000);

        $this->actingAs($kasir)
            ->postJson(route('kasir.pos.promoValidate'), ['code' => $promo->code, 'subtotal' => 5000])
            ->assertStatus(422)
            ->assertJsonPath('code', 'promo_invalid');

        $this->actingAs($kasir)
            ->postJson(route('kasir.pos.promoValidate'), ['code' => 'SALAH', 'subtotal' => 20000])
            ->assertStatus(422)
            ->assertJsonPath('code', 'promo_invalid');
    }

    public function test_guest_tidak_bisa_order(): void
    {
        $product = Product::factory()->create();
        $transactionCount = Transaction::query()->count();

        $this->postJson(route('kasir.pos.order'), [
            'payment_method' => 'tunai',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1],
            ],
        ])->assertUnauthorized();

        $this->assertSame($transactionCount, Transaction::query()->count());
    }
}
