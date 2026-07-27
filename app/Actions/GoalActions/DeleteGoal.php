<?php

namespace App\Actions\GoalActions;

use App\Models\Goal;
use DomainException;

class DeleteGoal
{
    /**
     * Delete the specified goal if it has no time entries.
     *
     * @throws DomainException
     */
    public function execute(Goal $goal): bool
    {
        if (! $goal->canBeDeleted()) {
            throw new DomainException('Cannot delete goal that has logged time entries. Archive it instead.');
        }

        return (bool) $goal->delete();
    }
}
