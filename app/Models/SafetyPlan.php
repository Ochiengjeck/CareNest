<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyPlan extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'warning_signs', 'coping_strategies',
        'distraction_people', 'distraction_places', 'help_people',
        'crisis_professionals', 'environment_safety', 'signers',
        'signature_id', 'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'warning_signs'       => 'array',
            'coping_strategies'   => 'array',
            'distraction_people'  => 'array',
            'distraction_places'  => 'array',
            'help_people'         => 'array',
            'crisis_professionals'=> 'array',
            'signers'             => 'array',
            'raw_signature_data'  => 'encrypted',
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
