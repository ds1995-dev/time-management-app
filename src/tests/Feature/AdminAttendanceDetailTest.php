<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attendance_detail_displays_selected_attendance_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田 太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
            'note' => '通常勤務',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-03-21 12:00:00',
            'break_end' => '2026-03-21 13:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
        $response->assertSee('2026年');
        $response->assertSee('3月21日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_admin_sees_validation_error_when_clock_in_is_after_clock_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'requested_clock_in' => '19:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '',
                'requested_break_end' => '',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '管理者修正',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors([
            'requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_sees_validation_error_when_break_start_is_after_clock_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '19:00',
                'requested_break_end' => '19:30',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '管理者修正',
            ]);

        $response->assertSessionHasErrors([
            'requested_break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_admin_sees_validation_error_when_break_end_is_after_clock_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '17:00',
                'requested_break_end' => '19:00',
                'requested_break2_start' => '',
                'requested_break2_end' => '',
                'requested_note' => '管理者修正',
            ]);

        $response->assertSessionHasErrors([
            'requested_break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_sees_validation_error_when_note_is_empty(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
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
}
