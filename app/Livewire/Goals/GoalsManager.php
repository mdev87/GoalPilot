<?php

namespace App\Livewire\Goals;

use App\Actions\GoalActions\ArchiveGoal;
use App\Actions\GoalActions\CreateGoal;
use App\Actions\GoalActions\DeleteGoal;
use App\Actions\GoalActions\UnarchiveGoal;
use App\Actions\GoalActions\UpdateGoal;
use App\Models\Goal;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Goals')]
class GoalsManager extends Component
{
    public string $name = '';

    public ?string $notes = null;

    public ?int $editingGoalId = null;

    public string $editName = '';

    public ?string $editNotes = null;

    public bool $showArchived = false;

    public ?string $errorMessage = null;

    /**
     * Create a new goal.
     */
    public function createGoal(CreateGoal $createGoal): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $createGoal->execute(auth()->guard()->user(), $this->name, $this->notes);

        $this->reset(['name', 'notes']);
        $this->errorMessage = null;
    }

    /**
     * Start editing a goal.
     */
    public function startEdit(Goal $goal): void
    {
        $this->authorize('update', $goal);

        $this->editingGoalId = $goal->id;
        $this->editName = $goal->name;
        $this->editNotes = $goal->notes;
    }

    /**
     * Cancel editing.
     */
    public function cancelEdit(): void
    {
        $this->reset(['editingGoalId', 'editName', 'editNotes']);
    }

    /**
     * Save goal update.
     */
    public function updateGoal(UpdateGoal $updateGoal): void
    {
        $goal = Goal::findOrFail($this->editingGoalId);
        $this->authorize('update', $goal);

        $this->validate([
            'editName' => 'required|string|max:255',
            'editNotes' => 'nullable|string|max:1000',
        ]);

        $updateGoal->execute($goal, $this->editName, $this->editNotes);

        $this->cancelEdit();
        $this->errorMessage = null;
    }

    /**
     * Archive goal.
     */
    public function archiveGoal(Goal $goal, ArchiveGoal $archiveGoal): void
    {
        $this->authorize('archive', $goal);

        try {
            $archiveGoal->execute($goal);
            $this->errorMessage = null;
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Unarchive goal.
     */
    public function unarchiveGoal(Goal $goal, UnarchiveGoal $unarchiveGoal): void
    {
        $this->authorize('archive', $goal);

        $unarchiveGoal->execute($goal);
        $this->errorMessage = null;
    }

    /**
     * Delete goal.
     */
    public function deleteGoal(Goal $goal, DeleteGoal $deleteGoal): void
    {
        $this->authorize('delete', $goal);

        try {
            $deleteGoal->execute($goal);
            $this->errorMessage = null;
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(): View
    {
        /** @var User */
        $user = auth()->guard()->user();

        $goalsQuery = Goal::query()->where('user_id', $user->id);

        if ($this->showArchived) {
            $goalsQuery->archived();
        } else {
            $goalsQuery->active();
        }

        $goals = $goalsQuery->latest()->get();

        return view('pages.goals', [
            'goals' => $goals,
        ]);
    }
}
