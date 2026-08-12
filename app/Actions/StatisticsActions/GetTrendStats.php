<?php

namespace App\Actions\StatisticsActions;

use App\Models\Goal;
use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Support\Collection;

class GetTrendStats
{
    /**
     * Calculate goal time distribution and trend statistics across all weeks for a user.
     *
     * @param  Collection<int, Week>  $weeks
     * @return array{
     *     total_historical_logged_minutes: int,
     *     goal_distributions: Collection<int, array{
     *         id: int,
     *         name: string,
     *         total_logged_minutes: int,
     *         percentage_of_total_time: float,
     *         is_archived: bool
     *     }>
     * }
     */
    public function execute(Collection $weeks): array
    {
        /** @var Collection<int, Collection<int, WeeklyGoalPlan>> */
        $weeklyPlansGroupedByGoal = $weeks->pluck('weeklyGoalPlans')
            ->flatten()
            ->groupBy('goal_id');

        $totalHistoricalLoggedMinutes = 0;
        $rawGoalData = collect();

        foreach ($weeklyPlansGroupedByGoal as $plans) {
            $goal = $plans->first()->goal;
            $goalLoggedMinutes = (int) $plans->sum('logged_minutes');
            $totalHistoricalLoggedMinutes += $goalLoggedMinutes;

            $rawGoalData->push([
                'id' => $goal->id,
                'name' => $goal->name,
                'total_logged_minutes' => $goalLoggedMinutes,
                'is_archived' => $goal->isArchived(),
            ]);
        }

        $goalDistributions = $rawGoalData->map(function (array $item) use ($totalHistoricalLoggedMinutes) {
            $percentage = $totalHistoricalLoggedMinutes > 0
                ? round(($item['total_logged_minutes'] / $totalHistoricalLoggedMinutes) * 100, 1)
                : 0.0;

            return [
                'id' => (int) $item['id'],
                'name' => (string) $item['name'],
                'total_logged_minutes' => (int) $item['total_logged_minutes'],
                'percentage_of_total_time' => (float) $percentage,
                'is_archived' => (bool) $item['is_archived'],
            ];
        })->sortByDesc('total_logged_minutes')->values();

        return [
            'total_historical_logged_minutes' => (int) $totalHistoricalLoggedMinutes,
            'goal_distributions' => $goalDistributions,
        ];
    }
}
