<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;

Schedule::command('salary:activate')->daily();
Schedule::command('geofence:expire')->daily();
Schedule::command('attendance:mark-absent')->hourly();
Schedule::command('leave:convert-to-cash')->yearlyOn(12, 31, '00:00');
Schedule::command('leaves:process-earned --type=monthly')->monthlyOn(1, '00:00');
Schedule::command('leaves:process-earned --type=annual')->yearlyOn(1, 1, '00:00');
Schedule::command('leaves:process-earned --type=monthly')
    ->monthlyOn(1, '00:45')->when(function () {
        return Carbon::now()->month % 2 === 1;
    })->description('Process every-other-month earned leaves');
