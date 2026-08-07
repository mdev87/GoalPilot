@use(Illuminate\Foundation\Inspiring)

@php
$hour = now()->hour;
$minute = now()->minute;
$time = $hour * 60 + $minute;

$greeting = match (true) {
$time >= (19 * 60 + 6) || $time < (5 * 60 + 15)=> __('Good night'),

    $time < (12 * 60)=> __('Good morning'),

        $time < (17 * 60)=> __('Good afternoon'),

            default
            => __('Good evening'),
            };
            @endphp

            <div class="space-y-8">

                {{-- Header & Personal Greeting --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <flux:heading size="xl" class="font-bold">
                            {{ $greeting }}, {{ auth()->user()->name }}! 👋
                        </flux:heading>
                        <flux:subheading class="mt-1">
                            {{ Inspiring::quotes()->random() }}
                        </flux:subheading>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:badge color="amber" variant="solid" icon="fire" size="lg">
                            {{ __('Current Streak: :count days', ['count' => $current_streak]) }}
                        </flux:badge>
                        <flux:badge color="zinc" icon="trophy" size="lg">
                            {{ __('Best: :count days', ['count' => $longest_streak]) }}
                        </flux:badge>
                    </div>
                </div>

                <flux:separator variant="subtle" />

                {{-- Stats Cards --}}
                <div class="flex gap-6 mb-6">
                    {{-- Card 1: Planned Time --}}
                    <div class="relative flex-1 rounded-lg px-6 py-4 shadow bg-zinc-50 dark:bg-zinc-700">
                        <flux:subheading>{{ __('Planned Time') }}</flux:subheading>

                        <flux:heading size="xl" class="mb-2">
                            {{ number_format($total_planned_minutes) }} <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('mins') }}</span>
                        </flux:heading>

                        <div class="flex items-center gap-1 font-medium text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:icon icon="clock" variant="micro" /> {{ __('Weekly Allocated') }}
                        </div>
                    </div>

                    {{-- Card 2: Logged Time --}}
                    <div class="relative flex-1 rounded-lg px-6 py-4 shadow bg-zinc-50 dark:bg-zinc-700 max-md:hidden">
                        <flux:subheading>{{ __('Logged Time') }}</flux:subheading>

                        <flux:heading size="xl" class="mb-2">
                            {{ number_format($total_logged_minutes) }} <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('mins') }}</span>
                        </flux:heading>

                        @php
                            $isLoggedOnTrack = $overall_completion_percentage >= 50;
                        @endphp
                        <div class="flex items-center gap-1 font-medium text-sm {{ $isLoggedOnTrack ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                            <flux:icon icon="{{ $isLoggedOnTrack ? 'arrow-trending-up' : 'arrow-trending-down' }}" variant="micro" /> {{ $overall_completion_percentage }}% {{ __('Logged') }}
                        </div>
                    </div>

                    {{-- Card 3: Completion Rate --}}
                    <div class="relative flex-1 rounded-lg px-6 py-4 shadow bg-zinc-50 dark:bg-zinc-700 max-lg:hidden">
                        <flux:subheading>{{ __('Overall Completion') }}</flux:subheading>

                        <flux:heading size="xl" class="mb-2">{{ $overall_completion_percentage }}%</flux:heading>

                        <div class="mt-2">
                            <flux:progress value="{{ $overall_completion_percentage }}" color="emerald" size="sm" />
                        </div>
                    </div>

                    {{-- Card 4: Streaks --}}
                    <div class="relative flex-1 rounded-lg px-6 py-4 shadow bg-zinc-50 dark:bg-zinc-700 max-lg:hidden">
                        <flux:subheading>{{ __('Current Streak') }}</flux:subheading>

                        <flux:heading size="xl" class="mb-2">
                            {{ $current_streak }} <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('days') }}</span>
                        </flux:heading>

                        <div class="flex items-center gap-1 font-medium text-sm text-amber-600 dark:text-amber-400">
                            <flux:icon icon="fire" variant="micro" /> {{ __('Best:') }} {{ $longest_streak }} {{ __('days') }}
                        </div>
                    </div>
                </div>

                {{-- Main Content Grid: Goal Breakdown & Recent Activity --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Goal Breakdown Table (2 Columns on large screens) --}}
                    <div class="lg:col-span-2 space-y-3">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">{{ __('Weekly Goal Breakdown') }}</flux:heading>
                            <flux:button variant="ghost" size="sm" icon="arrow-right" :href="route('goals.index')" wire:navigate class="cursor-pointer">
                                {{ __('Manage Goals') }}
                            </flux:button>
                        </div>

                        @if($goal_breakdown->isNotEmpty())
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Goal') }}</flux:table.column>
                                <flux:table.column class="max-md:hidden">{{ __('Priority') }}</flux:table.column>
                                <flux:table.column class="max-md:hidden">{{ __('Allocated') }}</flux:table.column>
                                <flux:table.column>{{ __('Logged') }}</flux:table.column>
                                <flux:table.column>{{ __('Progress') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($goal_breakdown as $goal)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">{{ $goal['name'] }}</flux:table.cell>
                                    <flux:table.cell class="max-md:hidden">
                                        <flux:badge size="sm" inset="top bottom">{{ $goal['priority_percentage'] }}%</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="max-md:hidden">{{ number_format($goal['allocated_minutes']) }} {{ __('mins') }}</flux:table.cell>
                                    <flux:table.cell variant="strong">{{ number_format($goal['logged_minutes']) }} {{ __('mins') }}</flux:table.cell>
                                    <flux:table.cell class="w-1/4 min-w-[140px]">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <flux:progress
                                                    value="{{ $goal['completion_percentage'] }}"
                                                    color="{{ $goal['completion_percentage'] >= 100 ? 'emerald' : 'blue' }}" />
                                            </div>
                                            <span class="text-xs text-zinc-500 min-w-[3ch]">{{ $goal['completion_percentage'] }}%</span>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                        @else
                        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-700/50 border border-dashed border-zinc-200 dark:border-zinc-600 rounded-lg text-zinc-500 dark:text-zinc-400 text-sm">
                            {{ __('No goal allocations found for the active week.') }}
                        </div>
                        @endif
                    </div>

                    {{-- Recent Activity Feed --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">{{ __('Recent Activity') }}</flux:heading>
                            <flux:button variant="ghost" size="sm" icon="arrow-right" :href="route('time-entries.index')" wire:navigate class="cursor-pointer">
                                {{ __('View All') }}
                            </flux:button>
                        </div>

                        @if(isset($recent_time_entries) && $recent_time_entries->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($recent_time_entries as $entry)
                            <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700 flex items-center justify-between gap-3 text-sm">
                                <div class="space-y-0.5 min-w-0 flex-1">
                                    <div class="font-medium truncate text-zinc-900 dark:text-zinc-100">
                                        {{ $entry->weeklyGoalPlan->goal->name ?? __('Goal') }}
                                    </div>
                                    @if($entry->note)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                        {{ $entry->note }}
                                    </p>
                                    @endif
                                    <span class="text-[11px] text-zinc-400 dark:text-zinc-400 block">
                                        {{ \Illuminate\Support\Carbon::parse($entry->datetime)->diffForHumans() }}
                                    </span>
                                </div>
                                <flux:badge size="sm" color="zinc" class="shrink-0 font-semibold">
                                    {{ $entry->duration_in_minutes }} {{ __('mins') }}
                                </flux:badge>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-700/50 border border-dashed border-zinc-200 dark:border-zinc-600 rounded-lg text-zinc-500 dark:text-zinc-400 text-sm">
                            {{ __('No time logged recently.') }}
                        </div>
                        @endif
                    </div>
                </div>

            </div>
