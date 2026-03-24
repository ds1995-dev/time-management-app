<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_date_time_is_displayed_in_ui_format(): void
    {
        $now = Carbon::create(2026, 3, 21, 9, 5, 0, 'Asia/Tokyo');
        Carbon::setTestNow($now);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年3月21日(土)');
        $response->assertSee('09:05');
    }
}
