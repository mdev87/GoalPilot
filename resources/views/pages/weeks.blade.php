<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Weekly Planning</flux:heading>
            <flux:subheading size="lg">Set your planned hours and goal priorities for the active week cycle.</flux:subheading>
        </div>
    </div>

    @if ($errorMessage)
    <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4" heading="{{ $errorMessage }}" />
    @endif

    @if ($successMessage)
    <flux:callout variant="success" icon="check-circle" class="mb-4" heading="{{ $successMessage }}" />
    @endif

    @if (! $activeWeek)
    {{-- Start New Week Form --}}
    <flux:card class="space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-zinc-200 dark:border-zinc-800">
            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg">
                <flux:icon icon="calendar" class="size-6" />
            </div>
            <div>
                <flux:heading size="lg">Start New Week</flux:heading>
                <flux:subheading size="md">Define your target timeframe and total available capacity for this week.</flux:subheading>
            </div>
        </div>

        <form wire:submit.prevent="createWeek" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-6">
                <flux:field>
                    <flux:label>Week Start Date</flux:label>
                    <flux:input type="date" wire:model="weekStartDate" class="cursor-pointer" />
                    <flux:error name="weekStartDate" />
                </flux:field>

                <flux:field>
                    <flux:label>Planned Time (Minutes)</flux:label>
                    <flux:input type="number" wire:model.live="plannedMinutes" min="30" max="4800" step="30" />
                    <flux:description>
                        Approx. {{ round((int) $plannedMinutes / 60, 1) }} hours total capacity
                    </flux:description>
                    <flux:error name="plannedMinutes" />
                </flux:field>
            </div>

            <div class="flex justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button type="submit" variant="primary" icon="plus" class="cursor-pointer">
                    Create Active Week
                </flux:button>
            </div>
        </form>
    </flux:card>
    @else
    {{-- Active Week Section --}}
    <flux:card class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200 dark:border-zinc-800">
            <div>
                <div class="flex items-center gap-2">
                    <flux:badge color="indigo" class="font-semibold uppercase">Active Week</flux:badge>
                    <span class="text-xs text-zinc-500">
                        {{ $activeWeek->week_start_date->format('M d, Y') }} – {{ $activeWeek->getEndDate()->format('M d, Y') }}
                    </span>
                </div>
                <flux:heading size="lg" class="mt-1">
                    Capacity: {{ number_format($activeWeek->planned_minutes) }} mins ({{ round($activeWeek->planned_minutes / 60, 1) }} hrs)
                </flux:heading>
            </div>
            <div>
                @if ($activeWeek->hasEnded())
                <flux:button
                    wire:click="lockWeek({{ $activeWeek->id }})"
                    wire:target="lockWeek({{ $activeWeek->id }})"
                    variant="danger"
                    size="sm"
                    icon="lock-closed"
                    class="cursor-pointer">
                    Lock & Archive Week
                </flux:button>
                @else
                <div class="flex items-center gap-2">
                    <flux:badge color="zinc" size="sm">Active (In Progress)</flux:badge>
                    <span class="text-xs text-zinc-400" title="Week ends on {{ $activeWeek->getEndDate()->format('M d, Y') }}">
                        Lockable on {{ $activeWeek->getEndDate()->format('M d') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Priority Allocation Form --}}
        <form wire:submit.prevent="savePlans" class="space-y-6">
            <div>
                <flux:heading size="lg">Goal Priority Allocation</flux:heading>
                <flux:subheading size="md">Distribute your planned hours across your active goals (Must total exactly 100%).</flux:subheading>
            </div>

            <div class="space-y-3">
                @forelse ($activeGoals as $goal)
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 rounded-xl">
                    <div>
                        <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $goal->name }}</h4>
                        @if ($goal->notes)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $goal->notes }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <flux:input
                            type="number"
                            step="1"
                            min="0"
                            max="100"
                            wire:model="priorities.{{ $goal->id }}"
                            placeholder="0"
                            class="w-24 text-right font-medium" />
                        <span class="text-sm font-semibold text-zinc-500">%</span>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-500 text-sm">
                    No active goals found. <a href="{{ route('goals.index') }}" wire:navigate class="text-indigo-600 underline font-medium">Create goals first</a> to assign priorities.
                </div>
                @endforelse
            </div>

            @if (count($activeGoals) > 0)
            <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button type="submit" variant="primary" icon="check" class="cursor-pointer">
                    Save Allocation
                </flux:button>
            </div>
            @endif
        </form>
    </flux:card>
    @endif

    {{-- Past Locked Weeks History Section --}}
    <div class="space-y-4 pt-4">
        <div class="flex items-center gap-2 pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <flux:icon icon="clock" class="size-5 text-zinc-500" />
            <flux:heading size="lg">Past Weeks History</flux:heading>
        </div>

        <div class="space-y-4">
            @forelse ($pastWeeks as $week)
            <flux:card class="p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <flux:badge color="zinc" size="sm">Locked</flux:badge>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">
                                {{ $week->week_start_date->format('M d, Y') }} – {{ $week->getEndDate()->format('M d, Y') }}
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            Planned Capacity: {{ number_format($week->planned_minutes) }} mins ({{ round($week->planned_minutes / 60, 1) }} hrs)
                        </p>
                    </div>
                    <span class="text-xs text-zinc-400">
                        Locked at {{ $week->locked_at ? $week->locked_at->format('M d, Y H:i') : 'N/A' }}
                    </span>
                </div>

                <div class="space-y-2">
                    <h5 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Goal Performance Breakdown</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse ($week->weeklyGoalPlans as $plan)
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/40 rounded-lg space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $plan->goal->name }}</span>
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $plan->priority_percentage }}% Priority</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span>Logged: {{ $plan->logged_minutes }} mins</span>
                                <span>Target: {{ $plan->allocated_minutes }} mins</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-zinc-400">No goals were planned for this week.</p>
                        @endforelse
                    </div>
                </div>
            </flux:card>
            @empty
            <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-500 text-sm">
                No past locked weeks found in history.
            </div>
            @endforelse
        </div>
    </div>
</div>