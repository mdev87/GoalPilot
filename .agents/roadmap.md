# GoalPilot - Development Roadmap

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
- [x] **2.1.5 Routing & Navigation**: Registered `Route::livewire('goals', 'pages::goals')` in `routes/web.php` and added Goals link to `sidebar.blade.php`.
- [x] **2.1.6 Feature Tests**: Implemented `tests/Feature/GoalTest.php` covering CRUD operations, archiving, and authorization.

### 2.2 Weekly Planning Feature ✅
- [x] **2.2.1 Data Models**: Created `weeks` and `weekly_goal_plans` migrations and models (`Week`, `WeeklyGoalPlan`) with `#[Scope]` attributes (`active`, `locked`).
- [x] **2.2.2 Business Actions**: Created `CreateWeek`, `LockWeek`, and `SetWeeklyGoalPlans` Action classes.
- [x] **2.2.3 Policy Authorization**: Created `WeekPolicy` and `WeeklyGoalPlanPolicy`.
- [x] **2.2.4 Single-File Page Component**: Built `resources/views/pages/⚡weeks.blade.php` for setting planned hours, creating weeks, locking weeks, and allocating goal priority percentages.
- [x] **2.2.5 Routing & Navigation**: Registered `Route::livewire('weeks', 'pages::weeks')` in `routes/web.php` and added Weeks link to `sidebar.blade.php`.
- [x] **2.2.6 Feature Tests**: Implemented `tests/Feature/WeekTest.php` and `tests/Feature/WeeklyGoalPlanTest.php`.

### 2.3 Time Logging Feature ✅
- [x] **2.3.1 Data Model**: Created `time_entries` table migration and `TimeEntry` model.
- [x] **2.3.2 Business Actions**: Created `CreateTimeEntry` and `DeleteTimeEntry` Action classes.
- [x] **2.3.3 Policy Authorization**: Created `TimeEntryPolicy`.
- [x] **2.3.4 Single-File Page Component**: Built `resources/views/pages/⚡time-entries.blade.php` for logging time against active weekly plans and viewing recent entries.
- [x] **2.3.5 Routing & Navigation**: Registered `Route::livewire('time-entries', 'pages::time-entries')` in `routes/web.php` and added Time Log link to `sidebar.blade.php`.
- [x] **2.3.6 Feature Tests**: Implemented `tests/Feature/TimeEntryTest.php`.

### 2.4 Domain Events & Integration ✅
- [x] **2.4.1 Events**: Created `TimeLogged` and `WeekCreated` domain events.
- [x] **2.4.2 Feature Tests**: Implemented `tests/Feature/EventTest.php`.

---

## Phase 3: Dashboard & Analytics ⏳
- [ ] **3.1 Dashboard Data Aggregation**: Create `GetDashboardData` action to calculate weekly target vs logged hours, overall goal completion percentage, and active week status.
- [ ] **3.2 Dashboard Page UI**: Update `resources/views/dashboard.blade.php` with summary cards, progress bars per goal, and quick time log actions.
- [ ] **3.3 Statistics Aggregation**: Create `GetWeeklyStats` and `GetTrendStats` actions for longitudinal analytics across historical locked weeks.
- [ ] **3.4 Analytics Page Component**: Create `resources/views/pages/⚡statistics.blade.php` page component with interactive time breakdown charts and trend views.
- [ ] **3.5 Routing & Sidebar**: Register `Route::livewire('statistics', 'pages::statistics')` in `routes/web.php` and add Statistics link to `sidebar.blade.php`.
- [ ] **3.6 Feature Tests**: Write tests for dashboard metrics calculation and statistics data aggregation.

---

## Phase 4: AI Insights Integration ⏳
- [ ] **4.1 AI SDK Setup**: Configure `laravel/ai` SDK with preferred AI provider (OpenAI / Gemini).
- [ ] **4.2 AI Agent Definition**: Create `WeeklyAnalysisAgent` defining prompt rules and structured JSON response schema for performance analysis.
- [ ] **4.3 Analysis Generation Action**: Create `GenerateWeeklyAnalysis` action to gather weekly logged data, query the AI Agent, and return recommendations.
- [ ] **4.4 On-Demand Analysis UI**: Add "Generate AI Insights" button and modal display component to the weekly planner and dashboard.
- [ ] **4.5 Integration Tests**: Write feature tests with mocked AI responses to verify analysis handling.

---

## Phase 5: Engagement & Activity Streaks ⏳
- [ ] **5.1 Data Model**: Create `user_activity_streaks` table migration and `UserActivityStreak` model.
- [ ] **5.2 Event Listener**: Create `UpdateStreakOnTimeLogged` listener triggered by the `TimeLogged` event to increment active day streaks.
- [ ] **5.3 Heatmap Component**: Build GitHub-style contribution heatmap widget showing active logging history.
- [ ] **5.4 Header Streak Counter**: Add active streak badge widget in the top app navigation bar.
- [ ] **5.5 Feature Tests**: Write tests for consecutive day calculation and streak maintenance.

---

## Phase 6: Optimization & Release Readiness ⏳
- [ ] **6.1 Code Audit**: Run `pint` code formatting and PHPStan static analysis.
- [ ] **6.2 Query Performance**: Audit database queries for N+1 issues and verify index usage across foreign keys.
- [ ] **6.3 Comprehensive Test Run**: Ensure 100% test pass rate across unit and feature test suites.