<?php

namespace App\Actions\WeekActions;

use App\Models\Week;

class LockWeek
{
    /**
     * Lock the specified week.
     */
    public function execute(Week $week): Week
    {
        if (! $week->isLocked()) {
            $week->update(['locked_at' => now()]);
        }

        return $week;
    }
}
