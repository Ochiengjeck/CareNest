<?php

use Illuminate\Support\Facades\Route;

// Medications
Route::middleware(['auth', 'verified', 'can:manage-medications'])->group(function () {
    Route::livewire('medications', 'pages::medications.index')->name('medications.index');
    Route::livewire('medications/create', 'pages::medications.create')->name('medications.create');
    Route::livewire('medications/{medication}/edit', 'pages::medications.edit')->name('medications.edit');
    Route::livewire('medications/{medication}', 'pages::medications.show')->name('medications.show');
});

// Medication Administration
Route::middleware(['auth', 'verified', 'can:administer-medications'])->group(function () {
    Route::livewire('medications/{medication}/administer', 'pages::medications.administer')->name('medications.administer');
});

// Vitals
Route::middleware(['auth', 'verified', 'can:manage-medications'])->group(function () {
    Route::livewire('vitals', 'pages::vitals.index')->name('vitals.index');
    Route::livewire('vitals/record', 'pages::vitals.create')->name('vitals.create');
    Route::livewire('vitals/{vital}', 'pages::vitals.show')->name('vitals.show');
});

// Incidents
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('can:manage-incidents')->group(function () {
        Route::livewire('incidents', 'pages::incidents.index')->name('incidents.index');
        Route::livewire('incidents/{incident}/edit', 'pages::incidents.edit')->name('incidents.edit');
        Route::livewire('incidents/{incident}', 'pages::incidents.show')->name('incidents.show');
    });

    Route::middleware('can:report-incidents')->group(function () {
        Route::livewire('incidents/create', 'pages::incidents.create')->name('incidents.create');
    });
});
