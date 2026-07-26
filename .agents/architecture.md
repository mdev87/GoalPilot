# Architecture

## Folder Structure

```
app/
  Actions/                  # Business logic (single-purpose, reusable)
    GoalActions/
    WeekActions/
    WeeklyGoalPlanActions/
    TimeEntryActions/
    DashboardActions/
    StatisticsActions/
    AIActions/
    ActivityStreakActions/
  Ai/
    Agents/                 # Laravel AI SDK agents
  Livewire/                 # Thin UI components (no business logic)
    Goals/
    Weeks/
    WeeklyGoalPlans/
    TimeEntries/
    Dashboard/
    Statistics/
    AI/
    ActivityStreak/
  Models/                   # Eloquent models
  Policies/                 # Authorization policies
  Events/                   # Domain events
  Listeners/                # Event handlers
  Data/                     # DTOs (if needed)
  Exceptions/               # Custom exceptions
  Http/
    Controllers/            # Minimal controllers (mostly auto-generated)
    Requests/               # Form request validation
  Providers/                # Service providers
  Console/                  # Artisan commands
```

## Key Conventions

### Models
- Located in `app/Models/`
- Use constructor property promotion for fillable attributes
- Use `[Fillable]` and `[Hidden]` attributes
- Relationships defined with PHPDoc types
- Scopes for common queries (active, archived, etc.)
- No business logic in models

### Actions
- Located in `app/Actions/` (grouped by feature)
- Single responsibility: one action per use case
- Dependencies injected via constructor
- Return values or throw exceptions (no nulls)
- Named as verb + noun: `CreateGoal`, `UpdateWeeklyPlan`
- Contain validation logic when not using form requests
- Used by Livewire components and future API controllers

### Livewire Components
- Located in `app/Livewire/` (grouped by feature)
- Thin: only handle UI state and user interactions
- Delegate business logic to Actions
- Use Volt-like single-file components when simple
- Use separate class + view when complex
- Emit events for communication when needed
- Validate input before passing to Actions

### Policies
- Located in `app/Policies/`
- One policy per model
- Methods: view, create, update, delete, restore, forceDelete
- Check ownership and authorization rules
- Used via `@can` Blade directive and `$this->authorize()`

### Events & Listeners
- Events: `app/Events/` (past tense: `TimeLogged`, `WeekCreated`)
- Listeners: `app/Listeners/` (verb + event: `UpdateStreakOnTimeLogged`)
- Events carry minimal data (IDs, not full models)
- Listeners perform side effects (streaks, caching, notifications)
- Avoid putting business logic in listeners

### AI
- Agents: `app/Ai/Agents/` (extend Laravel AI SDK's Agent)
- Structured output defined via `schema()` method
- Actions call agents and handle results
- Provider abstraction - never call AI directly from UI

### Database
- Migrations in `database/migrations/`
- Factories in `database/factories/`
- Seeders in `database/seeders/` (if needed)
- Use UUIDs or auto-increment IDs (auto-increment for simplicity)
- Timestamps on all tables
- Soft deletes via `archived_at` (not Laravel's soft deletes)
- Indexes on foreign keys and queried columns

### Testing
- PHPUnit with `RefreshDatabase` trait
- Factories in `database/factories/`
- Feature tests in `tests/Feature/` grouped by feature
- Unit tests in `tests/Unit/` for complex logic
- Test happy path, edge cases, and error conditions
- Mock external services (AI, email) in tests
- Tests run against SQLite in-memory

## Data Flow

1. **User Action** (click button, submit form)
2. **Livewire Component** validates input, calls Action
3. **Action** executes business logic, returns result or throws exception
4. **Livewire Component** updates UI state based on result
5. **Events** may be fired for side effects (handled asynchronously)
6. **UI Updates** reactively via Livewire's lifecycle

## Security

- Authentication: Laravel Fortify (email/password) + Socialite (Google)
- Authorization: Policies with Gate and @can
- CSRF protection: built-in Laravel middleware
- XSS prevention: Blade auto-escaping
- SQL injection: prevented by Eloquent/query builder
- Mass assignment: protected by Fillable attributes
- Validation: Form Requests or Actions