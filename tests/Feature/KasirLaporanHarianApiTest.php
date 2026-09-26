<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirLaporanHarianApiTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        $kasir = User::factory()->kasir()->create();
        $kasir->email_verified_at = now();
        $kasir->save();

        return $kasir;
    }

    public function test_api_mengembalikan_structure_json_benar(): void
    {
        $kasir = $this->kasir();

        // create a transaction today
        Transaction::factory()->create([
            'user_id' => $kasir->id,
            'total' => 50000,
            'status' => 'selesai',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($kasir)
            ->getJson(route('api.kasir.laporan-harian', ['tanggal' => now()->format('Y-m-d')]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tanggal',
                    'tanggal_label',
                    'penjualan_harian',
                    'stat_cards' => [
                        '*' => ['label', 'value', 'icon', 'modifier'],
                    ],
                    'metode' => ['count', 'total', 'rows' => [
                        ['nama', 'trx', 'total', 'total_formatted', 'icon', 'badge', 'persen'],
                    ]],
                    'rincian' => ['count', 'total', 'rows' => [
                        ['jam', 'id', 'jenis', 'metode', 'total', 'total_formatted', 'kasir'],
                    ]],
                    'ringkasan' => ['tanggal', 'items' => [['label', 'value', 'icon']]],
                ],
            ]);

        $data = $response->json('data');
        $this->assertTrue($data['metode']['count'] >= 1);
        $this->assertStringStartsWith('Rp ', $data['stat_cards'][0]['value']);
    }

    public function test_api_filter_kasir_id_bekerja(): void
    {
        $kasir1 = $this->kasir();
        $kasir2 = User::factory()->kasir()->create();
        $kasir2->email_verified_at = now();
        $kasir2->save();

        Transaction::factory()->create([
            'user_id' => $kasir1->id,
            'total' => 50000,
            'status' => 'selesai',
            'created_at' => now(),
        ]);
        Transaction::factory()->create([
            'user_id' => $kasir2->id,
            'total' => 70000,
            'status' => 'selesai',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($kasir1)
            ->getJson(route('api.kasir.laporan-harian', ['tanggal' => now()->format('Y-m-d'), 'kasir_id' => $kasir1->id]));

        $response->assertOk();
        $data = $response->json('data');
        // only kasir1's transaction
        $this->assertEquals(1, $data['metode']['count']);
    }
}
