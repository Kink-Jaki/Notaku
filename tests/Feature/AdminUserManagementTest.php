<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_user_tidak_menampilkan_tombol_aktifkan_dan_nonaktifkan(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/user-role')
            ->assertOk()
            ->assertSee('title="Edit user"', false)
            ->assertSee('title="Hapus user"', false)
            ->assertDontSee('title="Aktifkan user"', false)
            ->assertDontSee('title="Nonaktifkan user"', false);
    }

    public function test_daftar_user_tidak_menampilkan_kolom_status(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/user-role')
            ->assertOk()
            ->assertDontSee('name="status"', false)
            ->assertDontSee('is_active', false);
    }

    public function test_daftar_user_menampilkan_tombol_tautan_reset_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/user-role')
            ->assertOk()
            ->assertSee('title="Kirim tautan reset password"', false)
            ->assertSee(route('admin.user-role.reset-link', $user), false);
    }
}
