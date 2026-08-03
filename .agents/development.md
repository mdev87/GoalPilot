# Development Context

## Business Rules

1. **Week Creation**:
    - Users can have multiple weeks.
    - Only one week can be active at a time.
    - When creating a new week, the previous active week is automatically locked.
    - Weeks are created manually by users.

2. **Goal Prioritization**:
    - Each goal can be assigned a priority percentage (for example 20-100).
    - Goals attached to a week are stored in `weekly_goal_plans` and are validated through the action layer.
    - Planned minutes are derived from the week plan and the assigned percentage.

3. **Goal Lifecycle**:
    - Goals are archived, not deleted, when they have related time entries.
    - Archived goals can still hold historical time entries.
    - Goals without time entries can be deleted.

4. **Time Entries**:
    - Time can be logged against a weekly goal plan.
    - Entries are constrained to the active week range.
    - Updates and deletes are blocked for locked weeks.

5. **Activity Streaks**:
    - Current streak: consecutive days with logged time entries.
    - Longest streak: the maximum consecutive streak ever recorded.
    - If no entry is logged, the streak resets to zero.

## Current Progress

### Implemented

- ✅ Project creation and Laravel starter kit setup.
- ✅ Core authentication flow with email/password login and Google Sign-In.
- ✅ Goal management (create, edit, archive, unarchive, delete) with policies and Livewire UI.
- ✅ Weekly planning flow (create week, lock week, save goal priorities) with policies and Livewire UI.
- ✅ Time logging flow (create, edit, delete) with policies and Livewire UI.
- ✅ Domain events and listener support for week creation and streak updates.
- ✅ Activity streak model and listener for time-entry-based streak tracking.
- ✅ Feature tests covering goals, weeks, weekly plans, time entries, and events.

### Still Pending

- ⏳ Dashboard metrics aggregation and richer dashboard cards.
- ⏳ Statistics page and historical analytics views.
- ⏳ AI weekly analysis workflow.
- ⏳ Heatmap and header streak UI widgets.
- ⏳ Final code quality pass with Pint and PHPStan.
- ⏳ Production deployment preparation.

## Verification Status

- ✅ `php artisan test --compact` currently passes with 65 passed tests, 1 skipped, and 1 risky result.

## Testing Notes

- SQLite in-memory testing is used via the PHPUnit configuration.
- `RefreshDatabase` is used in the feature tests.
- The core feature suite is implemented as PHPUnit feature tests.
