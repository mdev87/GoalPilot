<?php

namespace App\Listeners;

use App\Events\TimeLogged;
use App\Models\UserActivityStreak;
use Illuminate\Support\Carbon;

class UpdateStreakOnTimeLogged
{
    /**
     * Handle the event.
     */
    public function handle(TimeLogged $event): void
    {
        $timeEntry = $event->timeEntry;
        $user = $timeEntry->weeklyGoalPlan->week->user;

        $streak = UserActivityStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_active_date' => null]
        );

        $entryDate = Carbon::parse($timeEntry->datetime)->startOfDay();

        if ($streak->last_active_date === null) {
            $streak->current_streak = 1;
            $streak->longest_streak = max(1, $streak->longest_streak);
            $streak->last_active_date = $entryDate;
            $streak->save();

            return;
        }

        $lastActive = Carbon::parse($streak->last_active_date)->startOfDay();

        if ($entryDate->equalTo($lastActive)) {
            return;
        }

        if ($entryDate->equalTo($lastActive->copy()->addDay())) {
            $streak->current_streak += 1;
            $streak->longest_streak = max($streak->current_streak, $streak->longest_streak);
            $streak->last_active_date = $entryDate;
            $streak->save();

            return;
        }

        if ($entryDate->greaterThan($lastActive->copy()->addDay())) {
            $streak->current_streak = 1;
            $streak->longest_streak = max(1, $streak->longest_streak);
            $streak->last_active_date = $entryDate;
            $streak->save();
        }
    }
}
