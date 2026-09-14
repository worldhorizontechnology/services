<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'services' => Service::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:admin,manager,executor,client',
            'phone' => 'nullable|string|max:32',
            'telegram_id' => 'nullable|integer',
            'telegram_username' => 'nullable|string|max:255',
            'calendarId' => 'nullable|string|max:255',
            'instagram_id' => 'nullable|string|max:255',
            'instagram_username' => 'nullable|string|max:255',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'telegram_id' => $validated['telegram_id'] ?? null,
            'telegram_username' => $validated['telegram_username'] ?? null,
            'calendarId' => $validated['calendarId'] ?? null,
            'instagram_id' => $validated['instagram_id'] ?? null,
            'instagram_username' => $validated['instagram_username'] ?? null,
        ]);

        if (!empty($validated['services'])) {
            $user->services()->sync($validated['services']);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
