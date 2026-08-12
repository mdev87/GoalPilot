<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserActivityStreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ActivityStreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_current_streak_returns_current_streak_when_active_today(): void
    {
        $user = User::factory()->create();
        $streak = UserActivityStreak::create([
            'user_id' => $user->id,
            'current_streak' => 5,
            'longest_streak' => 10,
            'last_active_date' => Carbon::today(),
        ]);

        $this->assertEquals(5, $streak->effective_current_streak);
    }

    public function test_effective_current_streak_returns_current_streak_when_active_yesterday(): void
    {
        $user = User::factory()->create();
        $streak = UserActivityStreak::create([
            'user_id' => $user->id,
            'current_streak' => 3,
            'longest_streak' => 7,
            'last_active_date' => Carbon::yesterday(),
        ]);

        $this->assertEquals(3, $streak->effective_current_streak);
    }

    public function test_effective_current_streak_returns_zero_when_inactive_for_more_than_one_day(): void
    {
        $user = User::factory()->create();
        $streak = UserActivityStreak::create([
            'user_id' => $user->id,
            'current_streak' => 8,
            'longest_streak' => 12,
            'last_active_date' => Carbon::today()->subDays(2),
        ]);

        $this->assertEquals(0, $streak->effective_current_streak);
        $this->assertEquals(12, $streak->longest_streak);
    }

    public function test_effective_current_streak_returns_zero_when_last_active_date_is_null(): void
    {
        $user = User::factory()->create();
        $streak = UserActivityStreak::create([
            'user_id' => $user->id,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_active_date' => null,
        ]);

        $this->assertEquals(0, $streak->effective_current_streak);
    }
}
