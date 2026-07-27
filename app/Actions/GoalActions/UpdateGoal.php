<?php

namespace App\Actions\GoalActions;

use App\Models\Goal;

class UpdateGoal
{
    /**
     * Update an existing goal.
     */
    public function execute(Goal $goal, string $name, ?string $notes = null): Goal
    {
        $goal->update([
            'name' => trim($name),
            'notes' => $notes ? trim($notes) : null,
        ]);

        return $goal;
    }
}
