<?php

use App\Http\Controllers\Auth\AzureAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\TwoFactor\Challenge as TwoFactorChallenge;
use App\Livewire\TwoFactor\Setup as TwoFactorSetup;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    Route::get('two-factor-challenge', TwoFactorChallenge::class)
        ->name('two-factor.challenge');

    Route::get('auth/azure/redirect', [AzureAuthController::class, 'redirect'])
        ->name('auth.azure.redirect');

    Route::get('auth/azure/callback', [AzureAuthController::class, 'callback'])
        ->name('auth.azure.callback');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::get('settings/two-factor', TwoFactorSetup::class)
        ->name('two-factor.setup');
});
