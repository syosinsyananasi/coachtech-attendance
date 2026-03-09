<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceTimeService
{
    public static function calculateBreakMinutes(Attendance $attendance): int
    {
        return $attendance->rests->sum(function ($rest) {
            if ($rest->rest_start && $rest->rest_end) {
                return $rest->rest_start->diffInMinutes($rest->rest_end);
            }
            return 0;
        });
    }

    public static function calculateTotalMinutes(Attendance $attendance, int $breakMinutes): int
    {
        if ($attendance->clock_in && $attendance->clock_out) {
            return $attendance->clock_in->diffInMinutes($attendance->clock_out) - $breakMinutes;
        }
        return 0;
    }

    public static function formatMinutes(int $minutes): string
    {
        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
