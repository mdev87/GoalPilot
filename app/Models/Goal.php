<?php

namespace App\Models;

use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $notes
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'name', 'notes', 'archived_at'])]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Scope query to active (non-archived) goals.
     *
     * @param  Builder<Goal>  $query
     */
    #[Scope]
    public function active(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /**
     * Scope query to archived goals.
     *
     * @param  Builder<Goal>  $query
     */
    #[Scope]
    public function archived(Builder $query): void
    {
        $query->whereNotNull('archived_at');
    }

    /**
     * Check if goal is archived.
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Check if goal can be hard deleted (only if no time entries exist).
     */
    public function canBeDeleted(): bool
    {
        return $this->timeEntries()->count() === 0;
    }

    /**
     * Check if this goal is allocated in any active (unlocked) week plan.
     */
    public function isAllocatedInActiveWeek(): bool
    {
        return $this->weeklyGoalPlans()
            ->whereHas('week', fn (Builder $query) => $query->whereNull('locked_at'))
            ->exists();
    }

    /**
     * Get the user that owns the goal.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the weekly goal plans for this goal.
     *
     * @return HasMany<WeeklyGoalPlan, $this>
     */
    public function weeklyGoalPlans(): HasMany
    {
        return $this->hasMany(WeeklyGoalPlan::class);
    }

    /**
     * Get all time entries logged against this goal across all weeks.
     *
     * @return HasManyThrough<TimeEntry, WeeklyGoalPlan, $this>
     */
    public function timeEntries(): HasManyThrough
    {
        return $this->hasManyThrough(TimeEntry::class, WeeklyGoalPlan::class);
    }
}
