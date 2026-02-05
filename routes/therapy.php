<?php

use App\Http\Controllers\TherapyReportExportController;
use Illuminate\Support\Facades\Route;

// Therapy Module Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Therapist's own pages and sessions (requires conduct-therapy)
    Route::middleware(['can:conduct-therapy'])->group(function () {
        Route::livewire('therapy/my-residents', 'pages::therapy.my-residents')->name('therapy.my-residents');
        Route::livewire('therapy/sessions/create', 'pages::therapy.sessions.create')->name('therapy.sessions.create');
        Route::livewire('therapy/sessions/{session}/document', 'pages::therapy.sessions.document')->name('therapy.sessions.document');
    });

    // View sessions (requires view-therapy)
    Route::middleware(['can:view-therapy'])->group(function () {
        Route::livewire('therapy/sessions', 'pages::therapy.sessions.index')->name('therapy.sessions.index');
        Route::livewire('therapy/sessions/{session}', 'pages::therapy.sessions.show')->name('therapy.sessions.show');
    });

    // Admin management (requires manage-therapy)
    Route::middleware(['can:manage-therapy'])->group(function () {
        Route::livewire('therapy/therapists', 'pages::therapy.therapists.index')->name('therapy.therapists.index');
        Route::livewire('therapy/therapists/{user}', 'pages::therapy.therapists.show')->name('therapy.therapists.show');
        Route::livewire('therapy/assignments', 'pages::therapy.assignments.index')->name('therapy.assignments.index');
        Route::livewire('therapy/assignments/create', 'pages::therapy.assignments.create')->name('therapy.assignments.create');
        Route::livewire('therapy/assignments/{assignment}/edit', 'pages::therapy.assignments.edit')->name('therapy.assignments.edit');
        Route::livewire('therapy/sessions/{session}/edit', 'pages::therapy.sessions.edit')->name('therapy.sessions.edit');
    });

    // AI Report Generation (requires view-therapy and view-reports)
    Route::middleware(['can:view-therapy', 'can:view-reports'])->group(function () {
        Route::livewire('therapy/reports/generate', 'pages::therapy.reports.generate')->name('therapy.reports.generate');

        // PDF & Word Export Routes
        Route::prefix('therapy/reports/export')->name('therapy.reports.export.')->group(function () {
            // Individual session
            Route::get('individual/{session}/pdf', [TherapyReportExportController::class, 'individualSessionPdf'])->name('individual.pdf');
            Route::get('individual/{session}/word', [TherapyReportExportController::class, 'individualSessionWord'])->name('individual.word');

            // Progress summary
            Route::get('progress-summary/pdf', [TherapyReportExportController::class, 'progressSummaryPdf'])->name('progress-summary.pdf');
            Route::get('progress-summary/word', [TherapyReportExportController::class, 'progressSummaryWord'])->name('progress-summary.word');

            // Therapist caseload
            Route::get('therapist-caseload/pdf', [TherapyReportExportController::class, 'therapistCaseloadPdf'])->name('therapist-caseload.pdf');
            Route::get('therapist-caseload/word', [TherapyReportExportController::class, 'therapistCaseloadWord'])->name('therapist-caseload.word');

            // Resident history
            Route::get('resident-history/pdf', [TherapyReportExportController::class, 'residentHistoryPdf'])->name('resident-history.pdf');
            Route::get('resident-history/word', [TherapyReportExportController::class, 'residentHistoryWord'])->name('resident-history.word');
        });
    });
});
