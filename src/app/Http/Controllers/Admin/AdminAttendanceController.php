<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function list()
    {
        $dateStr = request('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        $attendances = Attendance::with(['user', 'rests'])
            ->where('date', $date->format('Y-m-d'))
            ->get()
            ->map(function ($attendance) {
                $breakMinutes = $attendance->rests->sum(function ($rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        return $rest->rest_start->diffInMinutes($rest->rest_end);
                    }
                    return 0;
                });
                $totalMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out) - $breakMinutes;
                }

                return [
                    'id' => $attendance->id,
                    'user_name' => $attendance->user->name,
                    'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    'break_time' => sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                    'total_time' => sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60),
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

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        $year = $attendance->date->format('Y');
        $monthDay = $attendance->date->format('n月j日');

        $rests = $attendance->rests->map(function ($rest) {
            return [
                'start' => $rest->rest_start ? $rest->rest_start->format('H:i') : '',
                'end' => $rest->rest_end ? $rest->rest_end->format('H:i') : '',
            ];
        })->toArray();

        return view('admin.attendance.detail', compact('attendance', 'year', 'monthDay', 'rests'));
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
            if (empty($restData['start']) && empty($restData['end'])) {
                continue;
            }
            $restStart = !empty($restData['start']) ? Carbon::parse($date . ' ' . $restData['start']) : null;
            $restEnd = !empty($restData['end']) ? Carbon::parse($date . ' ' . $restData['end']) : null;

            if (isset($originalRests[$index])) {
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

        return redirect('/admin/attendance/' . $id);
    }

    public function staffList()
    {
        $staffs = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return view('admin.staff.list', compact('staffs'));
    }

    public function staffAttendance($id)
    {
        $staff = User::findOrFail($id);
        $month = request('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $dayNames = ['日','月','火','水','木','金','土'];

        $attendanceRecords = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests')
            ->orderBy('date')
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->date->format('Y-m-d');
            });

        $attendances = collect();
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            $dateKey = $current->format('Y-m-d');
            $dateLabel = $current->format('m/d') . '(' . $dayNames[$current->dayOfWeek] . ')';

            if ($attendanceRecords->has($dateKey)) {
                $attendance = $attendanceRecords->get($dateKey);
                $breakMinutes = $attendance->rests->sum(function ($rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        return $rest->rest_start->diffInMinutes($rest->rest_end);
                    }
                    return 0;
                });
                $totalMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out) - $breakMinutes;
                }

                $attendances->push([
                    'id' => $attendance->id,
                    'date' => $dateLabel,
                    'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    'break_time' => sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                    'total_time' => sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60),
                ]);
            } else {
                $attendances->push([
                    'id' => null,
                    'date' => $dateLabel,
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_time' => '',
                    'total_time' => '',
                ]);
            }
            $current->addDay();
        }

        $staffName = $staff->name;
        $staffId = $staff->id;
        $currentMonth = $startOfMonth->format('Y/m');
        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

        return view('admin.attendance.staff', compact(
            'attendances', 'staffName', 'staffId', 'currentMonth', 'prevMonth', 'nextMonth'
        ));
    }

    public function staffCsv($id)
    {
        $staff = User::findOrFail($id);
        $month = request('month', Carbon::now()->format('Y/m'));
        $parsedMonth = Carbon::parse(str_replace('/', '-', $month));
        $startOfMonth = $parsedMonth->startOfMonth();
        $endOfMonth = $parsedMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests')
            ->orderBy('date')
            ->get();

        $filename = $staff->name . '_' . $parsedMonth->format('Y_m') . '_勤怠.csv';

        return new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $breakMinutes = $attendance->rests->sum(function ($rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        return $rest->rest_start->diffInMinutes($rest->rest_end);
                    }
                    return 0;
                });
                $totalMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out) - $breakMinutes;
                }

                fputcsv($handle, [
                    $attendance->date->format('Y/m/d'),
                    $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                    sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
