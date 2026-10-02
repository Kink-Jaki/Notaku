<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganCartAddTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_produk_menampilkan_form_tambah_keranjang_dengan_qty(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $html = $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('marketplace.produk', $produk))
            ->assertOk()
            ->getContent();

        $form = substr($html, strpos($html, 'id="addToCartForm"'));

        $this->assertStringContainsString('name="product_id"', $form);
        $this->assertStringContainsString('name="qty"', $form);
    }

    public function test_tambah_keranjang_menyimpan_item_ke_session(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $this->actingAs(User::factory()->pelanggan()->create())
            ->postJson(route('pelanggan.cart.add'), [
                'product_id' => $produk->id,
                'qty' => 2,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'cart_count' => 2]);

        $this->assertSame(2, session('pelanggan_cart')[$produk->id]['qty']);
    }

    public function test_tambah_keranjang_tanpa_qty_ditolak(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $this->actingAs(User::factory()->pelanggan()->create())
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id])
            ->assertStatus(422)
            ->assertJson(['error' => 'Data tidak valid']);

        $this->assertEmpty(session('pelanggan_cart'));
    }

    public function test_respons_keranjang_menyertakan_daftar_item_untuk_client(): void
    {
        $produk = Product::factory()->create(['name' => 'Nasi Uduk', 'stock' => 10]);

        $this->actingAs(User::factory()->pelanggan()->create())
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 3])
            ->assertOk()
            ->assertJsonPath('cart_count', 3)
            ->assertJsonPath("cart.{$produk->id}.name", 'Nasi Uduk')
            ->assertJsonPath("cart.{$produk->id}.qty", 3);
    }

    public function test_update_keranjang_mengembalikan_cart_terkini(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $this->postJson(route('pelanggan.cart.update'), ['product_id' => $produk->id, 'qty' => 5])
            ->assertOk()
            ->assertJson(['success' => true, 'cart_count' => 5])
            ->assertJsonPath("cart.{$produk->id}.qty", 5);
    }

    public function test_hapus_item_mengembalikan_cart_kosong(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $this->postJson(route('pelanggan.cart.remove'), ['product_id' => $produk->id])
            ->assertOk()
            ->assertJson(['success' => true, 'cart_count' => 0]);

        $this->assertEmpty(session('pelanggan_cart'));
    }

    public function test_halaman_keranjang_mengirim_qty_via_input_tersembunyi(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $html = $this->get(route('pelanggan.cart'))->assertOk()->getContent();

        $form = substr($html, strpos($html, 'class="d-flex align-items-center gap-1 cart-update-form"'));

        $this->assertStringContainsString('name="qty"', $form);
        $this->assertStringContainsString('data-cart-qty-input', $form);
        $this->assertStringContainsString('data-cart-step="-1"', $form);
    }

    public function test_halaman_keranjang_mobile_menampilkan_hook_client_side(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->postJson(route('pelanggan.cart.add'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertOk();

        $html = $this->get(route('pelanggan.cart.mobile'))->assertOk()->getContent();

        $this->assertStringContainsString('data-cart-row="'.$produk->id.'"', $html);
        $this->assertStringContainsString('data-cart-qty-input', $html);
        $this->assertStringContainsString('data-cart-empty-state', $html);
    }

    public function test_halaman_katalog_pelanggan_render_tanpa_error(): void
    {
        $user = User::factory()->pelanggan()->create();

        $this->get(route('marketplace'))->assertOk();
        $this->get(route('pelanggan.katalog.index'))->assertOk();
        $this->actingAs($user)->get(route('pelanggan.katalog.mobile'))->assertOk();
    }
}
