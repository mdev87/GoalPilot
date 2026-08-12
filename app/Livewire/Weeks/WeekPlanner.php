<?php

namespace App\Livewire\Weeks;

use App\Actions\WeekActions\CreateWeek;
use App\Actions\WeekActions\LockWeek;
use App\Actions\WeeklyGoalPlanActions\SetWeeklyGoalPlans;
use App\Models\Goal;
use App\Models\User;
use App\Models\Week;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Weekly Planning')]
class WeekPlanner extends Component
{
    public string $weekStartDate = '';

    public int $plannedMinutes = 1200;

    /**
     * @var array<int, float>
     */
    public array $priorities = [];

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount(): void
    {
        $this->plannedMinutes = (int) config('week.default_planned_minutes', 1200);
        $this->weekStartDate = now()->startOfWeek()->toDateString();
        $this->loadActiveWeekPriorities();
    }

    public function loadActiveWeekPriorities(): void
    {
        /** @var User */
        $user = auth()->guard()->user();
        $activeWeek = $user->activeWeek;

        if ($activeWeek) {
            $this->plannedMinutes = $activeWeek->planned_minutes;
            $this->priorities = [];

            foreach ($activeWeek->weeklyGoalPlans as $plan) {
                $this->priorities[$plan->goal_id] = (float) $plan->priority_percentage;
            }
        }
    }

    public function createWeek(CreateWeek $createWeek): void
    {
        $this->validate([
            'weekStartDate' => [
                'required',
                'date',
                Rule::unique('weeks', 'week_start_date')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'plannedMinutes' => 'required|integer|min:'.config('week.min_planned_minutes', 30).'|max:'.config('week.max_planned_minutes', 4800),
        ]);

        try {
            $createWeek->execute(auth()->user(), $this->weekStartDate, $this->plannedMinutes);
            $this->loadActiveWeekPriorities();
            $this->successMessage = 'New week created successfully!';
            $this->errorMessage = null;
        } catch (InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function lockWeek(Week $week, LockWeek $lockWeek): void
    {
        $this->authorize('update', $week);

        try {
            $lockWeek->execute($week);
            $this->successMessage = 'Week locked and archived successfully.';
            $this->errorMessage = null;
        } catch (DomainException|InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function savePlans(SetWeeklyGoalPlans $setWeeklyGoalPlans): void
    {
        $activeWeek = auth()->user()->activeWeek;

        if (! $activeWeek) {
            $this->errorMessage = 'No active week to plan.';

            return;
        }

        $plans = [];

        foreach ($this->priorities as $goalId => $percentage) {
            if ((float) $percentage > 0) {
                $plans[] = [
                    'goal_id' => (int) $goalId,
                    'priority_percentage' => (float) $percentage,
                ];
            }
        }

        try {
            $setWeeklyGoalPlans->execute($activeWeek, $plans);
            $this->successMessage = 'Weekly goal plans saved!';
            $this->errorMessage = null;
        } catch (DomainException|InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function render(): View
    {
        /** @var User */
        $user = auth()->guard()->user();

        $activeWeek = $user->activeWeek;
        $activeGoals = Goal::query()->where('user_id', $user->id)->active()->get();

        /** @var Collection<int, Week> */
        $pastWeeks = Week::query()->where('user_id', $user->id)->locked()->with([
            'weeklyGoalPlans' => fn (Relation $query) => $query->with('goal')
                ->withSum('timeEntries', 'duration_in_minutes'),
            'timeEntries',
        ])->latest('week_start_date')->get();
        $pastWeeks->each(function ($week) {
            $week->weeklyGoalPlans->each->setRelation('week', $week);
        });

        return view('pages.weeks', [
            'activeWeek' => $activeWeek,
            'activeGoals' => $activeGoals,
            'pastWeeks' => $pastWeeks,
        ]);
    }
}
