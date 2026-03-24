<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory()->count(10)->create([
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        foreach ($users as $user) {
            $dates = collect(range(2, 30))
                ->shuffle()
                ->take(5)
                ->map(fn ($daysAgo) => now()->copy()->subDays($daysAgo)->toDateString())
                ->sort()
                ->values();

                foreach ($dates as $date) {
                $clockIn = Carbon::parse($date)->setTime(
                    fake()->numberBetween(8, 10),
                    fake()->randomElement([0, 15, 30, 45])
                );

                $workMinutes = fake()->randomElement([420, 435, 450, 465, 480, 495, 510, 525, 540]);
                $clockOut = $clockIn->copy()->addMinutes($workMinutes);

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                $breakCount = fake()->numberBetween(0, 2);

                $current = $clockIn->copy()->addHours(2);

                for ($i = 0; $i < $breakCount; $i++) {
                    $breakStart = $current->copy()->addMinutes(fake()->numberBetween(0, 30));
                    $breakEnd = $breakStart->copy()->addMinutes(fake()->numberBetween(15, 60));

                    if ($breakEnd->gt($clockOut)) {
                        break;
                    }

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                    ]);

                    $current = $breakEnd->copy()->addMinutes(30);
                }
            }
        }
    }
}
