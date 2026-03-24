@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-each-attendance-list.css') }}">
@endsection

@section('content')
@php
$month = \Carbon\Carbon::create(2023, 6, 1);
$daysInMonth = $month->daysInMonth;
$weekdays = ['日', '月', '火', '水', '木', '金', '土'];
@endphp

<div class="attendance-list-page">
    <section class="attendance-list" aria-label="勤怠一覧">
        <h1 class="attendance-list__title">{{ $user->name }}さんの勤怠</h1>

        <div class="attendance-list__month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="attendance-list__nav-link">← 前月</a>
            <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="attendance-list__picker-form">
                <input
                    id="month"
                    type="month"
                    name="month"
                    class="attendance-list__picker-input"
                    value="{{ $currentMonth->format('Y-m') }}"
                    onchange="this.form.submit()"
                >
                <button
                    type="button"
                    class="attendance-list__picker-trigger"
                    onclick="const picker = document.getElementById('month'); if (picker.showPicker) { picker.showPicker(); } else { picker.focus(); }"
                    aria-label="月を選択"
                >
                    <span class="attendance-list__picker-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v13a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 19.5v-13A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8h-15v9.5a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5V10ZM6 6H4.5a.5.5 0 0 0-.5.5V8h16V6.5a.5.5 0 0 0-.5-.5H18v1a1 1 0 1 1-2 0V6H8v1a1 1 0 1 1-2 0V6Zm-.5 6h3v3h-3v-3Zm5 0h3v3h-3v-3Zm5 0h3v3h-3v-3Zm-10 5h3v3h-3v-3Zm5 0h3v3h-3v-3Z"/>
                        </svg>
                    </span>
                    <span class="attendance-list__picker-text">{{ $currentMonth->format('Y/m') }}</span>
                </button>
            </form>
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="attendance-list__nav-link">翌月 →</a>
        </div>

        <div class="attendance-list__table-wrap">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                    @php $date = \Carbon\Carbon::parse($attendance->work_date);
                    @endphp
                    <tr>
                        <td>{{ $attendance->work_date->format('m/d') }}({{ $weekdays[$date->dayOfWeek] }})</td>
                        <td>{{ $attendance->clock_in->format('H:i') }}</td>
                        <td>{{ optional($attendance->clock_out)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->break_total)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->work_total)->format('H:i') }}</td>

                        <td><a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="attendance-table__detail">詳細</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="attendance-list__csv-wrap">
            <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}" class="attendance-list__csv-link">
                CSV出力
            </a>
        </div>
    </section>
</div>
@endsection