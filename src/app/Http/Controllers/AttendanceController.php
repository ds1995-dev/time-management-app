<?php

namespace App\Http\Controllers;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->latest('clock_in')
            ->first();

        if (! $attendance) {
            $status = 'off';

        } elseif ($attendance->clock_out) {
            $status = 'done';
        } else {
            $onBreak = $attendance->breaks()
                ->whereNull('break_end')
                ->exists();

            $status = $onBreak ? 'breaking' : 'working';
        };

        return view('index', compact('status', 'attendance'));
    }

    public function clockIn(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        Attendance::create([
            'user_id' => $userId,
            'work_date' => $today,
            'clock_in' => now(),
        ]);

        return redirect('/attendance');
    }

    public function clockOut(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect('/attendance')->with('just_clocked_out', true);
    }

    public function breakStart(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
        ]);

        return redirect('/attendance');
    }

    public function breakEnd(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();

        $break->update([
            'break_end' => now(),
        ]);

        return redirect('/attendance');
    }
}
