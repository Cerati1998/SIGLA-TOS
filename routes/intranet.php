<?php

use App\Http\Controllers\BulkDispatchController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\CertificationPhotoController;
use App\Http\Controllers\CheckpointController;
use App\Http\Controllers\CheckpointPhotoController;
use App\Http\Controllers\ClientOptionsController;
use App\Http\Controllers\DispatchedKitOptionsController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\KitDispatchController;
use App\Http\Controllers\ProviderOptionsController;
use App\Http\Controllers\SecurityDeviceDiscardPhotoController;
use App\Http\Controllers\SecurityDeviceLotController;
use App\Http\Controllers\WorkerOptionsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::view('users', 'pages.users.index')->name('users.index');

    Route::view('company', 'pages.company.edit')->name('company.edit');

    Route::view('branches', 'pages.branches.index')->name('branches.index');

    Route::view('shipping-lines', 'pages.shipping-lines.index')->name('shipping-lines.index');

    Route::view('providers', 'pages.providers.index')->name('providers.index');

    Route::post('providers/options', ProviderOptionsController::class)->name('providers.options');

    Route::view('clients', 'pages.clients.index')->name('clients.index');

    Route::view('workers', 'pages.workers.index')->name('workers.index');

    Route::view('yard-zones', 'pages.yard-zones.index')->name('yard-zones.index');

    Route::view('security-device-lots', 'pages.security-device-lots.index')->name('security-device-lots.index');

    Route::view('security-device-lots/create', 'pages.security-device-lots.create')->name('security-device-lots.create');

    Route::get('security-device-lots/{securityDeviceLot}', [SecurityDeviceLotController::class, 'show'])->name('security-device-lots.show');

    Route::get('security-device-lots/{securityDeviceLot}/edit', [SecurityDeviceLotController::class, 'edit'])->name('security-device-lots.edit');

    Route::get('security-devices/{securityDevice}/discard-photo', [SecurityDeviceDiscardPhotoController::class, 'show'])->name('security-devices.discard-photo');

    Route::get('security-devices/{securityDevice}/discard-photo/thumbnail', [SecurityDeviceDiscardPhotoController::class, 'thumbnail'])->name('security-devices.discard-photo.thumbnail');

    Route::view('security-device-types', 'pages.security-device-types.index')->name('security-device-types.index');

    Route::view('kit-types', 'pages.kit-types.index')->name('kit-types.index');

    Route::view('kits', 'pages.kits.index')->name('kits.index');

    Route::view('kits/create', 'pages.kits.create')->name('kits.create');

    Route::get('kits/{kit}', [KitController::class, 'show'])->name('kits.show');

    Route::get('kits/{kit}/pdf', [KitController::class, 'pdf'])->name('kits.pdf');

    Route::view('kit-dispatches', 'pages.kit-dispatches.index')->name('kit-dispatches.index');

    Route::get('kit-dispatches/{kitDispatch}/pdf', [KitDispatchController::class, 'pdf'])->name('kit-dispatches.pdf');

    Route::view('bulk-dispatches', 'pages.bulk-dispatches.index')->name('bulk-dispatches.index');

    Route::get('bulk-dispatches/{bulkDispatch}', [BulkDispatchController::class, 'show'])->name('bulk-dispatches.show');

    Route::post('kits/dispatched-options', DispatchedKitOptionsController::class)->name('kits.dispatched-options');

    Route::post('clients/options', ClientOptionsController::class)->name('clients.options');

    Route::post('workers/options', WorkerOptionsController::class)->name('workers.options');

    Route::view('gate-out-requests', 'pages.gate-out-requests.index')->name('gate-out-requests.index');

    Route::get('certifications', [CertificationController::class, 'index'])->name('certifications.index');

    Route::view('certifications/create', 'pages.certifications.create')->name('certifications.create');

    Route::view('certifications/list', 'pages.certifications.list')->name('certifications.list');

    Route::get('inspections/{type}', [InspectionController::class, 'index'])->whereIn('type', ['internal', 'external'])->name('inspections.index');

    Route::get('inspections/{type}/{certification}', [InspectionController::class, 'show'])->whereIn('type', ['internal', 'external'])->name('inspections.show');

    Route::get('certification-photos/{certificationPhoto}', [CertificationPhotoController::class, 'show'])->name('certification-photos.show');

    Route::get('certification-photos/{certificationPhoto}/thumbnail', [CertificationPhotoController::class, 'thumbnail'])->name('certification-photos.thumbnail');

    Route::view('checkpoints/pending', 'pages.checkpoints.pending')->name('checkpoints.pending');

    Route::get('checkpoints/{checkpoint}', [CheckpointController::class, 'show'])->whereNumber('checkpoint')->name('checkpoints.show');

    Route::get('checkpoints/{checkpoint}/container-photo', [CheckpointPhotoController::class, 'container'])->name('checkpoints.container-photo');

    Route::get('checkpoints/{checkpoint}/container-photo/thumbnail', [CheckpointPhotoController::class, 'containerThumbnail'])->name('checkpoints.container-photo.thumbnail');

    Route::get('checkpoint-photos/{checkpointPhoto}', [CheckpointPhotoController::class, 'show'])->name('checkpoint-photos.show');

    Route::get('checkpoint-photos/{checkpointPhoto}/thumbnail', [CheckpointPhotoController::class, 'thumbnail'])->name('checkpoint-photos.thumbnail');

    Route::get('certifications/{certification}', [CertificationController::class, 'show'])->name('certifications.show');

    Route::get('certifications/{certification}/pdf', [CertificationController::class, 'pdf'])->name('certifications.pdf');

    Route::view('certification-sections', 'pages.certification-sections.index')->name('certification-sections.index');

    Route::view('carriers', 'pages.carriers.index')->name('carriers.index');

    Route::view('vehicles', 'pages.vehicles.index')->name('vehicles.index');

    Route::view('vehicle-bodies', 'pages.vehicle-bodies.index')->name('vehicle-bodies.index');
});
