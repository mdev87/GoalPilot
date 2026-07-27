<?php

namespace App\Models;

use Database\Factories\WeeklyGoalPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $week_id
 * @property int $goal_id
 * @property float $priority_percentage
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Week $week
 * @property-read Goal $goal
 * @property-read int $allocated_minutes
 * @property-read int $logged_minutes
 */
#[Fillable(['week_id', 'goal_id', 'priority_percentage'])]
class WeeklyGoalPlan extends Model
{
    /** @use HasFactory<WeeklyGoalPlanFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority_percentage' => 'float',
            'week_id' => 'integer',
            'goal_id' => 'integer',
        ];
    }

    /**
     * Get the week for this plan.
     *
     * @return BelongsTo<Week, $this>
     */
    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    /**
     * Get the goal for this plan.
     *
     * @return BelongsTo<Goal, $this>
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the time entries for this weekly goal plan.
     *
     * @return HasMany<TimeEntry, $this>
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Calculate allocated minutes based on week's planned minutes and priority percentage.
     */
    public function getAllocatedMinutesAttribute(): int
    {
        return (int) round(($this->week->planned_minutes ?? 0) * ($this->priority_percentage / 100));
    }

    /**
     * Calculate total logged minutes for this plan.
     */
    public function getLoggedMinutesAttribute(): int
    {
        return (int) $this->timeEntries()->sum('duration_in_minutes');
    }
}
