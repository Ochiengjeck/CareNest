<?php

namespace App\Concerns;

trait ShiftValidationRules
{
    protected function shiftRules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'type' => ['required', 'string', 'in:morning,afternoon,night,custom'],
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled,no_show'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
