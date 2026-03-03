<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BhpProgressNote extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'discharge_date', 'progress_note',
        'treatment_goals_progress', 'sobriety_physical_health', 'cognitive_emotional',
        'therapeutic_support', 'progress_towards_goals', 'barriers',
        'summary_continued_stay', 'bhp_name_credential', 'signers',
        'signature_id', 'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'discharge_date'     => 'date',
            'signers'            => 'array',
            'raw_signature_data' => 'encrypted',
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
