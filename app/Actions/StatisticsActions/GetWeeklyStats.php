<?php

namespace App\Actions\StatisticsActions;

use Illuminate\Support\Collection;

class GetWeeklyStats
{
    /**
     * Aggregate weekly statistics over a given timeframe (number of past weeks).
     *
     * @return array{
     *     total_weeks: int,
     *     locked_weeks_count: int,
     *     average_completion_percentage: float,
     *     weekly_trends: Collection<int, array{
     *         id: int,
     *         week_start_date: string,
     *         end_date: string,
     *         planned_minutes: int,
     *         logged_minutes: int,
     *         completion_percentage: float,
     *         is_locked: bool
     *     }>
     * }
     */
    public function execute(Collection $weeks): array
    {
        $totalWeeks = $weeks->count();
        $lockedWeeksCount = 0;
        $totalCompletionPercentages = 0.0;
        $weeklyTrends = collect();

        foreach ($weeks as $week) {
            $plannedMinutes = $week->planned_minutes;
            $loggedMinutes = (int) $week->weeklyGoalPlans->sum('logged_minutes');

            $completionPercentage = $plannedMinutes > 0
                ? round(($loggedMinutes / $plannedMinutes) * 100, 1)
                : 0.0;

            if ($week->isLocked()) {
                $lockedWeeksCount++;
            }

            $totalCompletionPercentages += $completionPercentage;

            $weeklyTrends->push([
                'id' => $week->id,
                'week_start_date' => $week->week_start_date->format('Y-m-d'),
                'end_date' => $week->getEndDate()->format('Y-m-d'),
                'planned_minutes' => $plannedMinutes,
                'logged_minutes' => $loggedMinutes,
                'completion_percentage' => $completionPercentage,
                'is_locked' => $week->isLocked(),
            ]);
        }

        $averageCompletionPercentage = $totalWeeks > 0
            ? round($totalCompletionPercentages / $totalWeeks, 1)
            : 0.0;

        return [
            'total_weeks' => $totalWeeks,
            'locked_weeks_count' => $lockedWeeksCount,
            'average_completion_percentage' => $averageCompletionPercentage,
            'weekly_trends' => $weeklyTrends,
        ];
    }
}
