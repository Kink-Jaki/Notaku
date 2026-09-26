<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRouteBindingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_form_edit_user_role_terikat_ke_user(): void
    {
        $user = User::factory()->kasir()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.user-role.index'))
            ->assertOk()
            ->assertSee(route('admin.user-role.update', $user), false);
    }

    public function test_update_user_role_memperbarui_user_terpilih(): void
    {
        $user = User::factory()->kasir()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.user-role.update', $user), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
                'role' => 'pelanggan',
            ])
            ->assertRedirect(route('admin.user-role.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'role' => 'pelanggan',
        ]);
        $this->assertSame(2, User::count());
    }

    public function test_form_edit_produk_terikat_ke_produk(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.produk.index'))
            ->assertOk()
            ->assertSee(route('admin.produk.update', ['produk' => $product]), false);
    }

    public function test_form_edit_kategori_terikat_ke_kategori(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.kategori.index'))
            ->assertOk()
            ->assertSee(route('admin.kategori.update', $category), false);
    }

    public function test_detail_produk_terikat_ke_produk(): void
    {
        $product = Product::factory()->create();

        $this->get(route('marketplace.produk', $product))
            ->assertOk()
            ->assertSee($product->name);
    }
}
