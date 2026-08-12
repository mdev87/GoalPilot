@props(['data'])

@php
    $weeksGrid = $data['weeks_grid'] ?? [];
    $monthsHeader = $data['months_header'] ?? [];
    $totalActiveDays = $data['total_active_days'] ?? 0;
    $totalHours = round(($data['total_logged_minutes'] ?? 0) / 60, 1);
@endphp

<div class="rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-white dark:bg-zinc-800 p-6 shadow-sm space-y-5"
    x-data="{
        tooltipText: '',
        tooltipVisible: false,
        tooltipX: 0,
        tooltipY: 0,
        showTooltip(event, text) {
            this.tooltipText = text;
            this.tooltipVisible = true;
            const rect = event.target.getBoundingClientRect();
            this.tooltipX = rect.left + (rect.width / 2);
            this.tooltipY = rect.top - 8;
        },
        hideTooltip() {
            this.tooltipVisible = false;
        }
    }">

    {{-- Component Header & Summary Metrics --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="lg" class="font-bold flex items-center gap-2">
                <flux:icon icon="fire" class="size-5 text-amber-500" />
                {{ __('Activity Contribution Heatmap') }}
            </flux:heading>
            <flux:subheading>
                {{ __('Visual timeline of your daily goal execution over the past year.') }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-700/60 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $totalActiveDays }}</span> {{ __('Active Days') }}
            </div>
            <div class="px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-700/60 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $totalHours }}</span> {{ __('Hrs Logged') }}
            </div>
        </div>
    </div>

    {{-- Heatmap Grid Container with Horizontal Scroll --}}
    <div class="overflow-x-auto pb-2 scrollbar-thin">
        <div class="min-w-[760px] space-y-2">
            
            {{-- Month Labels Header Row --}}
            <div class="flex text-[11px] font-medium text-zinc-400 dark:text-zinc-400 pl-8">
                @foreach ($weeksGrid as $colIdx => $week)
                    @php
                        $monthMatch = collect($monthsHeader)->firstWhere('col_index', $colIdx);
                    @endphp
                    <div class="w-3.5 shrink-0 text-center">
                        @if ($monthMatch)
                            <span class="truncate block text-zinc-600 dark:text-zinc-400 font-semibold">
                                {{ $monthMatch['name'] }}
                            </span>
                        @endif
                    </div>
                    <div class="w-1 shrink-0"></div>
                @endforeach
            </div>

            {{-- Contribution Grid (7 Days per column) --}}
            <div class="flex gap-1">
                {{-- Day of Week Labels Column --}}
                <div class="flex flex-col justify-between text-[10px] font-medium text-zinc-400 dark:text-zinc-400 pr-2 shrink-0 py-0.5">
                    <span class="h-3.5 leading-none">{{ __('Mon') }}</span>
                    <span class="h-3.5 leading-none"></span>
                    <span class="h-3.5 leading-none">{{ __('Wed') }}</span>
                    <span class="h-3.5 leading-none"></span>
                    <span class="h-3.5 leading-none">{{ __('Fri') }}</span>
                    <span class="h-3.5 leading-none"></span>
                    <span class="h-3.5 leading-none"></span>
                </div>

                {{-- Week Columns --}}
                <div class="flex gap-1">
                    @foreach ($weeksGrid as $week)
                        <div class="flex flex-col gap-1">
                            @foreach ($week['days'] as $day)
                                @php
                                    $mins = $day['logged_minutes'];
                                    $hrs = round($mins / 60, 1);
                                    $tooltip = $day['is_future']
                                        ? __(':date: Upcoming', ['date' => $day['formatted_date']])
                                        : ($mins > 0
                                            ? __(':date: :mins mins (:hrs hrs)', ['date' => $day['formatted_date'], 'mins' => $mins, 'hrs' => $hrs])
                                            : __(':date: No activity logged', ['date' => $day['formatted_date']]));

                                    $bgClass = match ($day['level']) {
                                        1 => 'bg-emerald-200 dark:bg-emerald-950 border border-emerald-300/40 dark:border-emerald-800/50',
                                        2 => 'bg-emerald-400 dark:bg-emerald-800',
                                        3 => 'bg-emerald-600 dark:bg-emerald-600',
                                        4 => 'bg-emerald-700 dark:bg-emerald-400',
                                        default => $day['is_future']
                                            ? 'bg-zinc-100/50 dark:bg-zinc-800/30 border border-dashed border-zinc-200 dark:border-zinc-700/60 opacity-30'
                                            : 'bg-zinc-200/90 dark:bg-zinc-700/80 border border-zinc-300/40 dark:border-zinc-600/40',
                                    };
                                @endphp

                                <div class="size-3.5 rounded-[3px] transition-transform duration-150 hover:scale-125 cursor-pointer {{ $bgClass }}"
                                    @mouseenter="showTooltip($event, '{{ addslashes($tooltip) }}')"
                                    @mouseleave="hideTooltip()">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend Footer --}}
            <div class="flex items-center justify-between pt-2 text-xs text-zinc-500 dark:text-zinc-400">
                <span class="text-[11px]">{{ __('365 days of activity tracking') }}</span>

                <div class="flex items-center gap-1.5 text-[11px]">
                    <span>{{ __('Less') }}</span>
                    <div class="size-3.5 rounded-[3px] bg-zinc-200/90 dark:bg-zinc-700/80 border border-zinc-300/40 dark:border-zinc-600/40"></div>
                    <div class="size-3.5 rounded-[3px] bg-emerald-200 dark:bg-emerald-950"></div>
                    <div class="size-3.5 rounded-[3px] bg-emerald-400 dark:bg-emerald-800"></div>
                    <div class="size-3.5 rounded-[3px] bg-emerald-600 dark:bg-emerald-600"></div>
                    <div class="size-3.5 rounded-[3px] bg-emerald-700 dark:bg-emerald-400"></div>
                    <span>{{ __('More') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Micro Tooltip --}}
    <div x-show="tooltipVisible"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="`left: ${tooltipX}px; top: ${tooltipY}px; transform: translate(-50%, -100%);`"
        class="fixed z-50 px-2.5 py-1 rounded bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 text-xs shadow-lg pointer-events-none whitespace-nowrap font-medium">
        <span x-text="tooltipText"></span>
    </div>
</div>
