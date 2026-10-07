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

    public function test_panel_admin_memiliki_toggle_tema_terang_gelap(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('theme-toggle__icon--moon', false);
    }

    public function test_panel_admin_tidak_pakai_tombol_light_bootstrap(): void
    {
        $admin = User::factory()->admin()->create();

        // btn-light punya background putih hardcode dari Bootstrap, jadi blok
        // putih menyilaukan di mode gelap dan tidak pernah ikut berubah tema.
        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('btn btn-soft dropdown-toggle', false)
            ->assertDontSee('btn btn-light', false);
    }

    public function test_tema_diterapkan_sebelum_render_pertama(): void
    {
        $admin = User::factory()->admin()->create();

        // Tanpa script inline di <head>, data-theme baru di-set oleh bundle Vite
        // yang deferred — halaman akan berkedip terang dulu di mode gelap.
        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee("getItem('notaku-theme')", false)
            ->assertSee('prefers-color-scheme: dark', false);
    }

    public function test_token_abu_abu_bootstrap_ikut_berganti_tema(): void
    {
        // .form-text / .text-muted / .text-secondary mengambil warna dari
        // token Bootstrap sendiri (#6c757d statis). Tanpa bridge ini teksnya
        // tetap hitam di atas surface gelap.
        $css = (string) file_get_contents(resource_path('css/variables.css'));

        $this->assertStringContainsString('--bs-secondary-color: var(--color-text-subtle)', $css);
        $this->assertStringContainsString('--bs-body-secondary-color: var(--color-text-subtle)', $css);

        // Token harus ada di kedua tema; abu-abu terang #94A3B8 hanya 2.65:1
        // di atas putih sehingga tidak boleh dipakai untuk mode terang.
        $this->assertStringContainsString('--color-text-subtle: #64748B', $css);
        $this->assertStringContainsString('--color-text-subtle: #94A3B8', $css);
    }

    public function test_token_link_secondary_ikut_berganti_tema(): void
    {
        // .link-secondary (ikon Edit/Reset di kolom Aksi) mengambil warna dari
        // --bs-secondary-rgb. Kalau di-hardcode di layout.css, warnanya membeku
        // di abu-abu terang dan ikut hampir hitam di mode gelap.
        $variables = (string) file_get_contents(resource_path('css/variables.css'));
        $layout = (string) file_get_contents(resource_path('css/layout.css'));

        $this->assertStringContainsString('--bs-secondary-rgb: var(--color-text-subtle-rgb)', $variables);
        $this->assertStringContainsString('--color-text-subtle-rgb: 100, 116, 139', $variables);
        $this->assertStringContainsString('--color-text-subtle-rgb: 148, 163, 184', $variables);

        // Nilai angkanya tidak boleh ada di layout.css — itu yang membekukan.
        $this->assertStringNotContainsString('--bs-secondary-rgb:', $layout);
        $this->assertStringNotContainsString('100, 116, 139', $layout);
    }

    public function test_bootstrap_dimuat_sebelum_token_tema(): void
    {
        // Bridge token di variables.css hanya menang kalau Bootstrap lebih dulu,
        // karena keduanya :root dengan spesifisitas sama.
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertLessThan(
            strpos($css, 'variables.css'),
            strpos($css, 'bootstrap.css'),
            'bootstrap.css harus diimpor sebelum variables.css.',
        );
    }

    public function test_warna_sel_tabel_ikut_token_tema(): void
    {
        // Bootstrap menurunkan warna sel lewat .table { --bs-table-color:
        // var(--bs-emphasis-color) }. --bs-emphasis-color hanya di-override
        // untuk [data-bs-theme], yang tidak dipakai aplikasi ini — sehingga
        // nilainya tetap #000 dan tiap <td> polos tampil hitam di mode gelap.
        $layout = (string) file_get_contents(resource_path('css/layout.css'));

        $this->assertStringContainsString('--bs-emphasis-color: var(--color-text)', $layout);
    }
}
