<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'executor_id' => User::factory()->state(['role' => 'executor']),
            'status' => fake()->randomElement(['pending', 'assigned', 'in_progress', 'done']),
            'start_date' => now()->addDays(fake()->numberBetween(1, 14)),
            'due_date' => now()->addDays(fake()->numberBetween(15, 30)),
        ];
    }
}
