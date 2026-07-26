# Development Context

## Business Rules

1. **Week Creation**:
   - Users can have multiple weeks
   - Only one week can be active at a time
   - When creating a new week, the previous week is automatically locked
   - Weeks are created manually by users

2. **Goal Prioritization**:
   - Each goal assigned a priority percentage (e.g., 20-100)
   - All goals attached to a week must sum to 100% (currently enforced by code)
   - Planned minutes = (priority %) × (optional min/max multiplier)

3. **Goal Lifecycle**:
   - Goals are archived, not deleted (if they have TimeEntries)
   - Archived goals can still have TimeEntries (for historical accuracy)
   - Goals without TimeEntries can be deleted

4. **Time Entries**:
   - Can be logged against WeeklyGoalPlan
   - Timestamps affected by user timezone
   - Duration calculated from start/end times

5. **Activity Streaks**:
   - Current streak: consecutive days with TimeEntries
   - Longest streak: maximum consecutive days ever
   - No entries = streak resets to 0

## Current Progress

### Completed Items
- ✅ Project creation (GoalPilot folder)
- ✅ Dependencies installed: laravel/socialite, laravel/ai, morilog/jalali
- ✅ PostgreSQL environment configured (.env, DB_DATABASE=goalpilot, DB_CONNECTION=pgsql)
- ✅ config/week.php created (min=30, default=1200, max=4800 planned minutes)
- ✅ config/services.php updated with Google config
- ✅ .env APP_NAME=GoalPilot set
- ✅ Google Sign-In configuration added (.env keys for GOOGLE_CLIENT_ID, SECRET, REDIRECT_URL)
- ✅ Migrations for Goals, Weeks, WeeklyGoalPlan, TimeEntries, UserActivityStreaks created
- ✅ separate branches for each migration file (commit suggestions ready for user review)
- ✅ Documentation structure created (.agents/ folder)

### In Progress Items
- ⏳ Updating time_entries and user_activity_streaks migrations
- ⏳ Domain models (Goals, Weeks, WeeklyGoalPlan, TimeEntries, UserActivityStreaks) with relationships
- ⏳ Action classes for periodic and daily tasks
- ⏳ Policy classes for authorization
- ⏳ Event/Listener system for streak tracking and week locking
- ⏳ Authentication backend (Session, Google Sign-In)
- ⏳ Livewire components for UI
- ⏳ PHPUnit tests for all features

### Unfinished Tasks (From Roadmap)
1. Complete remaining migrations (time_entries, user_activity_streaks)
2. Create all domain models with proper relationships and scopes
3. Create Action classes for:
   - Periodic tasks (week locking, deadline tracking)
   - Daily tasks (streak updates)
4. Create Policy classes for Goals, Weeks, TimeEntries, ActivityStreaks
5. Implement Event/Listener for streak logging
6. Implement Event/Listener for week locking
7. Create authentication features:
   - Session-based authentication
   - Google Sign-In integration
   - Protected routes/middleware
8. Create Livewire components:
   - Goal management
   - Week planning/ui
   - Time entry logging
   - Calendar view (Jalali)
   - Streak tracking
9. Write PHPUnit tests for all features
10. Integration (combine backend frontend)
11. Production deployment

## Known Issues
- None currently

## Next Immediate Steps
1. Complete migrations for time_entries and user_activity_streaks
2. Create model classes with relationships (Goals, Weeks, WeeklyGoalPlan, TimeEntries, UserActivityStreaks)
3. Create Action classes for:
   - Update goal priorities (ensure sum = 100%)
   - Auto-lock previous week on new week creation
   - Update activity streaks daily
4. Create Policy classes
5. Implement Events and Listeners for streak tracking and week locking

## Testing Notes
- SQLite in-memory (phpunit.xml overrides DB_CONNECTION=sqlite)
- RefreshDatabase trait needed in test classes
- All tests are PHPUnit v12 feature tests