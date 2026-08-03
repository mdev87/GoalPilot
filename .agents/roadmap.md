# GoalPilot - Development Roadmap

Status reflects the current codebase as of 2026-08-04.

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

## Phase 3: Dashboard & Analytics 🟡

- [ ] **3.1 Dashboard Data Aggregation**: No dedicated `GetDashboardData` action exists yet; the dashboard currently uses placeholder UI.
- [ ] **3.2 Dashboard Page UI**: The dashboard view exists, but it still needs real summary cards and progress visuals tied to week data.
- [ ] **3.3 Statistics Aggregation**: No `GetWeeklyStats` or `GetTrendStats` actions exist yet.
- [ ] **3.4 Analytics Page Component**: No statistics page component or chart UI has been added.
- [ ] **3.5 Routing & Sidebar**: Statistics routes and navigation are not yet implemented.
- [ ] **3.6 Feature Tests**: Dashboard/statistics behavior is not covered by dedicated tests yet.

---

## Phase 4: AI Insights Integration ⏳

- [ ] **4.1 AI SDK Setup**: The Laravel AI SDK is installed, but no production AI agent or provider wiring has been completed.
- [ ] **4.2 AI Agent Definition**: No `WeeklyAnalysisAgent` or structured analysis schema exists yet.
- [ ] **4.3 Analysis Generation Action**: No analysis generation action has been implemented.
- [ ] **4.4 On-Demand Analysis UI**: No AI insights button or modal UI exists yet.
- [ ] **4.5 Integration Tests**: No AI response handling tests have been added.

---

## Phase 5: Engagement & Activity Streaks 🟡

- [x] **5.1 Data Model**: Created `user_activity_streaks` table migration and `UserActivityStreak` model.
- [x] **5.2 Event Listener**: Implemented `UpdateStreakOnTimeLogged` and wired it to the `TimeLogged` event.
- [ ] **5.3 Heatmap Component**: No GitHub-style contribution heatmap widget exists yet.
- [ ] **5.4 Header Streak Counter**: No streak badge is shown in the app header yet.
- [x] **5.5 Feature Tests**: Basic streak behavior is covered by `tests/Feature/EventTest.php`.

---

## Phase 6: Optimization & Release Readiness 🟡

- [ ] **6.1 Code Audit**: Formatting and static analysis have not yet been run as a final pass.
- [ ] **6.2 Query Performance**: No dedicated query-performance audit has been completed.
- [x] **6.3 Comprehensive Test Run**: The Laravel suite currently passes with 65 tests passing, 1 skipped, and 1 risky result.
