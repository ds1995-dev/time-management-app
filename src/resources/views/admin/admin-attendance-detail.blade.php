@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-page">
    <section class="attendance-detail" aria-label="勤怠詳細">
        <h1 class="attendance-detail__title">勤怠詳細</h1>

                <!-- 承認待ち -->
        @if ($changeRequest && $changeRequest->status === 'pending')
        <form id="attendance-edit-form" class="attendance-detail__card" method="POST" action="{{ route('change.request', ['id' => $attendance->id]) }}">
            @csrf
            <div class="attendance-detail__row">
                <p class="attendance-detail__label">名前</p>
                <div class="attendance-detail__value attendance-detail__value--name">{{ $attendance->user->name }}</div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">日付</p>
                <div class="attendance-detail__value attendance-detail__value--date">
                    <span>{{ $attendance->work_date->format('Y年') }}</span>
                    <span>{{ $attendance->work_date->format('n月j日') }}</span>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">出勤・退勤</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <p>{{ $changeRequest->requested_clock_in->format('H:i') }}</p>
                    <span>〜</span>
                    <p>{{ $changeRequest->requested_clock_out->format('H:i') }}</p>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">休憩</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <p>{{ $changeRequest->requestBreaks->get(0)?->requested_break_start?->format('H:i') ?? '' }}</p>
                    <span>〜</span>
                    <p>{{ $changeRequest->requestBreaks->get(0)?->requested_break_end?->format('H:i') ?? '' }}</p>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">休憩2</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <p>{{ $changeRequest->requestBreaks->get(1)?->requested_break_start?->format('H:i') ?? '' }}</p>
                    <span>〜</span>
                    <p>{{ $changeRequest->requestBreaks->get(1)?->requested_break_end?->format('H:i') ?? '' }}</p>
                </div>
            </div>

            <div class="attendance-detail__row attendance-detail__row--note">
                <p class="attendance-detail__label">備考</p>
                <div class="attendance-detail__value attendance-detail__value--note">
                    <p>{{ $changeRequest->requested_note }}</p>
                </div>
            </div>
        </form>
        <div class="attendance-detail__actions">
            <p class="attendance-detail__note">*承認待ちのため修正はできません。</p>
        </div>

        <!-- 修正申請前 -->
        @else
        <form id="attendance-edit-form" class="attendance-detail__card" method="POST" action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}">
            @csrf
            <div class="attendance-detail__row">
                <p class="attendance-detail__label">名前</p>
                <div class="attendance-detail__value attendance-detail__value--name">{{ $attendance->user->name }}</div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">日付</p>
                <div class="attendance-detail__value attendance-detail__value--date">
                    <span>{{ $attendance->work_date->format('Y年') }}</span>
                    <span>{{ $attendance->work_date->format('n月j日') }}</span>
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">出勤・退勤</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    @php
                    $clockError = $errors->first('requested_clock_in') ?: $errors->first('requested_clock_out');
                    @endphp

                    @if ($clockError)
                    <p class="attendance-detail__error">{{ $clockError }}</p>
                    @endif
                    <input name="requested_clock_in" type="text" value="{{ $attendance->clock_in->format('H:i') }}" aria-label="出勤時刻">
                    <span>〜</span>
                    <input type="text" name="requested_clock_out" value="{{ $attendance->clock_out->format('H:i') }}" aria-label="退勤時刻">
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">休憩</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    @php
                    $break1Error = $errors->first('requested_break_start') ?: $errors->first('requested_break_end');
                    @endphp

                    @if ($break1Error)
                    <p class="attendance-detail__error">{{ $break1Error }}</p>
                    @endif
                    <input type="text" name="requested_break_start" value="{{ $attendance->breaks->get(0)?->break_start?->format('H:i') ?? '' }}" aria-label="休憩1開始">
                    <span>〜</span>
                    <input type="text" name="requested_break_end" value="{{ $attendance->breaks->get(0)?->break_end?->format('H:i') ?? '' }}" aria-label="休憩1終了">
                </div>
            </div>

            <div class="attendance-detail__row">
                <p class="attendance-detail__label">休憩2</p>
                <div class="attendance-detail__value attendance-detail__value--time">
                    @php
                    $break2Error = $errors->first('requested_break2_start') ?: $errors->first('requested_break2_end');
                    @endphp

                    @if ($break2Error)
                    <p class="attendance-detail__error">{{ $break2Error }}</p>
                    @endif
                    <input type="text" name="requested_break2_start" value="{{ $attendance->breaks->get(1)?->break_start?->format('H:i') ?? '' }}" aria-label="休憩2開始">
                    <span>〜</span>
                    <input type="text" name="requested_break2_end" value="{{ $attendance->breaks->get(1)?->break_end?->format('H:i') ?? '' }}" aria-label="休憩2終了">
                </div>
            </div>

            <div class="attendance-detail__row attendance-detail__row--note">
                <p class="attendance-detail__label">備考</p>
                <div class="attendance-detail__value attendance-detail__value--note">
                    @error('requested_note')
                    <p class="attendance-detail__error">{{ $message }}</p>
                    @enderror
                    <textarea name="requested_note" aria-label="備考"></textarea>
                </div>
            </div>
        </form>
            <div class="attendance-detail__actions">
                <button type="submit" form="attendance-edit-form" class="attendance-detail__button">修正</button>
            </div>
        @endif
    </section>
</div>
@endsection