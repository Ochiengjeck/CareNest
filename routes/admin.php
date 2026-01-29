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
    Route::livewire('settings/chatbot', 'pages::admin.settings.chatbot')->name('admin.settings.chatbot');
});
