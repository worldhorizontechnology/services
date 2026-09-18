<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'campaign_id' => null,
            'status' => fake()->randomElement(['new', 'pending', 'in_progress', 'completed', 'cancelled']),
            'payment_status' => fake()->randomElement(['unpaid', 'partial', 'paid']),
            'discount_amount' => fake()->randomFloat(2, 0, 25),
            'total_amount' => fake()->randomFloat(2, 25, 500),
        ];
    }
}
