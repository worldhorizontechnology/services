<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel_id' => Channel::factory(),
            'name' => fake()->sentence(3),
            'promo_code' => strtoupper(fake()->bothify('PROMO-##??')),
            'budget' => fake()->randomFloat(2, 100, 5000),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ];
    }
}
