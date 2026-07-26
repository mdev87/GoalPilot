# Project: GoalPilot

## Goal

A weekly goal and time management application. Users define goals, plan their week with priority-based time allocation, log time spent, and receive AI-powered insights.

This project has three purposes:

1. **Portfolio project** — demonstrate strong Laravel skills
2. **Personal tool** — the developer uses it daily
3. **Future product** — potential SaaS with web and mobile apps

## Target Users

- Primary: Persian-speaking users (Farsi)
- Secondary: International users (English)
- Initially: the developer only
- Future: general public

## MVP Features

| Feature | Status |
|---|---|
| Email/password authentication | ✅ Done (Livewire starter kit) |
| Google Sign-In | ✅ Done |
| Goals (CRUD + archive + delete) | ✅ Done |
| Weekly planning (create week, set priorities) | ✅ Done |
| WeeklyGoalPlans (priority per goal per week) | ✅ Done |
| Time entries (log time against goals) | ✅ Done |
| Dashboard (current week status) | ⬜ Not started |
| Statistics (per-week, trend) | ⬜ Not started |
| AI weekly analysis (on user request) | ⬜ Not started |
| Activity streak | ⬜ Not started |
| Heatmap (GitHub-style) | ⬜ Not started |

## Out of Scope (for now)

- REST API (architecture-ready, not implemented)
- Mobile app (Flutter, future)
- Notifications
- Calendar integration
- Timer/stopwatch for time tracking
- Goal colors (removed from MVP)
- Two-factor authentication
- Passkeys

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| Database | PostgreSQL (dev), SQLite (tests) |
| Frontend | Livewire 4, Volt, Flux UI, Tailwind CSS 4 |
| Testing | PHPUnit, SQLite in-memory |
| AI | Laravel AI SDK |
| Auth | Laravel Fortify (email/password) + Socialite (Google) |
| Calendar | morilog/jalali (Persian calendar) |
| Code style | Laravel Pint (laravel preset) |
| Static analysis | PHPStan level 7 (Larastan) |
| Dev tools | Laravel Boost (MCP server for AI assistants) |

## Domain Model

```
User
 ├── Goals (reusable across weeks)
 │     ├── name
 │     ├── notes
 │     └── archived_at
 │
 └── Weeks (one active at a time)
       ├── planned_minutes (30-4800)
       ├── locked_at (set when new week starts)
       │
       ├── WeeklyGoalPlans (bridge to Goals)
       │     └── priority_percentage (0-100, totals must equal 100%)
       │
       └── TimeEntries (via WeeklyGoalPlan)
             ├── datetime
             ├── duration_in_minutes
             └── note (optional)

UserActivityStreak
 └── current_streak, longest_streak, last_active_date
```
