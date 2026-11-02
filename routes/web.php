<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->name('home');

Route::middleware('cache.headers:no_store;private')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

    require __DIR__.'/settings.php';
});
