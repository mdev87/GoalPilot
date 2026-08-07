# Decisions

## Project Setup

### Decision #1: Project Name
- **Decision**: Name the project "GoalPilot"
- **Reason**: Descriptive, brandable, not taken. Better than "Cadence" which the user rejected.
- **Date**: 2026-07-24

### Decision #2: Tech Stack
- **Decision**: Laravel 13, PHP 8.5, PostgreSQL, Livewire 3/4, Volt, Tailwind CSS 4, Flux UI
- **Reason**: Modern Laravel stack, Livewire for rapid UI development, Tailwind for styling, Flux for premium components
- **Date**: 2026-07-24

### Decision #3: Authentication
- **Decision**: Email/password (Laravel Fortify) + Google Sign-In (Laravel Socialite)
- **Reason**: Standard approach, Socialite makes Google easy to add, Fortify provides solid auth foundation
- **Date**: 2026-07-24

### Decision #4: Testing Framework
- **Decision**: Use PHPUnit (not Pest) despite user's initial preference
- **Reason**: The Laravel Boost guidelines require PHPUnit, and changing it would conflict with Boost agent skills. The project structure is already set up for PHPUnit.
- **Date**: 2026-07-24

## Architecture Decisions

### Decision #5: No Repository Pattern
- **Decision**: Do not use Repository Pattern
- **Reason**: Eloquent is already a good repository. Adding repositories adds unnecessary abstraction layers without significant benefit for this project size.
- **Reference**: Laravel Boost Guidelines - "Do not use the Repository Pattern. Many people think it's more professional. In my opinion, in Laravel it usually just adds an unnecessary layer."
- **Date**: 2026-07-24

### Decision #6: Action Classes for Business Logic
- **Decision**: Use Action classes for business logic (single-purpose, reusable)
- **Reason**: Actions can be called from Livewire, future API, CLI, and jobs without modification. Keeps Livewire components thin.
- **Reference**: ChatGPT's recommendation and Laravel Boost Guidelines
- **Date**: 2026-07-24

### Decision #7: Services Only When Necessary
- **Decision**: Use Services only when they provide real value (coordinating multiple actions)
- **Reason**: Avoid creating services for simple logic. Most business logic fits in single Action classes.
- **Reference**: Laravel Boost Guidelines
- **Date**: 2026-07-24

### Decision #8: Policies for Authorization
- **Decision**: Use Policies for authorization logic
- **Reason**: Standard Laravel approach, keeps authorization logic centralized and testable
- **Reference**: Laravel Boost Guidelines
- **Date**: 2026-07-24

### Decision #9: Events for Side Effects
- **Decision**: Use Events for side effects (streak updates, notifications, caching)
- **Reason**: Decouples side effects from main logic, makes it easy to add new side effects later
- **Reference**: ChatGPT's recommendation and Laravel Boost Guidelines
- **Date**: 2026-07-24

### Decision #10: Feature-based Organization
- **Decision**: Organize code by feature, not by type (with exceptions for Models)
- **Reason**: Keeps related code together, easier to navigate as project grows
- **Structure**: 
  - app/Actions/ grouped by feature
  - app/Livewire/ grouped by feature  
  - app/Models/ centralized (exceptions to feature grouping)
  - app/Policies/ centralized
  - app/Events/ and app/Listeners/ centralized
- **Reference**: ChatGPT's recommendation and Laravel Boost Guidelines
- **Date**: 2026-07-24

### Decision #11: Dates Stored as Gregorian/UTC
- **Decision**: Store all dates as Gregorian/UTC in database, convert for display
- **Reason**: Enables internationalization, avoids calendar system lock-in, standard practice
- **Reference**: Discussion on Jalali vs Gregorian calendar
- **Date**: 2026-07-24

### Decision #12: Jalali Calendar for Display
- **Decision**: Use morilog/jalali for Persian calendar display when user selects Persian locale
- **Reason**: Supports Persian-speaking users while maintaining internationalization capability
- **Reference**: User is Persian-speaking, wants i18n-ready architecture
- **Date**: 2026-07-24

## Domain Model Decisions

### Decision #13: Goals Belong to User
- **Decision**: Goals have user_id foreign key, belong to User
- **Reason**: Goals are user-specific resources
- **Reference**: Standard ownership pattern
- **Date**: 2026-07-24

### Decision #14: Goals Can Be Archived
- **Decision**: Goals can be archived (soft delete) but not hard deleted if they have TimeEntries
- **Decision**: Goals with no TimeEntries can be hard deleted
- **Reason**: Preserve historical data integrity while allowing cleanup of unused goals
- **Reference**: User requirement: "Goals should be deletable if they are not associated with any TimeEntry"
- **Date**: 2026-07-24

### Decision #15: Goal Notes Field
- **Decision**: Goals have a nullable notes field for additional context
- **Reason**: User requested: "I think it would be good if Goals could also have notes."
- **Date**: 2026-07-24

### Decision #16: WeeklyGoalPlan Bridge Model
- **Decision**: Use WeeklyGoalPlan model to connect Goals to Weeks with priority percentage
- **Reason**: Enables per-week priorities (same goal can have different priorities in different weeks)
- **Reference**: Domain model discussion - fixes the flaw of global goal priority
- **Date**: 2026-07-24

### Decision #17: TimeEntry Belongs to WeeklyGoalPlan
- **Decision**: TimeEntry belongs to WeeklyGoalPlan (not directly to Week + Goal)
- **Reason**: Enforces referential integrity - can only log time against goals that are part of the week's plan
- **Reference**: Architecture discussion - more normalized approach
- **Date**: 2026-07-24

