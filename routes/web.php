<?php

use Illuminate\Support\Facades\Route;

// Public website routes (home, about, services, etc.)
require __DIR__.'/public.php';

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Chatbot API
Route::post('chatbot/send', function (\Illuminate\Http\Request $request) {
    $message = trim($request->input('message', ''));
    $history = $request->input('history', []);

    if (empty($message)) {
        return response()->json(['success' => false, 'content' => '']);
    }

    $provider = system_setting('chatbot_provider', 'groq');
    $model = system_setting('chatbot_model', 'llama-3.3-70b-versatile');
    $systemPrompt = system_setting('chatbot_system_prompt', '');

    if (empty($systemPrompt)) {
        $systemPrompt = 'You are a helpful assistant for CareNest, a care home management system. Help users with questions about residents, care plans, medications, and daily operations. Be concise and friendly.';
    }

    try {
        $aiManager = app(\App\Services\AI\AiManager::class);

        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $msg) {
            if (in_array($msg['role'] ?? '', ['user', 'assistant'])) {
                $apiMessages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $apiMessages[] = ['role' => 'user', 'content' => $message];

        $response = $aiManager->provider($provider)->chat($apiMessages, [
            'model' => $model,
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ]);

        return response()->json([
            'success' => $response->success,
            'content' => $response->success
                ? $response->content
                : ($response->error ?? 'Sorry, I encountered an error. Please try again.'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'content' => 'Sorry, something went wrong. Please try again.',
        ]);
    }
})->middleware(['auth'])->name('chatbot.send');

// Public Chatbot API (no auth, rate-limited)
Route::post('public-chatbot/send', function (\Illuminate\Http\Request $request) {
    $message = trim($request->input('message', ''));
    $history = $request->input('history', []);

    if (empty($message)) {
        return response()->json(['success' => false, 'content' => '']);
    }

    $provider = system_setting('chatbot_provider', 'groq');
    $model = system_setting('chatbot_model', 'llama-3.3-70b-versatile');
    $systemPrompt = system_setting('public_chatbot_system_prompt', '');

    if (empty($systemPrompt)) {
        $systemPrompt = 'You are a friendly assistant for CareNest, a professional care home. Help visitors with questions about our services, visiting hours, admission process, and facilities. Be warm, professional, and concise. Do not share any private resident or staff information.';
    }

    try {
        $aiManager = app(\App\Services\AI\AiManager::class);

        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $msg) {
            if (in_array($msg['role'] ?? '', ['user', 'assistant'])) {
                $apiMessages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $apiMessages[] = ['role' => 'user', 'content' => $message];

        $response = $aiManager->provider($provider)->chat($apiMessages, [
            'model' => $model,
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ]);

        return response()->json([
            'success' => $response->success,
            'content' => $response->success
                ? $response->content
                : ($response->error ?? 'Sorry, I encountered an error. Please try again.'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'content' => 'Sorry, something went wrong. Please try again.',
        ]);
    }
})->middleware(['throttle:10,1'])->name('public-chatbot.send');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/residents.php';
require __DIR__.'/clinical.php';
require __DIR__.'/staff.php';
require __DIR__.'/therapy.php';
require __DIR__.'/reports.php';
