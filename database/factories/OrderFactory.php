<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20000, 250000);

        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('08##########'),
            'delivery_type' => 'pickup',
            'address' => null,
            'note' => fake()->optional()->sentence(6),
            'status' => fake()->randomElement([
                Order::STATUS_PENDING, Order::STATUS_PROCESSING,
                Order::STATUS_COMPLETED, Order::STATUS_REJECTED,
            ]),
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'payment_method' => 'manual',
        ];
    }

    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_type' => 'delivery',
            'address' => 'Jl. Merdeka No. 1, Bandung',
        ]);
    }

    public function pickup(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_type' => 'pickup',
            'address' => null,
        ]);
    }
}
