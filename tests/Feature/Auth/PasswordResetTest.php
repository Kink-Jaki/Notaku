<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_forgot_password_tidak_lagi_tersedia(): void
    {
        $this->get('/forgot-password')->assertNotFound();
    }

    public function test_admin_bisa_membuat_tautan_reset_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.user-role.reset-link', $user));

        $response->assertOk()
            ->assertJsonPath('email', $user->email)
            ->assertJsonStructure(['message', 'url', 'email', 'expires_in_minutes']);

        $this->assertNotSame('', $this->tokenFrom($response->json('url')));
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_tautan_reset_hanya_bisa_dibuat_oleh_admin(): void
    {
        $kasir = User::factory()->kasir()->create();
        $user = User::factory()->create();

        $this->actingAs($kasir)
            ->postJson(route('admin.user-role.reset-link', $user))
            ->assertForbidden();

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_membuat_tautan_baru_membatalkan_tautan_lama(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $oldToken = $this->tokenFrom(
            $this->actingAs($admin)->postJson(route('admin.user-role.reset-link', $user))->json('url')
        );

        $this->actingAs($admin)->postJson(route('admin.user-role.reset-link', $user))->assertOk();

        $this->post('/logout');

        $this->post('/reset-password', [
            'token' => $oldToken,
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_bisa_diubah_lewat_tautan_yang_dibuat_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $token = $this->tokenFrom(
            $this->actingAs($admin)
                ->postJson(route('admin.user-role.reset-link', $user))
                ->json('url')
        );

        // Tautan dibuka oleh user yang belum login, jadi admin harus keluar dulu
        // agar middleware guest tidak mengarahkan balik ke dashboard.
        $this->post('/logout');

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Simpan Password Baru');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    /**
     * Ambil token dari URL reset agar test tidak bergantung pada format link.
     */
    private function tokenFrom(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return basename($path);
    }
}
