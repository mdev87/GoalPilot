# GoalPilot - Development Roadmap

Status reflects the current codebase as of 2026-08-08.

## Phase 1: Project Setup & Authentication ✅

- [x] **1.1 Framework & Starter Kit Setup**: Laravel 13 installation with Livewire Starter Kit (Volt/Flux UI integration).
- [x] **1.2 Database Setup**: PostgreSQL database connection configured with default migrations.
- [x] **1.3 Package Dependencies**: Installed `laravel/socialite`, `laravel/ai`, and `morilog/jalali`.
- [x] **1.4 Google OAuth Authentication**: Configured Google provider credentials, created `GoogleController`, added routes (`/auth/google`, `/auth/google/callback`), and user registration/login flow.
- [x] **1.5 Agent Guidelines & Project Docs**: Established `.agents/` documentation architecture (`architecture.md`, `decisions.md`, `project.md`, `development.md`).

---

## Phase 2: Core Domain Features (Atomic Slices) ✅

### 2.1 Goal Management Feature ✅

- [x] **2.1.1 Data Model**: Created `goals` table migration and `Goal` model with `#[Scope]` attributes (`active`, `archived`).
- [x] **2.1.2 Business Actions**: Created `CreateGoal`, `UpdateGoal`, `ArchiveGoal`, `UnarchiveGoal`, and `DeleteGoal` Action classes.
- [x] **2.1.3 Policy Authorization**: Created `GoalPolicy` defining user ownership checks.
- [x] **2.1.4 Single-File Page Component**: Built `resources/views/pages/⚡goals.blade.php` handling goal creation, editing, archiving, and deletion.
- [x] **2.1.5 Routing & Navigation**: Registered `Route::get('goals', GoalsManager::class)` in `routes/web.php` and added Goals link to the app sidebar.
- [x] **2.1.6 Feature Tests**: Implemented `tests/Feature/GoalTest.php` covering CRUD operations, archiving, and authorization.

### 2.2 Weekly Planning Feature ✅

- [x] **2.2.1 Data Models**: Created `weeks` and `weekly_goal_plans` migrations and models (`Week`, `WeeklyGoalPlan`) with `#[Scope]` attributes (`active`, `locked`).
- [x] **2.2.2 Business Actions**: Created `CreateWeek`, `LockWeek`, and `SetWeeklyGoalPlans` Action classes.
- [x] **2.2.3 Policy Authorization**: Created `WeekPolicy` and `WeeklyGoalPlanPolicy`.
- [x] **2.2.4 Single-File Page Component**: Built `resources/views/pages/⚡weeks.blade.php` for setting planned hours, creating weeks, locking weeks, and allocating goal priority percentages.
- [x] **2.2.5 Routing & Navigation**: Registered `Route::get('weeks', WeekPlanner::class)` in `routes/web.php` and added Weeks link to the sidebar.
- [x] **2.2.6 Feature Tests**: Implemented `tests/Feature/WeekTest.php` and `tests/Feature/WeeklyGoalPlanTest.php`.

### 2.3 Time Logging Feature ✅

- [x] **2.3.1 Data Model**: Created `time_entries` table migration and `TimeEntry` model.
- [x] **2.3.2 Business Actions**: Created `CreateTimeEntry`, `UpdateTimeEntry`, and `DeleteTimeEntry` Action classes.
- [x] **2.3.3 Policy Authorization**: Created `TimeEntryPolicy` and `WeeklyGoalPlanPolicy`.
- [x] **2.3.4 Single-File Page Component**: Built `resources/views/pages/⚡time-entries.blade.php` for logging time against active weekly plans and viewing recent entries.
- [x] **2.3.5 Routing & Navigation**: Registered `Route::get('time-entries', TimeLogger::class)` in `routes/web.php` and added Time Log link to the sidebar.
- [x] **2.3.6 Feature Tests**: Implemented `tests/Feature/TimeEntryTest.php`.

### 2.4 Domain Events & Integration ✅

- [x] **2.4.1 Events**: Created `TimeLogged` and `WeekCreated` domain events.
- [x] **2.4.2 Feature Tests**: Implemented `tests/Feature/EventTest.php`.

---

## Phase 3: Dashboard & Analytics ✅

- [x] **3.1 Dashboard Data Aggregation**: Created `GetDashboardData` action to query active week allocations, total planned/logged minutes, completion percentages, streaks, and recent activity.
- [x] **3.2 Unified Dashboard UI**: Implemented unified native Flux UI dashboard layout combining header stats cards, goal breakdown table, and recent activity feed.
- [x] **3.3 Statistics Aggregation**: Implemented `GetWeeklyStats` and `GetTrendStats` actions for weekly execution trends and goal time distributions.
- [x] **3.4 Chart Visualizations Engine**: Configured Chart.js in `resources/js/app.ts` & `dashboard-charts.ts` rendering interactive execution bar charts, goal distribution doughnut charts, and 1-year area trend charts with Livewire `morphed` hook.
- [x] **3.5 Routing & Navigation**: Consolidated Statistics into `/dashboard` route and updated navigation links.
- [x] **3.6 Feature Tests**: Implemented comprehensive suite in `tests/Feature/DashboardTest.php` covering authentication, Livewire component state, calculation accuracy, and timeframe parameters.

---

## Phase 4: AI Insights Integration ✅

- [x] **4.1 AI SDK Setup**: Configured Laravel AI SDK with published `config/ai.php` supporting OpenRouter, OpenAI, and custom OpenAI-compatible providers.
- [x] **4.2 AI Agent Definition**: Implemented `WeeklyAnalysisAgent` with structured JSON schema (`summary`, `achievements`, `areas_for_improvement`, `actionable_recommendations`, `execution_score`).
- [x] **4.3 Analysis Generation Action**: Created `GenerateWeeklyAnalysis` action class with database data hash caching and 3-per-day rate limiting.
- [x] **4.4 On-Demand Analysis UI**: Built dedicated AI Insights hero card section on the Dashboard with enlarged typography, loading state, and safe error masking.
- [x] **4.5 Integration Tests**: Added comprehensive feature test suite in `tests/Feature/WeeklyAnalysisTest.php` covering generation, caching, rate limits, and safe error handling.

---

## Phase 5: Engagement & Activity Streaks ✅

- [x] **5.1 Data Model**: Created `user_activity_streaks` table migration and `UserActivityStreak` model with `Attribute::make` syntax.
- [x] **5.2 Event Listener**: Implemented `UpdateStreakOnTimeLogged` and wired it to the `TimeLogged` event.
- [x] **5.3 Heatmap Component**: Created GitHub-style 52-week contribution heatmap widget (`<x-activity-heatmap />`) and `GetActivityHeatmapData` action.
- [x] **5.4 Header Streak Counter**: Integrated current and best streak counters into the unified dashboard header.
- [x] **5.5 Feature Tests**: Comprehensive test suite in `tests/Feature/ActivityStreakTest.php` and `tests/Feature/ActivityHeatmapTest.php`.

---

## Phase 6: Optimization & Release Readiness 🟡

- [x] **6.1 Code Audit**: Formatted codebase using `vendor/bin/pint --dirty --format agent`.
- [x] **6.2 Query Performance**: Optimized Eloquent eager loading and relation sum subqueries across actions, reducing dashboard query counts below 10.
- [x] **6.3 Comprehensive Test Run**: Full test suite passing cleanly (`php artisan test --compact`).
