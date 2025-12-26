<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultShiftScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates default shift schedules for all existing tenants.
     * Shifts are tenant-specific to ensure proper data isolation.
     */
    public function run(): void
    {
        // Get all tenant IDs
        $tenants = DB::table('tenants')->pluck('id')->toArray();

        if (empty($tenants)) {
            echo "\n⚠ Warning: No tenants found in the database.\n";
            echo "Please create at least one tenant before running this seeder.\n";
            return;
        }

        $shiftTemplates = [
            [
                'name' => 'Morning Shift (8:00 AM - 5:00 PM)',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 9,
                'grace_period' => 15,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => false,
                'notes' => 'Standard morning shift with 1 hour lunch break',
            ],
            [
                'name' => 'Day Shift (9:00 AM - 6:00 PM)',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 9,
                'grace_period' => 15,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => false,
                'notes' => 'Standard day shift with 1 hour lunch break',
            ],
            [
                'name' => 'Afternoon Shift (1:00 PM - 10:00 PM)',
                'start_time' => '13:00:00',
                'end_time' => '22:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 9,
                'grace_period' => 15,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => false,
                'notes' => 'Afternoon to evening shift with 1 hour dinner break',
            ],
            [
                'name' => 'Night Shift (10:00 PM - 7:00 AM)',
                'start_time' => '22:00:00',
                'end_time' => '07:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 9,
                'grace_period' => 15,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => true,
                'notes' => 'Overnight shift with 1 hour break, extra hours allowed',
            ],
            [
                'name' => 'Early Morning Shift (6:00 AM - 3:00 PM)',
                'start_time' => '06:00:00',
                'end_time' => '15:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 9,
                'grace_period' => 15,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => false,
                'notes' => 'Early morning shift with 1 hour lunch break',
            ],
            [
                'name' => 'Mid-Day Shift (11:00 AM - 8:00 PM)',
                'start_time' => '11:00:00',
                'end_time' => '20:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 9,
                'grace_period' => 15,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => false,
                'notes' => 'Mid-day shift with 1 hour break',
            ],
            [
                'name' => 'Flexible Shift (8:00 AM - 5:00 PM)',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_minutes' => 60,
                'maximum_allowed_hours' => 10,
                'grace_period' => 30,
                'is_flexible' => true,
                'allowed_minutes_before_clock_in' => 60,
                'allow_extra_hours' => true,
                'notes' => 'Flexible shift with more lenient clock-in/out times',
            ],
            [
                'name' => 'Part-Time Morning (8:00 AM - 12:00 PM)',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'break_minutes' => 0,
                'maximum_allowed_hours' => 4,
                'grace_period' => 10,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 15,
                'allow_extra_hours' => false,
                'notes' => 'Part-time morning shift, no break',
            ],
            [
                'name' => 'Part-Time Afternoon (1:00 PM - 5:00 PM)',
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'break_minutes' => 0,
                'maximum_allowed_hours' => 4,
                'grace_period' => 10,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 15,
                'allow_extra_hours' => false,
                'notes' => 'Part-time afternoon shift, no break',
            ],
            [
                'name' => '12-Hour Day Shift (7:00 AM - 7:00 PM)',
                'start_time' => '07:00:00',
                'end_time' => '19:00:00',
                'break_minutes' => 90,
                'maximum_allowed_hours' => 12,
                'grace_period' => 20,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => true,
                'notes' => 'Extended 12-hour day shift with 1.5 hour total breaks',
            ],
            [
                'name' => '12-Hour Night Shift (7:00 PM - 7:00 AM)',
                'start_time' => '19:00:00',
                'end_time' => '07:00:00',
                'break_minutes' => 90,
                'maximum_allowed_hours' => 12,
                'grace_period' => 20,
                'is_flexible' => false,
                'allowed_minutes_before_clock_in' => 30,
                'allow_extra_hours' => true,
                'notes' => 'Extended 12-hour night shift with 1.5 hour total breaks',
            ],
        ];

        $now = Carbon::now();
        $totalInserted = 0;
        $totalSkipped = 0;

        // Try to get the first global user as the creator (usually admin)
        $globalUser = DB::table('global_users')->first();
        $createdByType = null;
        $createdById = null;

        if ($globalUser) {
            $createdByType = 'App\\Models\\GlobalUser';
            $createdById = $globalUser->id;
        } else {
            // Fallback: try to get the first user from the first tenant
            $firstUser = DB::table('users')->where('tenant_id', $tenants[0] ?? null)->first();
            if ($firstUser) {
                $createdByType = 'App\\Models\\User';
                $createdById = $firstUser->id;
            }
        }

        echo "\n📋 Creating default shifts for " . count($tenants) . " tenant(s)...\n\n";

        foreach ($tenants as $tenantId) {
            foreach ($shiftTemplates as $template) {
                $shift = $template;
                $shift['tenant_id'] = $tenantId;
                $shift['branch_id'] = null;
                $shift['created_at'] = $now;
                $shift['updated_at'] = $now;
                $shift['created_by_type'] = $createdByType;
                $shift['created_by_id'] = $createdById;
                $shift['updated_by_type'] = $createdByType;
                $shift['updated_by_id'] = $createdById;

                try {
                    // Check if shift already exists for this tenant
                    $exists = DB::table('shift_lists')
                        ->where('tenant_id', $tenantId)
                        ->where('name', $shift['name'])
                        ->exists();

                    if (!$exists) {
                        DB::table('shift_lists')->insert($shift);
                        $totalInserted++;
                    } else {
                        $totalSkipped++;
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "✗ Error inserting '{$shift['name']}': {$e->getMessage()}\n";
                    } else {
                        $totalSkipped++;
                    }
                }
            }
        }

        echo "✓ Successfully created {$totalInserted} shift schedule(s)!\n";
        if ($totalSkipped > 0) {
            echo "⊘ Skipped {$totalSkipped} duplicate shift(s).\n";
        }

        echo "\n📝 Available Shift Templates:\n";
        foreach ($shiftTemplates as $shift) {
            echo "  - {$shift['name']}\n";
        }
        echo "\n✅ Shifts are now available at: http://127.0.0.1:8000/shift-list\n";
    }
}
