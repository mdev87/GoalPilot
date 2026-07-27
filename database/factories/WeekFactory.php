<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Week>
 */
class WeekFactory extends Factory
{
    protected $model = Week::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'week_start_date' => fake()->date(),
            'planned_minutes' => config('week.default_planned_minutes', 1200),
            'locked_at' => null,
        ];
    }

    /**
     * Indicate that the week is locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'locked_at' => now(),
        ]);
    }
}
