<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

    Route::get('/jualan', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/jualan', [SalesController::class, 'store'])->name('sales.store');
    Route::post('/jualan/pelarasan', [SalesController::class, 'storeAdjustment'])->name('sales.adjust');

    Route::get('/penilaian', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/penilaian/{assessment}', [AssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/penilaian/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
    Route::post('/penilaian/{assessment}/lulus', [AssessmentController::class, 'approve'])->name('assessments.approve');
    Route::post('/penilaian/{assessment}/pulang', [AssessmentController::class, 'returnForCorrection'])->name('assessments.return');
    Route::post('/penilaian/{assessment}/buka', [AssessmentController::class, 'reopen'])->name('assessments.reopen');

    Route::post('/tempoh/buka', [PeriodController::class, 'open'])->name('periods.open');
    Route::post('/tempoh/{period}/kunci', [PeriodController::class, 'lock'])->name('periods.lock');
    Route::post('/tempoh/{period}/buka-semula', [PeriodController::class, 'reopen'])->name('periods.reopen');

    Route::get('/ganjaran', [RewardController::class, 'index'])->name('rewards.index');
    Route::post('/ganjaran/{reward}/sah', [RewardController::class, 'verify'])->name('rewards.verify');
    Route::post('/ganjaran/{reward}/bayar', [RewardController::class, 'markPaid'])->name('rewards.pay');
    Route::post('/ganjaran/{reward}/tahan', [RewardController::class, 'withhold'])->name('rewards.withhold');

    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

    Route::get('/tetapan', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/tetapan', [SettingsController::class, 'update'])->name('settings.update');
});
