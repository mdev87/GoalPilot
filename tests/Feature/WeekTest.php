<?php

namespace Tests\Feature;

use App\Actions\WeekActions\CreateWeek;
use App\Actions\WeekActions\LockWeek;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WeekTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_week(): void
    {
        $user = User::factory()->create();
        $action = new CreateWeek;

        $week = $action->execute($user, '2026-08-01', 1200);

        $this->assertDatabaseHas('weeks', [
            'id' => $week->id,
            'user_id' => $user->id,
            'planned_minutes' => 1200,
            'locked_at' => null,
        ]);
        $this->assertEquals('2026-08-01', $week->week_start_date->toDateString());
    }

    public function test_creating_a_new_week_locks_the_previous_active_week(): void
    {
        $user = User::factory()->create();
        $oldWeek = Week::factory()->create([
            'user_id' => $user->id,
            'week_start_date' => '2026-07-18',
            'locked_at' => null,
        ]);

        $newWeek = (new CreateWeek)->execute($user, '2026-07-25', 1200);

        $this->assertTrue($oldWeek->fresh()->isLocked());
        $this->assertFalse($newWeek->isLocked());
    }

    public function test_cannot_create_week_with_invalid_planned_minutes(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        (new CreateWeek)->execute($user, '2026-08-01', 10); // Less than min 30
    }

    public function test_cannot_create_duplicate_week_start_date(): void
    {
        $user = User::factory()->create();
        (new CreateWeek)->execute($user, '2026-07-01', 1200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A week starting on 2026-07-01 already exists for your account.');

        (new CreateWeek)->execute($user, '2026-07-01', 1200);
    }

    public function test_user_can_lock_a_week_that_has_ended(): void
    {
        $week = Week::factory()->create([
            'week_start_date' => now()->subDays(10)->toDateString(),
            'locked_at' => null,
        ]);

        (new LockWeek)->execute($week);

        $this->assertTrue($week->fresh()->isLocked());
    }

    public function test_cannot_lock_week_before_end_date(): void
    {
        $week = Week::factory()->create([
            'week_start_date' => now()->toDateString(), // Started today, has not ended
            'locked_at' => null,
        ]);

        $this->expectException(\DomainException::class);
        (new LockWeek)->execute($week);
    }

    public function test_week_relationships_work_correctly(): void
    {
        $week = Week::factory()->create();
        $plan = WeeklyGoalPlan::factory()->create(['week_id' => $week->id]);
        $entry = TimeEntry::factory()->create(['weekly_goal_plan_id' => $plan->id]);

        $this->assertInstanceOf(User::class, $week->user);
        $this->assertCount(1, $week->weeklyGoalPlans);
        $this->assertCount(1, $week->goals);
        $this->assertCount(1, $week->timeEntries);
        $this->assertEquals($entry->id, $week->timeEntries->first()->id);
    }
}
