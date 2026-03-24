<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_all_of_their_attendance_records(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-10',
            'clock_in' => '2026-03-10 09:00:00',
            'clock_out' => '2026-03-10 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-15',
            'clock_in' => '2026-03-15 10:00:00',
            'clock_out' => '2026-03-15 19:00:00',
        ]);

        Attendance::create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-03-12',
            'clock_in' => '2026-03-12 09:00:00',
            'clock_out' => '2026-03-12 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('03/10');
        $response->assertSee('03/15');
        $response->assertDontSee('03/12');
    }

    public function test_current_month_is_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
    }

    public function test_previous_month_attendance_is_displayed_when_prev_month_is_selected(): void
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-20',
            'clock_in' => '2026-02-20 09:00:00',
            'clock_out' => '2026-02-20 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-20',
            'clock_in' => '2026-03-20 09:00:00',
            'clock_out' => '2026-03-20 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-02');

        $response->assertStatus(200);
        $response->assertSee('2026/02');
        $response->assertSee('02/20');
        $response->assertDontSee('03/20');
    }

    public function test_next_month_attendance_is_displayed_when_next_month_is_selected(): void
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-20',
            'clock_in' => '2026-03-20 09:00:00',
            'clock_out' => '2026-03-20 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '2026-04-10 09:00:00',
            'clock_out' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('04/10');
        $response->assertDontSee('03/20');
    }

    public function test_user_can_navigate_to_attendance_detail_page(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('2026年');
        $response->assertSee('3月21日');
    }
}