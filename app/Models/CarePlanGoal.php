<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarePlanGoal extends Model
{
    protected $fillable = [
        'care_plan_id',
        'problem_description',
        'case_manager_actions',
        'client_actions',
        'progress_status',
        'target_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
        ];
    }

    public function carePlan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class);
    }

    public function getProgressStatusLabelAttribute(): string
    {
        return match ($this->progress_status) {
            'not_started'     => 'Not Started',
            'making_progress' => 'Making Progress',
            'achieved'        => 'Achieved',
            'not_achieved'    => 'Not Achieved',
            default           => ucfirst(str_replace('_', ' ', $this->progress_status)),
        };
    }

    public function getProgressStatusColorAttribute(): string
    {
        return match ($this->progress_status) {
            'achieved'        => 'green',
            'making_progress' => 'blue',
            'not_achieved'    => 'red',
            'not_started'     => 'zinc',
            default           => 'zinc',
        };
    }
}
