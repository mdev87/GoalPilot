<?php

namespace App\Livewire\TimeEntries;

use App\Actions\TimeEntryActions\CreateTimeEntry;
use App\Actions\TimeEntryActions\DeleteTimeEntry;
use App\Actions\TimeEntryActions\UpdateTimeEntry;
use App\Models\TimeEntry;
use App\Models\WeeklyGoalPlan;
use DomainException;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Time Log')]
class TimeLogger extends Component
{
    public ?int $weeklyGoalPlanId = null;

    public string $datetime = '';

    public int $durationInMinutes = 30;

    public ?string $note = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public ?int $editingEntryId = null;

    public string $editDatetime = '';

    public int $editDurationInMinutes = 30;

    public ?string $editNote = null;

    public function mount(): void
    {
        $this->datetime = now()->format('Y-m-d\TH:i');
    }

    public function logTime(CreateTimeEntry $createTimeEntry): void
    {
        $activeWeek = auth()->user()->activeWeek;

        if (! $activeWeek) {
            $this->errorMessage = 'No active week found.';

            return;
        }

        $this->validate([
            'weeklyGoalPlanId' => 'required|exists:weekly_goal_plans,id',
            'datetime' => [
                'required',
                'date',
                'after_or_equal:'.$activeWeek->week_start_date->startOfDay()->toDateTimeString(),
                'before_or_equal:'.$activeWeek->getEndDate()->endOfDay()->toDateTimeString(),
            ],
            'durationInMinutes' => 'required|integer|min:1|max:1440',
            'note' => 'nullable|string|max:1000',
        ]);

        $plan = WeeklyGoalPlan::findOrFail($this->weeklyGoalPlanId);
        $this->authorize('update', $plan);

        try {
            $createTimeEntry->execute($plan, $this->datetime, $this->durationInMinutes, $this->note);
            $this->reset(['note']);
            $this->datetime = now()->format('Y-m-d\TH:i');
            $this->successMessage = 'Time logged successfully!';
            $this->errorMessage = null;
        } catch (DomainException|InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function startEdit(TimeEntry $entry): void
    {
        $this->authorize('update', $entry);
        $this->editingEntryId = $entry->id;
        $this->editDatetime = $entry->datetime->format('Y-m-d\TH:i');
        $this->editDurationInMinutes = $entry->duration_in_minutes;
        $this->editNote = $entry->note;
    }

    public function cancelEdit(): void
    {
        $this->editingEntryId = null;
        $this->reset(['editDatetime', 'editDurationInMinutes', 'editNote']);
    }

    public function updateEntry(UpdateTimeEntry $updateTimeEntry): void
    {
        if (! $this->editingEntryId) {
            return;
        }

        $activeWeek = auth()->user()->activeWeek;

        if (! $activeWeek) {
            $this->errorMessage = 'No active week found.';

            return;
        }

        $entry = TimeEntry::findOrFail($this->editingEntryId);
        $this->authorize('update', $entry);

        $this->validate([
            'editDatetime' => [
                'required',
                'date',
                'after_or_equal:'.$activeWeek->week_start_date->startOfDay()->toDateTimeString(),
                'before_or_equal:'.$activeWeek->getEndDate()->endOfDay()->toDateTimeString(),
            ],
            'editDurationInMinutes' => 'required|integer|min:1|max:1440',
            'editNote' => 'nullable|string|max:1000',
        ]);

        try {
            $updateTimeEntry->execute($entry, $this->editDatetime, $this->editDurationInMinutes, $this->editNote);
            $this->cancelEdit();
            $this->successMessage = 'Time entry updated successfully!';
            $this->errorMessage = null;
        } catch (DomainException|InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function deleteEntry(TimeEntry $entry, DeleteTimeEntry $deleteTimeEntry): void
    {
        $this->authorize('delete', $entry);

        try {
            $deleteTimeEntry->execute($entry);
            $this->successMessage = 'Time entry deleted.';
            $this->errorMessage = null;
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function render(): View
    {
        $activeWeek = auth()->user()->activeWeek;
        $plans = $activeWeek ? $activeWeek->weeklyGoalPlans()->with('goal', 'timeEntries')->get() : collect();

        $recentEntries = $activeWeek ? $activeWeek->timeEntries()->with('weeklyGoalPlan.goal')->latest()->get() : collect();

        return view('pages.time-entries', [
            'activeWeek' => $activeWeek,
            'plans' => $plans,
            'recentEntries' => $recentEntries,
        ]);
    }
}
