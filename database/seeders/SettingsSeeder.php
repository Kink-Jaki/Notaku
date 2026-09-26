<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate([], [
            'brand_name' => 'Notaku',
            'logo_path' => null,
            'favicon_path' => null,
            'color_primary' => '#4F46E5',
            'color_primary_dark' => '#4338CA',
            'color_success' => '#10B981',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
        ]);
    }
}
