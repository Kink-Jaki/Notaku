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
            ->assertSee('app-footer', false)
            ->assertSee($user->name)
            ->assertSee('<span class="app-sidebar__nav-text">Keranjang</span>', false)
            ->assertSee('<span class="app-sidebar__nav-text">Pusat Informasi</span>', false)
            ->assertSee('<span class="app-sidebar__nav-text">Profil</span>', false)
            ->assertSee('data-tooltip="Logout"', false)
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
            ->assertSee('<span class="app-sidebar__nav-text">Pusat Informasi</span>', false)
            ->assertSee(route('login'), false);
    }

    public function test_menu_member_tampil_dengan_badge_coming_soon_dan_disabled(): void
    {
        $user = User::factory()->pelanggan()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('<span class="app-sidebar__nav-text">Member</span>', false)
            ->assertSee('badge badge-soon', false)
            ->assertSee('>Soon', false)
            ->assertSee('<button type="button" class="nav-link" disabled aria-disabled="true"', false);
    }

    public function test_sidebar_kasir_statis_dan_hanya_bisa_diminimize(): void
    {
        $kasir = User::factory()->kasir()->create();

        $this->actingAs($kasir)
            ->get('/kasir/dashboard')
            ->assertOk()
            ->assertSee('app-sidebar offcanvas-lg offcanvas-start', false)
            ->assertSee('app-sidebar__nav-text', false)
            ->assertSee('sidebarCollapseBtn', false)
            ->assertDontSee('sidebar-toggle--always', false)
            ->assertDontSee('app-sidebar--drawer', false)
            ->assertDontSee('app-main--no-sidebar', false);
    }

    public function test_token_tema_diumumkan_tanpa_menimpa_blok_gelap(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/kasir/dashboard')->assertOk();

        // sidebarCss() sudah membawa bloknya sendiri; membungkusnya di :root
        // lain membuatnya jadi nesting dan mati diam-diam.
        $response->assertSee('[data-theme="dark"] {', false)
            ->assertSee(':root:not([data-theme="dark"]) {', false);

        $this->assertStringNotContainsString(
            '{'.PHP_EOL.'    :root {',
            $response->getContent(),
        );
    }

    public function test_sidebar_guest_menyembunyikan_cta_saat_diperkecil(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('app-sidebar__guest-cta', false)
            ->assertSee('app-sidebar__hint', false)
            ->assertSee('sidebarCollapseBtn', false);
    }

    public function test_sidebar_admin_menampilkan_menu_member(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('<span class="app-sidebar__nav-text">Member</span>', false)
            ->assertSee('badge badge-soon', false)
            ->assertDontSee('app-sidebar--drawer', false);
    }
}
