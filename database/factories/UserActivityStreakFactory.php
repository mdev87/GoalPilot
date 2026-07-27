<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserActivityStreak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserActivityStreak>
 */
class UserActivityStreakFactory extends Factory
{
    protected $model = UserActivityStreak::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'current_streak' => 1,
            'longest_streak' => 1,
            'last_active_date' => now()->toDateString(),
        ];
    }
}
