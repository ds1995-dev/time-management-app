@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-request-approve.css') }}">
@endsection

@section('content')
<div class="attendance-detail-page">
    <section class="attendance-detail" aria-label="勤怠詳細">
        <h1 class="attendance-detail__title">勤怠詳細</h1>

        <form id="attendance-edit-form" class="attendance-detail__card" method="POST" action="{{ route('admin.approval.execute', ['attendance_correct_request_id' => $attendance->id]) }}">
            @csrf
            <div class="attendance-detail__row">
                <p class="attendance-detail__label">名前</p>
                <div class="attendance-detail__value attendance-detail__value--name">{{ $attendance->attendance->user->name }}</div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">日付</p>
                <div class="attendance-detail__value attendance-detail__value--date">
                    <span>{{ $attendance->requested_clock_in->format('Y年') }}</span>
                    <span>{{ $attendance->requested_clock_out->format('n月j日') }}</span>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">出勤・退勤</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <p>{{ $attendance->requested_clock_in->format('H:i') }}</p>
                    <span>〜</span>
                    <p>{{ $attendance->requested_clock_out->format('H:i') }}</p>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">休憩</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <p>{{ $attendance->requestBreaks->get(0)?->requested_break_start?->format('H:i') ?? ''}}</p>
                    <span>〜</span>
                    <p>{{ $attendance->requestBreaks->get(0)?->requested_break_end?->format('H:i') ?? ''}}</p>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">休憩2</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <p>{{ $attendance->requestBreaks->get(1)?->requested_break_start?->format('H:i') ?? ''}}</p>
                    <span>〜</span>
                    <p>{{ $attendance->requestBreaks->get(1)?->requested_break_end?->format('H:i') ?? ''}}</p>
                </div>
            </div>

            <div class="attendance-detail__row attendance-detail__row--note">
                <p class="attendance-detail__label">備考</p>
                <div class="attendance-detail__value attendance-detail__value--note">
                    <p>{{ $attendance->requested_note ?? '' }}</p>
                </div>
            </div>
        </form>
        <div class="attendance-detail__actions">
            @if ($attendance->status === 'pending')
            <button type="submit" form="attendance-edit-form" class="attendance-detail__button">承認</button>
            @else
            <button type="button" class="btn-approved" disabled aria-disabled="true">承認済み</button>
            @endif
        </div>
    </section>
</div>
@endsection