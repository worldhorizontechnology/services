<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Orders/Index', [
            'orders' => Order::with(['customer', 'services'])->latest()->paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Orders/Create', [
            'customers' => Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'status' => ['required', 'string', 'in:new,in_progress,completed,cancelled'],
            'payment_status' => ['required', 'string', 'in:unpaid,paid,refunded'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data) {
            $services = Service::whereIn('id', array_column($data['services'], 'service_id'))->get()->keyBy('id');
            $discount = (float) ($data['discount_amount'] ?? 0);
            $total = 0;
            $pivotData = [];
            foreach ($data['services'] as $line) {
                $service = $services->get($line['service_id']);
                $unitPrice = (float) $service->price;
                $total += $unitPrice * (int) $line['quantity'];
                $pivotData[$line['service_id']] = ['quantity' => $line['quantity'], 'price' => $unitPrice];
            }

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'campaign_id' => $data['campaign_id'] ?? null,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'discount_amount' => $discount,
                'total_amount' => max(0, $total - $discount),
            ]);

            $order->services()->attach($pivotData);
        });

        return redirect()->route('orders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): Response
    {
        return Inertia::render('Orders/Show', [
            'order' => $order->load(['customer', 'campaign', 'services', 'payments', 'assignments.executor']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order): Response
    {
        return Inertia::render('Orders/Edit', [
            'order' => $order->load('services'),
            'customers' => Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'status' => ['required', 'string', 'in:new,in_progress,completed,cancelled'],
            'payment_status' => ['required', 'string', 'in:unpaid,paid,refunded'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data, $order) {
            $services = Service::whereIn('id', array_column($data['services'], 'service_id'))->get()->keyBy('id');
            $discount = (float) ($data['discount_amount'] ?? 0);
            $total = 0;
            $pivotData = [];
            foreach ($data['services'] as $line) {
                $service = $services->get($line['service_id']);
                $unitPrice = (float) $service->price;
                $total += $unitPrice * (int) $line['quantity'];
                $pivotData[$line['service_id']] = ['quantity' => $line['quantity'], 'price' => $unitPrice];
            }

            $order->update([
                'customer_id' => $data['customer_id'],
                'campaign_id' => $data['campaign_id'] ?? null,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'discount_amount' => $discount,
                'total_amount' => max(0, $total - $discount),
            ]);

            $order->services()->sync($pivotData);
        });

        return redirect()->route('orders.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index');
    }
}
