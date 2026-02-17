<?php

namespace Database\Factories;

use App\Models\Rest;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'rest_start' => null,
            'rest_end' => null,
        ];
    }
}
