<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_general_users_name_and_email(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => '田中太郎',
            'email' => 'tanaka@example.com',
            'is_admin' => false,
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('田中太郎');
        $response->assertSee('tanaka@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    public function test_admin_can_see_selected_user_attendance_list(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => '田中太郎',
            'is_admin' => false,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        $response->assertStatus(200);
        $response->assertSee('田中太郎');
        $response->assertSee('03/21');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_previous_month_attendance_is_displayed_for_selected_user(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-20',
            'clock_in' => '2026-02-20 09:00:00',
            'clock_out' => '2026-02-20 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-20',
            'clock_in' => '2026-03-20 10:00:00',
            'clock_out' => '2026-03-20 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=2026-02");

        $response->assertStatus(200);
        $response->assertSee('2026/02');
        $response->assertSee('02/20');
        $response->assertDontSee('03/20');
    }

    public function test_next_month_attendance_is_displayed_for_selected_user(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-20',
            'clock_in' => '2026-03-20 09:00:00',
            'clock_out' => '2026-03-20 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '2026-04-10 10:00:00',
            'clock_out' => '2026-04-10 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=2026-04");

        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('04/10');
        $response->assertDontSee('03/20');
    }

    public function test_admin_can_open_selected_users_attendance_detail_page(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-21',
            'clock_in' => '2026-03-21 09:00:00',
            'clock_out' => '2026-03-21 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('2026年');
        $response->assertSee('3月21日');
    }
}
