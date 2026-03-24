<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $currentDay = $request->filled('day')
            ? Carbon::createFromFormat('Y-m-d', $request->day)
            : now();

        $prevDay = $currentDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $currentDay->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with(['breaks', 'user'])
            ->where('work_date', $currentDay->toDateString())
            ->orderBy('clock_in')
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

                $attendance->break_total = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
                $attendance->work_total = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

                return $attendance;
            });
        return view('admin.admin-attendance-list', compact('attendances', 'currentDay', 'prevDay', 'nextDay'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($id);

        $changeRequest = AttendanceChangeRequest::where('attendance_id', $id)
            ->latest()
            ->first();

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

        $attendance->break_total = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $attendance->work_total = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

        return view('admin.admin-attendance-detail', compact('attendance', 'changeRequest'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $workDate = $attendance->work_date->format('Y-m-d');

        $breaks = [
            ['start' => $request->requested_break_start, 'end' => $request->requested_break_end],
            ['start' => $request->requested_break2_start, 'end' => $request->requested_break2_end],
        ];

        DB::transaction(function () use ($attendance, $request, $workDate, $breaks) {
            $attendance->update([
                'clock_in' => $workDate . ' ' . $request->requested_clock_in . ':00',
                'clock_out' => $workDate . ' ' . $request->requested_clock_out . ':00',
                'note' => $request->requested_note,
            ]);

            $attendance->breaks()->delete();

            foreach ($breaks as $break) {
                if (! $break['start'] && ! $break['end']) {
                    continue;
                }

                $attendance->breaks()->create([
                    'break_start' => $break['start'] ? $workDate . ' ' . $break['start'] . ':00' : null,
                    'break_end' => $break['end'] ? $workDate . ' ' . $break['end'] . ':00' : null,
                ]);
            }
        });

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
    }

    public function show(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $currentMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : now()->startOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
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

                $attendance->break_total = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
                $attendance->work_total = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

                return $attendance;
            });

        return view('admin.admin-each-attendance-list', [
            'user' => $user,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'attendances' => $attendances,
        ]);
    }

    public function exportCsv(Request $request, $userId): StreamedResponse
    {
        $user = User::findOrFail($userId);
        $currentMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : now()->startOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $currentMonth->copy()->startOfMonth()->toDateString(),
                $currentMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('work_date')
            ->get();

        $filename = sprintf('%s_%s_attendance.csv', $user->name, $currentMonth->format('Y-m'));

        return response()->streamDownload(function () use ($attendances) {
            $out = fopen('php://output', 'w');

            // Excel向けBOM
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['日付', '出勤', '退勤', '休憩合計(分)', '勤務合計(分)']);

            foreach ($attendances as $a) {
                $breakMinutes = $a->breaks->sum(function ($b) {
                    if (! $b->break_start || ! $b->break_end) return 0;
                    return Carbon::parse($b->break_start)->diffInMinutes(Carbon::parse($b->break_end));
                });

                $workMinutes = 0;
                if ($a->clock_in && $a->clock_out) {
                    $total = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out));
                    $workMinutes = max($total - $breakMinutes, 0);
                }

                fputcsv($out, [
                    optional($a->work_date)->format('Y-m-d'),
                    optional($a->clock_in)->format('H:i'),
                    optional($a->clock_out)->format('H:i'),
                    $breakMinutes,
                    $workMinutes,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
