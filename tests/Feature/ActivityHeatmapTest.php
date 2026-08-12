<?php

namespace Tests\Feature;

use App\Actions\ActivityStreakActions\GetActivityHeatmapData;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ActivityHeatmapTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_activity_heatmap_data_returns_expected_structure(): void
    {
        $user = User::factory()->create();

        $action = new GetActivityHeatmapData;
        $result = $action->execute($user, 365);

        $this->assertArrayHasKey('total_logged_minutes', $result);
        $this->assertArrayHasKey('total_active_days', $result);
        $this->assertArrayHasKey('current_streak', $result);
        $this->assertArrayHasKey('longest_streak', $result);
        $this->assertArrayHasKey('weeks_grid', $result);
        $this->assertArrayHasKey('months_header', $result);

        $this->assertNotEmpty($result['weeks_grid']);
        $this->assertCount(7, $result['weeks_grid'][0]['days']);
    }

    public function test_get_activity_heatmap_data_calculates_daily_minutes_and_intensity_level(): void
    {
        $user = User::factory()->create();

        $week = Week::factory()->create([
            'user_id' => $user->id,
            'week_start_date' => Carbon::today()->startOfWeek(),
        ]);
        $plan = WeeklyGoalPlan::factory()->create([
            'week_id' => $week->id,
        ]);

        // Log 150 minutes today (level 3: 121-240 mins)
        TimeEntry::factory()->create([
            'weekly_goal_plan_id' => $plan->id,
            'duration_in_minutes' => 150,
            'datetime' => Carbon::today(),
        ]);

        $action = new GetActivityHeatmapData;
        $result = $action->execute($user, 365);

        $this->assertEquals(150, $result['total_logged_minutes']);
        $this->assertEquals(1, $result['total_active_days']);

        $lastWeek = end($result['weeks_grid']);
        $todayEntry = collect($lastWeek['days'])->firstWhere('date', Carbon::today()->format('Y-m-d'));

        $this->assertNotNull($todayEntry);
        $this->assertEquals(150, $todayEntry['logged_minutes']);
        $this->assertEquals(3, $todayEntry['level']);
    }

    public function test_dashboard_renders_heatmap_widget_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Activity Contribution Heatmap');
        $response->assertSee('365 days of activity tracking');
    }
}
