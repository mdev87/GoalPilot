<?php

namespace App\Actions\DashboardActions;

use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Support\Collection;

class GetDashboardData
{
    /**
     * Aggregate dashboard summary data for the given user.
     *
     * @return array{
     *     active_week: Week|null,
     *     total_planned_minutes: int,
     *     total_logged_minutes: int,
     *     overall_completion_percentage: float,
     *     goal_breakdown: Collection<int, array{
     *         id: int,
     *         name: string,
     *         priority_percentage: float,
     *         allocated_minutes: int,
     *         logged_minutes: int,
     *         completion_percentage: float
     *     }>,
     *     current_streak: int,
     *     longest_streak: int,
     *     recent_time_entries: Collection
     * }
     */
    public function execute(User $user): array
    {
        /** @var Week|null $activeWeek */
        $activeWeek = $user->weeks()
            ->active()
            ->with(['weeklyGoalPlans.goal', 'weeklyGoalPlans.timeEntries'])
            ->latest('id')
            ->first();

        $totalPlannedMinutes = $activeWeek ? $activeWeek->planned_minutes : 0;
        $totalLoggedMinutes = 0;
        $goalBreakdown = collect();

        if ($activeWeek) {
            /** @var Collection<int, WeeklyGoalPlan> $plans */
            $plans = $activeWeek->weeklyGoalPlans;

            foreach ($plans as $plan) {
                $logged = $plan->logged_minutes;
                $allocated = $plan->allocated_minutes;
                $totalLoggedMinutes += $logged;

                $completionPercentage = $allocated > 0
                    ? round(($logged / $allocated) * 100, 1)
                    : 0.0;

                $goalBreakdown->push([
                    'id' => $plan->goal->id,
                    'name' => $plan->goal->name,
                    'priority_percentage' => (float) $plan->priority_percentage,
                    'allocated_minutes' => $allocated,
                    'logged_minutes' => $logged,
                    'completion_percentage' => $completionPercentage,
                ]);
            }
        }

        $overallCompletionPercentage = $totalPlannedMinutes > 0
            ? round(($totalLoggedMinutes / $totalPlannedMinutes) * 100, 1)
            : 0.0;

        $streak = $user->streak;

        $recentEntries = $user->weeks()
            ->with(['weeklyGoalPlans.goal', 'weeklyGoalPlans.timeEntries' => fn($query) => $query->latest('datetime')])
            ->get()
            ->flatMap(fn(Week $w) => $w->timeEntries)
            ->sortByDesc('datetime')
            ->take(5)
            ->values();

        return [
            'active_week' => $activeWeek,
            'total_planned_minutes' => $totalPlannedMinutes,
            'total_logged_minutes' => $totalLoggedMinutes,
            'overall_completion_percentage' => $overallCompletionPercentage,
            'goal_breakdown' => $goalBreakdown,
            'current_streak' => $streak ? $streak->current_streak : 0,
            'longest_streak' => $streak ? $streak->longest_streak : 0,
            'recent_time_entries' => $recentEntries,
        ];
    }
}
