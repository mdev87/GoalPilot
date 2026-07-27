<?php

namespace App\Actions\GoalActions;

use App\Models\Goal;

class ArchiveGoal
{
    /**
     * Archive the specified goal.
     */
    public function execute(Goal $goal): Goal
    {
        $goal->update(['archived_at' => now()]);

        return $goal;
    }
}
