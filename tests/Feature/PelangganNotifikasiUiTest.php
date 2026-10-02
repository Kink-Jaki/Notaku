<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganNotifikasiUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_topbar_pelanggan_login_mempunyai_lonceng_notifikasi(): void
    {
        $pelanggan = User::factory()->pelanggan()->create();

        $response = $this->actingAs($pelanggan)
            ->get(route('marketplace'))
            ->assertOk();

        $html = (string) $response->getContent();

        $this->assertStringContainsString('id="notifBell"', $html);
        $this->assertStringContainsString('id="notifDropdown"', $html);
        $this->assertStringContainsString('data-notif-badge', $html);
        $this->assertStringContainsString('data-list-url="'.route('pelanggan.notifikasi.list').'"', $html);
        $this->assertStringContainsString('data-read-url="'.route('pelanggan.notifikasi.read').'"', $html);
        $this->assertStringContainsString('pollNotifikasi', $html);
    }

    public function test_tamu_tidak_menampilkan_lonceng_notifikasi(): void
    {
        $response = $this->get(route('marketplace'))->assertOk();

        $html = (string) $response->getContent();

        $this->assertStringNotContainsString('id="notifBell"', $html);
        $this->assertStringNotContainsString('id="notifDropdown"', $html);
        $this->assertStringNotContainsString('data-list-url', $html);
    }

    public function test_api_notifikasi_mengembalikan_struktur_yang_dipakai_frontend(): void
    {
        $pelanggan = User::factory()->pelanggan()->create();

        $order = Order::factory()->for($pelanggan)->create([
            'status' => Order::STATUS_PROCESSING,
        ]);

        $pelanggan->notify(new OrderStatusUpdated($order));

        $this->actingAs($pelanggan)
            ->get(route('pelanggan.notifikasi.list'))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'unread_count',
                'notifications' => [
                    [
                        'id',
                        'read_at',
                        'created_at',
                        'data' => ['order_number', 'status', 'title', 'message', 'url'],
                    ],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.data.status', Order::STATUS_PROCESSING);
    }
}
