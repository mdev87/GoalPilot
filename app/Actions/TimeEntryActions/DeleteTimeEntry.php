<?php

namespace App\Actions\TimeEntryActions;

use App\Models\TimeEntry;
use DomainException;

class DeleteTimeEntry
{
    /**
     * Delete a time entry.
     *
     * @throws DomainException
     */
    public function execute(TimeEntry $timeEntry): bool
    {
        if ($timeEntry->weeklyGoalPlan->week->isLocked()) {
            throw new DomainException('Cannot delete time entries from a locked week.');
        }

        return (bool) $timeEntry->delete();
    }
}
