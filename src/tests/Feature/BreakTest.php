<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_break(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 12, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->copy()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->get('/attendance')
            ->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance/break-start')
            ->assertRedirect('/attendance');

        $this->actingAs($user)->get('/attendance')
            ->assertSee('休憩中');
    }

    public function test_user_can_take_break_multiple_times_in_a_day(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 12, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->copy()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 13, 0, 0));
        $this->actingAs($user)->post('/attendance/break-end');

        $this->actingAs($user)->get('/attendance')
            ->assertSee('休憩入');
    }

    public function test_user_can_end_break(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 12, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->copy()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-start');

        $this->actingAs($user)->get('/attendance')
            ->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 13, 0, 0));

        $this->actingAs($user)->post('/attendance/break-end')
            ->assertRedirect('/attendance');

        $this->actingAs($user)->get('/attendance')
            ->assertSee('勤務中');
    }

    public function test_user_can_end_break_multiple_times_in_a_day(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 12, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->copy()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 13, 0, 0));
        $this->actingAs($user)->post('/attendance/break-end');

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 15, 0, 0));
        $this->actingAs($user)->post('/attendance/break-start');

        $this->actingAs($user)->get('/attendance')
            ->assertSee('休憩戻');
    }

    public function test_break_time_is_visible_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 21, 9, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 3, 21, 13, 0, 0));
        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('03/21');
        $response->assertSee('01:00');
    }
}