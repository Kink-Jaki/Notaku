<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'qty' => fake()->numberBetween(1, 5),
        ];
    }
}
