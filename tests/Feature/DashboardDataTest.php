<?php

namespace Tests\Feature;

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
