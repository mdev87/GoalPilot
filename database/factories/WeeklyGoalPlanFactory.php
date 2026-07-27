<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\Week;
use App\Models\WeeklyGoalPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<WeeklyGoalPlan>
 */
class WeeklyGoalPlanFactory extends Factory
{
    protected $model = WeeklyGoalPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'week_id' => Week::factory(),
            'goal_id' => Goal::factory(),
            'priority_percentage' => 100.0,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (WeeklyGoalPlan $plan) {
            $weekUser = DB::table('weeks')->where('id', $plan->week_id)->value('user_id');
            $goalUser = DB::table('goals')->where('id', $plan->goal_id)->value('user_id');

            if ($weekUser && $goalUser && $weekUser !== $goalUser) {
                DB::table('goals')->where('id', $plan->goal_id)->update(['user_id' => $weekUser]);
            }
        });
    }
}
