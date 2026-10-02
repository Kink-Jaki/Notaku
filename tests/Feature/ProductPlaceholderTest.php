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

    public function test_produk_tanpa_gambar_memakai_placeholder_kategori(): void
    {
        $kategori = Category::factory()->create(['name' => 'Makanan']);
        $produk = Product::factory()->for($kategori)->create(['image' => null]);

        $this->get(route('marketplace.produk', $produk))
            ->assertOk()
            ->assertSee('product-placeholder', false)
            ->assertSee('Gambar belum tersedia untuk Makanan', false)
            ->assertSee('ph-makanan', false);
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

    public function test_grid_mobile_memakai_placeholder_kategori(): void
    {
        $kategori = Category::factory()->create(['name' => 'Minuman']);
        Product::factory()->for($kategori)->create(['image' => null]);

        $this->actingAs(User::factory()->pelanggan()->create())
            ->get(route('pelanggan.katalog.mobile'))
            ->assertOk()
            ->assertSee('product-placeholder', false)
            ->assertSee('ph-minuman', false);
    }

    public function test_api_katalog_mengembalikan_data_brand(): void
    {
        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonPath('brand.name', 'Toko Contoh')
            ->assertJsonPath('brand.logo', asset('storage/branding/logo-contoh.png'));
    }
}
