<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Authorization extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'recipient_person_agency', 'recipient_address',
        'recipient_phone', 'recipient_fax', 'recipient_email', 'agency_name',
        'information_released', 'purpose', 'expiration_type', 'expiration_date',
        'expiration_other', 'employee_signature_id', 'employee_raw_signature_data',
        'resident_raw_signature_data', 'witness', 'signers', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'information_released'       => 'array',
            'expiration_date'            => 'date',
            'signers'                    => 'array',
            'employee_raw_signature_data'=> 'encrypted',
            'resident_raw_signature_data'=> 'encrypted',
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

    public function employeeSignature(): BelongsTo
    {
        return $this->belongsTo(Signature::class, 'employee_signature_id');
    }
}
