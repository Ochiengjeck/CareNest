<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'title',
        'description',
        'document_year',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'expires_at',
        'notes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'file_size' => 'integer',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expires_at')
            ->whereDate('expires_at', '>', now())
            ->whereDate('expires_at', '<=', now()->addDays($days));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', now());
    }

    // Accessors

    public function getFileIconAttribute(): string
    {
        return match ($this->file_type) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document',
            'jpg', 'jpeg', 'png' => 'photo',
            default => 'paper-clip',
        };
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function getCategoryLabelAttribute(): string
    {
        return static::categories()[$this->category]['label'] ?? $this->category;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'pending' => 'amber',
            'expired' => 'red',
            'requires_update' => 'orange',
            default => 'zinc',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Completed',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'requires_update' => 'Requires Update',
            default => $this->status,
        };
    }

    // Static helpers

    public static function categories(): array
    {
        return [
            'personal_information' => [
                'label' => 'Personal Information',
                'icon' => 'user',
                'description' => 'Staff profile and personal details',
                'uploadable' => false,
            ],
            'offer_letter' => [
                'label' => 'Offer Letter',
                'icon' => 'envelope',
                'description' => 'Employment offer documentation',
                'uploadable' => true,
            ],
            'employment_contract' => [
                'label' => 'Employment Contract',
                'icon' => 'document-text',
                'description' => 'Signed employment contract',
                'uploadable' => true,
            ],
            'job_description' => [
                'label' => 'Job Description',
                'icon' => 'briefcase',
                'description' => 'Role duties and responsibilities',
                'uploadable' => true,
            ],
            'tb_risk_assessment' => [
                'label' => 'TB Risk Assessment',
                'icon' => 'heart',
                'description' => 'Healthcare compliance assessment',
                'uploadable' => true,
            ],
            'background_dbs_check' => [
                'label' => 'Background / DBS Check',
                'icon' => 'shield-check',
                'description' => 'Safeguarding clearance documentation',
                'uploadable' => true,
            ],
            'right_to_work' => [
                'label' => 'Right to Work',
                'icon' => 'identification',
                'description' => 'Eligibility to work verification',
                'uploadable' => true,
            ],
            'care_certificate' => [
                'label' => 'Care Certificate',
                'icon' => 'academic-cap',
                'description' => 'Care sector compliance certificate',
                'uploadable' => true,
            ],
            'tax_documents' => [
                'label' => 'Tax Documents',
                'icon' => 'calculator',
                'description' => 'Tax and payroll forms',
                'uploadable' => true,
            ],
            'compliance_forms' => [
                'label' => 'Compliance Forms',
                'icon' => 'clipboard-document-check',
                'description' => 'General regulatory compliance forms',
                'uploadable' => true,
            ],
            'termination_documents' => [
                'label' => 'Termination Documents',
                'icon' => 'arrow-right-end-on-rectangle',
                'description' => 'Exit and offboarding paperwork',
                'uploadable' => true,
            ],
            'other' => [
                'label' => 'Other Documents',
                'icon' => 'paper-clip',
                'description' => 'Miscellaneous staff documents',
                'uploadable' => true,
            ],
        ];
    }
}
