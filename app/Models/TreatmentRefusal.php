<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentRefusal extends Model
{
    protected $fillable = [
        'resident_id', 'refusal_date', 'illness_description',
        'signature_id', 'raw_signature_data', 'signers', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'refusal_date'       => 'date',
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
