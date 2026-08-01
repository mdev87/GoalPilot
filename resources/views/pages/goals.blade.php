<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">My Goals</flux:heading>
            <flux:subheading>Manage your recurring weekly objectives and goals.</flux:subheading>
        </div>
        <div>
            <flux:button
                wire:click="$toggle('showArchived')"
                variant="outline"
                icon="archive-box"
                size="sm"
                class="cursor-pointer">
                {{ $showArchived ? 'Show Active Goals' : 'Show Archived Goals' }}
            </flux:button>
        </div>
    </div>

    @if ($errorMessage)
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4" heading="{{ $errorMessage }}" />
    @endif

    {{-- Create Goal Form --}}
    @if (! $showArchived)
    <flux:card class="space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-zinc-200 dark:border-zinc-800">
            <div class="p-2 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg">
                <flux:icon icon="plus-circle" class="size-5" />
            </div>
            <div>
                <flux:heading size="lg">Create New Goal</flux:heading>
                <flux:subheading>Define a recurring weekly goal to track performance and time allocation.</flux:subheading>
            </div>
        </div>

        <form wire:submit.prevent="createGoal" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-4">
                <flux:field>
                    <flux:label>Goal Name</flux:label>
                    <flux:input type="text" wire:model="name" placeholder="e.g., Deep Work, Exercise, Reading" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Notes / Description (Optional)</flux:label>
                    <flux:input type="text" wire:model="notes" placeholder="e.g., Focus on backend architecture tasks" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button type="submit" variant="primary" icon="plus" class="cursor-pointer">
                    Add Goal
                </flux:button>
            </div>
        </form>
    </flux:card>
    @endif

    {{-- Goals List --}}
    <div class="space-y-3">
        @forelse ($goals as $goal)
        <flux:card wire:key="goal-{{ $goal->id }}" class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            @if ($editingGoalId === $goal->id)
            <form wire:submit.prevent="updateGoal" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 items-start gap-3">
                    <flux:field>
                        <flux:input type="text" wire:model="editName" placeholder="Goal name" />
                        <flux:error name="editName" />
                    </flux:field>
                    <flux:field>
                        <flux:input type="text" wire:model="editNotes" placeholder="Notes (optional)" />
                        <flux:error name="editNotes" />
                    </flux:field>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button type="submit" variant="primary" size="sm" icon="check" class="cursor-pointer px-2 py-1 h-8 text-xs">
                        Save
                    </flux:button>
                    <flux:button wire:click="cancelEdit" variant="ghost" size="sm" class="cursor-pointer px-2 py-1 h-8 text-xs">
                        Cancel
                    </flux:button>
                </div>
            </form>
            @else
            <div>
                <div class="flex items-center gap-2">
                    <h4 class="font-semibold text-zinc-900 dark:text-zinc-100 text-base">{{ $goal->name }}</h4>
                    @if ($goal->isArchived())
                    <flux:badge color="amber" size="sm">Archived</flux:badge>
                    @endif
                </div>
                @if ($goal->notes)
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $goal->notes }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <flux:button
                    wire:click="startEdit({{ $goal->id }})"
                    wire:target="startEdit({{ $goal->id }})"
                    variant="primary"
                    color="indigo"
                    size="sm"
                    icon="pencil"
                    class="cursor-pointer font-medium">
                    Edit
                </flux:button>
                @if ($goal->isArchived())
                <flux:button
                    wire:click="unarchiveGoal({{ $goal->id }})"
                    wire:target="unarchiveGoal({{ $goal->id }})"
                    variant="primary"
                    color="emerald"
                    size="sm"
                    icon="arrow-path"
                    class="cursor-pointer font-medium">
                    Unarchive
                </flux:button>
                @else
                <flux:button
                    wire:click="archiveGoal({{ $goal->id }})"
                    wire:target="archiveGoal({{ $goal->id }})"
                    variant="primary"
                    color="amber"
                    size="sm"
                    icon="archive-box"
                    class="cursor-pointer font-medium">
                    Archive
                </flux:button>
                @endif
                <flux:button
                    wire:click="deleteGoal({{ $goal->id }})"
                    wire:target="deleteGoal({{ $goal->id }})"
                    variant="danger"
                    size="sm"
                    icon="trash"
                    class="cursor-pointer font-medium">
                    Delete
                </flux:button>
            </div>
            @endif
        </flux:card>
        @empty
        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-500 text-sm">
            {{ $showArchived ? 'No archived goals found.' : 'No active goals yet. Create one above!' }}
        </div>
        @endforelse
    </div>
</div>