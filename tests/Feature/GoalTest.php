<?php

namespace Tests\Feature;

use App\Actions\GoalActions\ArchiveGoal;
use App\Actions\GoalActions\CreateGoal;
use App\Actions\GoalActions\DeleteGoal;
use App\Actions\GoalActions\UnarchiveGoal;
use App\Actions\GoalActions\UpdateGoal;
use App\Models\Goal;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WeeklyGoalPlan;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_goal(): void
    {
        $user = User::factory()->create();
        $action = new CreateGoal;

        $goal = $action->execute($user, 'Deep Work', 'Focused coding hours');

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'user_id' => $user->id,
            'name' => 'Deep Work',
            'notes' => 'Focused coding hours',
            'archived_at' => null,
        ]);
    }

    public function test_user_can_update_a_goal(): void
    {
        $goal = Goal::factory()->create(['name' => 'Old Name']);
        $action = new UpdateGoal;

        $action->execute($goal, 'New Name', 'Updated notes');

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'name' => 'New Name',
            'notes' => 'Updated notes',
        ]);
    }

    public function test_user_can_archive_a_goal_not_in_active_week(): void
    {
        $goal = Goal::factory()->create();

        (new ArchiveGoal)->execute($goal);

        $this->assertTrue($goal->fresh()->isArchived());
    }

    public function test_goal_allocated_in_active_week_cannot_be_archived(): void
    {
        $goal = Goal::factory()->create();
        WeeklyGoalPlan::factory()->create(['goal_id' => $goal->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('allocated in your current week\'s plan');

        (new ArchiveGoal)->execute($goal);
    }

    public function test_user_can_unarchive_a_goal(): void
    {
        $goal = Goal::factory()->create(['archived_at' => now()]);

        (new UnarchiveGoal)->execute($goal);

        $this->assertFalse($goal->fresh()->isArchived());
    }

    public function test_goal_without_time_entries_can_be_deleted(): void
    {
        $goal = Goal::factory()->create();

        $result = (new DeleteGoal)->execute($goal);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    public function test_goal_with_time_entries_cannot_be_deleted(): void
    {
        $goal = Goal::factory()->create();
        $plan = WeeklyGoalPlan::factory()->create(['goal_id' => $goal->id]);
        TimeEntry::factory()->create(['weekly_goal_plan_id' => $plan->id]);

        $this->expectException(DomainException::class);
        (new DeleteGoal)->execute($goal);
    }

    public function test_goal_relationships_work_correctly(): void
    {
        $goal = Goal::factory()->create();
        $plan = WeeklyGoalPlan::factory()->create(['goal_id' => $goal->id]);
        $entry = TimeEntry::factory()->create(['weekly_goal_plan_id' => $plan->id]);

        $this->assertInstanceOf(User::class, $goal->user);
        $this->assertCount(1, $goal->weeklyGoalPlans);
        $this->assertCount(1, $goal->timeEntries);
        $this->assertEquals($entry->id, $goal->timeEntries->first()->id);
    }
}
