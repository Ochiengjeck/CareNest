<?php

use App\Http\Controllers\AdlFormExportController;
use App\Http\Controllers\InitialAssessmentExportController;
use App\Http\Controllers\AppointmentLogExportController;
use App\Http\Controllers\AsamChecklistExportController;
use App\Http\Controllers\AuthorizationExportController;
use App\Http\Controllers\BhpProgressNoteExportController;
use App\Http\Controllers\ContactNoteExportController;
use App\Http\Controllers\DischargeReportExportController;
use App\Http\Controllers\FaceSheetExportController;
use App\Http\Controllers\FinancialTransactionExportController;
use App\Http\Controllers\MentalStatusExamExportController;
use App\Http\Controllers\SafetyPlanExportController;
use App\Http\Controllers\ShiftProgressNoteExportController;
use App\Http\Controllers\StaffingNoteExportController;
use App\Http\Controllers\TreatmentRefusalExportController;
use Illuminate\Support\Facades\Route;

// Residents
Route::middleware(['auth', 'verified', 'can:view-residents'])->group(function () {
    Route::livewire('residents', 'pages::residents.index')->name('residents.index');

    Route::middleware('can:manage-residents')->group(function () {
        Route::livewire('residents/create', 'pages::residents.create')->name('residents.create');
        Route::livewire('residents/{resident}/edit', 'pages::residents.edit')->name('residents.edit');
        Route::livewire('residents/{resident}/discharge', 'pages::residents.discharge')->name('residents.discharge');
        Route::livewire('residents/{resident}/progress-notes/create', 'pages::residents.progress-note-create')->name('residents.progress-notes.create');
        Route::livewire('residents/{resident}/adl/create', 'pages::residents.adl-form-create')->name('residents.adl.create');
        Route::livewire('residents/{resident}/financial-transactions/create', 'pages::residents.financial-transaction-create')->name('residents.financial-transactions.create');
        Route::livewire('residents/{resident}/staffing-notes/create', 'pages::residents.staffing-note-create')->name('residents.staffing-notes.create');
        Route::livewire('residents/{resident}/authorizations/create', 'pages::residents.authorization-create')->name('residents.authorizations.create');
        Route::livewire('residents/{resident}/contact-notes/create', 'pages::residents.contact-note-create')->name('residents.contact-notes.create');
        Route::livewire('residents/{resident}/bhp-progress-notes/create', 'pages::residents.bhp-progress-note-create')->name('residents.bhp-progress-notes.create');
        Route::livewire('residents/{resident}/asam-checklists/create', 'pages::residents.asam-checklist-create')->name('residents.asam-checklists.create');
        Route::livewire('residents/{resident}/face-sheets/create', 'pages::residents.face-sheet-create')->name('residents.face-sheets.create');
        Route::livewire('residents/{resident}/safety-plans/create', 'pages::residents.safety-plan-create')->name('residents.safety-plans.create');
        Route::livewire('residents/{resident}/mental-status/create', 'pages::residents.mental-status-create')->name('residents.mental-status.create');
        Route::livewire('residents/{resident}/treatment-refusals/create', 'pages::residents.treatment-refusal-create')->name('residents.treatment-refusals.create');
        Route::livewire('residents/{resident}/appointment-logs/create', 'pages::residents.appointment-log-create')->name('residents.appointment-logs.create');
        Route::livewire('residents/{resident}/initial-assessments/create', 'pages::residents.initial-assessment-create')->name('residents.initial-assessments.create');

        // Discharge Report Exports
        Route::prefix('residents/discharge/export')->name('residents.discharge.export.')->group(function () {
            Route::get('{discharge}/pdf', [DischargeReportExportController::class, 'dischargeSummaryPdf'])->name('pdf');
            Route::get('{discharge}/word', [DischargeReportExportController::class, 'dischargeSummaryWord'])->name('word');
        });
    });

    Route::livewire('residents/{resident}/progress-notes', 'pages::residents.progress-notes')->name('residents.progress-notes');
    Route::livewire('progress-notes/{shiftProgressNote}', 'pages::residents.progress-note-show')->name('progress-notes.show');

    // Shift Progress Note Exports
    Route::get('progress-notes/{shiftProgressNote}/export/pdf', [ShiftProgressNoteExportController::class, 'pdf'])->name('progress-notes.export.pdf');
    Route::get('residents/{resident}/progress-notes/export/pdf', [ShiftProgressNoteExportController::class, 'residentPdf'])->name('residents.progress-notes.export.pdf');

    // ADL Tracking Forms
    Route::livewire('residents/{resident}/adl', 'pages::residents.adl-forms')->name('residents.adl.index');
    Route::livewire('adl/{adlForm}', 'pages::residents.adl-form-show')->name('adl.show');
    Route::get('adl/{adlForm}/export/pdf', [AdlFormExportController::class, 'pdf'])->name('adl.export.pdf');

    // Financial Transaction Records
    Route::livewire('residents/{resident}/financial-transactions', 'pages::residents.financial-transactions')->name('residents.financial-transactions.index');
    Route::livewire('financial-transactions/{financialTransactionRecord}', 'pages::residents.financial-transaction-show')->name('financial-transactions.show');
    Route::get('financial-transactions/{financialTransactionRecord}/export/pdf', [FinancialTransactionExportController::class, 'pdf'])->name('financial-transactions.export.pdf');

    // Staffing Notes
    Route::livewire('residents/{resident}/staffing-notes', 'pages::residents.staffing-notes')->name('residents.staffing-notes.index');
    Route::livewire('staffing-notes/{staffingNote}', 'pages::residents.staffing-note-show')->name('staffing-notes.show');
    Route::get('staffing-notes/{staffingNote}/export/pdf', [StaffingNoteExportController::class, 'pdf'])->name('staffing-notes.export.pdf');

    // Authorizations
    Route::livewire('residents/{resident}/authorizations', 'pages::residents.authorizations')->name('residents.authorizations.index');
    Route::livewire('authorizations/{authorization}', 'pages::residents.authorization-show')->name('authorizations.show');
    Route::get('authorizations/{authorization}/export/pdf', [AuthorizationExportController::class, 'pdf'])->name('authorizations.export.pdf');

    // Contact Notes
    Route::livewire('residents/{resident}/contact-notes', 'pages::residents.contact-notes')->name('residents.contact-notes.index');
    Route::livewire('contact-notes/{contactNote}', 'pages::residents.contact-note-show')->name('contact-notes.show');
    Route::get('contact-notes/{contactNote}/export/pdf', [ContactNoteExportController::class, 'pdf'])->name('contact-notes.export.pdf');

    // BHP Progress Notes
    Route::livewire('residents/{resident}/bhp-progress-notes', 'pages::residents.bhp-progress-notes')->name('residents.bhp-progress-notes.index');
    Route::livewire('bhp-progress-notes/{bhpProgressNote}', 'pages::residents.bhp-progress-note-show')->name('bhp-progress-notes.show');
    Route::get('bhp-progress-notes/{bhpProgressNote}/export/pdf', [BhpProgressNoteExportController::class, 'pdf'])->name('bhp-progress-notes.export.pdf');

    // ASAM Checklists
    Route::livewire('residents/{resident}/asam-checklists', 'pages::residents.asam-checklists')->name('residents.asam-checklists.index');
    Route::livewire('asam-checklists/{asamChecklist}', 'pages::residents.asam-checklist-show')->name('asam-checklists.show');
    Route::get('asam-checklists/{asamChecklist}/export/pdf', [AsamChecklistExportController::class, 'pdf'])->name('asam-checklists.export.pdf');

    // Face Sheets
    Route::livewire('residents/{resident}/face-sheets', 'pages::residents.face-sheets')->name('residents.face-sheets.index');
    Route::livewire('face-sheets/{faceSheet}', 'pages::residents.face-sheet-show')->name('face-sheets.show');
    Route::get('face-sheets/{faceSheet}/export/pdf', [FaceSheetExportController::class, 'pdf'])->name('face-sheets.export.pdf');

    // Safety Plans
    Route::livewire('residents/{resident}/safety-plans', 'pages::residents.safety-plans')->name('residents.safety-plans.index');
    Route::livewire('safety-plans/{safetyPlan}', 'pages::residents.safety-plan-show')->name('safety-plans.show');
    Route::get('safety-plans/{safetyPlan}/export/pdf', [SafetyPlanExportController::class, 'pdf'])->name('safety-plans.export.pdf');

    // Mental Status Exams
    Route::livewire('residents/{resident}/mental-status', 'pages::residents.mental-status')->name('residents.mental-status.index');
    Route::livewire('mental-status/{mentalStatusExam}', 'pages::residents.mental-status-show')->name('mental-status.show');
    Route::get('mental-status/{mentalStatusExam}/export/pdf', [MentalStatusExamExportController::class, 'pdf'])->name('mental-status.export.pdf');

    // Treatment Refusals
    Route::livewire('residents/{resident}/treatment-refusals', 'pages::residents.treatment-refusals')->name('residents.treatment-refusals.index');
    Route::livewire('treatment-refusals/{treatmentRefusal}', 'pages::residents.treatment-refusal-show')->name('treatment-refusals.show');
    Route::get('treatment-refusals/{treatmentRefusal}/export/pdf', [TreatmentRefusalExportController::class, 'pdf'])->name('treatment-refusals.export.pdf');

    // Appointment Logs
    Route::livewire('residents/{resident}/appointment-logs', 'pages::residents.appointment-logs')->name('residents.appointment-logs.index');
    Route::livewire('appointment-logs/{appointmentLog}', 'pages::residents.appointment-log-show')->name('appointment-logs.show');
    Route::get('appointment-logs/{appointmentLog}/export/pdf', [AppointmentLogExportController::class, 'pdf'])->name('appointment-logs.export.pdf');

    // Initial Assessments
    Route::livewire('residents/{resident}/initial-assessments', 'pages::residents.initial-assessments')->name('residents.initial-assessments.index');
    Route::livewire('initial-assessments/{initialAssessment}', 'pages::residents.initial-assessment-show')->name('initial-assessments.show');
    Route::get('initial-assessments/{initialAssessment}/export/pdf', [InitialAssessmentExportController::class, 'pdf'])->name('initial-assessments.export.pdf');

    Route::livewire('residents/{resident}/reports', 'pages::residents.reports')->name('residents.reports');
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
