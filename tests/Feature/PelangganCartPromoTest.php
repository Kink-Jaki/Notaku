<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganCartPromoTest extends TestCase
{
    use RefreshDatabase;

    public function test_terapkan_promo_mengembalikan_objek_promo_untuk_client(): void
    {
        $produk = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $promo = PromoCode::factory()->create(['type' => 'percent', 'value' => 10, 'min_order' => 0]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $this->postJson(route('pelanggan.cart.promo'), ['code' => $promo->code])
            ->assertOk()
            ->assertJson(['success' => true, 'discount' => 2000])
            ->assertJsonPath('promo.code', $promo->code)
            ->assertJsonPath('promo.discount', 2000);

        $this->assertSame(2000, session('pelanggan_promo')['discount']);
    }

    public function test_hapus_promo_mengembalikan_promo_null(): void
    {
        $produk = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $promo = PromoCode::factory()->create(['type' => 'percent', 'value' => 10, 'min_order' => 0]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $this->postJson(route('pelanggan.cart.promo'), ['code' => $promo->code])
            ->assertOk();

        $this->postJson(route('pelanggan.cart.promoRemove'))
            ->assertOk()
            ->assertJson(['success' => true, 'promo' => null]);

        $this->assertNull(session('pelanggan_promo'));
    }

    public function test_halaman_keranjang_menampilkan_baris_diskon_promo(): void
    {
        $produk = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $promo = PromoCode::factory()->create(['type' => 'percent', 'value' => 10, 'min_order' => 0]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $this->postJson(route('pelanggan.cart.promo'), ['code' => $promo->code])
            ->assertOk();

        $html = $this->get(route('pelanggan.cart'))->assertOk()->getContent();

        $this->assertStringContainsString('data-cart-discount-row', $html);
        $this->assertStringContainsString($promo->code, $html);
        $this->assertStringContainsString('data-promo-applied', $html);
        $this->assertStringContainsString('data-promo-code', $html);
    }
}
