<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceActionRequest;
use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestRest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->format('Y-m-d'))
            ->first();

        $status = $attendance ? $attendance->status : 0;

        return view('attendance.index', compact('status'));
    }

    public function store(AttendanceActionRequest $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();
        $action = $request->input('action');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->format('Y-m-d'))
            ->first();

        if ($action === 'clock_in' && !$attendance) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today->format('Y-m-d'),
                'clock_in' => $now,
                'status' => 1,
            ]);
        } elseif ($action === 'clock_out' && $attendance && $attendance->status === 1) {
            $attendance->update([
                'clock_out' => $now,
                'status' => 3,
            ]);
        } elseif ($action === 'break_start' && $attendance && $attendance->status === 1) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start' => $now,
            ]);
            $attendance->update(['status' => 2]);
        } elseif ($action === 'break_end' && $attendance && $attendance->status === 2) {
            $rest = Rest::where('attendance_id', $attendance->id)
                ->whereNull('rest_end')
                ->latest()
                ->first();
            if ($rest) {
                $rest->update(['rest_end' => $now]);
            }
            $attendance->update(['status' => 1]);
        }

        return redirect('/attendance');
    }

    public function list()
    {
        $user = Auth::user();
        $month = request('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $dayNames = ['日', '月', '火', '水', '木', '金', '土'];

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

            if ($attendance) {
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
                    'date' => $date->format('m/d') . '(' . $dayNames[$date->dayOfWeek] . ')',
                    'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    'break_time' => sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                    'total_time' => sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60),
                ]);
            } else {
                $attendances->push([
                    'id' => null,
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

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        $user = Auth::user();

        if ($attendance->user_id !== $user->id) {
            abort(403);
        }

        $pendingRequest = CorrectionRequest::where('attendance_id', $id)
            ->where('status', 0)
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

    public function update(AttendanceDetailRequest $request, $id)
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
            'status' => 0,
        ]);

        $rests = $request->input('rests', []);
        $originalRests = $attendance->rests;

        foreach ($rests as $index => $restData) {
            if (empty($restData['start']) && empty($restData['end'])) {
                continue;
            }
            $restStart = !empty($restData['start']) ? Carbon::parse($date . ' ' . $restData['start']) : null;
            $restEnd = !empty($restData['end']) ? Carbon::parse($date . ' ' . $restData['end']) : null;

            CorrectionRequestRest::create([
                'correction_request_id' => $correctionRequest->id,
                'rest_id' => isset($originalRests[$index]) ? $originalRests[$index]->id : null,
                'request_rest_start' => $restStart,
                'request_rest_end' => $restEnd,
            ]);
        }

        return redirect('/attendance/detail/' . $id);
    }
}
