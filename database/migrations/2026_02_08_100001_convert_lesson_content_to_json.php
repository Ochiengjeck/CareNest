<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // --- Mentorship Lessons: convert content text → JSON ---
        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->longText('content_json')->nullable()->after('content');
        });

        foreach (DB::table('mentorship_lessons')->get() as $lesson) {
            $html = $lesson->content
                ? Str::markdown($lesson->content, ['html_input' => 'escape', 'allow_unsafe_links' => false])
                : '<p></p>';

            $structured = json_encode([
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
            ]);

            DB::table('mentorship_lessons')
                ->where('id', $lesson->id)
                ->update(['content_json' => $structured]);
        }

        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->renameColumn('content_json', 'content');
        });

        // --- Mentorship Sessions: convert lesson_content_snapshot text → JSON ---
        Schema::table('mentorship_sessions', function (Blueprint $table) {
            $table->longText('snapshot_json')->nullable()->after('lesson_content_snapshot');
        });

        foreach (DB::table('mentorship_sessions')->whereNotNull('lesson_content_snapshot')->get() as $session) {
            $html = Str::markdown($session->lesson_content_snapshot, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

            $structured = json_encode([
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
            ]);

            DB::table('mentorship_sessions')
                ->where('id', $session->id)
                ->update(['snapshot_json' => $structured]);
        }

        Schema::table('mentorship_sessions', function (Blueprint $table) {
            $table->dropColumn('lesson_content_snapshot');
        });

        Schema::table('mentorship_sessions', function (Blueprint $table) {
            $table->renameColumn('snapshot_json', 'lesson_content_snapshot');
        });
    }

    public function down(): void
    {
        // Reverse for lessons: JSON → text (lossy — extract first section text)
        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->longText('content_text')->nullable()->after('content');
        });

        foreach (DB::table('mentorship_lessons')->get() as $lesson) {
            $data = json_decode($lesson->content, true);
            $text = '';
            if (isset($data['sections'])) {
                foreach ($data['sections'] as $section) {
                    $text .= "## {$section['title']}\n\n";
                    $text .= strip_tags($section['content'] ?? '') . "\n\n";
                    foreach ($section['subsections'] ?? [] as $sub) {
                        $text .= "### {$sub['title']}\n\n";
                        $text .= strip_tags($sub['content'] ?? '') . "\n\n";
                    }
                }
            }

            DB::table('mentorship_lessons')
                ->where('id', $lesson->id)
                ->update(['content_text' => trim($text)]);
        }

        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->dropColumn('content');
        });
        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->renameColumn('content_text', 'content');
        });

        // Reverse for sessions
        Schema::table('mentorship_sessions', function (Blueprint $table) {
            $table->longText('snapshot_text')->nullable()->after('lesson_content_snapshot');
        });

        foreach (DB::table('mentorship_sessions')->whereNotNull('lesson_content_snapshot')->get() as $session) {
            $data = json_decode($session->lesson_content_snapshot, true);
            $text = '';
            if (isset($data['sections'])) {
                foreach ($data['sections'] as $section) {
                    $text .= strip_tags($section['content'] ?? '') . "\n\n";
                }
            }

            DB::table('mentorship_sessions')
                ->where('id', $session->id)
                ->update(['snapshot_text' => trim($text)]);
        }

        Schema::table('mentorship_sessions', function (Blueprint $table) {
            $table->dropColumn('lesson_content_snapshot');
        });
        Schema::table('mentorship_sessions', function (Blueprint $table) {
            $table->renameColumn('snapshot_text', 'lesson_content_snapshot');
        });
    }
};
