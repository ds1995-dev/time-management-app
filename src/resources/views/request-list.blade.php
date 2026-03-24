@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endsection

@section('content')

<div class="request-list-page">
    <section class="request-list" aria-label="申請一覧">
        <h1 class="request-list__title">申請一覧</h1>

        <div class="request-list__tabs">
            <a href="{{ url('/stamp_correction_request/list?status=pending') }}" class="request-list__tab {{ $status === 'pending' ? 'request-list__tab--active' : '' }}">承認待ち</a>
            <a href="{{ url('/stamp_correction_request/list?status=approved') }}" class="request-list__tab {{ $status ==='approved' ? 'request-list__tab--active' : '' }}">承認済み</a>
        </div>

        <div class="request-list__table-wrap">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td>{{ $request['status'] === 'pending' ? '承認待ち' : '承認済み' }}</td>
                            <td>{{ $request->attendance->user->name }}</td>
                            <td>{{ $request['requested_clock_in']->format('Y-m-d') }}</td>
                            <td>{{ $request['requested_note'] }}</td>
                            <td>{{ $request['created_at']->format('Y-m-d') }}</td>
                            @if (session('ui_role') === 'admin')
                            <!-- 管理者用 -->
                            <td><a href="{{ route('admin.approval', ['attendance_correct_request_id' => $request->id]) }}" class="request-table__detail">詳細</a></td>
                            @else
                            <!-- 一般用 -->
                            <td><a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="request-table__detail">詳細</a></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
