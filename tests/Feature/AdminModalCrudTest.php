<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModalCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_produk_baru_bisa_ditambahkan_melalui_modal(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('admin.produk.store'), [
                'name' => 'Produk Dari Modal',
                'description' => 'Deskripsi produk',
                'price' => 15000,
                'stock' => 5,
                'category_id' => $category->id,
                'is_active' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('products', ['name' => 'Produk Dari Modal']);
    }

    public function test_validasi_produk_gagal_mengembalikan_error_json(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.produk.store'), ['price' => -1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock', 'category_id']);
    }

    public function test_produk_bisa_diedit_melalui_modal(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->putJson(route('admin.produk.update', $product), [
                'name' => 'Produk Hasil Edit',
                'description' => 'Deskripsi baru',
                'price' => 20000,
                'stock' => 3,
                'category_id' => $category->id,
                'is_active' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Produk Hasil Edit']);
    }

    public function test_kategori_baru_bisa_ditambahkan_melalui_modal(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.kategori.store'), [
                'name' => 'Kategori Dari Modal',
                'slug' => 'kategori-dari-modal',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('categories', ['slug' => 'kategori-dari-modal']);
    }

    public function test_kategori_duplikat_mengembalikan_error_json(): void
    {
        Category::factory()->create(['name' => 'Makanan', 'slug' => 'makanan']);

        $this->actingAs($this->admin())
            ->postJson(route('admin.kategori.store'), [
                'name' => 'Makanan',
                'slug' => 'makanan',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    public function test_kategori_bisa_diedit_melalui_modal(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->putJson(route('admin.kategori.update', $category), [
                'name' => 'Kategori Diubah',
                'slug' => 'kategori-diubah',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Kategori Diubah']);
    }

    public function test_promo_baru_bisa_ditambahkan_melalui_modal(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.promo.store'), [
                'code' => 'MODALHEMAT',
                'type' => 'percent',
                'value' => 10,
                'min_order' => 20000,
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('promo_codes', ['code' => 'MODALHEMAT']);
    }

    public function test_promo_bisa_diedit_melalui_modal(): void
    {
        $promo = PromoCode::factory()->create();

        $this->actingAs($this->admin())
            ->putJson(route('admin.promo.update', $promo), [
                'code' => $promo->code,
                'type' => 'fixed',
                'value' => 5000,
                'min_order' => 10000,
                'is_active' => 1,
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('promo_codes', ['id' => $promo->id, 'type' => 'fixed', 'value' => 5000]);
    }

    public function test_user_baru_bisa_ditambahkan_melalui_modal(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.user-role.store'), [
                'name' => 'User Dari Modal',
                'email' => 'modal@example.test',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'role' => 'kasir',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('users', ['email' => 'modal@example.test', 'role' => 'kasir']);
    }

    public function test_validasi_user_gagal_mengembalikan_error_json(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.user-role.store'), ['email' => 'bukan-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_user_bisa_diedit_melalui_modal(): void
    {
        $user = User::factory()->kasir()->create();

        $this->actingAs($this->admin())
            ->putJson(route('admin.user-role.update', $user), [
                'name' => 'User Hasil Edit',
                'email' => $user->email,
                'role' => 'pelanggan',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'User Hasil Edit', 'role' => 'pelanggan']);
    }
}
