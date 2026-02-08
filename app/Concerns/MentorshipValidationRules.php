<?php

namespace App\Concerns;

trait MentorshipValidationRules
{
    /**
     * Validation rules for creating/editing a mentorship topic.
     */
    protected function mentorshipTopicRules(): array
    {
        return [
            'topic_date' => ['required', 'date'],
            'day_of_week' => ['required', 'string', 'max:20'],
            'time_slot' => ['required', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * Validation rules for attachment upload.
     */
    protected function mentorshipAttachmentRules(): array
    {
        return [
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * Validation rules for personal notes.
     */
    protected function mentorshipNoteRules(): array
    {
        return [
            'noteContent' => ['required', 'string', 'max:10000'],
        ];
    }

    /**
     * Validation rules for CSV upload.
     */
    protected function mentorshipCsvUploadRules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    /**
     * Validation rules for lessons library.
     */
    protected function mentorshipLessonRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:10000'],
            'summary' => ['nullable', 'string', 'max:500'],
            'is_published' => ['boolean'],
            'visibility' => ['required', 'in:private,shared'],
        ];
    }

    /**
     * Validation rules for teaching sessions.
     */
    protected function mentorshipSessionRules(): array
    {
        return [
            'topic_id' => ['required', 'exists:mentorship_topics,id'],
            'lesson_id' => ['nullable', 'exists:mentorship_lessons,id'],
            'session_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'participant_count' => ['required_if:status,completed', 'integer', 'min:0', 'max:500'],
            'participant_notes' => ['nullable', 'string', 'max:2000'],
            'session_notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
        ];
    }

    /**
     * Validation rules for completing a session.
     */
    protected function mentorshipSessionCompletionRules(): array
    {
        return [
            'participant_count' => ['required', 'integer', 'min:0', 'max:500'],
            'participant_notes' => ['nullable', 'string', 'max:2000'],
            'session_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
