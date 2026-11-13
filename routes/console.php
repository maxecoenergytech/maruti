<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

\Illuminate\Support\Facades\Schedule::command('quotations:expire')->dailyAt('00:05');
\Illuminate\Support\Facades\Schedule::command('app:backup-database')->dailyAt('02:00');
