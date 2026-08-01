<?php

namespace Tests\Feature;

use App\Actions\WeeklyGoalPlanActions\SetWeeklyGoalPlans;
use App\Models\Goal;
use App\Models\User;
use App\Models\Week;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WeeklyGoalPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_weekly_goal_plans(): void
    {
        $user = User::factory()->create();
        $week = Week::factory()->create(['user_id' => $user->id, 'planned_minutes' => 1000]);
        $goal1 = Goal::factory()->create(['user_id' => $user->id]);
        $goal2 = Goal::factory()->create(['user_id' => $user->id]);

        $action = new SetWeeklyGoalPlans;
        $action->execute($week, [
            ['goal_id' => $goal1->id, 'priority_percentage' => 60.0],
            ['goal_id' => $goal2->id, 'priority_percentage' => 40.0],
        ]);

        $this->assertDatabaseHas('weekly_goal_plans', [
            'week_id' => $week->id,
            'goal_id' => $goal1->id,
            'priority_percentage' => 60.0,
        ]);

        $this->assertDatabaseHas('weekly_goal_plans', [
            'week_id' => $week->id,
            'goal_id' => $goal2->id,
            'priority_percentage' => 40.0,
        ]);
    }

    public function test_priorities_must_total_100_percent(): void
    {
        $user = User::factory()->create();
        $week = Week::factory()->for($user)->create();
        $goal = Goal::factory()->for($user)->create();

        $this->expectException(InvalidArgumentException::class);

        (new SetWeeklyGoalPlans)->execute($week, [
            ['goal_id' => $goal->id, 'priority_percentage' => 80.0],
        ]);
    }

    public function test_cannot_modify_plans_in_locked_week(): void
    {
        $user = User::factory()->create();
        $week = Week::factory()->for($user)->locked()->create();
        $goal = Goal::factory()->for($user)->create();

        $this->expectException(DomainException::class);

        (new SetWeeklyGoalPlans)->execute($week, [
            ['goal_id' => $goal->id, 'priority_percentage' => 100.0],
        ]);
    }

    public function test_cannot_add_another_users_goal_to_week(): void
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
}
