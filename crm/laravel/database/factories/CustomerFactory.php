<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->unique()->e164PhoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'channel_id' => Channel::factory(),
            'campaign_id' => Campaign::factory(),
            'entry_point' => fake()->randomElement(['direct_message', 'call', 'promo', 'office']),
            'status' => fake()->randomElement(['new', 'active', 'repeat']),
            'telegram_id' => (string) fake()->unique()->numberBetween(100000, 999999999),
            'instagram_id' => (string) fake()->unique()->numberBetween(100000, 999999999),
            'whatsapp' => fake()->e164PhoneNumber(),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}