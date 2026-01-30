<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:view-reports'])->prefix('reports')->group(function () {
    Route::livewire('/', 'pages::reports.index')->name('reports.index');
    Route::livewire('residents', 'pages::reports.residents')->name('reports.residents');
    Route::livewire('clinical', 'pages::reports.clinical')->name('reports.clinical');
    Route::livewire('staff', 'pages::reports.staff')->name('reports.staff');
    Route::livewire('audit-logs', 'pages::reports.audit-logs')->name('reports.audit-logs');
    Route::livewire('ai-generate', 'pages::reports.ai-generate')->name('reports.ai-generate');
});
