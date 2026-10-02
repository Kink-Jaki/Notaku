<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganBuyNowTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadCheckout(Product $product, int $qty = 1): array
    {
        return [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'delivery_type' => 'pickup',
            'note' => 'Tanpa pedas',
            'payment_method' => 'online',
            'product_id' => $product->id,
            'qty' => $qty,
        ];
    }

    public function test_checkout_beli_sekarang_tampil_tanpa_keranjang(): void
    {
        $produk = Product::factory()->create(['name' => 'Ayam Geprek Level 5', 'stock' => 10]);

        $response = $this->actingAs($this->pelanggan())
            ->get(route('pelanggan.checkout', ['product_id' => $produk->id, 'qty' => 2]))
            ->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('Ayam Geprek Level 5', $html);
        $this->assertStringContainsString('name="product_id"', $html);
        $this->assertStringContainsString('value="'.$produk->id.'"', $html);
        $this->assertEmpty(session('pelanggan_cart'));
    }

    public function test_checkout_mobile_beli_sekarang_tampil_tanpa_keranjang(): void
    {
        $produk = Product::factory()->create(['name' => 'Es Teh Manis', 'stock' => 10]);

        $html = $this->actingAs($this->pelanggan())
            ->get(route('pelanggan.checkout.mobile', ['product_id' => $produk->id, 'qty' => 3]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Es Teh Manis', $html);
        $this->assertStringContainsString('name="product_id"', $html);
        $this->assertStringContainsString('value="3"', $html);
        $this->assertEmpty(session('pelanggan_cart'));
    }

    private function nearestForm(DOMNode $node): ?DOMElement
    {
        for ($parent = $node->parentNode; $parent !== null; $parent = $parent->parentNode) {
            if ($parent->nodeName === 'form' && $parent instanceof DOMElement) {
                return $parent;
            }
        }

        return null;
    }

    /**
     * <form> tidak boleh bersarang: browser menutup form utama di </form> pertama,
     * sehingga tombol "Submit Pesanan" jatuh ke luar form dan klik tidak mengirim apa pun.
     */
    public function test_tombol_submit_checkout_belong_to_form_checkout(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $urls = [
            'desktop' => route('pelanggan.checkout', ['product_id' => $produk->id, 'qty' => 1]),
            'mobile' => route('pelanggan.checkout.mobile', ['product_id' => $produk->id, 'qty' => 1]),
        ];

        foreach ($urls as $label => $url) {
            $html = $this->actingAs($this->pelanggan())->get($url)->assertOk()->getContent();

            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            $this->assertSame(
                0,
                $xpath->query('//form//form')->length,
                "[{$label}] ada <form> bersarang, tombol submit akan lepas dari form checkout",
            );

            $submit = $xpath->query('//button[@type="submit"][contains(@class, "btn-brand")]')->item(0);
            $this->assertNotNull($submit, "[{$label}] tombol submit tidak ditemukan");

            $formId = $submit instanceof DOMElement ? $submit->getAttribute('form') : '';
            $owner = $formId !== ''
                ? $xpath->query('//form[@id="'.$formId.'"]')->item(0)
                : $this->nearestForm($submit);

            $this->assertNotNull($owner, "[{$label}] tombol submit tidak dimiliki form manapun");
            $this->assertSame(
                route('pelanggan.checkout.store'),
                $owner instanceof DOMElement ? $owner->getAttribute('action') : null,
                "[{$label}] tombol submit dikirim ke form yang salah",
            );
        }
    }

    public function test_checkout_tanpa_produk_dan_keranjang_kosong_dialihkan(): void
    {
        $this->actingAs($this->pelanggan())
            ->get(route('pelanggan.checkout'))
            ->assertRedirect(route('marketplace'));

        $this->assertSame('Keranjang masih kosong', session('swal_warning'));
    }

    public function test_tamu_tidak_bisa_mengakses_checkout_beli_sekarang(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $this->get(route('pelanggan.checkout', ['product_id' => $produk->id]))
            ->assertRedirect(route('login'));
    }

    public function test_submit_beli_sekarang_membuat_pesanan_tanpa_keranjang(): void
    {
        $produk = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $response = $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.checkout.store'), $this->payloadCheckout($produk, 2));

        $order = Order::firstOrFail();
        $expectedUrl = route('pelanggan.pembayaran.status', $order).'?fake_pay=1';

        $response->assertRedirect($expectedUrl);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);

        $order = Order::firstOrFail();

        $this->assertSame(Order::STATUS_UNPAID, $order->status);
        $this->assertSame(30000, (int) $order->subtotal);
        $this->assertSame(30000, (int) $order->total);
        $this->assertSame(2, (int) $order->items()->first()->qty);
        $this->assertSame($produk->id, (int) $order->items()->first()->product_id);
        $this->assertEmpty(session('pelanggan_cart'));
    }

    public function test_submit_beli_sekarang_tidak_mengosongkan_keranjang(): void
    {
        $dibeli = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $diKeranjang = Product::factory()->create(['price' => 7000, 'stock' => 10]);

        $response = $this->actingAs($this->pelanggan())
            ->withSession(['pelanggan_cart' => [
                $diKeranjang->id => [
                    'id' => $diKeranjang->id,
                    'name' => $diKeranjang->name,
                    'price' => $diKeranjang->price,
                    'stock' => $diKeranjang->stock,
                    'qty' => 1,
                ],
            ]])
            ->post(route('pelanggan.checkout.store'), $this->payloadCheckout($dibeli, 1));

        $order = Order::firstOrFail();
        $expectedUrl = route('pelanggan.pembayaran.status', $order).'?fake_pay=1';

        $response->assertRedirect($expectedUrl);

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(1, Order::firstOrFail()->items()->count());

        $cart = session('pelanggan_cart');
        $this->assertArrayHasKey($diKeranjang->id, $cart);
        $this->assertSame(1, $cart[$diKeranjang->id]['qty']);
    }

    public function test_submit_beli_sekarang_stok_kurang_ditolak(): void
    {
        $produk = Product::factory()->create(['stock' => 2]);
        $url = route('pelanggan.checkout', ['product_id' => $produk->id, 'qty' => 5]);

        $this->actingAs($this->pelanggan())
            ->from($url)
            ->post(route('pelanggan.checkout.store'), $this->payloadCheckout($produk, 5))
            ->assertRedirect($url);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame('Stok tidak mencukupi untuk jumlah yang diminta', session('swal_warning'));
    }

    public function test_beli_sekarang_produk_nonaktif_ditolak(): void
    {
        $produk = Product::factory()->create(['stock' => 10, 'is_active' => false]);

        $this->actingAs($this->pelanggan())
            ->get(route('pelanggan.checkout', ['product_id' => $produk->id]))
            ->assertRedirect(route('marketplace'));

        $this->assertSame('Produk tidak ditemukan atau sudah tidak tersedia', session('swal_warning'));
    }

    public function test_halaman_produk_mempunyai_tombol_beli_sekarang(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $html = $this->actingAs($this->pelanggan())
            ->get(route('marketplace.produk', $produk))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Beli Sekarang', $html);
        $this->assertStringContainsString('id="buyNowBtn"', $html);
        $this->assertStringContainsString(
            e(route('pelanggan.checkout', ['product_id' => $produk->id, 'qty' => 1])),
            $html
        );
    }
}
