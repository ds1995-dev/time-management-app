<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $currentMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : now()->startOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [
                $currentMonth->copy()->startOfMonth()->toDateString(),
                $currentMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('work_date')
            ->get()
            ->map(function ($attendance) {
                $breakMinutes = $attendance->breaks->sum(function ($break) {
                    if (! $break->break_start || ! $break->break_end) {
                        return 0;
                    }

                    return Carbon::parse($break->break_start)
                        ->diffInMinutes(Carbon::parse($break->break_end));
                });

                $workMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $workMinutes = max($totalMinutes - $breakMinutes, 0);
                }

                $attendance->break_total = $breakMinutes > 0
                    ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                    : null;

                $attendance->work_total = $workMinutes > 0
                    ? sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60)
                    : null;

                return $attendance;
            });

        return view('attendance-list', [
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'attendances' => $attendances,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($id);

        $changeRequest = AttendanceChangeRequest::with('requestBreaks')
            ->where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        abort_unless(
            $attendance->user_id === auth()->id() || auth()->user()->is_admin,
            403
        );

        return view('attendance-detail', compact('attendance', 'changeRequest'));
    }
}
