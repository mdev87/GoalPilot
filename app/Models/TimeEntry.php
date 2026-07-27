<?php

namespace App\Models;

use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $weekly_goal_plan_id
 * @property Carbon $datetime
 * @property int $duration_in_minutes
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WeeklyGoalPlan $weeklyGoalPlan
 */
#[Fillable(['weekly_goal_plan_id', 'datetime', 'duration_in_minutes', 'note'])]
class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'datetime' => 'datetime',
            'duration_in_minutes' => 'integer',
            'weekly_goal_plan_id' => 'integer',
        ];
    }

    /**
     * Get the weekly goal plan that owns the time entry.
     *
     * @return BelongsTo<WeeklyGoalPlan, $this>
     */
    public function weeklyGoalPlan(): BelongsTo
    {
        return $this->belongsTo(WeeklyGoalPlan::class);
    }

    /**
     * Get the goal through the weekly goal plan.
     *
     * @return HasOneThrough<Goal, WeeklyGoalPlan, $this>
     */
    public function goal(): HasOneThrough
    {
        return $this->hasOneThrough(
            Goal::class,
            WeeklyGoalPlan::class,
            'id', // Foreign key on weekly_goal_plans table...
            'id', // Foreign key on goals table...
            'weekly_goal_plan_id', // Local key on time_entries table...
            'goal_id' // Local key on weekly_goal_plans table...
        );
    }

    /**
     * Get the week through the weekly goal plan.
     *
     * @return HasOneThrough<Week, WeeklyGoalPlan, $this>
     */
    public function week(): HasOneThrough
    {
        return $this->hasOneThrough(
            Week::class,
            WeeklyGoalPlan::class,
            'id',
            'id',
            'weekly_goal_plan_id',
            'week_id'
        );
    }
}
