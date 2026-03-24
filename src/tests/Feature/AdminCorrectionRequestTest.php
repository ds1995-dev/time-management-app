<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use App\Models\AttendanceChangeRequestBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_pending_correction_requests(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user1 = User::factory()->create(['name' => '田中太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 10:00:00',
            'clock_out' => '2026-03-21 19:00:00',
        ]);

        AttendanceChangeRequest::create([
            'attendance_id' => $attendance1->id,
            'status' => 'pending',
            'requested_clock_in' => '2026-03-21 09:30:00',
            'requested_clock_out' => '2026-03-21 18:30:00',
            'requested_note' => '申請A',
        ]);

        AttendanceChangeRequest::create([
            'attendance_id' => $attendance2->id,
            'status' => 'pending',
            'requested_clock_in' => '2026-03-21 10:30:00',
            'requested_clock_out' => '2026-03-21 19:30:00',
            'requested_note' => '申請B',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('田中太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('申請A');
        $response->assertSee('申請B');
    }

    public function test_admin_can_see_all_approved_correction_requests(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '田中太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        AttendanceChangeRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'requested_clock_in' => '2026-03-21 09:30:00',
            'requested_clock_out' => '2026-03-21 18:30:00',
            'requested_note' => '承認済申請',
            'approved_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('田中太郎');
        $response->assertSee('承認済申請');
    }

    public function test_admin_can_see_correction_request_detail(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '田中太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $request = AttendanceChangeRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' => '2026-03-21 10:00:00',
            'requested_clock_out' => '2026-03-21 19:00:00',
            'requested_note' => '電車遅延',
        ]);

        AttendanceChangeRequestBreak::create([
            'request_id' => $request->id,
            'requested_break_start' => '2026-03-21 12:00:00',
            'requested_break_end' => '2026-03-21 13:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('田中太郎');
        $response->assertSee('2026年');
        $response->assertSee('3月21日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('電車遅延');
    }

    public function test_admin_can_approve_correction_request_and_attendance_is_updated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
            'note' => '元データ',
        ]);

        $request = AttendanceChangeRequest::create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' => '2026-03-21 10:00:00',
            'requested_clock_out' => '2026-03-21 19:00:00',
            'requested_note' => '管理者承認テスト',
        ]);

        AttendanceChangeRequestBreak::create([
            'request_id' => $request->id,
            'requested_break_start' => '2026-03-21 12:00:00',
            'requested_break_end' => '2026-03-21 13:00:00',
        ]);

        $response = $this->actingAs($admin)->post("/stamp_correction_request/approve/{$request->id}");

        $response->assertRedirect("/stamp_correction_request/approve/{$request->id}");

        $this->assertDatabaseHas('attendance_change_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2026-03-21 10:00:00',
            'clock_out' => '2026-03-21 19:00:00',
            'note' => '管理者承認テスト',
        ]);

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-03-21 12:00:00',
            'break_end' => '2026-03-21 13:00:00',
        ]);
    }
}