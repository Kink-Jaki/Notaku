<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_uses_seeded_transaction_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@posapp.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('skeleton-card')
            ->assertDontSee('TRX-DEMO-001')
            ->assertDontSee('TRX-DEMO-002');

        $this->actingAs($admin)
            ->get(route('api.admin.dashboard'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertSee('TRX-DEMO-001')
            ->assertSee('TRX-DEMO-002');
    }

    public function test_admin_dashboard_top_spender_tierlist_ranks_users_by_total_spent(): void
    {
        $admin = User::factory()->admin()->create();
        $topSpender = User::factory()->pelanggan()->create(['name' => 'Siti Spender']);
        $secondSpender = User::factory()->pelanggan()->create(['name' => 'Budi Halo']);

        Order::factory()->for($topSpender)->create([
            'status' => Order::STATUS_COMPLETED, 'subtotal' => 500_000, 'total' => 500_000,
        ]);
        Order::factory()->for($topSpender)->create([
            'status' => Order::STATUS_COMPLETED, 'subtotal' => 100_000, 'total' => 100_000,
        ]);
        Order::factory()->for($secondSpender)->create([
            'status' => Order::STATUS_COMPLETED, 'subtotal' => 300_000, 'total' => 300_000,
        ]);
        Order::factory()->create([
            'user_id' => User::factory()->pelanggan()->create()->id,
            'status' => Order::STATUS_PENDING,
            'subtotal' => 900_000,
            'total' => 900_000,
        ]);

        $spenders = $this->actingAs($admin)
            ->get(route('api.admin.dashboard'))
            ->assertOk()
            ->json('data.topSpenders');

        $this->assertCount(2, $spenders);
        $this->assertSame('Siti Spender', $spenders[0]['name']);
        $this->assertSame(1, $spenders[0]['rank']);
        $this->assertSame('S', $spenders[0]['tier']);
        $this->assertSame(600_000, $spenders[0]['total']);
        $this->assertSame(2, $spenders[0]['orders']);
        $this->assertSame('Budi Halo', $spenders[1]['name']);
        $this->assertSame(2, $spenders[1]['rank']);
        $this->assertSame('A', $spenders[1]['tier']);
        $this->assertSame(300_000, $spenders[1]['total']);
    }

    public function test_cashier_receipt_uses_transaction_type_stored_in_database(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('email', 'kasir@posapp.test')->firstOrFail();

        $this->actingAs($cashier)
            ->get(route('kasir.transaksi', 'TRX-DEMO-002'))
            ->assertOk()
            ->assertSee('Online')
            ->assertSee('QRIS');
    }
}
