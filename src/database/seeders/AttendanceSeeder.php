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

        $startOfMonth = Carbon::create(2026, 1, 1);
        $endOfMonth = Carbon::create(2026, 1, 31);
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

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
