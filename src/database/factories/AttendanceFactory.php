<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $day = fake()->dateTimeBetween('-30 days', '-2 days');
        $base = \Carbon\Carbon::instance($day);

        $clockIn = $base->copy()->setTime(
            fake()->numberBetween(8,10),
            fake()->randomElement([0, 15, 30, 45])
        );

        $workMinutes = fake()->randomElement([420, 435, 450, 465, 480, 495, 510, 525, 540]);
        $clockOut = $clockIn->copy()->addMinutes($workMinutes);

        return [
            'user_id' => User::factory(),
            'work_date' => $clockIn->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
