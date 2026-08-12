<?php

namespace App\Livewire\Dashboard;

use App\Actions\AIActions\GenerateWeeklyAnalysis;
use App\Actions\DashboardActions\GetDashboardData;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Inspiring;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public int $timeframeWeeks = 8;

    public string $inspiringQuote = '';

    /**
     * @var array{summary: string, achievements: array<int, string>, areas_for_improvement: array<int, string>, actionable_recommendations: array<int, string>, execution_score: int}|null
     */
    public ?array $aiAnalysis = null;

    public ?string $aiErrorMessage = null;

    public function mount(): void
    {
        $this->inspiringQuote = Inspiring::quotes()->random();
    }

    public function setTimeframe(int $weeks): void
    {
        $this->timeframeWeeks = in_array($weeks, [4, 8, 12, 52]) ? $weeks : 8;
    }

    public function generateAiAnalysis(GenerateWeeklyAnalysis $generateWeeklyAnalysis): void
    {
        $this->aiErrorMessage = null;

        try {
            /** @var User $user */
            $user = auth()->guard()->user();

            $this->aiAnalysis = $generateWeeklyAnalysis->execute($user);
        } catch (ValidationException $e) {
            $this->aiErrorMessage = implode(' ', $e->validator->errors()->all());
        } catch (\Throwable $e) {
            logger()->error('AI Analysis generation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->aiErrorMessage = __('Unable to generate AI insights at this time. Please try again later.');
        }
    }

    public function render(GetDashboardData $getDashboardData): View
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $getDashboardData->execute($user, $this->timeframeWeeks);

        return view('pages.dashboard', $data);
    }
}
