<?php

namespace App\Actions\TimeEntryActions;

use App\Events\TimeLogged;
use App\Models\TimeEntry;
use App\Models\WeeklyGoalPlan;
use DomainException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class CreateTimeEntry
{
    /**
     * Create a new time entry for a weekly goal plan.
     *
     * @throws DomainException|InvalidArgumentException
     */
    public function execute(WeeklyGoalPlan $weeklyGoalPlan, string|Carbon $datetime, int $durationInMinutes, ?string $note = null): TimeEntry
    {
        if ($weeklyGoalPlan->week->isLocked()) {
            throw new DomainException('Cannot log time for a locked week.');
        }

        if ($durationInMinutes <= 0) {
            throw new InvalidArgumentException('Duration must be greater than zero minutes.');
        }

        $entry = TimeEntry::create([
            'weekly_goal_plan_id' => $weeklyGoalPlan->id,
            'datetime' => is_string($datetime) ? Carbon::parse($datetime) : $datetime,
            'duration_in_minutes' => $durationInMinutes,
            'note' => $note ? trim($note) : null,
        ]);

        TimeLogged::dispatch($entry);

        return $entry;
    }
}
