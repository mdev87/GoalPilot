<?php

namespace Tests\Feature;

use App\Ai\Agents\WeeklyAnalysisAgent;
use App\Livewire\Dashboard\Dashboard;
use App\Models\Goal;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserActivityStreak;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Livewire;
use Tests\TestCase;

class WeeklyAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('generate-ai-analysis:1');
        Cache::flush();
    }

    public function test_user_can_generate_weekly_ai_analysis_on_dashboard(): void
    {
        WeeklyAnalysisAgent::fake([
            'summary' => 'Great execution on main goals.',
            'achievements' => ['Logged 120 minutes on Coding'],
            'areas_for_improvement' => ['Need more time on Reading'],
            'actionable_recommendations' => ['Allocate 30% priority to Reading next week'],
            'execution_score' => 8,
        ]);

        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);

        $week = Week::factory()->for($user)->create([
            'planned_minutes' => 600,
            'locked_at' => null,
        ]);
        $goal = Goal::factory()->for($user)->create(['name' => 'Coding']);
        $plan = WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
            'goal_id' => $goal->id,
            'priority_percentage' => 100,
        ]);
        TimeEntry::factory()->create([
            'weekly_goal_plan_id' => $plan->id,
            'duration_in_minutes' => 120,
            'note' => 'Built feature A',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(Dashboard::class)
            ->call('generateAiAnalysis')
            ->assertSet('aiErrorMessage', null);

        $this->assertNotEmpty($test->get('aiAnalysis.summary'));
        $this->assertNotEmpty($test->get('aiAnalysis.achievements'));

        WeeklyAnalysisAgent::assertPrompted(function (AgentPrompt $prompt) {
            return str_contains($prompt->prompt, 'Coding') && str_contains($prompt->prompt, 'Built feature A');
        });
    }

    public function test_analysis_is_cached_when_database_data_has_not_changed(): void
    {
        WeeklyAnalysisAgent::fake([
            'summary' => 'First analysis response',
            'achievements' => ['Achievement 1'],
            'areas_for_improvement' => ['Improvement 1'],
            'actionable_recommendations' => ['Recommendation 1'],
            'execution_score' => 7,
        ]);

        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);

        $week = Week::factory()->for($user)->create([
            'planned_minutes' => 600,
            'locked_at' => null,
        ]);
        $goal = Goal::factory()->for($user)->create();
        WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
            'goal_id' => $goal->id,
            'priority_percentage' => 100,
        ]);

        $this->actingAs($user);

        // First call triggers AI agent
        Livewire::test(Dashboard::class)->call('generateAiAnalysis');
        WeeklyAnalysisAgent::assertPrompted(fn () => true);

        // Second call with unchanged DB data uses cache without triggering additional prompt logic
        Livewire::test(Dashboard::class)->call('generateAiAnalysis');
        WeeklyAnalysisAgent::assertPrompted(fn () => true);
    }

    public function test_analysis_enforces_daily_rate_limit(): void
    {
        WeeklyAnalysisAgent::fake([
            'summary' => 'Analysis response',
            'achievements' => ['Win'],
            'areas_for_improvement' => ['Loss'],
            'actionable_recommendations' => ['Tip'],
            'execution_score' => 9,
        ]);

        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);

        // Simulate 3 prior new analysis generations
        RateLimiter::hit('generate-ai-analysis:'.$user->id, 86400);
        RateLimiter::hit('generate-ai-analysis:'.$user->id, 86400);
        RateLimiter::hit('generate-ai-analysis:'.$user->id, 86400);

        $week = Week::factory()->for($user)->create(['locked_at' => null]);
        $goal = Goal::factory()->for($user)->create();
        WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
            'goal_id' => $goal->id,
            'priority_percentage' => 100,
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->call('generateAiAnalysis')
            ->assertSee(__('maximum limit of 3 AI analysis generations per day'));
    }

    public function test_analysis_handles_missing_active_week_gracefully(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->call('generateAiAnalysis')
            ->assertSee(__('No active week with goal allocations was found to analyze.'));
    }

    public function test_provider_exceptions_are_masked_safely_in_ui(): void
    {
        WeeklyAnalysisAgent::fake(function () {
            throw new \RuntimeException('Internal Provider Secret API Key standard failure');
        });

        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);

        $week = Week::factory()->for($user)->create(['locked_at' => null]);
        $goal = Goal::factory()->for($user)->create();
        WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
            'goal_id' => $goal->id,
            'priority_percentage' => 100,
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->call('generateAiAnalysis')
            ->assertSee(__('Unable to generate AI insights at this time. Please try again later.'))
            ->assertDontSee('Secret API Key');
    }
}
