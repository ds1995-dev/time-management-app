<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_users_attendance_for_the_day(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => '田中太郎',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
        ]);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 10:00:00',
            'clock_out' => '2026-03-21 19:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-20',
            'clock_in' => '2026-03-20 09:00:00',
            'clock_out' => '2026-03-20 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('田中太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('2026-03-20');
    }

    public function test_current_date_is_displayed_on_admin_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/03/21');
    }

    public function test_previous_day_attendance_is_displayed_when_prev_day_is_selected(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => '田中太郎',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-20',
            'clock_in' => '2026-03-20 09:00:00',
            'clock_out' => '2026-03-20 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 10:00:00',
            'clock_out' => '2026-03-21 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?day=2026-03-20');

        $response->assertStatus(200);
        $response->assertSee('2026/03/20');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_next_day_attendance_is_displayed_when_next_day_is_selected(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => '田中太郎',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-22',
            'clock_in' => '2026-03-22 10:00:00',
            'clock_out' => '2026-03-22 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?day=2026-03-22');

        $response->assertStatus(200);
        $response->assertSee('2026/03/22');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}
