<?php

namespace App\Actions\WeeklyGoalPlanActions;

use App\Models\Goal;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SetWeeklyGoalPlans
{
    /**
     * Set the goal plan priorities for a week.
     *
     * @param  array<int, array{goal_id: int, priority_percentage: float}>  $plans
     *
     * @throws DomainException|InvalidArgumentException
     */
    public function execute(Week $week, array $plans): void
    {
        if ($week->isLocked()) {
            throw new DomainException('Cannot modify a locked week.');
        }

        $totalPriority = array_reduce($plans, function ($carry, $plan) {
            return $carry + (float) $plan['priority_percentage'];
        }, 0.0);

        if (abs($totalPriority - 100.0) > 0.01) {
            throw new InvalidArgumentException("Total priority percentage must equal 100%. Current total: {$totalPriority}%.");
        }

        DB::transaction(function () use ($week, $plans) {
            $existingGoalIds = [];

            foreach ($plans as $plan) {
                $goalId = (int) $plan['goal_id'];
                $priority = (float) $plan['priority_percentage'];

                $goal = Goal::find($goalId);
                if (! $goal || $goal->user_id !== $week->user_id) {
                    throw new InvalidArgumentException("Goal ID {$goalId} does not belong to the owner of this week.");
                }

                $existingGoalIds[] = $goalId;

                WeeklyGoalPlan::updateOrCreate(
                    [
                        'week_id' => $week->id,
                        'goal_id' => $goalId,
                    ],
                    [
                        'priority_percentage' => $priority,
                    ]
                );
            }

            // Remove plans for goals no longer included in the plan
            $week->weeklyGoalPlans()->whereNotIn('goal_id', $existingGoalIds)->delete();
        });
    }
}
