<?php

namespace App\Livewire\Dashboard;

use App\Actions\DashboardActions\GetDashboardData;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Inspiring;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public int $timeframeWeeks = 8;

    public string $inspiringQuote = '';

    public function mount(): void
    {
        $this->inspiringQuote = Inspiring::quotes()->random();
    }

    public function setTimeframe(int $weeks): void
    {
        $this->timeframeWeeks = in_array($weeks, [4, 8, 12, 52]) ? $weeks : 8;
    }

    public function render(GetDashboardData $getDashboardData): View
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $getDashboardData->execute($user, $this->timeframeWeeks);

        return view('pages.dashboard', $data);
    }
}
