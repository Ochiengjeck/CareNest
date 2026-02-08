<?php

namespace App\Services;

use App\Models\MentorshipLesson;
use App\Models\MentorshipTopic;
use App\Services\AI\AiManager;
use Illuminate\Support\Str;

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
     * Generate a structured lesson for a topic. Returns structured array or null.
     */
    public function generateLesson(MentorshipTopic $topic): ?array
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $prompt = $this->buildLessonPrompt($topic);
            $response = $this->aiManager->executeForUseCaseJson('mentorship_lesson_generation', $prompt);

            if ($response->success && $response->content) {
                return $this->parseAndValidateJson($response->content);
            }

            return null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Generate lesson from title and category (for standalone lessons).
     */
    public function generateLessonFromInput(string $title, string $category, ?string $description = null): ?array
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $prompt = $this->buildPromptFromInput($title, $category, $description);
            $response = $this->aiManager->executeForUseCaseJson('mentorship_lesson_generation', $prompt);

            if ($response->success && $response->content) {
                return $this->parseAndValidateJson($response->content);
            }

            return null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Generate a brief summary for lesson content.
     */
    public function generateSummary(array|string $content): ?string
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $textContent = $this->extractTextFromContent($content);

            $prompt = "Summarize the following educational lesson in 2-3 sentences for a preview card. "
                . "Focus on the main learning objective and key takeaway.\n\n"
                . "Lesson Content:\n{$textContent}";

            $response = $this->aiManager->executeForUseCase('mentorship_lesson_generation', $prompt);

            return $response->success ? $response->content : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parse and validate AI JSON response into structured content.
     */
    protected function parseAndValidateJson(string $raw): ?array
    {
        $data = json_decode($raw, true);

        if (! is_array($data) || ! isset($data['sections']) || ! is_array($data['sections'])) {
            return null;
        }

        // Ensure each section has required fields and unique IDs
        foreach ($data['sections'] as &$section) {
            $section['id'] = $section['id'] ?? (string) Str::uuid();
            $section['title'] = $section['title'] ?? '';
            $section['content'] = $section['content'] ?? '';
            $section['media'] = $this->validateMediaEntries($section['media'] ?? []);
            $section['media_suggestions'] = $section['media_suggestions'] ?? [];

            foreach ($section['subsections'] ?? [] as &$sub) {
                $sub['id'] = $sub['id'] ?? (string) Str::uuid();
                $sub['title'] = $sub['title'] ?? '';
                $sub['content'] = $sub['content'] ?? '';
                $sub['media'] = $this->validateMediaEntries($sub['media'] ?? []);
                $sub['media_suggestions'] = $sub['media_suggestions'] ?? [];
            }
            unset($sub);
        }
        unset($section);

        // Recalculate metadata
        $data['metadata'] = $this->calculateMetadata($data['sections']);

        return $data;
    }

    /**
     * Validate and filter media entries from AI response.
     * Only keeps entries with valid video URLs (YouTube/Vimeo).
     * Uploaded files (images/documents with paths) are also preserved.
     */
    protected function validateMediaEntries(array $media): array
    {
        $validated = [];

        foreach ($media as $entry) {
            if (! is_array($entry) || empty($entry['type'])) {
                continue;
            }

            if ($entry['type'] === 'video' && ! empty($entry['url'])) {
                $url = $entry['url'];
                // Only keep valid YouTube or Vimeo URLs
                if (
                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url) ||
                    preg_match('/vimeo\.com\/(\d+)/', $url)
                ) {
                    $validated[] = [
                        'type' => 'video',
                        'url' => $url,
                        'title' => $entry['title'] ?? 'Video',
                    ];
                }
            } elseif (in_array($entry['type'], ['image', 'document']) && ! empty($entry['path'])) {
                // User-uploaded files — preserve as-is
                $validated[] = $entry;
            }
        }

        return $validated;
    }

    /**
     * Calculate metadata counts from sections.
     */
    public function calculateMetadata(array $sections): array
    {
        $subsectionCount = 0;
        $videoCount = 0;
        $imageCount = 0;
        $documentCount = 0;
        $suggestedVideoCount = 0;
        $suggestedImageCount = 0;

        foreach ($sections as $section) {
            $subsectionCount += count($section['subsections'] ?? []);

            foreach ($section['media'] ?? [] as $m) {
                match ($m['type'] ?? '') {
                    'video' => $videoCount++,
                    'image' => $imageCount++,
                    'document' => $documentCount++,
                    default => null,
                };
            }

            foreach ($section['media_suggestions'] ?? [] as $ms) {
                match ($ms['type'] ?? '') {
                    'video' => $suggestedVideoCount++,
                    'image' => $suggestedImageCount++,
                    default => null,
                };
            }

            foreach ($section['subsections'] ?? [] as $sub) {
                foreach ($sub['media'] ?? [] as $m) {
                    match ($m['type'] ?? '') {
                        'video' => $videoCount++,
                        'image' => $imageCount++,
                        'document' => $documentCount++,
                        default => null,
                    };
                }
                foreach ($sub['media_suggestions'] ?? [] as $ms) {
                    match ($ms['type'] ?? '') {
                        'video' => $suggestedVideoCount++,
                        'image' => $suggestedImageCount++,
                        default => null,
                    };
                }
            }
        }

        return [
            'section_count' => count($sections),
            'subsection_count' => $subsectionCount,
            'video_count' => $videoCount,
            'image_count' => $imageCount,
            'document_count' => $documentCount,
            'suggested_video_count' => $suggestedVideoCount,
            'suggested_image_count' => $suggestedImageCount,
        ];
    }

    /**
     * Extract plain text from structured or string content for summary generation.
     */
    protected function extractTextFromContent(array|string $content): string
    {
        if (is_string($content)) {
            return Str::limit(strip_tags($content), 3000);
        }

        $text = '';
        foreach ($content['sections'] ?? [] as $section) {
            $text .= ($section['title'] ?? '') . "\n";
            $text .= strip_tags($section['content'] ?? '') . "\n\n";
            foreach ($section['subsections'] ?? [] as $sub) {
                $text .= ($sub['title'] ?? '') . "\n";
                $text .= strip_tags($sub['content'] ?? '') . "\n\n";
            }
        }

        return Str::limit(trim($text), 3000);
    }

    /**
     * Build the lesson generation prompt for a topic.
     */
    protected function buildLessonPrompt(MentorshipTopic $topic): string
    {
        $categoryContext = $this->getCategoryContext($topic->category);

        return $this->buildJsonPrompt($topic->title, $topic->category, $topic->description, $categoryContext);
    }

    /**
     * Build prompt from manual input (no topic model).
     */
    protected function buildPromptFromInput(string $title, string $category, ?string $description): string
    {
        $categoryContext = $this->getCategoryContext($category);

        return $this->buildJsonPrompt($title, $category, $description, $categoryContext);
    }

    /**
     * Build the JSON-mode lesson generation prompt.
     */
    protected function buildJsonPrompt(string $title, string $category, ?string $description, string $categoryContext): string
    {
        $descLine = $description ? "Description: {$description}\n" : '';

        return <<<PROMPT
You are an expert clinical educator creating a structured lesson for behavioral health staff at a recovery-focused care facility.

Topic: {$title}
Category: {$category}
{$descLine}
{$categoryContext}

Return a structured lesson as valid JSON with this exact schema:

{
  "sections": [
    {
      "id": "unique-string-id",
      "title": "Section Title",
      "content": "<p>HTML content using <strong>bold</strong>, <em>italic</em>, <ul><li>lists</li></ul>, <h3>subheadings</h3>, <h4>minor headings</h4></p>",
      "media": [
        {"type": "video", "url": "https://www.youtube.com/watch?v=REAL_VIDEO_ID", "title": "Video title describing the content"}
      ],
      "media_suggestions": [
        {"type": "image", "description": "What image or diagram would illustrate this concept"}
      ],
      "subsections": [
        {
          "id": "unique-string-id",
          "title": "Subsection Title",
          "content": "<p>HTML content...</p>",
          "media": [],
          "media_suggestions": []
        }
      ]
    }
  ],
  "metadata": {
    "section_count": 4,
    "subsection_count": 6,
    "video_count": 3,
    "suggested_image_count": 2
  }
}

Create 4-5 main sections:
1. Learning Objectives - 3-5 specific, measurable objectives staff will achieve
2. Key Concepts - Core ideas with clinical terminology, evidence-based context
3. Practical Application - 2-3 real-world scenarios or case examples with techniques, phrases, interventions
4. Discussion Questions - 3-4 thought-provoking questions for group reflection
5. Summary - 3-5 key takeaways

Guidelines:
- Use simple HTML tags in content: <p>, <strong>, <em>, <ul>, <ol>, <li>, <h3>, <h4>, <br>
- Each main section should have 1-3 subsections where appropriate (Key Concepts and Practical Application benefit most from subsections)
- Include 3-5 REAL YouTube video URLs in the media array. These MUST be actual, real, existing YouTube videos relevant to the topic. Use well-known educational channels (TED, TEDx, Khan Academy, Psych Hub, Dr. Todd Grande, Therapy in a Nutshell, etc.). Place videos in the sections they are most relevant to.
- Suggest 1-3 helpful diagrams or images in media_suggestions
- Keep total word count around 800-1200 words distributed across sections
- Use professional language appropriate for healthcare staff
- Generate unique string IDs for each section and subsection (e.g. "sec-1", "sec-2", "sub-1-1")

Return ONLY valid JSON. No markdown, no extra text.
PROMPT;
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
        array $content,
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

    /**
     * Create an empty structured content array.
     */
    public static function emptyContent(): array
    {
        return [
            'sections' => [],
            'metadata' => [
                'section_count' => 0,
                'subsection_count' => 0,
                'video_count' => 0,
                'image_count' => 0,
                'document_count' => 0,
            ],
        ];
    }

    /**
     * Wrap plain text into a structured content array (for legacy conversion).
     */
    public static function wrapTextContent(string $text): array
    {
        $html = Str::markdown($text, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        return [
            'sections' => [[
                'id' => (string) Str::uuid(),
                'title' => 'Lesson Content',
                'content' => $html,
                'media' => [],
                'subsections' => [],
            ]],
            'metadata' => [
                'section_count' => 1,
                'subsection_count' => 0,
                'video_count' => 0,
                'image_count' => 0,
                'document_count' => 0,
            ],
        ];
    }
}
