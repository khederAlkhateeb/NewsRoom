<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SendWeeklyAdminReport;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
// Schedule the weekly admin report job to run every week
Schedule::job(new SendWeeklyAdminReport)->weekly();

// Schedule the article archiving command to run monthly
Schedule::command('articles:archive')->monthly();

// Schedule the article report command to run every Friday at 8:00 AM
Schedule::command('articles:report')->fridays()->at('08:00');