<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ThemeController;

Route::middleware('auth')->group(function () {
    Route::post('/profile/set-color', [ThemeController::class, 'setColor'])
        ->name('profile.set_color');

    Route::post('/settings/save-theme', [ThemeController::class, 'saveTheme'])
        ->name('settings.save_theme');
});