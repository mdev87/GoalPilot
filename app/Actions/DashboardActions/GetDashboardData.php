<?php

namespace App\Actions\DashboardActions;

use App\Actions\ActivityStreakActions\GetActivityHeatmapData;
use App\Actions\StatisticsActions\GetTrendStats;
use App\Actions\StatisticsActions\GetWeeklyStats;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserActivityStreak;
use App\Models\Week;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class GetDashboardData
{
    public function __construct(
        private GetWeeklyStats $getWeeklyStats,
        private GetTrendStats $getTrendStats,
        private GetActivityHeatmapData $getActivityHeatmapData
    ) {}

    /**
     * Aggregate complete unified dashboard and statistics data for the user.
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
     *     recent_time_entries: Collection<int, TimeEntry>,
     *     weekly_stats: array{
     *         total_weeks: int,
     *         locked_weeks_count: int,
     *         average_completion_percentage: float,
     *         weekly_trends: Collection<int, array{
     *             id: int,
     *             week_start_date: string,
     *             end_date: string,
     *             planned_minutes: int,
     *             logged_minutes: int,
     *             completion_percentage: float,
     *             is_locked: bool
     *         }>
     *     },
     *     trend_stats: array{
     *         total_historical_logged_minutes: int,
     *         goal_distributions: Collection<int, array{
     *             id: int,
     *             name: string,
     *             total_logged_minutes: int,
     *             percentage_of_total_time: float,
     *             is_archived: bool
     *         }>
     *     }
     * }
     */
    public function execute(User $user, int $timeframeWeeks = 8): array
    {
        $weeks = $user->weeks()
            ->with([
                'weeklyGoalPlans' => fn (Relation $query) => $query->with('goal')
                    ->withSum('timeEntries', 'duration_in_minutes'),
            ])
            ->latest('week_start_date')
            ->take($timeframeWeeks)
            ->get()
            ->reverse()
            ->values();

        $activeWeek = $weeks->whereNull('locked_at')->first();
        $activeWeek?->load([
            'timeEntries' => fn (Relation $query) => $query->limit(5)
                ->latest('datetime'),
        ]);

        $totalPlannedMinutes = $activeWeek ? $activeWeek->planned_minutes : 0;
        $totalLoggedMinutes = 0;
        $goalBreakdown = collect();

        $activeWeek?->weeklyGoalPlans->each(function ($plan) use ($activeWeek, $goalBreakdown, &$totalLoggedMinutes) {
            $plan->setRelation('week', $activeWeek);

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
        });

        $overallCompletionPercentage = $totalPlannedMinutes > 0
            ? round(($totalLoggedMinutes / $totalPlannedMinutes) * 100, 1)
            : 0.0;

        $goalsByPlanId = $activeWeek ? $activeWeek->weeklyGoalPlans->keyBy('id') : collect();
        $recentEntries = $activeWeek ? $activeWeek->timeEntries->map(function ($timeEntry) use ($goalsByPlanId) {
            $plan = $goalsByPlanId->get($timeEntry->weekly_goal_plan_id);

            if ($plan && $plan->relationLoaded('goal')) {
                $timeEntry->setRelation('goal', $plan->goal);
            }

            return $timeEntry;
        })
            ->sortDesc()
            ->take(5) : collect();

        $weeklyStats = $this->getWeeklyStats->execute($weeks);
        $trendStats = $this->getTrendStats->execute($weeks);
        $heatmapData = $this->getActivityHeatmapData->execute($user);

        $streak = $user->streak;

        return [
            'active_week' => $activeWeek,
            'total_planned_minutes' => $totalPlannedMinutes,
            'total_logged_minutes' => $totalLoggedMinutes,
            'overall_completion_percentage' => $overallCompletionPercentage,
            'goal_breakdown' => $goalBreakdown,
            'current_streak' => $streak instanceof UserActivityStreak ? $streak->effective_current_streak : 0,
            'longest_streak' => $streak instanceof UserActivityStreak ? $streak->longest_streak : 0,
            'recent_time_entries' => $recentEntries,
            'weekly_stats' => $weeklyStats,
            'trend_stats' => $trendStats,
            'heatmap_data' => $heatmapData,
        ];
    }
}
