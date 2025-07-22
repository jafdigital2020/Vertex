<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('salary:activate')->daily();
Schedule::command('geofence:expire')->daily();
Schedule::command('attendance:mark-absent')->hourly();
Schedule::command('leave:convert-to-cash')->yearlyOn(12, 31, '00:00');
