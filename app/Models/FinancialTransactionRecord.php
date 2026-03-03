<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransactionRecord extends Model
{
    protected $fillable = [
        'resident_id',
        'diagnosis',
        'entries',
        'signers',
        'signature_id',
        'raw_signature_data',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'entries'            => 'array',
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

    public function runningBalances(): array
    {
        $running  = 0;
        $balances = [];

        foreach ($this->entries ?? [] as $entry) {
            $running  += (float) ($entry['deposit'] ?? 0) - (float) ($entry['money_spent'] ?? 0);
            $balances[] = $running;
        }

        return $balances;
    }
}
