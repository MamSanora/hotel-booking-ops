<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:process-night-audit')->dailyAt('02:00');

// Must run AFTER process-night-audit so no-shows are stamped before the feedback loop reads them.
Schedule::command('app:optimize-overbooking')->dailyAt('02:05');
