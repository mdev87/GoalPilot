<?php

namespace App\Livewire\Dashboard;

use App\Actions\DashboardActions\GetDashboardData;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class DashboardOverview extends Component
{
    public function render(GetDashboardData $getDashboardData): View
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $getDashboardData->execute($user);

        return view('pages.dashboard-overview', $data);
    }
}
