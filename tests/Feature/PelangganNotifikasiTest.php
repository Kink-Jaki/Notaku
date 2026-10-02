<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganNotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    private function kasir(): User
    {
        $kasir = User::factory()->kasir()->create();
        $kasir->email_verified_at = now();
        $kasir->save();

        return $kasir;
    }

    private function orderPending(User $pelanggan): Order
    {
        $product = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $order = Order::factory()->for($pelanggan)->create([
            'status' => Order::STATUS_PENDING,
            'subtotal' => 15000,
            'total' => 15000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 15000,
            'qty' => 1,
        ]);

        return $order;
    }

    public function test_menyetujui_pesanan_membuat_notifikasi_untuk_pelanggan(): void
    {
        $pelanggan = $this->pelanggan();
        $order = $this->orderPending($pelanggan);

        $this->actingAs($this->kasir())
            ->from(route('kasir.antrian'))
            ->post(route('kasir.antrian.approve', $order), ['payment_method' => 'tunai'])
            ->assertRedirect(route('kasir.antrian'));

        $this->assertDatabaseCount('notifications', 1);

        $notification = $pelanggan->notifications()->firstOrFail();

        $this->assertSame(User::class, $notification->notifiable_type);
        $this->assertSame('processing', $notification->data['status']);
        $this->assertSame('Pesanan Disetujui', $notification->data['title']);
        $this->assertStringContainsString($order->order_number, $notification->data['message']);
        $this->assertNull($notification->read_at);
    }

    public function test_menolak_pesanan_membuat_notifikasi_berisi_alasan(): void
    {
        $pelanggan = $this->pelanggan();
        $order = $this->orderPending($pelanggan);
        $reason = 'Stok barang habis';

        $this->actingAs($this->kasir())
            ->from(route('kasir.antrian'))
            ->post(route('kasir.antrian.reject', $order), ['reason' => $reason])
            ->assertRedirect(route('kasir.antrian'));

        $this->assertDatabaseCount('notifications', 1);

        $data = $pelanggan->notifications()->firstOrFail()->data;

        $this->assertSame('rejected', $data['status']);
        $this->assertSame('Pesanan Ditolak', $data['title']);
        $this->assertSame($reason, $data['rejected_reason']);
        $this->assertStringContainsString($reason, $data['message']);
    }

    public function test_membatalkan_pesanan_membuat_notifikasi(): void
    {
        $pelanggan = $this->pelanggan();
        $order = $this->orderPending($pelanggan);

        $this->actingAs($pelanggan)
            ->put(route('pelanggan.order.cancel', $order))
            ->assertRedirect(route('pelanggan.pesanan-saya'));

        $this->assertDatabaseCount('notifications', 1);

        $data = $pelanggan->notifications()->firstOrFail()->data;

        $this->assertSame('rejected', $data['status']);
        $this->assertStringContainsString($order->order_number, $data['message']);
    }

    public function test_api_notifikasi_hanya_milik_user_login(): void
    {
        $pelanggan = $this->pelanggan();
        $pelangganLain = $this->pelanggan();
        $order = $this->orderPending($pelanggan);
        $orderLain = $this->orderPending($pelangganLain);

        $order->forceFill(['status' => Order::STATUS_PROCESSING])->save();
        $orderLain->forceFill(['status' => Order::STATUS_PROCESSING])->save();

        $pelanggan->notify(new OrderStatusUpdated($order->refresh()));
        $pelangganLain->notify(new OrderStatusUpdated($orderLain->refresh()));

        $this->assertDatabaseCount('notifications', 2);

        $response = $this->actingAs($pelanggan)
            ->get(route('pelanggan.notifikasi.list'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 1);

        $notifications = $response->json('notifications');

        $this->assertCount(1, $notifications);
        $this->assertSame($pelanggan->notifications()->firstOrFail()->id, $notifications[0]['id']);
        $this->assertSame('processing', $notifications[0]['data']['status']);
        $this->assertArrayHasKey('read_at', $notifications[0]);
        $this->assertArrayHasKey('created_at', $notifications[0]);
    }

    public function test_tamu_dan_kasir_tidak_bisa_mengakses_api_notifikasi(): void
    {
        $this->get(route('pelanggan.notifikasi.list'))->assertUnauthorized();
        $this->post(route('pelanggan.notifikasi.read'))->assertUnauthorized();

        $kasir = $this->kasir();
        $this->actingAs($kasir)->get(route('pelanggan.notifikasi.list'))->assertForbidden();
        $this->actingAs($kasir)->post(route('pelanggan.notifikasi.read'))->assertForbidden();
    }

    public function test_tandai_semua_dibaca(): void
    {
        $pelanggan = $this->pelanggan();
        $order = $this->orderPending($pelanggan);
        $pelanggan->notify(new OrderStatusUpdated($order->refresh()));
        $pelanggan->notify(new OrderStatusUpdated($order->refresh()));

        $this->assertSame(2, $pelanggan->unreadNotifications()->count());

        $this->actingAs($pelanggan)
            ->post(route('pelanggan.notifikasi.read'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0);

        $this->assertSame(0, $pelanggan->fresh()->unreadNotifications()->count());
        $this->assertSame(0, $pelanggan->notifications()->whereNull('read_at')->count());
        $this->assertSame(2, $pelanggan->notifications()->whereNotNull('read_at')->count());
    }
}
