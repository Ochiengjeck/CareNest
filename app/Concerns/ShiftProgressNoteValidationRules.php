<?php

namespace App\Concerns;

trait ShiftProgressNoteValidationRules
{
    protected function shiftProgressNoteRules(): array
    {
        return [
            'shift_date'                 => ['required', 'date'],
            'shift_start_time'           => ['nullable', 'date_format:H:i'],
            'shift_end_time'             => ['nullable', 'date_format:H:i', 'after:shift_start_time'],
            'appointment'                => ['nullable', 'array'],
            'appointment.*'              => ['string', 'in:no,pc,pcp,psych,specialist,dental,er,urgent_care,other'],
            'appointment_other'          => ['nullable', 'string', 'max:255'],
            'mood'                       => ['nullable', 'array'],
            'mood.*'                     => ['string', 'in:appropriate,anxious,worry,sad,depressed,irritable,angry,fearful,other'],
            'mood_other'                 => ['nullable', 'string', 'max:255'],
            'speech'                     => ['nullable', 'array'],
            'speech.*'                   => ['string', 'in:appropriate,selective_mute,quiet,nonverbal,hyperverbal,other'],
            'speech_other'               => ['nullable', 'string', 'max:255'],
            'behaviors'                  => ['nullable', 'array'],
            'behaviors.*'                => ['string', 'in:appropriate,verbal_aggression,physical_aggression,internal_stimuli,isolation,obsession,manipulative,impulsive,poor_boundaries,sexual_maladaptive,other'],
            'behaviors_other'            => ['nullable', 'string', 'max:255'],
            'resident_redirected'        => ['nullable', 'boolean'],
            'outing_in_community'        => ['nullable', 'boolean'],
            'therapy_participation'      => ['nullable', 'string', 'in:yes,no,refused'],
            'awol'                       => ['nullable', 'boolean'],
            'welfare_checks'             => ['nullable', 'boolean'],
            'medication_administered'    => ['nullable', 'string', 'in:yes,no,refused'],
            'meal_preparation'           => ['nullable', 'string', 'in:I,HP,R,PA,TA,VP,NP'],
            'meals'                      => ['nullable', 'array'],
            'meals.*'                    => ['string', 'in:breakfast_eaten,lunch_eaten,dinner_eaten,meal_refused'],
            'snacks'                     => ['nullable', 'array'],
            'snacks.*'                   => ['string', 'in:taken,refused'],
            'adls_completed'             => ['nullable', 'boolean'],
            'prompted_medications'       => ['nullable', 'boolean'],
            'prompted_adls'              => ['nullable', 'boolean'],
            'water_temperature_adjusted' => ['nullable', 'boolean'],
            'clothing_assistance'        => ['nullable', 'boolean'],
            'activities'                 => ['nullable', 'array'],
            'activities.*'               => ['string', 'in:journaling,coloring,socializing,board_games,park,arts_crafts,other'],
            'activities_other'           => ['nullable', 'string', 'max:255'],
            'note_summary'               => ['nullable', 'string', 'max:10000'],
            'signature_id'               => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
