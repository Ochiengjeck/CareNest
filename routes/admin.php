<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-users'])->prefix('admin')->group(function () {
    Route::redirect('/', 'admin/users');

    // User Management
    Route::livewire('users', 'pages::admin.users.index')->name('admin.users.index');
    Route::livewire('users/create', 'pages::admin.users.create')->name('admin.users.create');
    Route::livewire('users/{user}/edit', 'pages::admin.users.edit')->name('admin.users.edit');

    // Role Management (requires manage-roles permission)
    Route::middleware(['can:manage-roles'])->group(function () {
        Route::livewire('roles', 'pages::admin.roles.index')->name('admin.roles.index');
        Route::livewire('roles/{role}/edit', 'pages::admin.roles.edit')->name('admin.roles.edit');
    });
});

// System Settings (requires manage-settings permission)
Route::middleware(['auth', 'verified', 'can:manage-settings'])->prefix('admin')->group(function () {
    Route::redirect('settings', 'admin/settings/general');
    Route::livewire('settings/general', 'pages::admin.settings.general')->name('admin.settings.general');
    Route::livewire('settings/ai', 'pages::admin.settings.ai')->name('admin.settings.ai');

    // Agency Management
    Route::livewire('agencies', 'pages::admin.agencies.index')->name('admin.agencies.index');
    Route::livewire('agencies/create', 'pages::admin.agencies.create')->name('admin.agencies.create');
    Route::livewire('agencies/{agency}/edit', 'pages::admin.agencies.edit')->name('admin.agencies.edit');
});

// Audit Logs (requires view-audit-logs permission)
Route::middleware(['auth', 'verified', 'can:view-audit-logs'])->prefix('admin')->group(function () {
    Route::livewire('logs', 'pages::admin.logs.index')->name('admin.logs.index');
    Route::livewire('logs/{auditLog}', 'pages::admin.logs.show')->name('admin.logs.show');
});

// Website Content Management (requires manage-settings permission)
Route::middleware(['auth', 'verified', 'can:manage-settings'])->prefix('admin/website')->group(function () {
    Route::redirect('/', 'admin/website/settings');
    Route::livewire('settings', 'pages::admin.website.settings')->name('admin.website.settings');
    Route::livewire('testimonials', 'pages::admin.website.testimonials')->name('admin.website.testimonials');
    Route::livewire('team', 'pages::admin.website.team')->name('admin.website.team');
    Route::livewire('faq', 'pages::admin.website.faq')->name('admin.website.faq');
    Route::livewire('gallery', 'pages::admin.website.gallery')->name('admin.website.gallery');
    Route::livewire('services', 'pages::admin.website.services')->name('admin.website.services');
    Route::livewire('contact-submissions', 'pages::admin.website.contact-submissions')->name('admin.website.contact-submissions');
});
