<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; line-height: 1.4; }

    .page { padding: 24px 28px; }

    /* Header */
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 12px; }
    .header h1 { font-size: 15px; font-weight: bold; color: #1e40af; letter-spacing: 0.5px; }
    .header .subtitle { font-size: 9px; color: #555; margin-top: 2px; }
    .header .doc-title { font-size: 12px; font-weight: bold; color: #111; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }

    /* Resident strip */
    .resident-strip { background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 9px; padding: 1px 8px 1px 0; }
    .resident-strip .label { color: #555; font-weight: normal; }
    .resident-strip .value { font-weight: bold; color: #111; }

    /* Section */
    .section { margin-bottom: 10px; page-break-inside: avoid; }
    .section-title { background: #1e40af; color: #fff; font-size: 8.5px; font-weight: bold; padding: 4px 8px; border-radius: 3px 3px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .section-body { border: 1px solid #cbd5e1; border-top: none; padding: 8px 10px; border-radius: 0 0 3px 3px; }

    /* Pills (selected items) */
    .pills { margin: 0; padding: 0; }
    .pill { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 2px 8px; font-size: 8px; margin: 2px 3px 2px 0; font-weight: bold; }
    .pill-none { color: #999; font-style: italic; font-size: 8.5px; }

    /* Key-value grid */
    .kv-grid { }
    .kv-row { display: flex; margin-bottom: 4px; }
    .kv-label { width: 50%; color: #555; font-size: 9px; }
    .kv-value { width: 50%; font-weight: bold; font-size: 9px; }

    /* Two column layout */
    .two-col table { width: 100%; }
    .two-col td { width: 50%; vertical-align: top; padding-right: 8px; }

    /* Meal prep badge */
    .meal-badge { display: inline-block; background: #f0fdf4; border: 1px solid #86efac; color: #166534; border-radius: 4px; padding: 2px 10px; font-size: 11px; font-weight: bold; }
    .meal-legend { font-size: 8px; color: #666; margin-top: 4px; }

    /* Summary text */
    .summary-text { font-size: 9.5px; line-height: 1.6; color: #111; white-space: pre-wrap; }

    /* Signature */
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
    .sig-img { max-height: 60px; max-width: 180px; }
    .sig-name { font-weight: bold; font-size: 9px; margin-top: 4px; }
    .sig-meta { font-size: 8px; color: #666; }

    /* Footer */
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }

    .no-val { color: #aaa; font-style: italic; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Shift Progress Note</div>
    </div>

    {{-- Resident strip --}}
    <div class="resident-strip">
        <table>
            <tr>
                <td><span class="label">Resident: </span><span class="value">{{ $note->resident->full_name }}</span></td>
                <td><span class="label">DOB: </span><span class="value">{{ $note->resident->date_of_birth->format('m/d/Y') }}</span></td>
                <td><span class="label">Admitted: </span><span class="value">{{ $note->resident->admission_date->format('m/d/Y') }}</span></td>
                <td><span class="label">Room: </span><span class="value">{{ $note->resident->room_number ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Shift Date: </span><span class="value">{{ $note->shift_date->format('m/d/Y') }}</span></td>
                <td><span class="label">Shift: </span><span class="value">{{ $note->shift_type_label }}</span></td>
                <td><span class="label">Time: </span><span class="value">
                    @if($note->shift_start_time && $note->shift_end_time)
                        {{ \Carbon\Carbon::parse($note->shift_start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($note->shift_end_time)->format('g:i A') }}
                    @else —
                    @endif
                </span></td>
                <td><span class="label">Recorded by: </span><span class="value">{{ $note->recorder?->name ?? '—' }}</span></td>
            </tr>
        </table>
    </div>

    @php
        $moodLabels       = ['appropriate'=>'Appropriate','anxious'=>'Anxious','worry'=>'Worry','sad'=>'Sad','depressed'=>'Depressed','irritable'=>'Irritable','angry'=>'Angry','fearful'=>'Fearful','other'=>'Other'];
        $speechLabels     = ['appropriate'=>'Appropriate','selective_mute'=>'Selective Mute','quiet'=>'Quiet','nonverbal'=>'Nonverbal','hyperverbal'=>'Hyperverbal','other'=>'Other'];
        $behaviorLabels   = ['appropriate'=>'Appropriate','verbal_aggression'=>'Verbal Aggression','physical_aggression'=>'Physical Aggression','internal_stimuli'=>'Responding to Internal Stimuli','isolation'=>'Isolation','obsession'=>'Obsession','manipulative'=>'Manipulative','impulsive'=>'Impulsive','poor_boundaries'=>'Poor Boundaries','sexual_maladaptive'=>'Sexual Maladaptive Behaviors','other'=>'Other'];
        $appointmentLabels= ['no'=>'NO','pc'=>'PC','pcp'=>'PCP','psych'=>'Psych','specialist'=>'Specialist Visit','dental'=>'Dental','er'=>'Emergency Room','urgent_care'=>'Urgent Care','other'=>'Other'];
        $mealLabels       = ['breakfast_eaten'=>'Breakfast Eaten','lunch_eaten'=>'Lunch Eaten','dinner_eaten'=>'Dinner Eaten','meal_refused'=>'Meal Refused'];
        $snackLabels      = ['taken'=>'Taken','refused'=>'Refused'];
        $activityLabels   = ['journaling'=>'Journaling','coloring'=>'Coloring','socializing'=>'Socializing','board_games'=>'Board Games','park'=>'Park','arts_crafts'=>'Arts & Crafts','other'=>'Other'];
        $mealPrepLabels   = ['I'=>'Independent','HP'=>'Home Pass','R'=>'Refused','PA'=>'Partial Assist','TA'=>'Total Assist','VP'=>'Verbal Prompt','NP'=>'No Prompt'];
        $yn = fn($v) => match($v) { true => 'Yes', false => 'No', default => '—' };
        $ts = fn($v) => match($v) { 'yes'=>'Yes','no'=>'No','refused'=>'Refused',default=>'—' };
    @endphp

    {{-- Row 1: Appointment + Mood --}}
    <div class="two-col">
        <table>
            <tr>
                <td>
                    <div class="section">
                        <div class="section-title">Appointment</div>
                        <div class="section-body">
                            <div class="pills">
                                @forelse($note->appointment ?? [] as $k)
                                    <span class="pill">{{ $appointmentLabels[$k] ?? $k }}</span>
                                @empty
                                    <span class="pill-none">None</span>
                                @endforelse
                            </div>
                            @if($note->appointment_other)<div style="margin-top:4px;font-size:8.5px;">{{ $note->appointment_other }}</div>@endif
                        </div>
                    </div>
                </td>
                <td>
                    <div class="section">
                        <div class="section-title">Mood</div>
                        <div class="section-body">
                            <div class="pills">
                                @forelse($note->mood ?? [] as $k)
                                    <span class="pill">{{ $moodLabels[$k] ?? $k }}</span>
                                @empty
                                    <span class="pill-none">None</span>
                                @endforelse
                            </div>
                            @if($note->mood_other)<div style="margin-top:4px;font-size:8.5px;">{{ $note->mood_other }}</div>@endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Row 2: Speech + Behaviors --}}
    <div class="two-col">
        <table>
            <tr>
                <td>
                    <div class="section">
                        <div class="section-title">Speech</div>
                        <div class="section-body">
                            <div class="pills">
                                @forelse($note->speech ?? [] as $k)
                                    <span class="pill">{{ $speechLabels[$k] ?? $k }}</span>
                                @empty
                                    <span class="pill-none">None</span>
                                @endforelse
                            </div>
                            @if($note->speech_other)<div style="margin-top:4px;font-size:8.5px;">{{ $note->speech_other }}</div>@endif
                        </div>
                    </div>
                </td>
                <td>
                    <div class="section">
                        <div class="section-title">Behaviors</div>
                        <div class="section-body">
                            <div class="pills">
                                @forelse($note->behaviors ?? [] as $k)
                                    <span class="pill">{{ $behaviorLabels[$k] ?? $k }}</span>
                                @empty
                                    <span class="pill-none">None</span>
                                @endforelse
                            </div>
                            @if($note->behaviors_other)<div style="margin-top:4px;font-size:8.5px;">{{ $note->behaviors_other }}</div>@endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Quick Checks + Therapy/Medication --}}
    <div class="section">
        <div class="section-title">Clinical Checks</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td style="width:25%;font-size:9px;"><span style="color:#555">Resident redirected:</span> <strong>{{ $yn($note->resident_redirected) }}</strong></td>
                    <td style="width:25%;font-size:9px;"><span style="color:#555">Outing in community:</span> <strong>{{ $yn($note->outing_in_community) }}</strong></td>
                    <td style="width:25%;font-size:9px;"><span style="color:#555">AWOL:</span> <strong>{{ $yn($note->awol) }}</strong></td>
                    <td style="width:25%;font-size:9px;"><span style="color:#555">Welfare checks:</span> <strong>{{ $yn($note->welfare_checks) }}</strong></td>
                </tr>
                <tr style="margin-top:4px">
                    <td style="font-size:9px;padding-top:6px;"><span style="color:#555">Therapy session:</span> <strong>{{ $ts($note->therapy_participation) }}</strong></td>
                    <td style="font-size:9px;padding-top:6px;"><span style="color:#555">Medication given:</span> <strong>{{ $ts($note->medication_administered) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Meals --}}
    <div class="section">
        <div class="section-title">Meals</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td style="width:30%;vertical-align:top">
                        <div style="font-size:8.5px;color:#555;margin-bottom:3px">Meal Preparation</div>
                        @if($note->meal_preparation)
                            <span class="meal-badge">{{ $note->meal_preparation }}</span>
                            <div class="meal-legend">{{ $mealPrepLabels[$note->meal_preparation] ?? '' }}</div>
                        @else
                            <span class="no-val">Not recorded</span>
                        @endif
                    </td>
                    <td style="width:40%;vertical-align:top">
                        <div style="font-size:8.5px;color:#555;margin-bottom:3px">Meals</div>
                        <div class="pills">
                            @forelse($note->meals ?? [] as $k)
                                <span class="pill">{{ $mealLabels[$k] ?? $k }}</span>
                            @empty
                                <span class="pill-none">None</span>
                            @endforelse
                        </div>
                    </td>
                    <td style="width:30%;vertical-align:top">
                        <div style="font-size:8.5px;color:#555;margin-bottom:3px">Snacks</div>
                        <div class="pills">
                            @forelse($note->snacks ?? [] as $k)
                                <span class="pill">{{ $snackLabels[$k] ?? $k }}</span>
                            @empty
                                <span class="pill-none">None</span>
                            @endforelse
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ADLs --}}
    <div class="section">
        <div class="section-title">ADLs &amp; Prompts</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td style="width:20%;font-size:9px;"><span style="color:#555">ADLs completed:</span> <strong>{{ $yn($note->adls_completed) }}</strong></td>
                    <td style="width:27%;font-size:9px;"><span style="color:#555">Prompted medications:</span> <strong>{{ $yn($note->prompted_medications) }}</strong></td>
                    <td style="width:20%;font-size:9px;"><span style="color:#555">Prompted ADLs:</span> <strong>{{ $yn($note->prompted_adls) }}</strong></td>
                    <td style="width:20%;font-size:9px;"><span style="color:#555">Water temp adjusted:</span> <strong>{{ $yn($note->water_temperature_adjusted) }}</strong></td>
                    <td style="width:13%;font-size:9px;"><span style="color:#555">Clothing assist:</span> <strong>{{ $yn($note->clothing_assistance) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Activities --}}
    <div class="section">
        <div class="section-title">Activities</div>
        <div class="section-body">
            <div class="pills">
                @forelse($note->activities ?? [] as $k)
                    <span class="pill">{{ $activityLabels[$k] ?? $k }}</span>
                @empty
                    <span class="pill-none">None</span>
                @endforelse
            </div>
            @if($note->activities_other)<div style="margin-top:4px;font-size:8.5px;">{{ $note->activities_other }}</div>@endif
        </div>
    </div>

    {{-- Shift Note Summary --}}
    <div class="section">
        <div class="section-title">Shift Note Summary</div>
        <div class="section-body">
            @if($note->note_summary)
                <p class="summary-text">{{ $note->note_summary }}</p>
            @else
                <span class="no-val">No summary recorded.</span>
            @endif
        </div>
    </div>

    {{-- Signature --}}
    <div class="section">
        <div class="section-title">Signature</div>
        <div class="section-body">
            @php $sigUri = $note->signature?->getDataUri() ?? $note->raw_signature_data; @endphp
            @if($sigUri)
                <table style="width:100%">
                    <tr>
                        <td style="width:220px">
                            <div class="sig-box">
                                <img class="sig-img" src="{{ $sigUri }}" alt="Signature" />
                            </div>
                        </td>
                        <td style="padding-left:16px;vertical-align:bottom">
                            <div class="sig-name">{{ $note->recorder?->name ?? '—' }}</div>
                            <div class="sig-meta">{{ $note->created_at->format('m/d/Y g:i A') }}</div>
                        </td>
                    </tr>
                </table>
            @else
                <span class="no-val">Not signed.</span>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential Clinical Record</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>

</div>
</body>
</html>
