<?php

namespace App\Http\Controllers;

use App\Models\AttendanceChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRequestApprovalController extends Controller
{
    public function index($id)
    {
        $attendance = AttendanceChangeRequest::with('requestBreaks')->findOrFail($id);

        return view('admin.admin-request-approve', compact('attendance'));
    }

    public function approve($attendance_correct_request_id)
    {
        DB::transaction(function () use ($attendance_correct_request_id) {
            $req = AttendanceChangeRequest::with(['attendance', 'requestBreaks'])->findOrFail($attendance_correct_request_id);

            if ($req->status !== 'pending') {
                return;
            }

            $attendance = $req->attendance;

            $attendance->update([
                'clock_in' => $req->requested_clock_in,
                'clock_out' => $req->requested_clock_out,
                'note' => $req->requested_note,
            ]);

            $attendance->breaks()->delete();
            foreach ($req->requestBreaks as $rb) {
                $attendance->breaks()->create([
                    'break_start' => $rb->requested_break_start,
                    'break_end' => $rb->requested_break_end,
                ]);
            }

            $req->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);
        });
        return redirect()->route('admin.approval', ['attendance_correct_request_id' => $attendance_correct_request_id]);
    }
}
