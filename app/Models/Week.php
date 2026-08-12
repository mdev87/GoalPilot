<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\WeekFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $week_start_date
 * @property int $planned_minutes
 * @property Carbon|null $locked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, WeeklyGoalPlan> $weeklyGoalPlans
 * @property-read Collection<int, TimeEntry> $timeEntries
 *
 * @method static \Illuminate\Database\Eloquent\Builder<\App\Models\Week> active()
 * @method static \Illuminate\Database\Eloquent\Builder<\App\Models\Week> locked()
 */
#[Fillable(['user_id', 'week_start_date', 'planned_minutes', 'locked_at'])]
class Week extends Model
{
    /** @use HasFactory<WeekFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'planned_minutes' => 'integer',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * Scope query to active (unlocked) weeks.
     *
     * @param  Builder<Week>  $query
     */
    #[Scope]
    public function active(Builder $query): void
    {
        $query->whereNull('locked_at');
    }

    /**
     * Scope query to active (unlocked) weeks (Larastan alias).
     *
     * @param  Builder<Week>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('locked_at');
    }

    /**
     * Scope query to locked weeks.
     *
     * @param  Builder<Week>  $query
     */
    #[Scope]
    public function locked(Builder $query): void
    {
        $query->whereNotNull('locked_at');
    }

    /**
     * Scope query to locked weeks (Larastan alias).
     *
     * @param  Builder<Week>  $query
     */
    public function scopeLocked(Builder $query): void
    {
        $query->whereNotNull('locked_at');
    }

    /**
     * Check if the week is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Get the end date of the week (6 days after start date).
     */
    public function getEndDate(): CarbonInterface
    {
        return Carbon::parse($this->week_start_date)->addDays(6);
    }

    /**
     * Check if the week timeframe has completed (ended).
     */
    public function hasEnded(): bool
    {
        return now()->startOfDay()->greaterThan($this->getEndDate()->startOfDay());
    }

    /**
     * Get the user that owns the week.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the weekly goal plans for this week.
     *
     * @return HasMany<WeeklyGoalPlan, $this>
     */
    public function weeklyGoalPlans(): HasMany
    {
        return $this->hasMany(WeeklyGoalPlan::class);
    }

    /**
     * Get the goals associated with this week.
     *
     * @return BelongsToMany<Goal, $this>
     */
    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'weekly_goal_plans')
            ->withPivot('priority_percentage')
            ->withTimestamps();
    }

    /**
     * Get all time entries for this week through weekly goal plans.
     *
     * @return HasManyThrough<TimeEntry, WeeklyGoalPlan, $this>
     */
    public function timeEntries(): HasManyThrough
    {
        return $this->hasManyThrough(TimeEntry::class, WeeklyGoalPlan::class);
    }
}
