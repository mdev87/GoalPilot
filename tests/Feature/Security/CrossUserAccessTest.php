<?php

namespace Tests\Feature\Security;

use App\Actions\WeeklyGoalPlanActions\SetWeeklyGoalPlans;
use App\Livewire\TimeEntries\TimeLogger;
use App\Livewire\Weeks\WeekPlanner;
use App\Models\Goal;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class CrossUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_add_another_users_goal_via_action(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $week = Week::factory()->for($user1)->create();
        $otherUserGoal = Goal::factory()->for($user2)->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Goal ID {$otherUserGoal->id} does not belong to the owner of this week.");

        (new SetWeeklyGoalPlans)->execute($week, [
            ['goal_id' => $otherUserGoal->id, 'priority_percentage' => 100.0],
        ]);
    }

    public function test_user_cannot_save_plans_with_another_users_goal_via_week_planner_component(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $week = Week::factory()->for($user1)->create();
        $otherUserGoal = Goal::factory()->for($user2)->create();

        Livewire::actingAs($user1)
            ->test(WeekPlanner::class)
            ->set('priorities', [
                $otherUserGoal->id => 100.0,
            ])
            ->call('savePlans')
            ->assertSet('errorMessage', "Goal ID {$otherUserGoal->id} does not belong to the owner of this week.");

        $this->assertDatabaseMissing('weekly_goal_plans', [
            'week_id' => $week->id,
            'goal_id' => $otherUserGoal->id,
        ]);
    }

    public function test_user_cannot_log_time_for_another_users_plan_via_time_logger_component(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1Week = Week::factory()->for($user1)->create([
            'week_start_date' => now()->startOfWeek(),
        ]);

        $otherUserPlan = WeeklyGoalPlan::factory()->create(); // created for user2 via factory

        Livewire::actingAs($user1)
            ->test(TimeLogger::class)
            ->set('weeklyGoalPlanId', $otherUserPlan->id)
            ->set('datetime', $user1Week->week_start_date->format('Y-m-d\TH:i'))
            ->set('durationInMinutes', 30)
            ->call('logTime')
            ->assertForbidden();

        $this->assertDatabaseMissing('time_entries', [
            'weekly_goal_plan_id' => $otherUserPlan->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_time_entry_via_time_logger_component(): void
    {
        $user1 = User::factory()->create();
        $otherUserEntry = TimeEntry::factory()->create();

        Livewire::actingAs($user1)
            ->test(TimeLogger::class)
            ->call('deleteEntry', $otherUserEntry->id)
            ->assertForbidden();

        $this->assertDatabaseHas('time_entries', [
            'id' => $otherUserEntry->id,
        ]);
    }
}
