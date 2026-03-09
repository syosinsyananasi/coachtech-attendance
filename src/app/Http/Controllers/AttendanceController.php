<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestRest;
use App\Services\AttendanceTimeService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $month = request('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $dayNames = Attendance::DAY_NAMES;

        $records = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->keyBy(function ($a) {
                return $a->date->format('Y-m-d');
            });

        $attendances = collect();
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $attendance = $records->get($key);

            if ($attendance && $attendance->clock_in) {
                $breakMinutes = AttendanceTimeService::calculateBreakMinutes($attendance);
                $totalMinutes = AttendanceTimeService::calculateTotalMinutes($attendance, $breakMinutes);

                $attendances->push([
                    'id' => $attendance->id,
                    'raw_date' => $key,
                    'date' => $date->format('m/d') . '(' . $dayNames[$date->dayOfWeek] . ')',
                    'clock_in' => $attendance->clock_in->format('H:i'),
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    'break_time' => AttendanceTimeService::formatMinutes($breakMinutes),
                    'total_time' => AttendanceTimeService::formatMinutes($totalMinutes),
                ]);
            } else {
                $attendances->push([
                    'id' => $attendance ? $attendance->id : null,
                    'raw_date' => $key,
                    'date' => $date->format('m/d') . '(' . $dayNames[$date->dayOfWeek] . ')',
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_time' => '',
                    'total_time' => '',
                ]);
            }
        }

        $currentMonth = $startOfMonth->format('Y/m');
        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function redirectByDate($date)
    {
        $user = Auth::user();
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            ['status' => Attendance::STATUS_OFF]
        );

        return redirect()->route('attendance.show', $attendance->id);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        $user = Auth::user();

        if ($attendance->user_id !== $user->id) {
            abort(403);
        }

        $pendingRequest = CorrectionRequest::where('attendance_id', $id)
            ->where('status', CorrectionRequest::STATUS_PENDING)
            ->with('correctionRequestRests')
            ->first();

        $isPending = (bool) $pendingRequest;

        $year = $attendance->date->format('Y');
        $monthDay = $attendance->date->format('n月j日');

        if ($isPending) {
            $attendance->clock_in = $pendingRequest->request_clock_in;
            $attendance->clock_out = $pendingRequest->request_clock_out;
            $attendance->note = $pendingRequest->remark;

            $rests = $pendingRequest->correctionRequestRests->map(function ($rest) {
                return [
                    'start' => $rest->request_rest_start ? $rest->request_rest_start->format('H:i') : '',
                    'end' => $rest->request_rest_end ? $rest->request_rest_end->format('H:i') : '',
                ];
            })->toArray();
        } else {
            $rests = $attendance->rests->map(function ($rest) {
                return [
                    'start' => $rest->rest_start ? $rest->rest_start->format('H:i') : '',
                    'end' => $rest->rest_end ? $rest->rest_end->format('H:i') : '',
                ];
            })->toArray();
        }

        return view('attendance.detail', compact('attendance', 'year', 'monthDay', 'rests', 'isPending'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        $user = Auth::user();

        if ($attendance->user_id !== $user->id) {
            abort(403);
        }

        $date = $attendance->date->format('Y-m-d');
        $clockIn = Carbon::parse($date . ' ' . $request->input('clock_in'));
        $clockOut = Carbon::parse($date . ' ' . $request->input('clock_out'));

        $correctionRequest = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_clock_in' => $clockIn,
            'request_clock_out' => $clockOut,
            'remark' => $request->input('note'),
            'status' => CorrectionRequest::STATUS_PENDING,
        ]);

        $rests = $request->input('rests', []);
        $originalRests = $attendance->rests;

        foreach ($rests as $index => $restData) {
            $isEmpty = empty($restData['start']) && empty($restData['end']);
            $hasOriginal = isset($originalRests[$index]);

            if ($isEmpty && !$hasOriginal) {
                continue;
            }

            $restStart = !empty($restData['start']) ? Carbon::parse($date . ' ' . $restData['start']) : null;
            $restEnd = !empty($restData['end']) ? Carbon::parse($date . ' ' . $restData['end']) : null;

            CorrectionRequestRest::create([
                'correction_request_id' => $correctionRequest->id,
                'rest_id' => $hasOriginal ? $originalRests[$index]->id : null,
                'request_rest_start' => $restStart,
                'request_rest_end' => $restEnd,
            ]);
        }

        return redirect()->route('attendance.show', $id);
    }
}
