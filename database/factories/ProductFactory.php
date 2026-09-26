<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Nasi Goreng Spesial', 'Ayam Geprek', 'Mie Goreng Jawa', 'Bakso Sapi',
            'Es Teh Manis', 'Es Jeruk', 'Kopi Susu Gula Aren', 'Air Mineral 600ml',
            'Kentang Goreng', 'Pisang Goreng', 'Cireng Bumbu Rujak', 'Sosis Goreng',
            'Beras Premium 5kg', 'Minyak Goreng 1L', 'Gula Pasir 1kg', 'Telur Ayam 1kg',
        ];

        return [
            'name' => fake()->unique()->randomElement($names),
            'description' => fake()->sentence(8),
            'price' => fake()->randomElement([5000, 7000, 8000, 10000, 12000, 15000, 18000, 22000, 28000, 78000]),
            'stock' => fake()->numberBetween(0, 60),
            'image' => null,
            'is_active' => true,
        ];
    }
}
