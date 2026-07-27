<?php

namespace App\Actions\GoalActions;

use App\Models\Goal;

class UnarchiveGoal
{
    /**
     * Unarchive the specified goal.
     */
    public function execute(Goal $goal): Goal
    {
        $goal->update(['archived_at' => null]);

        return $goal;
    }
}
