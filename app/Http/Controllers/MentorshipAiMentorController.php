<?php

namespace App\Http\Controllers;

use App\Services\AI\AiManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorshipAiMentorController extends Controller
{
    protected string $defaultSystemPrompt = <<<'PROMPT'
You are an AI Mentor for CareNest's Mentorship Platform. Your role is to help behavioral health staff learn and grow professionally.

Your expertise includes:
- Evidence-based practices (DBT, CBT, Motivational Interviewing)
- Clinical skills and therapeutic techniques
- Professional development in behavioral health
- Recovery-oriented care principles
- Trauma-informed approaches
- Crisis intervention strategies

Guidelines:
- Be encouraging, educational, and supportive
- Provide practical, actionable advice
- Reference evidence-based practices when applicable
- Use professional but accessible language
- Acknowledge the complexity of working in behavioral health
- Encourage reflection and critical thinking

You may help with:
- Understanding clinical concepts and techniques
- Applying skills in real-world scenarios
- Processing challenging situations with residents
- Professional growth and skill development
- Questions about mentorship topics and lessons
PROMPT;

    public function __invoke(Request $request, AiManager $aiManager): JsonResponse
    {
        $message = trim($request->input('message', ''));
        $history = $request->input('history', []);
        $topicContext = $request->input('topic_context');

        if (empty($message)) {
            return response()->json(['success' => false, 'content' => '']);
        }

        // Check if mentorship chat is enabled
        $config = $aiManager->getUseCaseConfig('mentorship_chat');

        if (! $aiManager->isEnabled()) {
            return response()->json([
                'success' => false,
                'content' => 'AI features are currently disabled.',
            ]);
        }

        if (! ($config['enabled'] ?? false)) {
            return response()->json([
                'success' => false,
                'content' => 'AI Mentor is not enabled. Please contact your administrator.',
            ]);
        }

        $provider = $config['provider'] ?? 'groq';
        $model = $config['model'] ?? 'llama-3.3-70b-versatile';
        $systemPrompt = $config['system_prompt'] ?? $this->defaultSystemPrompt;

        // Append topic context if provided
        if (! empty($topicContext)) {
            $systemPrompt .= "\n\n---\nCurrent Topic Context:\n{$topicContext}";
        }

        try {
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
                'temperature' => $config['temperature'] ?? 0.7,
                'max_tokens' => $config['max_tokens'] ?? 1024,
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
    }
}
