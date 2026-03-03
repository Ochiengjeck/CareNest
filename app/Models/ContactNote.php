<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactNote extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'contact_date', 'contact_time',
        'person_contacted', 'contact_name', 'mode_of_contact', 'mode_other',
        'contact_summary', 'emergency_issue', 'signers', 'signature_id',
        'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'contact_date'     => 'date',
            'person_contacted' => 'array',
            'mode_of_contact'  => 'array',
            'emergency_issue'  => 'boolean',
            'signers'          => 'array',
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
