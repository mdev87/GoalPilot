<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Weekly Planned Minutes
    |--------------------------------------------------------------------------
    |
    | The default number of minutes a user plans to dedicate to their goals
    | each week. This is used when creating a new week for the first time.
    |
    */

    'default_planned_minutes' => (int) env('WEEK_DEFAULT_PLANNED_MINUTES', 1200),

    /*
    |--------------------------------------------------------------------------
    | Maximum Weekly Planned Minutes
    |--------------------------------------------------------------------------
    |
    | The maximum number of minutes a user can plan for a single week.
    | Default is 80 hours (4800 minutes).
    |
    */

    'max_planned_minutes' => (int) env('WEEK_MAX_PLANNED_MINUTES', 4800),

    /*
    |--------------------------------------------------------------------------
    | Minimum Weekly Planned Minutes
    |--------------------------------------------------------------------------
    |
    | The minimum number of minutes a user can plan for a single week.
    |
    */

    'min_planned_minutes' => (int) env('WEEK_MIN_PLANNED_MINUTES', 30),

];
