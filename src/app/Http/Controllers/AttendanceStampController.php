<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceStampRequest;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceStampController extends Controller
{
    public function stamp()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->format('Y-m-d'))
            ->first();

        $status = $attendance ? $attendance->status : Attendance::STATUS_OFF;

        return view('attendance.index', compact('status'));
    }

    public function store(AttendanceStampRequest $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();
        $action = $request->input('action');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->format('Y-m-d'))
            ->first();

        if ($action === 'clock_in' && (!$attendance || $attendance->status === Attendance::STATUS_OFF)) {
            if ($attendance) {
                $attendance->update([
                    'clock_in' => $now,
                    'status' => Attendance::STATUS_WORKING,
                ]);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $today->format('Y-m-d'),
                    'clock_in' => $now,
                    'status' => Attendance::STATUS_WORKING,
                ]);
            }
        } elseif ($action === 'clock_out' && $attendance && $attendance->status === Attendance::STATUS_WORKING) {
            $attendance->update([
                'clock_out' => $now,
                'status' => Attendance::STATUS_FINISHED,
            ]);
        } elseif ($action === 'break_start' && $attendance && $attendance->status === Attendance::STATUS_WORKING) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start' => $now,
            ]);
            $attendance->update(['status' => Attendance::STATUS_ON_BREAK]);
        } elseif ($action === 'break_end' && $attendance && $attendance->status === Attendance::STATUS_ON_BREAK) {
            $rest = Rest::where('attendance_id', $attendance->id)
                ->whereNull('rest_end')
                ->latest()
                ->first();
            if ($rest) {
                $rest->update(['rest_end' => $now]);
            }
            $attendance->update(['status' => Attendance::STATUS_WORKING]);
        }

        return redirect()->route('attendance.stamp');
    }
}
