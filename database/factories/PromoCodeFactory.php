<?php

namespace Database\Factories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromoCode>
 */
class PromoCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('PROMO###'),
            'type' => PromoCode::TYPE_PERCENT,
            'value' => fake()->randomElement([5, 10, 15, 20]),
            'min_order' => 0,
            'is_active' => true,
            'usage_limit' => null,
            'used_count' => 0,
        ];
    }
}
