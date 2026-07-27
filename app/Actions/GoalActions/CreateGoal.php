<?php

namespace App\Actions\GoalActions;

use App\Models\Goal;
use App\Models\User;

class CreateGoal
{
    /**
     * Create a new goal for the given user.
     */
    public function execute(User $user, string $name, ?string $notes = null): Goal
    {
        return Goal::create([
            'user_id' => $user->id,
            'name' => trim($name),
            'notes' => $notes ? trim($notes) : null,
        ]);
    }
}
