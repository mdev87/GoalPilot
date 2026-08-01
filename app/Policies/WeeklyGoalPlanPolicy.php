<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeeklyGoalPlan;

class WeeklyGoalPlanPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WeeklyGoalPlan $weeklyGoalPlan): bool
    {
        return $user->id === $weeklyGoalPlan->week->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WeeklyGoalPlan $weeklyGoalPlan): bool
    {
        return $user->id === $weeklyGoalPlan->week->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WeeklyGoalPlan $weeklyGoalPlan): bool
    {
        return $user->id === $weeklyGoalPlan->week->user_id;
    }
}
