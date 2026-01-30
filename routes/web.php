<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/residents.php';
require __DIR__.'/clinical.php';
require __DIR__.'/staff.php';
require __DIR__.'/therapy.php';
require __DIR__.'/reports.php';
