<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('salary:activate')->daily();
Schedule::command('geofence:expire')->daily();
Schedule::command('attendance:mark-absent')->hourly();
Schedule::command('leave:convert-to-cash')->yearlyOn(12, 31, '00:00');
Schedule::command('zkteco:sync')->everyMinute();          // polling for local devices
Schedule::command('attendance:consolidate')->everyTenMinutes(); // dispatch consolidate job (or call command to dispatch job)
Schedule::command('attendance:consolidate --date=' . now('Asia/Manila')->subDay()->toDateString())->dailyAt('23:55');
