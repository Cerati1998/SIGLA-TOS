<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->name('home');

Route::middleware('cache.headers:no_store;private')->group(function () {
    Route::prefix('intranet')->name('intranet.')->group(__DIR__.'/intranet.php');
    Route::prefix('extranet')->name('extranet.')->group(__DIR__.'/extranet.php');

    Route::get('.well-known/passkey-endpoints', function () {
        return response()->json([
            'enroll' => route('intranet.security.edit'),
            'manage' => route('intranet.security.edit'),
        ]);
    })->name('well-known.passkeys');
});
