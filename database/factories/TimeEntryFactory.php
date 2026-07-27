<?php

namespace Database\Factories;

use App\Models\TimeEntry;
use App\Models\WeeklyGoalPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weekly_goal_plan_id' => WeeklyGoalPlan::factory(),
            'datetime' => now(),
            'duration_in_minutes' => 60,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
