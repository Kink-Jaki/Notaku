<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDeliveryPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    private function kasir(): User
    {
        return User::factory()->kasir()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadCheckout(string $deliveryType = 'delivery', ?string $address = 'Jl. Merdeka No. 1, Bandung'): array
    {
        return [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'delivery_type' => $deliveryType,
            'address' => $address,
            'note' => 'Tanpa pedas',
            'payment_method' => $deliveryType === 'delivery' ? 'transfer' : 'tunai',
        ];
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function cartSession(Product $product, int $qty = 1): array
    {
        return [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'qty' => $qty,
            ],
        ];
    }

    public function test_checkout_menyimpan_no_wa_alamat_dan_jenis_pengiriman(): void
    {
        $produk = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $this->actingAs($this->pelanggan())
            ->withSession(['pelanggan_cart' => $this->cartSession($produk)])
            ->post(route('pelanggan.checkout.store'), $this->payloadCheckout('delivery'))
            ->assertRedirect(route('marketplace'));

        $this->assertDatabaseHas('orders', [
            'customer_phone' => '081234567890',
            'delivery_type' => 'delivery',
            'address' => 'Jl. Merdeka No. 1, Bandung',
        ]);
    }

    public function test_checkout_pickup_tidak_menyimpan_alamat(): void
    {
        $produk = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $this->actingAs($this->pelanggan())
            ->withSession(['pelanggan_cart' => $this->cartSession($produk)])
            ->post(route('pelanggan.checkout.store'), $this->payloadCheckout('pickup', null))
            ->assertRedirect(route('marketplace'));

        $order = Order::query()->firstOrFail();

        $this->assertSame('pickup', $order->delivery_type);
        $this->assertNull($order->address);
        $this->assertSame('081234567890', $order->customer_phone);
        $this->assertSame('Ambil di Tempat', $order->deliveryTypeLabel());
    }

    public function test_checkout_delivery_wajib_alamat(): void
    {
        $produk = Product::factory()->create(['price' => 15000, 'stock' => 10]);
        $url = route('pelanggan.checkout');

        $this->actingAs($this->pelanggan())
            ->withSession(['pelanggan_cart' => $this->cartSession($produk)])
            ->from($url)
            ->post(route('pelanggan.checkout.store'), $this->payloadCheckout('delivery', null))
            ->assertRedirect($url)
            ->assertSessionHasErrors('address');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_beli_sekarang_juga_menyimpan_info_pengiriman(): void
    {
        $produk = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $payload = $this->payloadCheckout('delivery', 'Jl. Kenanga No. 21, Jakarta') + [
            'product_id' => $produk->id,
            'qty' => 2,
        ];

        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.checkout.store'), $payload)
            ->assertRedirect(route('marketplace'));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'customer_phone' => '081234567890',
            'delivery_type' => 'delivery',
            'address' => 'Jl. Kenanga No. 21, Jakarta',
        ]);
    }

    public function test_api_antrian_menyertakan_info_pengiriman(): void
    {
        $pelanggan = $this->pelanggan();

        Order::factory()->delivery()->create([
            'user_id' => $pelanggan->id,
            'status' => Order::STATUS_PENDING,
            'customer_phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->kasir())
            ->getJson(route('api.kasir.antrian'));

        $response->assertOk()
            ->assertJsonPath('data.pending.0.customer_phone', '081234567890')
            ->assertJsonPath('data.pending.0.delivery_type', 'delivery')
            ->assertJsonPath('data.pending.0.delivery_type_label', 'Antar')
            ->assertJsonPath('data.pending.0.address', 'Jl. Merdeka No. 1, Bandung');
    }
}
