<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'google_calendar_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('google_calendar_id')->nullable();
            });
        }

        DB::table('users')
            ->whereNull('google_calendar_id')
            ->whereNotNull('calendarId')
            ->update([
                'google_calendar_id' => DB::raw('calendarId'),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'google_calendar_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('google_calendar_id');
            });
        }
    }
};