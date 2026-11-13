<?php

use App\Http\Controllers\ProviderOptionsController;
use App\Http\Controllers\SecurityDeviceLotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::view('users', 'pages.users.index')->name('users.index');

    Route::view('company', 'pages.company.edit')->name('company.edit');

    Route::view('branches', 'pages.branches.index')->name('branches.index');

    Route::view('shipping-lines', 'pages.shipping-lines.index')->name('shipping-lines.index');

    Route::view('providers', 'pages.providers.index')->name('providers.index');

    Route::post('providers/options', ProviderOptionsController::class)->name('providers.options');

    Route::view('workers', 'pages.workers.index')->name('workers.index');

    Route::view('security-device-lots', 'pages.security-device-lots.index')->name('security-device-lots.index');

    Route::view('security-device-lots/create', 'pages.security-device-lots.create')->name('security-device-lots.create');

    Route::get('security-device-lots/{securityDeviceLot}', [SecurityDeviceLotController::class, 'show'])->name('security-device-lots.show');

    Route::get('security-device-lots/{securityDeviceLot}/edit', [SecurityDeviceLotController::class, 'edit'])->name('security-device-lots.edit');

    Route::view('security-device-types', 'pages.security-device-types.index')->name('security-device-types.index');

    Route::view('kit-types', 'pages.kit-types.index')->name('kit-types.index');

    Route::view('gate-out-requests', 'pages.gate-out-requests.index')->name('gate-out-requests.index');

    Route::view('certification-sections', 'pages.certification-sections.index')->name('certification-sections.index');

    Route::view('carriers', 'pages.carriers.index')->name('carriers.index');

    Route::view('vehicles', 'pages.vehicles.index')->name('vehicles.index');

    Route::view('vehicle-bodies', 'pages.vehicle-bodies.index')->name('vehicle-bodies.index');
});
