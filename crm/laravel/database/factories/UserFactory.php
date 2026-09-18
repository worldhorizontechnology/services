<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'telegram_username' => fake()->unique()->userName(),
            'instagram_username' => fake()->unique()->userName(),
            'role' => fake()->randomElement(['admin', 'manager', 'executor', 'client']),
            'phone' => fake()->phoneNumber(),
            'telegram_id' => fake()->unique()->numberBetween(100000, 999999),
            'calendarId' => fake()->randomElement(['c_04f01fc0d6c3bbc095a8e690c414b57a48a5cc33f8e73e3f60550d38a61323d5@group.calendar.google.com',
            'c_08b677248164b3e1a1d8e5bc28b971bcc43013a484fdf74787fe70b1a2f0beac@group.calendar.google.com',
            'c_24e8997da65e8d76db35e2011794c73ebafbbdab7001832d69076419f021a973@group.calendar.google.com',
            'c_434bc1e5546d5c78f7f91fc8ccd71af05bf44382605ca7049d0de9623c6160b9@group.calendar.google.com']),
            'google_calendar_id' => fake()->optional()->randomElement([
                'c_04f01fc0d6c3bbc095a8e690c414b57a48a5cc33f8e73e3f60550d38a61323d5@group.calendar.google.com',
                'c_08b677248164b3e1a1d8e5bc28b971bcc43013a484fdf74787fe70b1a2f0beac@group.calendar.google.com',
            ]),
            'instagram_id' => fake()->unique()->numberBetween(100000, 999999),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
