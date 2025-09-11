<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminVacationRequestController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified', IsAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/requests', [AdminVacationRequestController::class, 'index'])->name('requests');
    Route::post('/requests/{requestModel}/update', [AdminVacationRequestController::class, 'update'])
        ->name('requests.request.update');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::post('/users/{user}/updateVacation', [AdminUserController::class, 'updateVacation'])->name('users.updateVacation');
    
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    
    Route::post('/settings/updateLimits', [AdminSettingsController::class, 'updateLimits'])
        ->name('settings.updateLimits');

    Route::post('/settings/setHoliday', [AdminSettingsController::class, 'storeHoliday'])
        ->name('settings.setHoliday');

    Route::delete('/settings/deleteHoliday/{id}', [AdminSettingsController::class, 'destroyHoliday'])
        ->name('settings.deleteHoliday');


    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
