<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $calendarId = $user->google_calendar_id ?: $user->calendarId;

        return Inertia::render('Dashboard', [
            'calendar' => $calendarId ? [
                'id' => $calendarId,
                'url' => 'https://calendar.google.com/calendar/u/0/r?cid=' . rawurlencode($calendarId),
            ] : null,
        ]);
    }
}