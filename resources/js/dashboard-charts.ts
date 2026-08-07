import Chart from 'chart.js/auto';

interface WeeklyTrendItem {
    id: number;
    week_start_date: string;
    end_date: string;
    planned_minutes: number;
    logged_minutes: number;
    completion_percentage: number;
    is_locked: boolean;
}

interface GoalDistributionItem {
    id: number;
    name: string;
    total_logged_minutes: number;
    percentage_of_total_time: number;
    is_archived: boolean;
}

declare global {
    interface Window {
        Chart: typeof Chart;
        initExecutionChart: (canvas: HTMLCanvasElement, data: WeeklyTrendItem[]) => Chart;
        initYearlyAreaChart: (canvas: HTMLCanvasElement, data: WeeklyTrendItem[]) => Chart;
        initGoalDistributionChart: (canvas: HTMLCanvasElement, data: GoalDistributionItem[]) => Chart;
    }
}

window.Chart = Chart;

window.initExecutionChart = (canvas: HTMLCanvasElement, data: WeeklyTrendItem[]): Chart => {
    const labels = data.map((item) => item.week_start_date);
    const plannedHours = data.map((item) => ((item.planned_minutes ?? 0) / 60).toFixed(1));
    const loggedHours = data.map((item) => ((item.logged_minutes ?? 0) / 60).toFixed(1));

    return new window.Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Logged (hrs)',
                    data: loggedHours as any,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                },
                {
                    label: 'Planned (hrs)',
                    data: plannedHours as any,
                    backgroundColor: '#6366f1',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#9ca3af' },
                },
            },
            scales: {
                x: { ticks: { color: '#9ca3af' }, grid: { display: false } },
                y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.1)' } },
            },
        },
    });
};

window.initYearlyAreaChart = (canvas: HTMLCanvasElement, data: WeeklyTrendItem[]): Chart => {
    const labels = data.map((item) => item.week_start_date);
    const loggedHours = data.map((item) => ((item.logged_minutes ?? 0) / 60).toFixed(1));

    return new window.Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Logged Time (hrs)',
                    data: loggedHours as any,
                    fill: true,
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#9ca3af' },
                },
            },
            scales: {
                x: { ticks: { color: '#9ca3af', maxTicksLimit: 12 }, grid: { display: false } },
                y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.1)' } },
            },
        },
    });
};

window.initGoalDistributionChart = (canvas: HTMLCanvasElement, data: GoalDistributionItem[]): Chart => {
    const labels = data.map((item) => item.name);
    const hours = data.map((item) => ((item.total_logged_minutes ?? 0) / 60).toFixed(1));
    const colors = ['#6366f1', '#10b981', '#06b6d4', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'];

    return new window.Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [
                {
                    data: hours as any,
                    backgroundColor: colors.slice(0, labels.length),
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: '#9ca3af' },
                },
            },
        },
    });
};
