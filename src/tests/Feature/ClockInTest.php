<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in_from_off_status(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        $this->actingAs($user)->get('/attendance')
            ->assertStatus(200)
            ->assertSee('勤務中');
    }

    public function test_clock_in_button_is_not_displayed_after_user_has_finished_work(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 18, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->copy()->setTime(9, 0),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    public function test_clock_in_time_is_visible_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('03/21');
        $response->assertSee('09:00');
    }
}
