<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\MarketingAnalyticsSnapshot;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $snapshot = MarketingAnalyticsSnapshot::aggregatePeriod(
        now()->subDay()->startOfDay(),
        now()->subDay()->endOfDay(),
        'daily'
    );

    $snapshot->notify(new TelegramNotification($snapshot));

})->dailyAt('01:00');

