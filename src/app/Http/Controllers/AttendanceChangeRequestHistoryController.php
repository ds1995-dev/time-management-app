<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;

class AttendanceChangeRequestHistoryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = AttendanceChangeRequest::with('attendance.user')
            ->where('status', $status);

        if (! auth()->user()->is_admin) {
            $attendanceIds = Attendance::where('user_id', Auth::id())->pluck('id');
            $query->whereIn('attendance_id', $attendanceIds);
        }

        $requests = $query->orderBy('requested_clock_in')->get();

        return view('request-list', compact('requests', 'status'));
    }
}
