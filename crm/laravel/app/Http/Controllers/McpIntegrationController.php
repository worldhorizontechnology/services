<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class McpIntegrationController extends Controller
{
    public function createCustomer(Request $request): JsonResponse
    {
        $this->authorizeMcp($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'channel_id' => ['nullable', 'integer', 'exists:channels,id'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'entry_point' => ['nullable', 'string', 'max:100'],
        ]);

        [$data['first_name'], $data['last_name']] = $this->splitName($data['name']);
        unset($data['name']);

        $customer = Customer::updateOrCreate(
            ['phone' => $data['phone']],
            $data
        );

        return response()->json($customer, $customer->wasRecentlyCreated ? 201 : 200);
    }

    public function createChannel(Request $request): JsonResponse
    {
        $this->authorizeMcp($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:online,offline'],
        ]);

        return response()->json(Channel::create($data), 201);
    }

    public function createCampaign(Request $request): JsonResponse
    {
        $this->authorizeMcp($request);
        $data = $request->validate([
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'name' => ['required', 'string', 'max:255'],
            'promo_code' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(Campaign::create($data), 201);
    }

    public function createAssignment(Request $request): JsonResponse
    {
        $this->authorizeMcp($request);
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'executor_id' => ['required', 'integer', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json(Assignment::create($data), 201);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $this->authorizeMcp($request);
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
        ]);

        $result = DB::transaction(function () use ($data): Order {
            $service = Service::findOrFail($data['service_id']);
            $quantity = (int) $data['quantity'];
            $discount = (float) ($data['discount_amount'] ?? 0);
            $unitPrice = (float) $service->price;

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'campaign_id' => $data['campaign_id'] ?? null,
                'discount_amount' => $discount,
                'total_amount' => max(0, ($unitPrice * $quantity) - $discount),
                'status' => $data['status'] ?? 'new',
                'payment_status' => $data['payment_status'] ?? 'unpaid',
            ]);
            $order->services()->attach($service->id, [
                'quantity' => $quantity,
                'price' => $unitPrice,
            ]);

            return $order->load('services');
        });

        return response()->json($result, 201);
    }

    public function createCompleteBooking(Request $request): JsonResponse
    {
        $this->authorizeMcp($request);
        $data = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:50'],
            'customer.channel_id' => ['nullable', 'integer', 'exists:channels,id'],
            'customer.campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'customer.entry_point' => ['nullable', 'string', 'max:100'],
            'order.campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'order.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'assignment.executor_id' => ['required', 'integer', 'exists:users,id'],
            'assignment.start_date' => ['required', 'date'],
        ]);

        $result = DB::transaction(function () use ($data): array {
            $customerData = $data['customer'];
            [$customerData['first_name'], $customerData['last_name']] = $this->splitName($customerData['name']);
            unset($customerData['name']);
            $customer = Customer::updateOrCreate(['phone' => $customerData['phone']], $customerData);

            $service = Service::findOrFail($data['service_id']);
            $discount = (float) ($data['order']['discount_amount'] ?? 0);
            $order = Order::create([
                'customer_id' => $customer->id,
                'campaign_id' => $data['order']['campaign_id'] ?? null,
                'discount_amount' => $discount,
                'total_amount' => max(0, (float) $service->price - $discount),
                'status' => 'new',
                'payment_status' => 'unpaid',
            ]);
            $order->services()->attach($service->id, ['quantity' => 1, 'price' => $service->price]);
            $assignment = Assignment::create([
                'order_id' => $order->id,
                'executor_id' => $data['assignment']['executor_id'],
                'start_date' => $data['assignment']['start_date'],
                'status' => 'pending',
            ]);

            return compact('customer', 'order', 'assignment');
        });

        return response()->json($result, 201);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0], $parts[1] ?? null];
    }

    private function authorizeMcp(Request $request): void
    {
        $expected = (string) config('services.mcp_token', env('LARAVEL_API_TOKEN', ''));
        $provided = (string) $request->bearerToken();

        if ($expected === '' || !hash_equals($expected, $provided)) {
            abort(401, 'Invalid MCP API token.');
        }
    }
}