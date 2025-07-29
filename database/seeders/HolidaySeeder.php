<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $holidays = [
            // Regular Holidays
            [
                'name' => 'New Year\'s Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '01-01',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Maundy Thursday',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '04-06', // Example for a fixed date
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Good Friday',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '04-07', // Example for a fixed date
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Labor Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '05-01',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Independence Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '06-12',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'National Heroes Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '08-29', // Example for a fixed date
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Eid al-Fitr',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => null, // Movable holiday
                'date' => '2025-04-25', // Example for a movable date
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Eid al-Adha',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => null, // Movable holiday
                'date' => '2025-06-15', // Example for a movable date
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Bonifacio Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '11-30',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Christmas Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '12-25',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Rizal Day',
                'type' => 'regular',
                'recurring' => true,
                'month_day' => '12-30',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],

            // Special (Non-working) Holidays
            [
                'name' => 'Chinese New Year',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '02-10', // Example for a fixed date
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'EDSA People Power Revolution Anniversary',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '02-25',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Black Saturday',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '04-07', // Example for a fixed date
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Ninoy Aquino Day',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '08-21',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'All Saints\' Day',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '11-01',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'All Souls\' Day',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '11-02',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Feast of the Immaculate Conception of the Blessed Virgin Mary',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '12-08',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Christmas Eve',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '12-24',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'New Year\'s Eve',
                'type' => 'special-non-working',
                'recurring' => true,
                'month_day' => '12-31',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],

            // Special (Working) Holidays
            [
                'name' => 'People Power Anniversary',
                'type' => 'special-working',
                'recurring' => true,
                'month_day' => '02-25',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
            [
                'name' => 'Ninoy Aquino Day',
                'type' => 'special-working',
                'recurring' => true,
                'month_day' => '08-21',
                'date' => null,
                'is_paid' => true,
                'status' => 'active',
                'tenant_id' => 1,
            ],
        ];

        // Insert the holidays into the database
        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
