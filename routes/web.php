<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Livewire\Dashboard\DashboardOverview;
use App\Livewire\Goals\GoalsManager;
use App\Livewire\TimeEntries\TimeLogger;
use App\Livewire\Weeks\WeekPlanner;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardOverview::class)->name('dashboard');
    Route::get('goals', GoalsManager::class)->name('goals.index');
    Route::get('weeks', WeekPlanner::class)->name('weeks.index');
    Route::get('time-entries', TimeLogger::class)->name('time-entries.index');
});

require __DIR__.'/settings.php';
