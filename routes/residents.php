<?php

use App\Http\Controllers\DischargeReportExportController;
use Illuminate\Support\Facades\Route;

// Residents
Route::middleware(['auth', 'verified', 'can:view-residents'])->group(function () {
    Route::livewire('residents', 'pages::residents.index')->name('residents.index');

    Route::middleware('can:manage-residents')->group(function () {
        Route::livewire('residents/create', 'pages::residents.create')->name('residents.create');
        Route::livewire('residents/{resident}/edit', 'pages::residents.edit')->name('residents.edit');
        Route::livewire('residents/{resident}/discharge', 'pages::residents.discharge')->name('residents.discharge');

        // Discharge Report Exports
        Route::prefix('residents/discharge/export')->name('residents.discharge.export.')->group(function () {
            Route::get('{discharge}/pdf', [DischargeReportExportController::class, 'dischargeSummaryPdf'])->name('pdf');
            Route::get('{discharge}/word', [DischargeReportExportController::class, 'dischargeSummaryWord'])->name('word');
        });
    });

    Route::livewire('residents/{resident}', 'pages::residents.show')->name('residents.show');
});

// Care Plans
Route::middleware(['auth', 'verified', 'can:view-care-plans'])->group(function () {
    Route::livewire('care-plans', 'pages::care-plans.index')->name('care-plans.index');

    Route::middleware('can:manage-care-plans')->group(function () {
        Route::livewire('residents/{resident}/care-plans/create', 'pages::care-plans.create')->name('care-plans.create');
        Route::livewire('care-plans/{carePlan}/edit', 'pages::care-plans.edit')->name('care-plans.edit');
    });

    Route::livewire('care-plans/{carePlan}', 'pages::care-plans.show')->name('care-plans.show');
});
