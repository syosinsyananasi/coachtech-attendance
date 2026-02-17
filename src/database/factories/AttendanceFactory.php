<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $date = $this->faker->date();
        $clockIn = Carbon::parse($date)->setTime(
            $this->faker->numberBetween(8, 10),
            $this->faker->randomElement([0, 15, 30, 45]),
            0
        );
        $clockOut = (clone $clockIn)->addHours($this->faker->numberBetween(8, 10));

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 3,
        ];
    }
}
