<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Channel;
use App\Models\Campaign;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Customers/Index', ['customers' => Customer::with(['channel', 'campaign'])->latest()->paginate(20)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Customers/Create', [
            'channels' => Channel::orderBy('name')->get(['id', 'name']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'channel_id' => ['nullable', 'exists:channels,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'entry_point' => ['nullable', 'string', 'max:100'],
        ]);
        [$data['first_name'], $data['last_name']] = $this->splitName($data['name']);
        unset($data['name']);
        Customer::create($data);

        return redirect()->route('customers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return Inertia::render('Customers/Show', ['customer' => $customer->load('orders')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'channels' => Channel::orderBy('name')->get(['id', 'name']),
            'campaigns' => Campaign::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'channel_id' => ['nullable', 'exists:channels,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'entry_point' => ['nullable', 'string', 'max:100'],
        ]));

        return redirect()->route('customers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index');
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0], $parts[1] ?? null];
    }
}
