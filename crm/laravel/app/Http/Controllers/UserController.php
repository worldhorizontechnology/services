<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('services')
            ->withCount(['assignments' => function ($query) {
                $query->where('status', '!=', 'done');
            }])
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function show(User $user): Response
    {
        $user->load('services:id,name,price');

        $assignments = $user->assignments()
            ->with('order:id,order_number,status,total_price')
            ->latest()
            ->paginate(15)
            ->through(fn ($assignment) => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'due_date' => $assignment->due_date?->format('Y-m-d H:i'),
                'order' => $assignment->order ? [
                    'id' => $assignment->order->id,
                    'number' => $assignment->order->order_number,
                    'status' => $assignment->order->status,
                ] : null,
            ]);

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'telegram_id' => $user->telegram_id,
                'telegram_username' => $user->telegram_username,
                'calendarId' => $user->calendarId,
                'instagram_id' => $user->instagram_id,
                'instagram_username' => $user->instagram_username,
                'created_at' => $user->created_at?->format('d.m.Y'),
                'services' => $user->services->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => $s->price,
                ]),
            ],
            'assignments' => $assignments,
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load('services'),
            'services' => Service::select('id', 'name', 'price')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,manager,executor,client',
            'phone' => 'nullable|string|max:32',
            'telegram_id' => 'nullable|integer',
            'telegram_username' => 'nullable|string|max:255',
            'calendarId' => 'nullable|string|max:255',
            'instagram_id' => 'nullable|string|max:255',
            'instagram_username' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $user->update(Arr::except($validated, ['services']));

        if (array_key_exists('services', $validated)) {
            $user->services()->sync($validated['services'] ?? []);
        }

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back();
    }
}