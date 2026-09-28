<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPlaceholderTest extends TestCase
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

    public function test_produk_tanpa_gambar_memakai_logo_dan_nama_brand(): void
    {
        $kategori = Category::factory()->create();
        $produk = Product::factory()->for($kategori)->create(['image' => null]);

        $this->get(route('marketplace.produk', $produk))
            ->assertOk()
            ->assertSee('product-placeholder', false)
            ->assertSee('Gambar produk Toko Contoh', false)
            ->assertSee('storage/branding/logo-contoh.png', false)
            ->assertSee('Toko Contoh');
    }

    public function test_produk_dengan_gambar_tidak_memakai_placeholder(): void
    {
        $kategori = Category::factory()->create();
        $produk = Product::factory()->for($kategori)->create(['image' => 'products/contoh.jpg']);

        $this->get(route('marketplace.produk', $produk))
            ->assertOk()
            ->assertSee('storage/products/contoh.jpg', false)
            ->assertDontSee('product-placeholder', false);
    }

    public function test_grid_mobile_memakai_placeholder_brand(): void
    {
        Product::factory()->create(['image' => null]);

        $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('pelanggan.katalog.mobile'))
            ->assertOk()
            ->assertSee('product-placeholder', false)
            ->assertSee('Toko Contoh');
    }

    public function test_api_katalog_mengembalikan_data_brand(): void
    {
        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonPath('brand.name', 'Toko Contoh')
            ->assertJsonPath('brand.logo', asset('storage/branding/logo-contoh.png'));
    }
}
