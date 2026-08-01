<?php

namespace Tests\Feature;

use App\Events\TimeLogged;
use App\Listeners\UpdateStreakOnTimeLogged;
use App\Models\TimeEntry;
use App\Models\UserActivityStreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_logging_time_updates_user_activity_streak(): void
    {
        $entry = TimeEntry::factory()->create(['datetime' => now()]);
        $user = $entry->weeklyGoalPlan->week->user;

        $listener = new UpdateStreakOnTimeLogged;
        $listener->handle(new TimeLogged($entry));

        $streak = UserActivityStreak::where('user_id', $user->id)->first();

        $this->assertNotNull($streak);
        $this->assertEquals(1, $streak->current_streak);
        $this->assertEquals(1, $streak->longest_streak);
    }
}
