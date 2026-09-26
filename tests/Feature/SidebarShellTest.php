<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_menampilkan_chrome_dan_sidebar_pelanggan(): void
    {
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('store-topbar', false)
            ->assertSee('storeSidebarOffcanvas', false)
            ->assertSee('store-main', false)
            ->assertSee('store-footer', false)
            ->assertSee($user->name)
            ->assertSee('<span class="flex-grow-1">Keranjang</span>', false)
            ->assertSee('<span class="flex-grow-1">Pusat Informasi</span>', false)
            ->assertSee('<span class="flex-grow-1">Profil</span>', false)
            ->assertSee('<span class="flex-grow-1">Logout</span>', false)
            ->assertSee('Marketplace', false);
    }

    public function test_sidebar_pelanggan_statis_di_desktop_dan_offcanvas_di_mobile(): void
    {
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('app-sidebar offcanvas-lg offcanvas-start', false)
            ->assertSee('store-topbar--with-sidebar', false)
            ->assertSee('store-main--with-sidebar', false);
    }

    public function test_guest_bisa_melihat_sidebar_tanpa_profil(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('storeSidebarOffcanvas', false)
            ->assertSee('<span class="flex-grow-1">Pusat Informasi</span>', false)
            ->assertSee(route('login'), false);
    }

    public function test_menu_member_tampil_dengan_badge_coming_soon_dan_disabled(): void
    {
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('<span class="flex-grow-1">Member</span>', false)
            ->assertSee('Coming Soon', false)
            ->assertSee('<button type="button" class="nav-link" disabled aria-disabled="true">', false);
    }

    public function test_sidebar_kasir_hanya_hamburger(): void
    {
        $kasir = User::factory()->kasir()->create();

        $this->actingAs($kasir)
            ->get('/kasir/dashboard')
            ->assertOk()
            ->assertSee('app-sidebar--drawer', false)
            ->assertSee('sidebar-toggle--always', false)
            ->assertSee('app-main--no-sidebar', false)
            ->assertSee('Coming Soon', false)
            ->assertDontSee('offcanvas-lg', false);
    }

    public function test_sidebar_admin_menampilkan_menu_member(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('<span class="flex-grow-1">Member</span>', false)
            ->assertSee('Coming Soon', false)
            ->assertDontSee('app-sidebar--drawer', false);
    }
}
