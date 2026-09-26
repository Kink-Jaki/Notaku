<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingsSeeder::class);

        User::factory()->create([
            'name' => 'Andi Pratama',
            'email' => 'admin@posapp.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Siti Rahmawati',
            'email' => 'kasir@posapp.test',
            'password' => 'password',
            'role' => User::ROLE_KASIR,
        ]);

        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'pelanggan@posapp.test',
            'password' => 'password',
            'role' => User::ROLE_PELANGGAN,
        ]);

        $categories = [
            'Makanan' => ['Nasi Goreng Spesial', 'Ayam Geprek', 'Mie Goreng Jawa', 'Bakso Sapi'],
            'Minuman' => ['Es Teh Manis', 'Es Jeruk', 'Kopi Susu Gula Aren', 'Air Mineral 600ml'],
            'Snack' => ['Kentang Goreng', 'Pisang Goreng', 'Cireng Bumbu Rujak', 'Sosis Goreng'],
            'Sembako' => ['Beras Premium 5kg', 'Minyak Goreng 1L', 'Gula Pasir 1kg', 'Telur Ayam 1kg'],
        ];

        $pricesWithStock = [
            'Nasi Goreng Spesial' => [22000, 24],
            'Ayam Geprek' => [18000, 18],
            'Mie Goreng Jawa' => [18000, 15],
            'Bakso Sapi' => [22000, 0],
            'Es Teh Manis' => [5000, 40],
            'Es Jeruk' => [7000, 32],
            'Kopi Susu Gula Aren' => [15000, 12],
            'Air Mineral 600ml' => [4000, 60],
            'Kentang Goreng' => [10000, 0],
            'Pisang Goreng' => [8000, 25],
            'Cireng Bumbu Rujak' => [12000, 20],
            'Sosis Goreng' => [10000, 30],
            'Beras Premium 5kg' => [78000, 10],
            'Minyak Goreng 1L' => [22000, 15],
            'Gula Pasir 1kg' => [18000, 0],
            'Telur Ayam 1kg' => [28000, 8],
        ];

        foreach ($categories as $categoryName => $productNames) {
            $category = Category::create([
                'name' => $categoryName,
                'slug' => str($categoryName)->slug(),
            ]);

            foreach ($productNames as $productName) {
                [$price, $stock] = $pricesWithStock[$productName];

                Product::create([
                    'category_id' => $category->id,
                    'name' => $productName,
                    'description' => 'Produk '.$productName.' berkualitas untuk kebutuhan harian Anda.',
                    'price' => $price,
                    'stock' => $stock,
                ]);
            }
        }

        PromoCode::create([
            'code' => 'RAMDISC10',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 10,
            'min_order' => 0,
            'is_active' => true,
            'usage_limit' => null,
            'used_count' => 0,
        ]);

        $this->call(DemoTransactionSeeder::class);
    }
}
