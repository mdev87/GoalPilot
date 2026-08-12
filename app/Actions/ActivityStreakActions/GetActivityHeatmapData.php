<?php

namespace App\Actions\ActivityStreakActions;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class GetActivityHeatmapData
{
    /**
     * Aggregate activity heatmap data for a user over a timeframe.
     *
     * @return array{
     *     total_logged_minutes: int,
     *     total_active_days: int,
     *     current_streak: int,
     *     longest_streak: int,
     *     weeks_grid: array<int, array{
     *         year_week: string,
     *         days: array<int, array{
     *             date: string,
     *             formatted_date: string,
     *             day_of_week: int,
     *             logged_minutes: int,
     *             level: int,
     *             is_future: bool
     *         }>
     *     }>,
     *     months_header: array<int, array{
     *         name: string,
     *         col_index: int
     *     }>
     * }
     */
    public function execute(User $user, int $days = 365): array
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays($days - 1)->startOfWeek(Carbon::MONDAY);
        $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch daily aggregated time entries for user
        $entries = TimeEntry::query()
            ->whereHas('weeklyGoalPlan.week', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereBetween('datetime', [$startDate->startOfDay(), $today->endOfDay()])
            ->get();

        /** @var array<string, int> $dailyMinutes */
        $dailyMinutes = [];

        foreach ($entries as $entry) {
            $dateKey = Carbon::parse($entry->datetime)->format('Y-m-d');
            $dailyMinutes[$dateKey] = ($dailyMinutes[$dateKey] ?? 0) + (int) $entry->duration_in_minutes;
        }

        $weeksGrid = [];
        $monthsHeader = [];

        $currentWeekPointer = $startDate->copy();
        $colIndex = 0;
        $lastMonthName = '';

        while ($currentWeekPointer->lte($endDate)) {
            $daysInWeek = [];

            for ($d = 0; $d < 7; $d++) {
                $dateObj = $currentWeekPointer->copy()->addDays($d);
                $dateKey = $dateObj->format('Y-m-d');
                $isFuture = $dateObj->gt($today);

                $minutes = $isFuture ? 0 : ($dailyMinutes[$dateKey] ?? 0);

                $level = match (true) {
                    $minutes === 0 => 0,
                    $minutes <= 60 => 1,
                    $minutes <= 120 => 2,
                    $minutes <= 240 => 3,
                    default => 4,
                };

                $daysInWeek[] = [
                    'date' => $dateKey,
                    'formatted_date' => $dateObj->format('M d, Y'),
                    'day_of_week' => $dateObj->dayOfWeekIso, // 1 (Mon) to 7 (Sun)
                    'logged_minutes' => $minutes,
                    'level' => $level,
                    'is_future' => $isFuture,
                ];

                // Track first occurrence of a month for the header column label
                if ($d === 0) {
                    $monthName = $dateObj->format('M');
                    if ($monthName !== $lastMonthName) {
                        $monthsHeader[] = [
                            'name' => $monthName,
                            'col_index' => $colIndex,
                        ];
                        $lastMonthName = $monthName;
                    }
                }
            }

            $weeksGrid[] = [
                'year_week' => $currentWeekPointer->format('o-W'),
                'days' => $daysInWeek,
            ];

            $colIndex++;
            $currentWeekPointer->addWeek();
        }

        $totalLoggedMinutes = array_sum($dailyMinutes);
        $totalActiveDays = count(array_filter($dailyMinutes, fn (int $mins) => $mins > 0));

        $streakModel = $user->streak;
        $currentStreak = $streakModel?->effective_current_streak ?? 0;
        $longestStreak = $streakModel?->longest_streak ?? 0;

        return [
            'total_logged_minutes' => $totalLoggedMinutes,
            'total_active_days' => $totalActiveDays,
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'weeks_grid' => $weeksGrid,
            'months_header' => $monthsHeader,
        ];
    }
}
