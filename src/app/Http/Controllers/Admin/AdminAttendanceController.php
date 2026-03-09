<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\Rest;
use App\Models\User;
use App\Services\AttendanceTimeService;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $dateStr = request('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        $attendanceRecords = Attendance::with(['user', 'rests'])
            ->where('date', $date->format('Y-m-d'))
            ->get()
            ->keyBy('user_id');

        $attendances = User::all()->map(function ($user) use ($attendanceRecords) {
            $attendance = $attendanceRecords->get($user->id);

            if ($attendance && $attendance->clock_in) {
                $breakMinutes = AttendanceTimeService::calculateBreakMinutes($attendance);
                $totalMinutes = AttendanceTimeService::calculateTotalMinutes($attendance, $breakMinutes);

                return [
                    'id' => $attendance->id,
                    'user_name' => str_replace(' ', '', $user->name),
                    'clock_in' => $attendance->clock_in->format('H:i'),
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    'break_time' => AttendanceTimeService::formatMinutes($breakMinutes),
                    'total_time' => AttendanceTimeService::formatMinutes($totalMinutes),
                ];
            }

            return [
                'id' => $attendance ? $attendance->id : null,
                'user_id' => $user->id,
                'user_name' => str_replace(' ', '', $user->name),
                'clock_in' => '',
                'clock_out' => '',
                'break_time' => '',
                'total_time' => '',
            ];
        });

        $currentDate = $date->format('Y年n月j日');
        $currentDateFormatted = $date->format('Y/m/d');
        $currentDateValue = $date->format('Y-m-d');
        $prevDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.list', compact(
            'attendances', 'currentDate', 'currentDateFormatted', 'currentDateValue', 'prevDate', 'nextDate'
        ));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        $isPending = CorrectionRequest::where('attendance_id', $id)
            ->where('status', CorrectionRequest::STATUS_PENDING)
            ->exists();

        $year = $attendance->date->format('Y');
        $monthDay = $attendance->date->format('n月j日');

        $rests = $attendance->rests->map(function ($rest) {
            return [
                'start' => $rest->rest_start ? $rest->rest_start->format('H:i') : '',
                'end' => $rest->rest_end ? $rest->rest_end->format('H:i') : '',
            ];
        })->toArray();

        return view('admin.attendance.detail', compact('attendance', 'year', 'monthDay', 'rests', 'isPending'));
    }

    public function update(AttendanceDetailRequest $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        $date = $attendance->date->format('Y-m-d');

        $attendance->update([
            'clock_in' => Carbon::parse($date . ' ' . $request->input('clock_in')),
            'clock_out' => Carbon::parse($date . ' ' . $request->input('clock_out')),
        ]);

        $rests = $request->input('rests', []);
        $originalRests = $attendance->rests;

        foreach ($rests as $index => $restData) {
            $isEmpty = empty($restData['start']) && empty($restData['end']);
            $hasOriginal = isset($originalRests[$index]);

            if ($isEmpty && $hasOriginal) {
                $originalRests[$index]->delete();
                continue;
            }

            if ($isEmpty) {
                continue;
            }

            $restStart = !empty($restData['start']) ? Carbon::parse($date . ' ' . $restData['start']) : null;
            $restEnd = !empty($restData['end']) ? Carbon::parse($date . ' ' . $restData['end']) : null;

            if ($hasOriginal) {
                $originalRests[$index]->update([
                    'rest_start' => $restStart,
                    'rest_end' => $restEnd,
                ]);
            } else {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start' => $restStart,
                    'rest_end' => $restEnd,
                ]);
            }
        }

        return redirect()->route('admin.attendance.show', $id);
    }
}
