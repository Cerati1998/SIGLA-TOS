<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::view('users', 'pages.users.index')->name('users.index');

    Route::view('company', 'pages.company.edit')->name('company.edit');

    Route::view('branches', 'pages.branches.index')->name('branches.index');

    Route::view('shipping-lines', 'pages.shipping-lines.index')->name('shipping-lines.index');

    Route::view('providers', 'pages.providers.index')->name('providers.index');

    Route::view('security-device-types', 'pages.security-device-types.index')->name('security-device-types.index');
});
