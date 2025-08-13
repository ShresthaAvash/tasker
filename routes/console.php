<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('schedule:run', function (Schedule $schedule) {
    $schedule->command('tasks:update-status')->daily();
})->purpose('Run the scheduled commands');