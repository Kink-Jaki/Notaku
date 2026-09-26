<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_panel_kasir_atau_admin(): void
    {
        $this->get('/kasir/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/kasir/pos')->assertRedirect('/login');
        $this->get('/admin/produk')->assertRedirect('/login');
    }

    public function test_guest_bisa_buka_katalog_shop(): void
    {
        $this->get('/katalog')->assertOk();
    }

    public function test_kasir_bisa_akses_panel_kasir_tapi_tidak_admin(): void
    {
        $kasir = User::factory()->kasir()->create();

        $this->actingAs($kasir)->get('/kasir/dashboard')->assertOk();
        $this->actingAs($kasir)->get('/kasir/pos')->assertOk();
        $this->actingAs($kasir)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($kasir)->get('/admin/produk')->assertForbidden();
    }

    public function test_admin_bisa_akses_panel_admin_tapi_tidak_panel_kasir(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/user-role')->assertOk();
        $this->actingAs($admin)->get('/kasir/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/kasir/pos')->assertForbidden();
    }

    public function test_pelanggan_tidak_bisa_akses_panel_kasir_atau_admin(): void
    {
        $pelanggan = User::factory()->pelanggan()->create();

        $this->actingAs($pelanggan)->get('/kasir/dashboard')->assertForbidden();
        $this->actingAs($pelanggan)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_dashboard_mengarahkan_per_role(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();
        $pelanggan = User::factory()->pelanggan()->create();

        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($kasir)->get('/dashboard')->assertRedirect(route('kasir.dashboard'));
        $this->actingAs($pelanggan)->get('/dashboard')->assertRedirect(route('marketplace'));
    }

    public function test_api_admin_dashboard_hanya_untuk_admin(): void
    {
        $this->get('/api/admin/dashboard')->assertUnauthorized();

        $kasir = User::factory()->kasir()->create();
        $this->actingAs($kasir)->get('/api/admin/dashboard')->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/api/admin/dashboard')->assertOk();
    }
}
