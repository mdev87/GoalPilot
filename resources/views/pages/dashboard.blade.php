@use(Illuminate\Foundation\Inspiring)

@php
    $hour = now()->hour;
    $minute = now()->minute;
    $time = $hour * 60 + $minute;

    $greeting = match (true) {
        $time >= 19 * 60 + 6 || $time < 5 * 60 + 15 => __('Good night'),
        $time < 12 * 60 => __('Good morning'),
        $time < 17 * 60 => __('Good afternoon'),
        default => __('Good evening'),
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
                {{ $inspiringQuote }}
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

    {{-- AI Insights Hero Section --}}
    <div
        class="rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-gradient-to-br from-indigo-50/50 via-white to-purple-50/30 dark:from-zinc-800 dark:via-zinc-800 dark:to-indigo-950/30 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <flux:icon icon="sparkles" class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg" class="font-bold flex items-center gap-2">
                        {{ __('AI Weekly Performance Insights') }}
                        @if ($aiAnalysis)
                            <flux:badge color="emerald" variant="solid" size="sm">
                                {{ __('Rating: :score/10', ['score' => $aiAnalysis['execution_score']]) }}
                            </flux:badge>
                        @endif
                    </flux:heading>
                    <flux:subheading>
                        {{ __('Get an on-demand, intelligent analysis of your goal progress, achievements, and improvement areas.') }}
                    </flux:subheading>
                </div>
            </div>

            <div>
                <flux:button wire:click="generateAiAnalysis" variant="primary"
                    class="cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-500"
                    :loading="false">
                    <span wire:loading.remove wire:target="generateAiAnalysis" class="flex gap-2 items-center">
                        <flux:icon icon="sparkles" class="size-5" />
                        {{ $aiAnalysis ? __('Refresh AI Insights') : __('Generate AI Insights') }}
                    </span>
                    <span wire:loading.flex wire:target="generateAiAnalysis" class="flex gap-2 items-center">
                        <flux:icon icon="arrow-path" class="animate-spin size-5" />
                        {{ __('Analyzing Performance...') }}
                    </span>
                </flux:button>
            </div>
        </div>

        @if ($aiErrorMessage)
            <div
                class="p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 text-sm flex items-center gap-2">
                <flux:icon icon="exclamation-triangle" class="size-5 shrink-0 text-red-500" />
                <span>{{ $aiErrorMessage }}</span>
            </div>
        @endif

        @if ($aiAnalysis)
            <div class="space-y-6 pt-2">
                {{-- Executive Summary --}}
                <div
                    class="p-5 rounded-lg bg-white/90 dark:bg-zinc-800/90 border border-zinc-200 dark:border-zinc-700 space-y-2">
                    <p class="font-semibold text-base text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <flux:icon icon="document-text" class="size-5 text-indigo-600 dark:text-indigo-400" />
                        {{ __('Executive Summary') }}
                    </p>
                    <p class="text-base leading-relaxed text-zinc-800 dark:text-zinc-200">
                        {{ $aiAnalysis['summary'] }}
                    </p>
                </div>

                {{-- Structured Breakdown Grids --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Achievements --}}
                    <div
                        class="p-5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/50 space-y-3">
                        <div class="flex items-center gap-2 font-bold text-emerald-900 dark:text-emerald-200 text-base">
                            <flux:icon icon="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                            {{ __('Key Achievements') }}
                        </div>
                        <ul class="space-y-2 text-sm text-zinc-800 dark:text-zinc-200">
                            @foreach ($aiAnalysis['achievements'] as $item)
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Areas for Improvement --}}
                    <div
                        class="p-5 rounded-lg bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 space-y-3">
                        <div class="flex items-center gap-2 font-bold text-amber-900 dark:text-amber-200 text-base">
                            <flux:icon icon="exclamation-circle" class="size-5 text-amber-600 dark:text-amber-400" />
                            {{ __('Areas for Improvement') }}
                        </div>
                        <ul class="space-y-2 text-sm text-zinc-800 dark:text-zinc-200">
                            @foreach ($aiAnalysis['areas_for_improvement'] as $item)
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Actionable Recommendations --}}
                    <div
                        class="p-5 rounded-lg bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-900/50 space-y-3">
                        <div class="flex items-center gap-2 font-bold text-blue-900 dark:text-blue-200 text-base">
                            <flux:icon icon="light-bulb" class="size-5 text-blue-600 dark:text-blue-400" />
                            {{ __('Recommendations') }}
                        </div>
                        <ul class="space-y-2 text-sm text-zinc-800 dark:text-zinc-200">
                            @foreach ($aiAnalysis['actionable_recommendations'] as $item)
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500 font-bold">•</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <flux:separator variant="subtle" />

    {{-- Stats Cards --}}
    <div class="flex gap-6 mb-6">
        {{-- Card 1: Planned Time --}}
        <div class="relative flex-1 rounded-lg px-6 py-4 shadow bg-zinc-50 dark:bg-zinc-700">
            <flux:subheading>{{ __('Planned Time') }}</flux:subheading>

            <flux:heading size="xl" class="mb-2">
                {{ number_format($total_planned_minutes) }} <span
                    class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('mins') }}</span>
            </flux:heading>

            <div class="flex items-center gap-1 font-medium text-sm text-zinc-500 dark:text-zinc-400">
                <flux:icon icon="clock" variant="micro" /> {{ __('Weekly Allocated') }}
            </div>
        </div>

        {{-- Card 2: Logged Time --}}
        <div class="relative flex-1 rounded-lg px-6 py-4 shadow bg-zinc-50 dark:bg-zinc-700 max-md:hidden">
            <flux:subheading>{{ __('Logged Time') }}</flux:subheading>

            <flux:heading size="xl" class="mb-2">
                {{ number_format($total_logged_minutes) }} <span
                    class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('mins') }}</span>
            </flux:heading>

            @php
                $isLoggedOnTrack = $overall_completion_percentage >= 50;
            @endphp
            <div
                class="flex items-center gap-1 font-medium text-sm {{ $isLoggedOnTrack ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                <flux:icon icon="{{ $isLoggedOnTrack ? 'arrow-trending-up' : 'arrow-trending-down' }}"
                    variant="micro" /> {{ $overall_completion_percentage }}% {{ __('Logged') }}
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
                {{ $current_streak }} <span
                    class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ __('days') }}</span>
            </flux:heading>

            <div class="flex items-center gap-1 font-medium text-sm text-amber-600 dark:text-amber-400">
                <flux:icon icon="fire" variant="micro" /> {{ __('Best:') }} {{ $longest_streak }}
                {{ __('days') }}
            </div>
        </div>
    </div>

    {{-- Main Content Grid: Goal Breakdown & Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Goal Breakdown Table (2 Columns on large screens) --}}
        <div class="lg:col-span-2 space-y-3">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Weekly Goal Breakdown') }}</flux:heading>
                <flux:button variant="ghost" size="sm" icon="arrow-right" :href="route('goals.index')"
                    wire:navigate class="cursor-pointer">
                    {{ __('Manage Goals') }}
                </flux:button>
            </div>

            @if ($goal_breakdown->isNotEmpty())
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
                                    <flux:badge size="sm" inset="top bottom">{{ $goal['priority_percentage'] }}%
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="max-md:hidden">{{ number_format($goal['allocated_minutes']) }}
                                    {{ __('mins') }}</flux:table.cell>
                                <flux:table.cell variant="strong">{{ number_format($goal['logged_minutes']) }}
                                    {{ __('mins') }}</flux:table.cell>
                                <flux:table.cell class="w-1/4 min-w-[140px]">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1">
                                            <flux:progress value="{{ $goal['completion_percentage'] }}"
                                                color="{{ $goal['completion_percentage'] >= 100 ? 'emerald' : 'blue' }}" />
                                        </div>
                                        <span
                                            class="text-xs text-zinc-500 min-w-[3ch]">{{ $goal['completion_percentage'] }}%</span>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <div
                    class="p-8 text-center bg-zinc-50 dark:bg-zinc-700/50 border border-dashed border-zinc-200 dark:border-zinc-600 rounded-lg text-zinc-500 dark:text-zinc-400 text-sm">
                    {{ __('No goal allocations found for the active week.') }}
                </div>
            @endif
        </div>

        {{-- Recent Activity Feed --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Recent Activity') }}</flux:heading>
                <flux:button variant="ghost" size="sm" icon="arrow-right" :href="route('time-entries.index')"
                    wire:navigate class="cursor-pointer">
                    {{ __('View All') }}
                </flux:button>
            </div>

            @if (isset($recent_time_entries) && $recent_time_entries->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($recent_time_entries as $entry)
                        <div
                            class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700 flex items-center justify-between gap-3 text-sm">
                            <div class="space-y-0.5 min-w-0 flex-1">
                                <div class="font-medium truncate text-zinc-900 dark:text-zinc-100">
                                    {{ $entry->goal->name ?? __('Goal') }}
                                </div>
                                @if ($entry->note)
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
                <div
                    class="p-8 text-center bg-zinc-50 dark:bg-zinc-700/50 border border-dashed border-zinc-200 dark:border-zinc-600 rounded-lg text-zinc-500 dark:text-zinc-400 text-sm">
                    {{ __('No time logged recently.') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Comprehensive Analytics & Execution Trends Section --}}
    <flux:separator variant="subtle" />

    <div class="space-y-6" x-data="{
        weeklyChart: null,
        yearlyChart: null,
        goalChart: null,
        renderCharts() {
            if (typeof window.Chart === 'undefined') return;
    
            const weeklyCanvas = this.$refs.weeklyCanvas;
            if (weeklyCanvas && weeklyCanvas.getContext('2d')) {
                if (this.weeklyChart) {
                    this.weeklyChart.destroy();
                    this.weeklyChart = null;
                }
                const data = @js($weekly_stats['weekly_trends'] ?? []);
                this.weeklyChart = window.initExecutionChart(weeklyCanvas, data);
                this.weeklyChart.resize();
            }
    
            const yearlyCanvas = this.$refs.yearlyCanvas;
            if (yearlyCanvas && yearlyCanvas.getContext('2d')) {
                if (this.yearlyChart) {
                    this.yearlyChart.destroy();
                    this.yearlyChart = null;
                }
                const data = @js($weekly_stats['weekly_trends'] ?? []);
                this.yearlyChart = window.initYearlyAreaChart(yearlyCanvas, data);
                this.yearlyChart.resize();
            }
    
            const goalCanvas = this.$refs.goalCanvas;
            if (goalCanvas && goalCanvas.getContext('2d')) {
                if (this.goalChart) {
                    this.goalChart.destroy();
                    this.goalChart = null;
                }
                const data = @js($trend_stats['goal_distributions'] ?? []);
                this.goalChart = window.initGoalDistributionChart(goalCanvas, data);
                this.goalChart.resize();
            }
        }
    }" x-init="$nextTick(() => renderCharts());
    Livewire.hook('morphed', () => $nextTick(() => renderCharts()))">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <flux:heading size="lg">{{ __('Analytics & Execution Trends') }}</flux:heading>
                <flux:subheading>
                    {{ __('Visual breakdown of weekly completion trends, annual tracking, and goal time distribution.') }}
                </flux:subheading>
            </div>

            <div class="flex items-center gap-2">
                <flux:button wire:click="setTimeframe(4)"
                    variant="{{ $timeframeWeeks === 4 ? 'primary' : 'subtle' }}" size="sm"
                    class="cursor-pointer">
                    {{ __('4 Weeks') }}
                </flux:button>
                <flux:button wire:click="setTimeframe(8)"
                    variant="{{ $timeframeWeeks === 8 ? 'primary' : 'subtle' }}" size="sm"
                    class="cursor-pointer">
                    {{ __('8 Weeks') }}
                </flux:button>
                <flux:button wire:click="setTimeframe(12)"
                    variant="{{ $timeframeWeeks === 12 ? 'primary' : 'subtle' }}" size="sm"
                    class="cursor-pointer">
                    {{ __('12 Weeks') }}
                </flux:button>
                <flux:button wire:click="setTimeframe(52)"
                    variant="{{ $timeframeWeeks === 52 ? 'primary' : 'subtle' }}" size="sm"
                    class="cursor-pointer">
                    {{ __('1 Year (52W)') }}
                </flux:button>
            </div>
        </div>

        {{-- Year Long-Term Area Chart --}}
        <div class="p-6 rounded-lg bg-zinc-50 dark:bg-zinc-700 shadow space-y-4">
            <div class="flex items-center justify-between">
                <flux:subheading class="font-semibold">{{ __('Time Tracking Trend (Area Overview)') }}
                </flux:subheading>
                <span
                    class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">{{ __('Past :count Weeks', ['count' => $timeframeWeeks]) }}</span>
            </div>
            <div class="h-64 relative w-full">
                <canvas x-ref="yearlyCanvas"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Weekly Execution Trend Bar Chart --}}
            <div class="p-6 rounded-lg bg-zinc-50 dark:bg-zinc-700 shadow space-y-4">
                <flux:subheading class="font-semibold">{{ __('Weekly Planned vs Logged Hours') }}</flux:subheading>
                <div class="h-64 relative w-full">
                    <canvas x-ref="weeklyCanvas"></canvas>
                </div>
            </div>

            {{-- Goal Time Distribution Doughnut Chart --}}
            <div class="p-6 rounded-lg bg-zinc-50 dark:bg-zinc-700 shadow space-y-4">
                <flux:subheading class="font-semibold">{{ __('Goal Time Distribution (All-Time)') }}</flux:subheading>
                <div class="h-64 relative w-full">
                    <canvas x-ref="goalCanvas"></canvas>
                </div>
            </div>
        </div>

        {{-- Detailed Statistics History Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-4">
            {{-- Weekly History List --}}
            <div class="space-y-3">
                <flux:subheading class="font-semibold">{{ __('Weekly History Details') }}</flux:subheading>

                @if (!empty($weekly_stats['weekly_trends']))
                    <div class="space-y-2">
                        @foreach ($weekly_stats['weekly_trends'] as $week)
                            <div
                                class="p-3.5 rounded-lg bg-zinc-50 dark:bg-zinc-700 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ \Illuminate\Support\Carbon::parse($week['week_start_date'])->format('M d') }}
                                        – {{ \Illuminate\Support\Carbon::parse($week['end_date'])->format('M d, Y') }}
                                    </span>
                                    @if ($week['is_locked'])
                                        <flux:badge size="sm" color="zinc" inset="top bottom">
                                            {{ __('Locked') }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="indigo" inset="top bottom">
                                            {{ __('Active') }}</flux:badge>
                                    @endif
                                </div>
                                <span class="font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ round($week['logged_minutes'] / 60, 1) }} /
                                    {{ round($week['planned_minutes'] / 60, 1) }} {{ __('hrs') }}
                                    ({{ $week['completion_percentage'] }}%)
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="p-6 text-center bg-zinc-50 dark:bg-zinc-700/50 border border-dashed border-zinc-200 dark:border-zinc-600 rounded-lg text-zinc-500 text-sm">
                        {{ __('No weekly history available for analytics.') }}
                    </div>
                @endif
            </div>

            {{-- Goal Time Distribution List --}}
            <div class="space-y-3">
                <flux:subheading class="font-semibold">{{ __('Goal Distribution Details') }}</flux:subheading>

                @if (!empty($trend_stats['goal_distributions']))
                    <div class="space-y-2">
                        @foreach ($trend_stats['goal_distributions'] as $goal)
                            <div
                                class="p-3.5 rounded-lg bg-zinc-50 dark:bg-zinc-700 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $goal['name'] }}
                                    </span>
                                    @if ($goal['is_archived'])
                                        <flux:badge size="sm" color="amber" inset="top bottom">
                                            {{ __('Archived') }}</flux:badge>
                                    @endif
                                </div>
                                <span class="font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ round($goal['total_logged_minutes'] / 60, 1) }} {{ __('hrs') }}
                                    ({{ $goal['percentage_of_total_time'] }}%)
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="p-6 text-center bg-zinc-50 dark:bg-zinc-700/50 border border-dashed border-zinc-200 dark:border-zinc-600 rounded-lg text-zinc-500 text-sm">
                        {{ __('No goal time logs available.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
