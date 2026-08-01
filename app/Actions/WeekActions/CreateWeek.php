<?php

namespace App\Actions\WeekActions;

use App\Events\WeekCreated;
use App\Models\User;
use App\Models\Week;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class CreateWeek
{
    /**
     * Create a new week for the given user, automatically locking any currently active week.
     *
     * @throws InvalidArgumentException
     */
    public function execute(User $user, string|Carbon $weekStartDate, ?int $plannedMinutes = null): Week
    {
        $min = (int) config('week.min_planned_minutes', 30);
        $max = (int) config('week.max_planned_minutes', 4800);
        $default = (int) config('week.default_planned_minutes', 1200);

        $plannedMinutes = $plannedMinutes ?? $default;

        if ($plannedMinutes < $min || $plannedMinutes > $max) {
            throw new InvalidArgumentException("Planned minutes must be between {$min} and {$max}.");
        }

        $startDate = is_string($weekStartDate) ? Carbon::parse($weekStartDate)->toDateString() : $weekStartDate->toDateString();

        if ($user->weeks()->whereDate('week_start_date', $startDate)->exists()) {
            throw new InvalidArgumentException("A week starting on {$startDate} already exists for your account.");
        }

        // Lock any currently active week for the user
        $user->weeks()->whereNull('locked_at')->update(['locked_at' => now()]);

        $week = Week::create([
            'user_id' => $user->id,
            'week_start_date' => $startDate,
            'planned_minutes' => $plannedMinutes,
            'locked_at' => null,
        ]);

        WeekCreated::dispatch($week);

        return $week;
    }
}
