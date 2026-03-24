<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceChangeRequest as AttendanceChangeRequestValidation;
use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use App\Models\AttendanceChangeRequestBreak;

class AttendanceChangeRequestController extends Controller
{

    public function store(AttendanceChangeRequestValidation $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $workDate = $attendance->work_date->format('Y-m-d');

        $breaks = [
            ['start' => $request->requested_break_start, 'end' => $request->requested_break_start],
            ['start' => $request->requested_break2_start, 'end' => $request->requested_break2_end],
        ];

        $changeRequest = AttendanceChangeRequest::create([
            'attendance_id' => $id,
            'status' => 'pending',
            'requested_clock_in' => $workDate . ' ' . $request->requested_clock_in . ':00',
            'requested_clock_out' => $workDate . ' ' . $request->requested_clock_out . ':00',
            'requested_note' => $request->requested_note,
        ]);

        foreach ($breaks as $b) {
            if ($b['start'] || $b['end']) {
                AttendanceChangeRequestBreak::create([
                    'request_id' => $changeRequest->id,
                    'requested_break_start' => $b['start'] ? $workDate.' '.$b['start'].':00' : null,
                    'requested_break_end' => $b['end'] ? $workDate.' '.$b['end'].':00' : null,
                ]);
            }
        }
        return redirect()->route('attendance.detail', ['id' => $id]);
    }
}
