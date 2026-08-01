<?php

namespace Tests\Feature;

use App\Actions\TimeEntryActions\CreateTimeEntry;
use App\Actions\TimeEntryActions\DeleteTimeEntry;
use App\Actions\TimeEntryActions\UpdateTimeEntry;
use App\Events\TimeLogged;
use App\Livewire\TimeEntries\TimeLogger;
use App\Models\Goal;
use App\Models\TimeEntry;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_time_entry_and_dispatches_event(): void
    {
        Event::fake([TimeLogged::class]);

        $plan = WeeklyGoalPlan::factory()->create();
        $action = new CreateTimeEntry;

        $entry = $action->execute($plan, '2026-07-26 10:00:00', 60, 'Started research');

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'weekly_goal_plan_id' => $plan->id,
            'duration_in_minutes' => 60,
            'note' => 'Started research',
        ]);

        Event::assertDispatched(TimeLogged::class);
    }

    public function test_cannot_create_time_entry_in_locked_week(): void
    {
        $plan = WeeklyGoalPlan::factory()->create();
        $plan->week->update(['locked_at' => now()]);

        $this->expectException(DomainException::class);

        (new CreateTimeEntry)->execute($plan, now(), 30);
    }

    public function test_user_can_update_time_entry(): void
    {
        $entry = TimeEntry::factory()->create(['duration_in_minutes' => 30]);

        (new UpdateTimeEntry)->execute($entry, now(), 90, 'Updated note');

        $this->assertEquals(90, $entry->fresh()->duration_in_minutes);
        $this->assertEquals('Updated note', $entry->fresh()->note);
    }

    public function test_user_can_delete_time_entry(): void
    {
        $entry = TimeEntry::factory()->create();

        (new DeleteTimeEntry)->execute($entry);

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    public function test_time_entry_relationships_return_correct_models(): void
    {
        $entry = TimeEntry::factory()->create();

        $this->assertInstanceOf(WeeklyGoalPlan::class, $entry->weeklyGoalPlan);
        $this->assertInstanceOf(Goal::class, $entry->goal);
        $this->assertInstanceOf(Week::class, $entry->week);

        $this->assertEquals($entry->weeklyGoalPlan->goal_id, $entry->goal->id);
        $this->assertEquals($entry->weeklyGoalPlan->week_id, $entry->week->id);
        $this->assertEquals($entry->week->user_id, $entry->goal->user_id);
    }

    public function test_user_can_edit_time_entry_via_time_logger_component(): void
    {
        $plan = WeeklyGoalPlan::factory()->create();
        $user = $plan->week->user;
        $entry = TimeEntry::factory()->for($plan)->create([
            'datetime' => $plan->week->week_start_date->copy()->addHours(12),
            'duration_in_minutes' => 30,
            'note' => 'Original note',
        ]);

        Livewire::actingAs($user)
            ->test(TimeLogger::class)
            ->call('startEdit', $entry->id)
            ->set('editDurationInMinutes', 90)
            ->set('editNote', 'Updated via component')
            ->call('updateEntry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'duration_in_minutes' => 90,
            'note' => 'Updated via component',
        ]);
    }
}
