<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffingNote extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'note_date', 'begin_time', 'end_time',
        'participant', 'presenting_issues', 'conducted_within_30_days',
        'treatment_plan_requested', 'step_down_discussed', 'goals_addressed',
        'note_summary', 'barriers', 'not_conducted_reason', 'recommendations',
        'signers', 'signature_id', 'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'note_date'                => 'date',
            'conducted_within_30_days' => 'boolean',
            'treatment_plan_requested' => 'boolean',
            'step_down_discussed'      => 'boolean',
            'signers'                  => 'array',
            'raw_signature_data'       => 'encrypted',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }
}
