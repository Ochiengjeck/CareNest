<?php

use App\Http\Controllers\MentorshipAiMentorController;
use Illuminate\Support\Facades\Route;

// Mentorship Module Routes
Route::middleware(['auth', 'verified'])->prefix('mentorship')->name('mentorship.')->group(function () {

    // All authenticated users can view
    Route::livewire('/', 'pages::mentorship.dashboard')->name('dashboard');
    Route::livewire('/topics/week', 'pages::mentorship.topics.week')->name('topics.week');

    // Teaching sessions - all authenticated users
    Route::livewire('/my-sessions', 'pages::mentorship.sessions.index')->name('sessions.index');
    Route::livewire('/sessions/start', 'pages::mentorship.sessions.start')->name('sessions.start');
    Route::livewire('/sessions/start/{topic}', 'pages::mentorship.sessions.start-topic')->name('sessions.start-topic');
    Route::livewire('/sessions/{session}', 'pages::mentorship.sessions.show')->name('sessions.show');
    Route::livewire('/sessions/{session}/edit', 'pages::mentorship.sessions.edit')->name('sessions.edit');

    // AI Mentor chat endpoint
    Route::post('/ai-mentor/send', MentorshipAiMentorController::class)->name('ai-mentor.send');

    // Management routes - requires manage-mentorship permission
    // These must come BEFORE the {topic} wildcard to avoid route conflicts
    Route::middleware(['can:manage-mentorship'])->group(function () {
        Route::livewire('/manage', 'pages::mentorship.topics.index')->name('topics.index');
        Route::livewire('/topics/create', 'pages::mentorship.topics.create')->name('topics.create');
        Route::livewire('/topics/{topic}/edit', 'pages::mentorship.topics.edit')->name('topics.edit');
        Route::livewire('/import/csv', 'pages::mentorship.import.csv')->name('import.csv');

        // AI Settings
        Route::livewire('/settings/ai', 'pages::mentorship.settings.ai')->name('settings.ai');

        // Lessons Library
        Route::livewire('/lessons', 'pages::mentorship.lessons.index')->name('lessons.index');
        Route::livewire('/lessons/create', 'pages::mentorship.lessons.create')->name('lessons.create');
        Route::livewire('/lessons/{lesson}/edit', 'pages::mentorship.lessons.edit')->name('lessons.edit');
        Route::livewire('/lessons/{lesson}', 'pages::mentorship.lessons.show')->name('lessons.show');

        // Reports
        Route::livewire('/reports', 'pages::mentorship.reports.index')->name('reports.index');
    });

    // Wildcard route must come AFTER specific routes
    Route::livewire('/topics/{topic}', 'pages::mentorship.topics.show')->name('topics.show');
});
