<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Dashboard;
use App\Models\Goal;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\UserActivityStreak;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);
        Week::factory()->create(['user_id' => $user->id, 'locked_at' => null]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_renders_livewire_dashboard_component(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);
        Week::factory()->create(['user_id' => $user->id, 'locked_at' => null]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertViewIs('pages.dashboard')
            ->assertViewHas('weekly_stats')
            ->assertViewHas('trend_stats');
    }

    public function test_dashboard_correctly_calculates_active_week_planned_and_logged_minutes(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create([
            'user_id' => $user->id,
            'current_streak' => 3,
            'longest_streak' => 7,
        ]);

        $goal1 = Goal::factory()->create(['user_id' => $user->id, 'name' => 'Coding']);
        $goal2 = Goal::factory()->create(['user_id' => $user->id, 'name' => 'Reading']);

        $week = Week::factory()->create([
            'user_id' => $user->id,
            'planned_minutes' => 1000,
            'locked_at' => null,
        ]);

        $plan1 = WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
            'goal_id' => $goal1->id,
            'priority_percentage' => 60.0,
        ]);

        $plan2 = WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
            'goal_id' => $goal2->id,
            'priority_percentage' => 40.0,
        ]);

        TimeEntry::factory()->create([
            'weekly_goal_plan_id' => $plan1->id,
            'duration_in_minutes' => 300,
            'datetime' => now(),
        ]);

        TimeEntry::factory()->create([
            'weekly_goal_plan_id' => $plan2->id,
            'duration_in_minutes' => 200,
            'datetime' => now()->subHour(),
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertViewHas('total_planned_minutes', 1000)
            ->assertViewHas('total_logged_minutes', 500)
            ->assertViewHas('overall_completion_percentage', 50.0)
            ->assertSee('Coding')
            ->assertSee('Reading');
    }

    public function test_dashboard_filters_out_locked_weeks_for_active_week_card(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);

        // Older locked week
        Week::factory()->create([
            'user_id' => $user->id,
            'planned_minutes' => 1200,
            'locked_at' => now()->subWeek(),
        ]);

        // Current active week
        $activeWeek = Week::factory()->create([
            'user_id' => $user->id,
            'planned_minutes' => 800,
            'locked_at' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertViewHas('active_week', function ($week) use ($activeWeek) {
                return $week !== null && $week->id === $activeWeek->id;
            })
            ->assertViewHas('total_planned_minutes', 800);
    }

    public function test_timeframe_change_updates_timeframe_weeks_property(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        UserActivityStreak::factory()->create(['user_id' => $user->id]);
        Week::factory()->create(['user_id' => $user->id, 'locked_at' => null]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSet('timeframeWeeks', 8)
            ->call('setTimeframe', 4)
            ->assertSet('timeframeWeeks', 4)
            ->call('setTimeframe', 12)
            ->assertSet('timeframeWeeks', 12)
            ->call('setTimeframe', 52)
            ->assertSet('timeframeWeeks', 52)
            ->call('setTimeframe', 999) // invalid input falls back to default 8
            ->assertSet('timeframeWeeks', 8);
    }
}
