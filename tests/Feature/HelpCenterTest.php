<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_pusat_bantuan_bisa_diakses_tanpa_login(): void
    {
        $this->get('/pusat-bantuan')
            ->assertOk()
            ->assertSee('Pusat Bantuan')
            ->assertSee('id="pusatBantuanAccordion"', false)
            ->assertSee('id="cara-memesan"', false)
            ->assertSee('id="barang-tidak-sesuai"', false)
            ->assertSee('id="lupa-password"', false)
            ->assertSee('id="hubungi-admin"', false);
    }

    public function test_tombol_helper_ada_di_layout_dan_mengarah_ke_topik(): void
    {
        $response = $this->get('/pusat-bantuan');

        $response->assertSee('class="helper-fab"', false);
        $response->assertSee('id="helperModal"', false);
        $response->assertSee('pusat-bantuan#cara-memesan', false);
        $response->assertSee('pusat-bantuan#barang-tidak-sesuai', false);
        $response->assertSee('pusat-bantuan#lupa-password', false);
        $response->assertSee('pusat-bantuan#hubungi-admin', false);
    }

    public function test_tombol_helper_muncul_di_halaman_publik(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('helper-fab', false);
    }

    public function test_topik_lupa_password_mengarah_ke_fitur_reset_breeze(): void
    {
        $this->get('/pusat-bantuan')
            ->assertOk()
            ->assertSee(route('password.request'), false);
    }
}
