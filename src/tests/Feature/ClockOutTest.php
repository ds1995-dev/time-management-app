<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_out(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 18, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->copy()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->get('/attendance')
            ->assertStatus(200)
            ->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/clock-out')
            ->assertRedirect('/attendance');

        $this->actingAs($user)->get('/attendance')
            ->assertStatus(200)
            ->assertSee('退勤済');
    }

    public function test_clock_out_time_is_visible_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 18, 0, 0));

        $this->actingAs($user)->post('/attendance/clock-out')
            ->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('03/21');
        $response->assertSee('18:00');
    }
}