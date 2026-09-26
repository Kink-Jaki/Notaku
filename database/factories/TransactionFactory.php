<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 350000);

        return [
            'transaction_number' => 'TRX-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'user_id' => User::factory()->kasir(),
            'customer_name' => fake()->optional()->name(),
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'payment_method' => 'tunai',
            'status' => 'selesai',
        ];
    }
}
