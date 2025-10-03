<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('salary:activate')->daily();
Schedule::command('geofence:expire')->daily();
Schedule::command('attendance:mark-absent')->hourly();
Schedule::command('leave:convert-to-cash')->yearlyOn(12, 31, '00:00');
Schedule::command('zkteco:sync')->everyMinute();          // polling for local devices
Schedule::command('attendance:consolidate')->everyTenMinutes(); // dispatch consolidate job (or call command to dispatch job)
Schedule::command('attendance:consolidate --date=' . now('Asia/Manila')->subDay()->toDateString())->dailyAt('23:55');
Schedule::command('invoices:generate')->dailyAt('09:00');
Schedule::command('invoices:generate-monthly-overage')
    ->monthlyOn(1, '09:00')  // Run on 1st day of every month at 9:00 AM
    ->withoutOverlapping()   // Prevent multiple instances running
    ->runInBackground();     // Run in background to avoid blocking

// ZKTeco Direct Device Sync
Schedule::command('zkteco:sync')->everyMinute();

// ✅ NEW: BioTime Real-time Sync (every 2 minutes for real-time feel)
Schedule::command('biotime:sync --minutes=5')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// ✅ NEW: BioTime Full Sync (every 30 minutes to catch missed records)
Schedule::command('biotime:sync --minutes=60')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// ✅ NEW: BioTime Daily Full Sync (catch everything from yesterday)
Schedule::command('biotime:sync --minutes=1440') // 24 hours
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->runInBackground();

// Existing attendance processing
Schedule::command('attendance:consolidate')->everyTenMinutes();
Schedule::command('attendance:consolidate --date=' . now('Asia/Manila')->subDay()->toDateString())->dailyAt('23:55');
Schedule::command('invoices:generate')->dailyAt('09:00');
Schedule::command('invoices:generate-monthly-overage')
    ->monthlyOn(1, '09:00')
    ->withoutOverlapping()
    ->runInBackground();
