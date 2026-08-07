<?php

namespace App\Models;

use Database\Factories\WeeklyGoalPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MissingAttributeException;
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
 * @property-read Collection<int, TimeEntry> $timeEntries
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
    public function allocatedMinutes(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if ($this->relationLoaded('week')) {
                    return round($this->week->planned_minutes * ($this->priority_percentage / 100));
                }

                throw new MissingAttributeException($this, 'allocated_minutes');
            }
        );
    }

    /**
     * Calculate total logged minutes for this plan.
     */
    public function loggedMinutes(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if (array_key_exists('time_entries_sum_duration_in_minutes', $this->attributes)) {
                    return $this->attributes['time_entries_sum_duration_in_minutes'] ?? 0;
                }

                if ($this->relationLoaded('timeEntries')) {
                    return $this->timeEntries->sum('duration_in_minutes');
                }

                throw new MissingAttributeException($this, 'logged_minutes');
            }
        );
    }
}
