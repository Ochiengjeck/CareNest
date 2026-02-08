<?php

namespace App\Services;

use App\Models\MentorshipLesson;
use App\Models\MentorshipTopic;
use App\Services\AI\AiManager;

class MentorshipLessonService
{
    public function __construct(private AiManager $aiManager) {}

    /**
     * Check if AI lesson generation is available.
     */
    public function isAiAvailable(): bool
    {
        try {
            return $this->aiManager->isEnabled()
                && $this->aiManager->isUseCaseEnabled('mentorship_lesson_generation')
                && $this->aiManager->isConfigured($this->aiManager->getUseCaseProvider('mentorship_lesson_generation'));
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Generate a structured lesson for a topic.
     */
    public function generateLesson(MentorshipTopic $topic): ?string
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $prompt = $this->buildLessonPrompt($topic);
            $response = $this->aiManager->executeForUseCase('mentorship_lesson_generation', $prompt);

            return $response->success ? $response->content : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Generate lesson from title and category (for standalone lessons).
     */
    public function generateLessonFromInput(string $title, string $category, ?string $description = null): ?string
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $prompt = $this->buildPromptFromInput($title, $category, $description);
            $response = $this->aiManager->executeForUseCase('mentorship_lesson_generation', $prompt);

            return $response->success ? $response->content : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Generate a brief summary for lesson content.
     */
    public function generateSummary(string $content): ?string
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $prompt = "Summarize the following educational lesson in 2-3 sentences for a preview card. "
                . "Focus on the main learning objective and key takeaway.\n\n"
                . "Lesson Content:\n{$content}";

            $response = $this->aiManager->executeForUseCase('mentorship_lesson_generation', $prompt);

            return $response->success ? $response->content : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Build the lesson generation prompt for a topic.
     */
    protected function buildLessonPrompt(MentorshipTopic $topic): string
    {
        $categoryContext = $this->getCategoryContext($topic->category);

        return "You are an expert clinical educator creating a lesson for behavioral health staff at a recovery-focused care facility.

Topic: {$topic->title}
Category: {$topic->category}
" . ($topic->description ? "Description: {$topic->description}\n" : '') . "
{$categoryContext}

Create a comprehensive educational lesson with the following structure:

## LEARNING OBJECTIVES
List 3-5 specific, measurable learning objectives staff will achieve.

## KEY CONCEPTS
Explain the main ideas with clear definitions and evidence-based context. Include relevant clinical terminology.

## PRACTICAL APPLICATION
Provide 2-3 real-world scenarios or case examples demonstrating how to apply these concepts with residents. Include specific techniques, phrases, or interventions.

## DISCUSSION QUESTIONS
Write 3-4 thought-provoking questions for group reflection or supervision discussions.

## SUMMARY
Summarize 3-5 key takeaways that staff should remember.

Use professional language appropriate for healthcare staff. Keep the total length around 800-1200 words. Format with clear headers using ##.";
    }

    /**
     * Build prompt from manual input (no topic model).
     */
    protected function buildPromptFromInput(string $title, string $category, ?string $description): string
    {
        $categoryContext = $this->getCategoryContext($category);

        return "You are an expert clinical educator creating a lesson for behavioral health staff at a recovery-focused care facility.

Topic: {$title}
Category: {$category}
" . ($description ? "Additional Context: {$description}\n" : '') . "
{$categoryContext}

Create a comprehensive educational lesson with the following structure:

## LEARNING OBJECTIVES
List 3-5 specific, measurable learning objectives staff will achieve.

## KEY CONCEPTS
Explain the main ideas with clear definitions and evidence-based context. Include relevant clinical terminology.

## PRACTICAL APPLICATION
Provide 2-3 real-world scenarios or case examples demonstrating how to apply these concepts with residents. Include specific techniques, phrases, or interventions.

## DISCUSSION QUESTIONS
Write 3-4 thought-provoking questions for group reflection or supervision discussions.

## SUMMARY
Summarize 3-5 key takeaways that staff should remember.

Use professional language appropriate for healthcare staff. Keep the total length around 800-1200 words. Format with clear headers using ##.";
    }

    /**
     * Get context-specific guidance for each category.
     */
    protected function getCategoryContext(string $category): string
    {
        return match ($category) {
            'Mental Health' => "Focus on evidence-based approaches like DBT, CBT, trauma-informed care, and emotional regulation techniques.",
            'Substance Use Disorder' => "Emphasize recovery-oriented concepts, harm reduction, relapse prevention, and motivational interviewing principles.",
            'Employment/Education' => "Cover skill-building for employment readiness, goal setting, time management, and educational planning.",
            'Physical Health' => "Address self-care routines, medication compliance, nutrition, exercise, sleep hygiene, and health literacy.",
            'Financial/Housing' => "Include budgeting basics, housing stability strategies, benefits navigation, and financial literacy.",
            'Psycho-Social/Family' => "Focus on communication skills, boundary setting, healthy relationships, and family dynamics in recovery.",
            'Spirituality' => "Address finding meaning and purpose, mindfulness practices, personal values, and hope in recovery.",
            default => "Use evidence-based practices relevant to behavioral health and recovery.",
        };
    }

    /**
     * Save generated lesson content to the library.
     */
    public function saveToLibrary(
        string $title,
        string $category,
        string $content,
        ?int $sourceTopicId = null,
        ?int $userId = null
    ): MentorshipLesson {
        $summary = $this->generateSummary($content);

        return MentorshipLesson::create([
            'title' => $title,
            'category' => $category,
            'content' => $content,
            'summary' => $summary,
            'source_topic_id' => $sourceTopicId,
            'is_ai_generated' => true,
            'is_published' => false,
            'created_by' => $userId,
        ]);
    }
}
