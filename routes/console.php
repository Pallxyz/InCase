<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
// Pengingat "siapkan barangmu": pagi (sebelum berangkat) & malam (menyiapkan besok).
Schedule::command('incase:send-item-reminders')->dailyAt('06:00');
Schedule::command('incase:send-item-reminders')->dailyAt('19:00');