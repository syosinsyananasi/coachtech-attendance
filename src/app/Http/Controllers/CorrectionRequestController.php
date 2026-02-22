<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
    public function list()
    {
        if (Auth::guard('admin')->check()) {
            return $this->adminList();
        }

        $user = Auth::user();
        $tab = request('tab', 'pending');
        $status = $tab === 'approved' ? 1 : 0;

        $requests = CorrectionRequest::where('user_id', $user->id)
            ->where('status', $status)
            ->with(['attendance', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'attendance_id' => $req->attendance_id,
                    'status_label' => $req->status === 0 ? '承認待ち' : '承認済み',
                    'user_name' => str_replace(' ', '', $req->user->name),
                    'target_date' => $req->attendance->date->format('Y/m/d'),
                    'reason' => $req->remark,
                    'request_date' => $req->created_at->format('Y/m/d'),
                ];
            });

        return view('correction_request.list', compact('requests', 'tab'));
    }

    private function adminList()
    {
        $tab = request('tab', 'pending');
        $status = $tab === 'approved' ? 1 : 0;

        $requests = CorrectionRequest::where('status', $status)
            ->with(['attendance', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'status_label' => $req->status === 0 ? '承認待ち' : '承認済み',
                    'user_name' => str_replace(' ', '', $req->user->name),
                    'target_date' => $req->attendance->date->format('Y/m/d'),
                    'reason' => $req->remark,
                    'request_date' => $req->created_at->format('Y/m/d'),
                ];
            });

        return view('admin.correction_request.list', compact('requests', 'tab'));
    }

    public function approve($id)
    {
        $correctionRequest = CorrectionRequest::with([
            'attendance.user',
            'attendance.rests',
            'correctionRequestRests',
        ])->findOrFail($id);

        $attendance = $correctionRequest->attendance;
        $isApproved = $correctionRequest->status === 1;

        $year = $attendance->date->format('Y');
        $monthDay = $attendance->date->format('n月j日');

        $rests = $correctionRequest->correctionRequestRests->map(function ($rest) {
            return [
                'start' => $rest->request_rest_start ? $rest->request_rest_start->format('H:i') : '',
                'end' => $rest->request_rest_end ? $rest->request_rest_end->format('H:i') : '',
            ];
        })->toArray();

        if (empty($rests)) {
            $rests = $attendance->rests->map(function ($rest) {
                return [
                    'start' => $rest->rest_start ? $rest->rest_start->format('H:i') : '',
                    'end' => $rest->rest_end ? $rest->rest_end->format('H:i') : '',
                ];
            })->toArray();
        }

        return view('correction_request.approve', compact(
            'correctionRequest', 'attendance', 'isApproved', 'year', 'monthDay', 'rests'
        ));
    }

    public function storeApproval($id)
    {
        $correctionRequest = CorrectionRequest::with([
            'correctionRequestRests',
            'attendance',
        ])->findOrFail($id);

        if ($correctionRequest->status !== 0) {
            return redirect()->route('correction_request.approve', $id);
        }

        $attendance = $correctionRequest->attendance;
        $attendance->update([
            'clock_in' => $correctionRequest->request_clock_in,
            'clock_out' => $correctionRequest->request_clock_out,
        ]);

        foreach ($correctionRequest->correctionRequestRests as $corrRest) {
            if ($corrRest->rest_id) {
                $corrRest->rest->update([
                    'rest_start' => $corrRest->request_rest_start,
                    'rest_end' => $corrRest->request_rest_end,
                ]);
            } else {
                \App\Models\Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start' => $corrRest->request_rest_start,
                    'rest_end' => $corrRest->request_rest_end,
                ]);
            }
        }

        $correctionRequest->update([
            'status' => 1,
            'approved_at' => Carbon::now(),
        ]);

        return redirect()->route('correction_request.approve', $id);
    }
}
