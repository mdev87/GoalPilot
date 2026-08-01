<div class="space-y-6">
    <div>
        <flux:heading size="xl" level="1">Time Log</flux:heading>
        <flux:subheading size="lg">Track time spent on your planned weekly goals.</flux:subheading>
    </div>

    @if ($errorMessage)
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4" heading="{{ $errorMessage }}" />
    @endif

    @if ($successMessage)
        <flux:callout variant="success" icon="check-circle" class="mb-4" heading="{{ $successMessage }}" />
    @endif

    @if (! $activeWeek)
        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-500 text-sm">
            Please create an active week first before logging time. <a href="{{ route('weeks.index') }}" class="text-indigo-600 underline font-medium">Go to Weekly Planner</a>
        </div>
    @elseif ($plans->isEmpty())
        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-500 text-sm">
            No goals planned for this week. <a href="{{ route('weeks.index') }}" class="text-indigo-600 underline font-medium">Allocate goal priorities</a> first.
        </div>
    @else
        {{-- Log Time Form --}}
        <flux:card class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-zinc-200 dark:border-zinc-800">
                <flux:icon icon="clock" class="size-5 text-indigo-600 dark:text-indigo-400" />
                <flux:heading size="lg">Log New Time Entry</flux:heading>
            </div>

            <form wire:submit.prevent="logTime" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 items-start gap-4">
                    <flux:field>
                        <flux:label>Goal</flux:label>
                        <flux:select wire:model="weeklyGoalPlanId">
                            <option value="">Select a Goal...</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->goal->name }} ({{ $plan->logged_minutes }}/{{ $plan->allocated_minutes }}m)</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="weeklyGoalPlanId" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Date & Time</flux:label>
                        <flux:input type="datetime-local" wire:model="datetime" min="{{ $activeWeek->week_start_date->format('Y-m-d\TH:i') }}" max="{{ $activeWeek->getEndDate()->format('Y-m-d\T23:59') }}" />
                        <flux:error name="datetime" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Duration (Minutes)</flux:label>
                        <flux:input type="number" wire:model="durationInMinutes" min="1" max="1440" />
                        <flux:error name="durationInMinutes" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Note (Optional)</flux:label>
                    <flux:input type="text" wire:model="note" placeholder="What specific work did you accomplish?" />
                    <flux:error name="note" />
                </flux:field>

                <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button type="submit" variant="primary" icon="clock" class="cursor-pointer">
                        Log Time
                    </flux:button>
                </div>
            </form>
        </flux:card>

        {{-- Recent Time Entries --}}
        <div class="space-y-3">
            <flux:heading size="md">Logged Entries this Week</flux:heading>
            <div class="space-y-2">
                @forelse ($recentEntries as $entry)
                    <flux:card wire:key="time-entry-{{ $entry->id }}" class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        @if ($editingEntryId === $entry->id)
                            <form wire:submit.prevent="updateEntry" class="w-full space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-3">
                                    <flux:field>
                                        <flux:label>Date & Time</flux:label>
                                        <flux:input type="datetime-local" wire:model="editDatetime" min="{{ $activeWeek->week_start_date->format('Y-m-d\TH:i') }}" max="{{ $activeWeek->getEndDate()->format('Y-m-d\T23:59') }}" />
                                        <flux:error name="editDatetime" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Duration (Minutes)</flux:label>
                                        <flux:input type="number" wire:model="editDurationInMinutes" min="1" max="1440" />
                                        <flux:error name="editDurationInMinutes" />
                                    </flux:field>
                                </div>
                                <flux:field>
                                    <flux:label>Note</flux:label>
                                    <flux:input type="text" wire:model="editNote" placeholder="Note" />
                                    <flux:error name="editNote" />
                                </flux:field>
                                <div class="flex items-center gap-2 justify-end">
                                    <flux:button type="submit" variant="primary" size="sm" icon="check" class="cursor-pointer">
                                        Save
                                    </flux:button>
                                    <flux:button wire:click="cancelEdit" variant="ghost" size="sm" class="cursor-pointer">
                                        Cancel
                                    </flux:button>
                                </div>
                            </form>
                        @else
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $entry->weeklyGoalPlan->goal->name }}</h4>
                                    <flux:badge size="sm" color="zinc">{{ $entry->duration_in_minutes }} mins</flux:badge>
                                    <span class="text-xs text-zinc-400">({{ $entry->datetime->format('M d, H:i') }})</span>
                                </div>
                                @if ($entry->note)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $entry->note }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <flux:button 
                                    wire:click="startEdit({{ $entry->id }})" 
                                    wire:target="startEdit({{ $entry->id }})" 
                                    variant="primary"
                                    color="indigo" 
                                    size="sm" 
                                    icon="pencil" 
                                    class="cursor-pointer font-medium"
                                >
                                    Edit
                                </flux:button>
                                <flux:button 
                                    wire:click="deleteEntry({{ $entry->id }})" 
                                    wire:target="deleteEntry({{ $entry->id }})" 
                                    variant="danger" 
                                    size="sm" 
                                    icon="trash" 
                                    class="cursor-pointer font-medium"
                                >
                                    Delete
                                </flux:button>
                            </div>
                        @endif
                    </flux:card>
                @empty
                    <div class="p-6 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-500 text-sm">
                        No time entries logged for this week yet.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
