<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentalStatusExam extends Model
{
    protected $fillable = [
        'resident_id', 'exam_date', 'before_appointment', 'after_appointment',
        'signature_id', 'raw_signature_data', 'signers', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date'          => 'date',
            'before_appointment' => 'array',
            'after_appointment'  => 'array',
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

    public static function categories(): array
    {
        return [
            'appearance' => [
                'label'   => 'Appearance',
                'options' => ['Well groomed', 'Casually groomed', 'Tattered', 'Other'],
            ],
            'behavior' => [
                'label'   => 'Behavior',
                'options' => ['Anxiety', 'Depression', 'Crying', 'Racing thoughts', 'Other'],
            ],
            'orientation' => [
                'label'   => 'Orientation',
                'options' => ['Oriented x 3', 'Partly oriented', 'Other'],
            ],
            'affect' => [
                'label'   => 'Affect',
                'options' => ['Normal in range', 'Appropriate to the situation', 'Congruent with mood', 'Blunted or restricted', 'Flat', 'Labile or very variable', 'Other'],
            ],
            'speech_thought' => [
                'label'   => 'Speech and Thought',
                'options' => ['Fluent. Normal rate', 'Normal volume', 'Halting speech', 'Selective mute', 'Word-finding difficulties', 'Other'],
            ],
            'thought_content' => [
                'label'   => 'Thought Content',
                'options' => ['Normal thought content', 'Fixed ideas', 'Delusions', 'Hallucinations', 'Other'],
            ],
            'consciousness' => [
                'label'   => 'Consciousness',
                'options' => ['Alert', 'Hypervigilant', 'Drowsy', 'Lethargic', 'Stuporous', 'Asleep', 'Comatose', 'Confused', 'Fluctuating', 'Other'],
            ],
            'memory' => [
                'label'   => 'Memory',
                'options' => ['Intact for recent memory', 'Intact for remote memory', 'Limited or deficient', 'Other'],
            ],
            'judgment' => [
                'label'   => 'Judgment',
                'options' => ['Good judgment', 'Fair judgment', 'Poor judgment', 'Other'],
            ],
            'mood' => [
                'label'   => 'Mood',
                'options' => ['Normal or euthymic', 'Sad or dysphoric', 'Hopeless', 'Variable mood', 'Irritable', 'Worried or anxious', 'Expansive or elevated mood', 'Other'],
            ],
        ];
    }
}
