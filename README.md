# GoalPilot

GoalPilot is a simple weekly goal setting and time management application built with Laravel and Livewire. It helps you allocate available hours each week across your goals, track logged time, and view productivity trends.

## Features

- **Goals Management**: Create, edit, archive, and delete goals.
- **Weekly Planning**: Set target hours for the week and assign priority percentages to your active goals.
- **Time Logging**: Log time spent on specific goal plans throughout the week.
- **Dashboard & Charts**: View weekly completion percentages, goal breakdowns, and execution trends with Chart.js.
- **AI Weekly Analysis**: Generate an on-demand summary of your weekly productivity using Laravel AI.
- **Streaks & Heatmap**: Track your daily logging streaks and view a 52-week contribution heatmap.
- **Authentication**: Supports standard login/registration as well as Google OAuth via Socialite.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3+
- **Frontend**: Livewire 4, Flux UI, Tailwind CSS v4
- **Database**: PostgreSQL (SQLite for testing)
- **Tooling**: PHPUnit, PHPStan (Larastan Level 7), Laravel Pint

## Quick Start

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/GoalPilot.git
   cd GoalPilot
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Set up environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run migrations and build assets:**
   ```bash
   php artisan migrate
   npm run build
   ```

5. **Start the local development server:**
   ```bash
   php artisan serve
   ```

## Development & Testing

```bash
# Run tests
php artisan test --compact

# Run static analysis
vendor/bin/phpstan analyse

# Run code formatter
vendor/bin/pint
```

## License

The MIT License (MIT).
