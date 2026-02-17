<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'reina.n@coachtech.com')->first();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $clockIn = (clone $date)->setTime(
                fake()->numberBetween(8, 10),
                fake()->randomElement([0, 15, 30, 45]),
                0
            );
            $clockOut = (clone $clockIn)->addHours(fake()->numberBetween(8, 10));

            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'status' => 3,
            ]);

            $restStart = (clone $clockIn)->setTime(12, 0, 0);
            $restEnd = (clone $restStart)->addHour();

            Rest::factory()->create([
                'attendance_id' => $attendance->id,
                'rest_start' => $restStart,
                'rest_end' => $restEnd,
            ]);
        }
    }
}
