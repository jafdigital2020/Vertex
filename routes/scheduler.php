<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('salary:activate')->daily();
Schedule::command('geofence:expire')->daily();
Schedule::command('attendance:mark-absent')->hourly();
