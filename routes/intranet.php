<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::view('users', 'pages.users.index')->name('users.index');

    Route::view('company', 'pages.company.edit')->name('company.edit');
});
