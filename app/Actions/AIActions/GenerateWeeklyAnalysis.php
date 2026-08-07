<?php

namespace App\Actions\AIActions;

use App\Ai\Agents\WeeklyAnalysisAgent;
use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Validation\ValidationException;

class GenerateWeeklyAnalysis
{
    /**
     * Generate an AI-powered weekly performance analysis for the given user.
     *
     * @return array{
     *     summary: string,
     *     achievements: array<int, string>,
     *     areas_for_improvement: array<int, string>,
     *     actionable_recommendations: array<int, string>,
     *     execution_score: int
     * }
     *
     * @throws ValidationException
     */
    public function execute(User $user, ?Week $week = null): array
    {
        $targetWeek = $week ?? $user->activeWeek()
            ->with(['weeklyGoalPlans.goal', 'weeklyGoalPlans.timeEntries'])
            ->first();

        if (! $targetWeek || $targetWeek->weeklyGoalPlans->isEmpty()) {
            throw ValidationException::withMessages([
                'week' => __('No active week with goal allocations was found to analyze.'),
            ]);
        }

        $targetWeek->loadMissing(['weeklyGoalPlans.goal', 'weeklyGoalPlans.timeEntries']);

        $totalPlannedMinutes = $targetWeek->planned_minutes;
        $totalLoggedMinutes = $targetWeek->weeklyGoalPlans->sum(
            fn (WeeklyGoalPlan $plan) => $plan->timeEntries->sum('duration_in_minutes')
        );

        $overallCompletionPercentage = $totalPlannedMinutes > 0
            ? min(100, (int) round(($totalLoggedMinutes / $totalPlannedMinutes) * 100))
            : 0;

        $startDateFormatted = $targetWeek->week_start_date->format('Y-m-d');
        $endDateFormatted = $targetWeek->getEndDate()->format('Y-m-d');

        $allocationsSummary = [];
        $recentNotes = [];

        foreach ($targetWeek->weeklyGoalPlans as $plan) {
            $goalName = $plan->goal->name ?? 'Unnamed Goal';
            $allocatedMins = (int) round($totalPlannedMinutes * ($plan->priority_percentage / 100));
            $loggedMins = $plan->timeEntries->sum('duration_in_minutes');
            $completion = $allocatedMins > 0
                ? (int) round(($loggedMins / $allocatedMins) * 100)
                : 0;

            $allocationsSummary[] = sprintf(
                '- Goal: %s | Priority: %d%% | Allocated: %d mins (%.1f hrs) | Logged: %d mins (%.1f hrs) | Progress: %d%%',
                $goalName,
                $plan->priority_percentage,
                $allocatedMins,
                $allocatedMins / 60,
                $loggedMins,
                $loggedMins / 60,
                $completion
            );

            foreach ($plan->timeEntries as $entry) {
                if (! empty($entry->note)) {
                    $recentNotes[] = sprintf('- [%s]: %s', $goalName, $entry->note);
                }
            }
        }

        $promptContext = sprintf(
            "User: %s\nWeek Period: %s to %s\nStatus: %s\nTotal Planned Time: %d minutes (%.1f hours)\nTotal Logged Time: %d minutes (%.1f hours)\nOverall Completion Rate: %d%%\n\nGoal Allocations Breakdown:\n%s\n\nRecent Entry Notes:\n%s",
            $user->name,
            $startDateFormatted,
            $endDateFormatted,
            $targetWeek->isLocked() ? 'Locked (Historical)' : 'Active (Current)',
            $totalPlannedMinutes,
            $totalPlannedMinutes / 60,
            $totalLoggedMinutes,
            $totalLoggedMinutes / 60,
            $overallCompletionPercentage,
            implode("\n", $allocationsSummary),
            ! empty($recentNotes) ? implode("\n", array_slice($recentNotes, 0, 10)) : 'No notes recorded.'
        );

        $agent = new WeeklyAnalysisAgent;
        $model = env('AI_MODEL', 'openai/gpt-4o-mini');
        $response = $agent->prompt($promptContext, model: $model);

        /** @var string $summary */
        $summary = $response['summary'] ?? '';

        /** @var array<int, string> $achievements */
        $achievements = array_values((array) ($response['achievements'] ?? []));

        /** @var array<int, string> $areasForImprovement */
        $areasForImprovement = array_values((array) ($response['areas_for_improvement'] ?? []));

        /** @var array<int, string> $actionableRecommendations */
        $actionableRecommendations = array_values((array) ($response['actionable_recommendations'] ?? []));

        /** @var int $executionScore */
        $executionScore = (int) ($response['execution_score'] ?? 5);

        return [
            'summary' => $summary,
            'achievements' => $achievements,
            'areas_for_improvement' => $areasForImprovement,
            'actionable_recommendations' => $actionableRecommendations,
            'execution_score' => $executionScore,
        ];
    }
}
