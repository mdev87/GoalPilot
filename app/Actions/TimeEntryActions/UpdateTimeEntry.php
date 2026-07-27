<?php

namespace App\Actions\TimeEntryActions;

use App\Models\TimeEntry;
use DomainException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class UpdateTimeEntry
{
    /**
     * Update an existing time entry.
     *
     * @throws DomainException|InvalidArgumentException
     */
    public function execute(TimeEntry $timeEntry, string|Carbon $datetime, int $durationInMinutes, ?string $note = null): TimeEntry
    {
        if ($timeEntry->weeklyGoalPlan->week->isLocked()) {
            throw new DomainException('Cannot update time entries in a locked week.');
        }

        if ($durationInMinutes <= 0) {
            throw new InvalidArgumentException('Duration must be greater than zero minutes.');
        }

        $timeEntry->update([
            'datetime' => is_string($datetime) ? Carbon::parse($datetime) : $datetime,
            'duration_in_minutes' => $durationInMinutes,
            'note' => $note ? trim($note) : null,
        ]);

        return $timeEntry;
    }
}
