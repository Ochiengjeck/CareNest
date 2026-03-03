<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdlTrackingForm extends Model
{
    protected $fillable = [
        'resident_id',
        'form_date',
        'entries',
        'signature_id',
        'raw_signature_data',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'form_date'          => 'date',
            'entries'            => 'array',
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

    public static function adlItems(): array
    {
        return [
            'selecting_clothes'        => 'Selecting Clothes',
            'bathing_showering'        => 'Bathing or Showering',
            'combing_hair'             => 'Combing Hair',
            'applying_lotion'          => 'Applying Lotion',
            'laundry'                  => 'Laundry',
            'dressing'                 => 'Dressing',
            'shampooing_hair'          => 'Shampooing Hair',
            'oral_care_evening'        => 'Oral Care Evening',
            'oral_care_morning'        => 'Oral Care Morning',
            'breakfast'                => 'Breakfast',
            'lunch'                    => 'Lunch',
            'dinner'                   => 'Dinner',
            'am_snack'                 => 'AM Snack',
            'pm_snack'                 => 'PM Snack',
            'am_bowel_movement'        => 'AM Bowel Movement',
            'pm_bowel_movement'        => 'PM Bowel Movement',
            'overnight_bowel_movement' => 'Overnight Bowel Movement',
            'hand_foot_nail_care'      => 'Hand & Foot Nail Care',
            'bed_mobility'             => 'Bed Mobility',
        ];
    }

    public static function levelLabels(): array
    {
        return [
            'no_assistance'       => 'No Assistance',
            'some_assistance'     => 'Some Assistance',
            'complete_assistance' => 'Complete Assistance',
            'not_applicable'      => 'Not Applicable',
            'refused'             => 'Refused',
        ];
    }
}
