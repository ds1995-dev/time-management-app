@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="attendance-page">
    <section class="attendance-card" aria-label="勤怠打刻">
        @if ($status === 'off')
        <p class="attendance-card__status">勤務外</p>
        @elseif ($status === 'working')
        <p class="attendance-card__status">勤務中</p>
        @elseif ($status === 'breaking')
        <p class="attendance-card__status">休憩中</p>
        @elseif ($status === 'done')
        <p class="attendance-card__status">退勤済</p>
        @endif
        <p class="attendance-card__date">{{ now()->isoFormat('YYYY年M月D日(ddd)') }}</p>
        <p class="attendance-card__time" id="current-time">{{ now()->format('H:i') }}</p>
        @if ($status === 'done')
        <p class="attendance-card__thanks">お疲れ様でした。</p>
        @elseif ($status === 'off')
        <form action="/attendance/clock-in" method="POST">
            @csrf
            <button class="attendance-card__button" type="submit">出勤</button>
        </form>
        @elseif ($status === 'working')
        <form method="POST">
            @csrf
            <button class="attendance-card__button" formaction="/attendance/clock-out" type="submit">退勤</button>
            <button class="attendance-card__button-break" formaction="/attendance/break-start" type="submit">休憩入</button>
        </form>
        @elseif ($status === 'breaking')
        <form action="/attendance/break-end" method="POST">
            @csrf
            <button class="attendance-card__button-break" type="submit">休憩戻</button>
        </form>
        @endif
    </section>

</div>

<script>
    (function() {
        const timeNode = document.getElementById('current-time');
        if (!timeNode) return;

        const pad = (n) => String(n).padStart(2, '0');
        const updateTime = () => {
            const now = new Date();
            timeNode.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
        };

        updateTime();
        setInterval(updateTime, 1000);
    })();
</script>
@endsection