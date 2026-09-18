<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Channel>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Instagram Direct Bot', 'Telegram Bot', 'Google Ads', 'Website']),
            'type' => fake()->randomElement(['online', 'offline']),
            'is_active' => true,
        ];
    }
}
