<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceCorrectionValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_is_shown_when_clock_in_is_after_clock_out(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'requested_clock_in' => '19:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '',
                'requested_break_end' => '',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '修正申請',
            ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");
        $response->assertSessionHasErrors([
            'requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_validation_error_is_shown_when_break_start_is_after_clock_out(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '19:00',
                'requested_break_end' => '19:30',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '修正申請',
            ]);

        $response->assertSessionHasErrors([
            'requested_break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_validation_error_is_shown_when_break_end_is_after_clock_out(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '17:00',
                'requested_break_end' => '19:00',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '修正申請',
            ]);

        $response->assertSessionHasErrors([
            'requested_break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_validation_error_is_shown_when_note_is_empty(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '',
                'requested_break_end' => '',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '',
            ]);

        $response->assertSessionHasErrors([
            'requested_note' => '備考を記入してください',
        ]);
    }

    public function test_attendance_correction_request_is_created(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => '12:00',
            'requested_break_end' => '13:00',
            'requested_break2_start' => '',
            'requested_break2_end' => '',
            'requested_note' => '電車遅延',
        ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");

        $this->assertDatabaseHas('attendance_change_requests', [
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_note' => '電車遅延',
        ]);
    }

    public function test_pending_requests_are_displayed_in_request_list(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => '',
            'requested_break_end' => '',
            'requested_break2_start' => '',
            'requested_break2_end' => '',
            'requested_note' => '修正申請',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('修正申請');
    }

    public function test_approved_requests_are_displayed_in_request_list(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => '',
            'requested_break_end' => '',
            'requested_break2_start' => '',
            'requested_break2_end' => '',
            'requested_note' => '承認確認',
        ]);

        $requestId = \App\Models\AttendanceChangeRequest::first()->id;

        $this->actingAs($admin)->post("/stamp_correction_request/approve/{$requestId}");

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認確認');
    }


    public function test_user_can_open_request_detail_page(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'requested_break_start' => '',
            'requested_break_end' => '',
            'requested_break2_start' => '',
            'requested_break2_end' => '',
            'requested_note' => '詳細確認',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
