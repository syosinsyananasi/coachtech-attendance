<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceTimeService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function index()
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

    public function show($id)
    {
        $staff = User::findOrFail($id);
        $month = request('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $dayNames = Attendance::DAY_NAMES;

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

            $attendance = $attendanceRecords->get($dateKey);
            if ($attendance && $attendance->clock_in) {
                $breakMinutes = AttendanceTimeService::calculateBreakMinutes($attendance);
                $totalMinutes = AttendanceTimeService::calculateTotalMinutes($attendance, $breakMinutes);

                $attendances->push([
                    'id' => $attendance->id,
                    'date' => $dateLabel,
                    'clock_in' => $attendance->clock_in->format('H:i'),
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    'break_time' => AttendanceTimeService::formatMinutes($breakMinutes),
                    'total_time' => AttendanceTimeService::formatMinutes($totalMinutes),
                ]);
            } else {
                $attendances->push([
                    'id' => $attendance ? $attendance->id : null,
                    'date' => $dateLabel,
                    'raw_date' => $dateKey,
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

    public function redirectByDate($staffId, $date)
    {
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $staffId, 'date' => $date],
            ['status' => Attendance::STATUS_OFF]
        );

        return redirect()->route('admin.attendance.show', $attendance->id);
    }

    public function exportCsv($id)
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
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $breakMinutes = AttendanceTimeService::calculateBreakMinutes($attendance);
                $totalMinutes = AttendanceTimeService::calculateTotalMinutes($attendance, $breakMinutes);

                fputcsv($handle, [
                    $attendance->date->format('Y/m/d'),
                    $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    AttendanceTimeService::formatMinutes($breakMinutes),
                    AttendanceTimeService::formatMinutes($totalMinutes),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
