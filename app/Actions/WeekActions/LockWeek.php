<?php

namespace App\Actions\WeekActions;

use App\Models\Week;
use DomainException;

class LockWeek
{
    /**
     * Lock the specified week.
     *
     * @throws DomainException
     */
    public function execute(Week $week): Week
    {
        if (! $week->hasEnded()) {
            throw new DomainException("Cannot lock a week before its end date ({$week->getEndDate()->format('Y-m-d')}).");
        }

        if (! $week->isLocked()) {
            $week->update(['locked_at' => now()]);
        }

        return $week;
    }
}
