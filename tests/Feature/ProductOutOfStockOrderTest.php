<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOutOfStockOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create([
            'brand_name' => 'Toko Contoh',
            'logo_path' => 'branding/logo-contoh.png',
        ]);
    }

    public function test_api_katalog_mengurutkan_produk_habis_di_bawah(): void
    {
        $kategori = Category::factory()->create();

        $baru = Product::factory()->for($kategori)->create([
            'name' => 'A Baru Tersedia',
            'stock' => 20,
            'created_at' => '2026-01-03 00:00:00',
        ]);
        $lama = Product::factory()->for($kategori)->create([
            'name' => 'B Lama Tersedia',
            'stock' => 15,
            'created_at' => '2026-01-01 00:00:00',
        ]);
        $terbaruHabis = Product::factory()->for($kategori)->create([
            'name' => 'C Habis Terbaru',
            'stock' => 0,
            'created_at' => '2026-01-04 00:00:00',
        ]);
        $lamaHabis = Product::factory()->for($kategori)->create([
            'name' => 'D Habis Lama',
            'stock' => -3,
            'created_at' => '2026-01-02 00:00:00',
        ]);

        $urut = collect($this->getJson(route('api.produk'))
            ->assertOk()
            ->json('produk.data'))
            ->pluck('id')
            ->all();

        $this->assertSame([$baru->id, $lama->id, $terbaruHabis->id, $lamaHabis->id], $urut);
    }

    public function test_katalog_mobile_mengurutkan_produk_habis_di_bawah(): void
    {
        $kategori = Category::factory()->create();

        $tersedia = Product::factory()->for($kategori)->create([
            'stock' => 20,
            'created_at' => '2026-01-01 00:00:00',
        ]);
        $habis = Product::factory()->for($kategori)->create([
            'stock' => 0,
            'created_at' => '2026-01-04 00:00:00',
        ]);

        $response = $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('pelanggan.katalog.mobile'));

        $urut = $response->viewData('produk')->pluck('id')->all();

        $this->assertSame([$tersedia->id, $habis->id], $urut);
    }

    public function test_pos_kasir_mengurutkan_produk_habis_di_bawah(): void
    {
        $kategori = Category::factory()->create();

        $habis = Product::factory()->for($kategori)->create([
            'name' => 'Z Habis',
            'stock' => 0,
            'created_at' => '2026-01-04 00:00:00',
        ]);
        $tersedia = Product::factory()->for($kategori)->create([
            'name' => 'A Tersedia',
            'stock' => 20,
            'created_at' => '2026-01-01 00:00:00',
        ]);

        $urut = $this->actingAs(User::factory()->kasir()->create())
            ->get(route('kasir.pos'))
            ->assertOk()
            ->viewData('produk')
            ->pluck('id')
            ->all();

        $this->assertSame([$tersedia->id, $habis->id], $urut);
    }
}
