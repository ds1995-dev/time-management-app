<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceHistoryController;
use App\Http\Controllers\AttendanceChangeRequestController;
use App\Http\Controllers\AttendanceChangeRequestHistoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAttendanceHistoryController;
use App\Http\Controllers\AdminRequestListController;
use App\Http\Controllers\AdminRequestApprovalController;

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/admin/login', function () {
    return view('admin.admin-login');
})->name('admin.login');

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware(['auth','verified']);
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware(['auth', 'verified']);
Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
    ->middleware(['auth', 'verified']);
Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
    ->middleware(['auth', 'verified']);

Route::get('/attendance/list', [AttendanceHistoryController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.list');
Route::get('attendance/detail/{id}', [AttendanceHistoryController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.detail');

Route::post('/attendance/detail/{id}', [AttendanceChangeRequestController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('change.request');

Route::get('/stamp_correction_request/list', [AttendanceChangeRequestHistoryController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('change.request.list');

Route::get('/admin/staff/list', [AdminController::class, 'index'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('staff.list');

Route::get('/admin/attendance/list', [AdminAttendanceHistoryController::class, 'index'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.attendance.list');
Route::get('/admin/attendance/{id}', [AdminAttendanceHistoryController::class, 'detail'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.attendance.detail');
Route::post('/admin/attendance/{id}', [AdminAttendanceHistoryController::class, 'update'])
    ->middleware(['auth', 'admin','verified'])
    ->name('admin.attendance.update');
Route::get('/admin/attendance/staff/{id}', [AdminAttendanceHistoryController::class, 'show'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.attendance.staff');
Route::get('/admin/attendance/staff/{id}/csv', [AdminAttendanceHistoryController::class, 'exportCsv'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.attendance.staff.csv');

Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestApprovalController::class, 'index'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.approval');
Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestApprovalController::class, 'approve'])
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.approval.execute');