<?php

namespace App\Actions\GoalActions;

use App\Models\Goal;
use DomainException;

class ArchiveGoal
{
    /**
     * Archive the specified goal.
     *
     * @throws DomainException
     */
    public function execute(Goal $goal): Goal
    {
        if ($goal->isAllocatedInActiveWeek()) {
            throw new DomainException(
                'This goal cannot be archived because it is allocated in your current week\'s plan. Remove it from the weekly plan first.'
            );
        }

        $goal->update(['archived_at' => now()]);

        return $goal;
    }
}