### Decision #18: One Active Week Rule
- **Decision**: Only one active (unlocked) Week can exist at any time
- **Decision**: Creating a new week locks the previous week
- **Reason**: Prevents confusion, matches user's mental model of weekly planning cycles
- **Reference**: Business rules discussion
- **Date**: 2026-07-24

### Decision #19: Priority Must Total 100%
- **Decision**: Weekly goal priorities must total exactly 100%
- **Reason**: Required for proper time allocation calculation
- **Reference**: Business rules discussion
- **Date**: 2026-07-24

### Decision #20: Time Allocation Formula
- **Decision**: Allocated time = Weekly planned minutes × Goal priority percentage
- **Reason**: Simple proportional allocation based on user priorities
- **Reference**: Business rules discussion
- **Date**: 2026-07-24

### Decision #21: Historical Weeks Are Immutable
- **Decision**: Locked (historical) weeks must never be modified
- **Reason**: Preserves data integrity for reporting and analysis
- **Reference**: Business rules discussion
- **Date**: 2026-07-24

### Decision #22: Manual Week Creation
- **Decision**: Users manually create new weeks (no auto-creation)
- **Reason**: Gives user control, avoids unexpected behavior, simpler to implement
- **Reference**: Discussion on week creation timing
- **Date**: 2026-07-24

### Decision #23: Time Entry Structure
- **Decision**: TimeEntry has: datetime (full date/time), duration_in_minutes (integer), optional note
- **Reason**: Supports logging time after the fact, flexible duration, context via notes
- **Reference**: User clarified: "date field to store the full date and time" and "duration field"
- **Date**: 2026-07-24

### Decision #24: Activity Streak Definition
- **Decision**: Any logged time (even 1 minute) counts as an active day for the streak
- **Reason**: User specified: "Any logged time should count, even if it's just a small amount."
- **Reference**: Activity streak discussion
- **Date**: 2026-07-24

### Decision #25: Heatmap Style
- **Decision**: GitHub-style contribution graph showing activity over time
- **Reason**: User specified: "A GitHub-style heatmap."
- **Reference**: Heatmap discussion
- **Date**: 2026-07-24

### Decision #26: AI Analysis On-Demand Only
- **Decision**: AI analysis runs only when user explicitly requests it
- **Reason**: User specified: "Only when the user explicitly requests it. They may not always want an AI analysis, and I don't want to waste tokens unnecessarily."
- **Reference**: AI analysis timing discussion
- **Date**: 2026-07-24

### Decision #27: Overtime Allowed
- **Decision**: Users can log more time than allocated (overtime is allowed)
- **Reason**: User specified: "users should be allowed to log more time than they originally allocated" and later the system should encourage them
- **Reference**: Overtime logging discussion
- **Date**: 2026-07-24

### Decision #28: Maximum Weekly Planned Time
- **Decision**: Maximum weekly planned time is 80 hours (4800 minutes)
- **Reason**: Prevents absurd entries while allowing realistic high-activity weeks
- **Reference**: Discussion on reasonable upper limit for weekly time
- **Date**: 2026-07-24

### Decision #34: Goal and Week User Ownership Matching Rule
- **Decision**: All Goals added to a Week's `WeeklyGoalPlan` must belong to the exact same user who owns the `Week`. Users cannot attach another user's goal to their own week, nor modify/log time against another user's goals or plans.
- **Reason**: Enforces strict user data boundary and privacy rules across model factories, action validation, policies, and Livewire components.
- **Date**: 2026-07-27

## Technical Implementation Decisions

### Decision #29: Time Storage in Minutes
- **Decision**: Store all time values as integers representing minutes
- **Reason**: Avoids floating point precision issues, matches user's mental model
- **Reference**: Old project used minutes, user confirmed
- **Date**: 2026-07-24

### Decision #30: Laravel Boost as Dev Dependency
- **Decision**: Install Laravel Boost as a development dependency
- **Reason**: Provides MCP server for AI coding assistants to better understand the codebase
- **Reference**: User's tech stack list, Laravel Boost documentation
- **Date**: 2026-07-24

## UI/UX Decisions

### Decision #31: Week Starts on Saturday (Configurable)
- **Decision**: Default week start day is Saturday (Persian convention), but configurable per user
- **Reason**: Supports Persian convention while enabling internationalization
- **Reference**: Discussion on week start day and internationalization
- **Date**: 2026-07-24

### Decision #32: Dashboard Shows Current Week Status
- **Decision**: Dashboard shows current week goals with progress bars and quick time entry
- **Reason**: Provides immediate visibility into current commitments and progress
- **Reference**: Dashboard discussion
- **Date**: 2026-07-24

### Decision #33: Statistics Show Per-Week and Trend Views
- **Decision**: Statistics show time spent vs allocated per goal per week, plus trend over weeks
- **Reason**: Gives users both detailed and longitudinal views of their performance
- **Reference**: Statistics discussion
- **Date**: 2026-07-24

### Decision #35: Unified Dashboard & Chart.js Integration
- **Decision**: Consolidate the Statistics page directly into the main Dashboard view (`/dashboard`) as an appended "Analytics & Execution Trends" section. Use Chart.js (configured via TypeScript `resources/js/app.ts` and `dashboard-charts.ts`) with Livewire's `morphed` hook for instant, interactive chart re-renders upon timeframe switching (`4W`, `8W`, `12W`, `52W`).
- **Reason**: Eliminates sparse sub-pages, unifies goal execution tracking into a single personal productivity dashboard, and provides visual progress analytics.
- **Date**: 2026-08-08
