<?php

namespace App\Concerns;

trait ArtMeetingValidationRules
{
    public function artMeetingRules(): array
    {
        return [
            'meeting_date'             => ['required', 'date'],
            'meeting_type'             => ['required', 'string', 'in:scheduled,emergency,discharge_planning'],
            'attendees'                => ['nullable', 'array'],
            'attendees.*'              => ['nullable', 'string', 'max:255'],
            'discussion_notes'         => ['nullable', 'string'],
            'treatment_plan_revisions' => ['nullable', 'string'],
            'next_meeting_date'        => ['nullable', 'date', 'after:meeting_date'],
        ];
    }
}
