<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsamChecklist extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'discharge_date',
        'dimension_1', 'dimension_2', 'dimension_3',
        'dimension_4', 'dimension_5', 'dimension_6',
        'asam_score', 'level_of_care', 'residential', 'comment',
        'signers', 'signature_id', 'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'discharge_date'     => 'date',
            'dimension_1'        => 'array',
            'dimension_2'        => 'array',
            'dimension_3'        => 'array',
            'dimension_4'        => 'array',
            'dimension_5'        => 'array',
            'dimension_6'        => 'array',
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

    public static function dimensions(): array
    {
        return [
            1 => [
                'title'     => 'Dimension 1: Acute Intoxication and/or Withdrawal Potential',
                'questions' => [
                    'q1' => 'Is the resident currently intoxicated or experiencing withdrawal symptoms?',
                    'q2' => 'What is the history of substance use and withdrawal?',
                    'q3' => 'What is the risk of severe withdrawal?',
                    'q4' => 'Does the resident need medical monitoring for withdrawal?',
                ],
            ],
            2 => [
                'title'     => 'Dimension 2: Biomedical Conditions and Complications',
                'questions' => [
                    'q1' => 'Does the resident have chronic or acute medical conditions?',
                    'q2' => 'Is the resident currently receiving medical care?',
                    'q3' => 'Are there medical issues impacting recovery?',
                    'q4' => 'Is coordination with PCP/specialists needed?',
                ],
            ],
            3 => [
                'title'     => 'Dimension 3: Emotional, Behavioral, or Cognitive Conditions',
                'questions' => [
                    'q1' => 'Are there mental health diagnoses present?',
                    'q2' => 'Is there risk of harm to self or others?',
                    'q3' => 'Are there cognitive impairments affecting treatment?',
                    'q4' => 'Is a psychiatric evaluation needed?',
                ],
            ],
            4 => [
                'title'     => 'Dimension 4: Readiness to Change',
                'questions' => [
                    'q1' => "What is the resident's stage of change?",
                    'q2' => 'What is their motivation for treatment?',
                    'q3' => 'Is there ambivalence or resistance to treatment?',
                    'q4' => 'What engagement strategies are needed?',
                ],
            ],
            5 => [
                'title'     => 'Dimension 5: Relapse, Continued Use, or Continued Problem Potential',
                'questions' => [
                    'q1' => 'What is the relapse history?',
                    'q2' => 'What are the identified triggers and risks?',
                    'q3' => 'What coping skills does the resident have?',
                    'q4' => 'Does the resident need structured support to prevent relapse?',
                ],
            ],
            6 => [
                'title'     => 'Dimension 6: Recovery/Living Environment',
                'questions' => [
                    'q1' => 'Is housing stable and recovery-supportive?',
                    'q2' => 'Is the resident exposed to substance use in their environment?',
                    'q3' => 'What is the strength of the support system?',
                    'q4' => 'Are case management needs identified?',
                ],
            ],
        ];
    }
}
