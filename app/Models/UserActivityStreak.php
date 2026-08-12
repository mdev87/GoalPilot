<?php

namespace App\Models;

use Database\Factories\UserActivityStreakFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $current_streak
 * @property int $longest_streak
 * @property Carbon|null $last_active_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $effective_current_streak
 * @property-read User $user
 */
#[Fillable(['user_id', 'current_streak', 'longest_streak', 'last_active_date'])]
class UserActivityStreak extends Model
{
    /** @use HasFactory<UserActivityStreakFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_active_date' => 'date',
        ];
    }

    /**
     * Get the effective current streak considering inactivity lapses.
     *
     * @return Attribute<int, never>
     */
    protected function effectiveCurrentStreak(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if ($this->last_active_date === null) {
                    return 0;
                }

                $yesterday = Carbon::today()->subDay();

                if (Carbon::parse($this->last_active_date)->startOfDay()->lt($yesterday)) {
                    return 0;
                }

                return $this->current_streak;
            },
        );
    }

    /**
     * Get the user that owns the streak record.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
