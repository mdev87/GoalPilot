<?php

namespace App\Actions\AIActions;

use App\Ai\Agents\WeeklyAnalysisAgent;
use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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

        $plansCount = $targetWeek->weeklyGoalPlans->count();
        $entriesCount = $targetWeek->weeklyGoalPlans->sum(fn (WeeklyGoalPlan $p) => $p->timeEntries->count());
        $targetWeekUpdatedAt = $targetWeek->updated_at;
        $latestPlanUpdated = $targetWeek->weeklyGoalPlans->max('updated_at');
        $latestEntryUpdated = $targetWeek->weeklyGoalPlans
            ->flatMap(fn (WeeklyGoalPlan $p) => $p->timeEntries)
            ->max('updated_at');

        $latestPlanTimestamp = $latestPlanUpdated instanceof \DateTimeInterface ? $latestPlanUpdated->getTimestamp() : 0;
        $latestEntryTimestamp = $latestEntryUpdated instanceof \DateTimeInterface ? $latestEntryUpdated->getTimestamp() : 0;

        $dataHash = md5(implode('|', [
            $targetWeek->id,
            $targetWeekUpdatedAt instanceof \DateTimeInterface ? $targetWeekUpdatedAt->getTimestamp() : 0,
            $totalPlannedMinutes,
            $totalLoggedMinutes,
            $plansCount,
            $entriesCount,
            $latestPlanTimestamp,
            $latestEntryTimestamp,
        ]));

        $cacheKey = "user_{$user->id}_week_{$targetWeek->id}_ai_analysis_{$dataHash}";

        if (Cache::has($cacheKey)) {
            /** @var array{summary: string, achievements: array<int, string>, areas_for_improvement: array<int, string>, actionable_recommendations: array<int, string>, execution_score: int} $cached */
            $cached = Cache::get($cacheKey);

            return $cached;
        }

        $rateLimitKey = 'generate-ai-analysis:'.$user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $hours = ceil($seconds / 3600);

            throw ValidationException::withMessages([
                'rate_limit' => __('You have reached the maximum limit of 3 AI analysis generations per day. Please try again in :hours hour(s).', ['hours' => $hours]),
            ]);
        }

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

        RateLimiter::hit($rateLimitKey, decaySeconds: 86400);

        $agent = new WeeklyAnalysisAgent;
        /** @var string $model */
        $model = config('ai.model', 'openai/gpt-4o-mini');
        $response = $agent->prompt($promptContext, model: $model);

        /** @var array<string, mixed> $responseData */
        $responseData = method_exists($response, 'toArray') ? $response->toArray() : (array) $response;

        /** @var string $summary */
        $summary = (string) ($responseData['summary'] ?? '');

        /** @var array<int, string> $achievements */
        $achievements = array_values((array) ($responseData['achievements'] ?? []));

        /** @var array<int, string> $areasForImprovement */
        $areasForImprovement = array_values((array) ($responseData['areas_for_improvement'] ?? []));

        /** @var array<int, string> $actionableRecommendations */
        $actionableRecommendations = array_values((array) ($responseData['actionable_recommendations'] ?? []));

        /** @var int $executionScore */
        $executionScore = (int) ($responseData['execution_score'] ?? 5);

        $result = [
            'summary' => $summary,
            'achievements' => $achievements,
            'areas_for_improvement' => $areasForImprovement,
            'actionable_recommendations' => $actionableRecommendations,
            'execution_score' => $executionScore,
        ];

        Cache::put($cacheKey, $result, now()->addDays(7));

        return $result;
    }
}
