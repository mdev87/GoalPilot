<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TimeEntry $timeEntry): bool
    {
        return $user->id === $timeEntry->weeklyGoalPlan->week->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimeEntry $timeEntry): bool
    {
        return $user->id === $timeEntry->weeklyGoalPlan->week->user_id && ! $timeEntry->weeklyGoalPlan->week->isLocked();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        return $user->id === $timeEntry->weeklyGoalPlan->week->user_id && ! $timeEntry->weeklyGoalPlan->week->isLocked();
    }
}
