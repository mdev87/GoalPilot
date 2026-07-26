# Roadmap

## Phase 1: Project Foundation ✅
- [x] Laravel project setup (Livewire starter kit)
- [x] PostgreSQL configured
- [x] Google Sign-In configuration added
- [x] Dependencies installed: socialite, laravel/ai, morilog/jalali
- [x] config/week.php created (min/default/max planned minutes)
- [x] Project structure established
- [x] Initial commit with configuration

## Phase 2: Core Domain ✅
- [x] Goals migration, model, actions, policies, Livewire
- [x] Weeks migration, model, actions, policies, Livewire
- [x] WeeklyGoalPlans migration, model, actions, Livewire
- [x] TimeEntries migration, model, actions, policies, Livewire
- [x] Events and listeners (TimeLogged, WeekCreated)
- [x] Tests for all core domain features

## Phase 3: Dashboard & Statistics
- [ ] Dashboard Livewire component
- [ ] GetDashboardData action
- [ ] Statistics Livewire component
- [ ] GetWeeklyStats and GetTrendStats actions
- [ ] Statistics views and charts
- [ ] Tests for dashboard and statistics

## Phase 4: AI Integration
- [ ] Laravel AI SDK configured
- [ ] WeeklyAnalysisAgent created
- [ ] GenerateWeeklyAnalysis action
- [ ] AnalysisView Livewire component
- [ ] Tests with mocked AI provider

## Phase 5: Engagement Features
- [ ] UserActivityStreak migration, model
- [ ] UpdateStreak action
- [ ] StreakDisplay Livewire component
- [ ] Heatmap Livewire component
- [ ] Event listener for streak updates
- [ ] Tests for streak and heatmap

## Phase 6: Polish & Testing
- [ ] Comprehensive feature test coverage
- [ ] Edge case testing
- [ ] Performance review
- [ ] Documentation
- [ ] Final polish

## Future Phases (post-MVP)
- [ ] REST API (sanctum-based, ready from Phase 4)
- [ ] Notifications (email, in-app, push)
- [ ] Calendar integration (Google Calendar, iCal)
- [ ] Advanced statistics (reports, exports)
- [ ] Mobile app (Flutter or React Native)
- [ ] Team sharing and collaboration
- [ ] Recurring goals
- [ ] Goal templates
- [ ] Dark mode