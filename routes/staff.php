<?php

use Illuminate\Support\Facades\Route;

// Staff Directory
Route::middleware(['auth', 'verified', 'can:view-staff'])->group(function () {
    Route::livewire('staff', 'pages::staff.index')->name('staff.index');
    Route::livewire('staff/{user}', 'pages::staff.show')->name('staff.show');

    Route::middleware('can:manage-staff')->group(function () {
        Route::livewire('staff/{user}/edit', 'pages::staff.edit')->name('staff.edit');
    });
});

// Shift Schedule
Route::middleware(['auth', 'verified', 'can:manage-staff'])->group(function () {
    Route::livewire('shifts', 'pages::shifts.index')->name('shifts.index');
    Route::livewire('shifts/create', 'pages::shifts.create')->name('shifts.create');
    Route::livewire('shifts/{shift}/edit', 'pages::shifts.edit')->name('shifts.edit');
    Route::livewire('shifts/{shift}', 'pages::shifts.show')->name('shifts.show');
});
