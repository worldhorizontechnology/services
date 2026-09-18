<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $channels = Channel::factory(3)->create();
        $campaigns = Campaign::factory(5)->create([
            'channel_id' => fn () => $channels->random()->id,
        ]);

        $services = Service::factory(6)->create();
        $masters = User::factory(5)->state([
            'role' => 'executor',
            'is_active' => true,
        ])->create();

        foreach ($services as $service) {
            $service->executors()->sync(
                $masters->random(fake()->numberBetween(1, min(3, $masters->count())))->pluck('id')->all()
            );
        }

        $customers = Customer::factory(12)->create([
            'channel_id' => fn () => $channels->random()->id,
            'campaign_id' => fn () => $campaigns->random()->id,
        ]);

        foreach ($customers as $customer) {
            $order = Order::factory()->create([
                'customer_id' => $customer->id,
                'campaign_id' => $campaigns->random()->id,
            ]);

            $selectedServices = $services->random(fake()->numberBetween(1, min(3, $services->count())));
            foreach ($selectedServices as $service) {
                $quantity = fake()->numberBetween(1, 2);
                $order->services()->attach($service->id, [
                    'quantity' => $quantity,
                    'price' => $service->price,
                ]);
            }

            $executor = $order->services()->with('executors')->get()
                ->flatMap->executors
                ->unique('id')
                ->first();

            if ($executor) {
                Assignment::factory()->create([
                    'order_id' => $order->id,
                    'executor_id' => $executor->id,
                ]);
            }

            Payment::factory()->create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
            ]);
        }

        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'role' => 'admin',
            'google_calendar_id' => null,
        ]);
    }
}
