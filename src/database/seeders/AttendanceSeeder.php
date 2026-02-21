<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ja_JP');
        $users = User::all();

        $startDate = Carbon::create(2026, 1, 15);
        $endDate = Carbon::create(2026, 2, 20);
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($users as $user) {
            foreach ($period as $date) {
                if ($date->isWeekend()) {
                    continue;
                }

                $clockIn = (clone $date)->setTime(
                    $faker->numberBetween(8, 10),
                    $faker->randomElement([0, 15, 30, 45]),
                    0
                );
                $clockOut = (clone $clockIn)->addHours($faker->numberBetween(8, 10));

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
}
