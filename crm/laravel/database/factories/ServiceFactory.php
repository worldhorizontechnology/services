<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Classic Massage',
                'Deep Tissue Massage',
                'Facial Treatment',
                'Manicure',
                'Hair Styling',
            ]) . ' ' . fake()->unique()->numberBetween(1, 999999),
            'price' => fake()->randomFloat(2, 25, 180),
            'is_active' => true,
        ];
    }
}
