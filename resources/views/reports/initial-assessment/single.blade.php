<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; line-height: 1.4; }
    .page { padding: 24px 28px; }
    .header { text-align: center; border-bottom: 2px solid #0d9488; padding-bottom: 10px; margin-bottom: 12px; }
    .header h1 { font-size: 15px; font-weight: bold; color: #0d9488; }
    .header .doc-title { font-size: 12px; font-weight: bold; color: #111; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }
    .resident-strip { background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 9px; padding: 1px 8px 1px 0; }
    .resident-strip .label { color: #555; }
    .resident-strip .value { font-weight: bold; }
    .section { margin-bottom: 10px; page-break-inside: avoid; }
    .section-title { background: #0d9488; color: #fff; font-size: 8.5px; font-weight: bold; padding: 4px 8px; border-radius: 3px 3px 0 0; text-transform: uppercase; }
    .section-body { border: 1px solid #cbd5e1; border-top: none; padding: 8px 10px; border-radius: 0 0 3px 3px; }
    .two-col { display: flex; gap: 16px; flex-wrap: wrap; }
    .two-col > div { flex: 1; min-width: 140px; }
    .field-row { margin-bottom: 6px; }
    .field-label { font-size: 8px; font-weight: bold; color: #555; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px; }
    .field-value { font-size: 9px; color: #111; white-space: pre-wrap; line-height: 1.5; }
    .field-value.empty { color: #aaa; font-style: italic; }
    .cat-row { display: flex; align-items: flex-start; gap: 12px; padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
    .cat-row:last-child { border-bottom: none; }
    .cat-label { width: 120px; flex-shrink: 0; font-size: 8px; font-weight: bold; color: #374151; padding-top: 2px; }
    .chip { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 10px; padding: 1px 6px; font-size: 8px; margin: 1px 2px 1px 0; }
    .no-sel { color: #aaa; font-style: italic; font-size: 8.5px; }
    .sub-table { width: 100%; border-collapse: collapse; font-size: 8px; }
    .sub-table th { background: #f1f5f9; padding: 3px 5px; text-align: left; font-weight: bold; border: 1px solid #e2e8f0; }
    .sub-table td { padding: 3px 5px; border: 1px solid #e2e8f0; vertical-align: top; }
    .risk-badge { display: inline-block; padding: 1px 7px; border-radius: 8px; font-size: 8px; font-weight: bold; }
    .risk-low { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .risk-moderate { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
    .risk-high { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
    .risk-imminent { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .si-none { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .si-passive { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
    .si-active { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .signer-item { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 2px 8px; font-size: 8px; margin: 2px 3px 2px 0; font-weight: bold; }
    .no-val { color: #aaa; font-style: italic; }
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
    .sig-img { max-height: 60px; max-width: 180px; }
    .sig-name { font-weight: bold; font-size: 9px; margin-top: 4px; }
    .sig-meta { font-size: 8px; color: #666; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }
    .grid-2 { display: flex; flex-wrap: wrap; gap: 0; }
    .grid-2 > div { width: 50%; padding-right: 8px; margin-bottom: 4px; }
    .grid-3 > div { width: 33.33%; padding-right: 8px; margin-bottom: 4px; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Initial Assessment</div>
    </div>

    <div class="resident-strip">
        <table><tr>
            <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
            <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
            <td><span class="label">DOB: </span><span class="value">{{ $record->resident->date_of_birth->format('m/d/Y') }}</span></td>
            <td><span class="label">Assessment Date: </span><span class="value">{{ $record->assessment_date?->format('m/d/Y') ?? '—' }}</span></td>
            @if($record->risk_level)
            <td><span class="label">Risk: </span><span class="risk-badge risk-{{ $record->risk_level }}">{{ ucfirst($record->risk_level) }}</span></td>
            @endif
        </tr></table>
    </div>

    {{-- Section 1: Assessment Information --}}
    <div class="section">
        <div class="section-title">Assessment Information</div>
        <div class="section-body">
            <div class="grid-2 grid-3">
                <div class="field-row"><div class="field-label">Assessment Date</div><div class="field-value">{{ $record->assessment_date?->format('m/d/Y') ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Time</div><div class="field-value">{{ $record->assessment_time ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Assessor</div><div class="field-value">{{ $record->assessor_name ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Referral Source</div><div class="field-value">{{ $record->referral_source ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Primary Language</div><div class="field-value">{{ $record->primary_language ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Court Ordered</div><div class="field-value">{{ $record->court_ordered ? 'Yes' : 'No' }}</div></div>
            </div>
        </div>
    </div>

    {{-- Section 2: Demographics --}}
    <div class="section">
        <div class="section-title">Psychosocial / Demographics</div>
        <div class="section-body">
            <div class="grid-2 grid-3">
                <div class="field-row"><div class="field-label">Marital Status</div><div class="field-value">{{ $record->marital_status ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Employment Status</div><div class="field-value">{{ $record->employment_status ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Education Level</div><div class="field-value">{{ $record->education_level ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Living Situation</div><div class="field-value">{{ $record->living_situation ?? '—' }}</div></div>
                <div class="field-row"><div class="field-label">Veteran Status</div><div class="field-value">{{ $record->veteran_status ? 'U.S. Veteran' : 'No' }}</div></div>
            </div>
        </div>
    </div>

    {{-- Section 3: Presenting Problem --}}
    @if($record->chief_complaint || $record->presenting_problem || $record->duration_of_problem || $record->previous_treatments || $record->goals_for_treatment)
    <div class="section">
        <div class="section-title">Presenting Problem</div>
        <div class="section-body">
            @if($record->chief_complaint)
            <div class="field-row"><div class="field-label">Chief Complaint / Reason for Admission</div><div class="field-value">{{ $record->chief_complaint }}</div></div>
            @endif
            @if($record->presenting_problem)
            <div class="field-row"><div class="field-label">Description of Presenting Problem</div><div class="field-value">{{ $record->presenting_problem }}</div></div>
            @endif
            @if($record->duration_of_problem)
            <div class="field-row"><div class="field-label">Duration of Problem</div><div class="field-value">{{ $record->duration_of_problem }}</div></div>
            @endif
            @if($record->previous_treatments)
            <div class="field-row"><div class="field-label">Previous Treatment Attempts</div><div class="field-value">{{ $record->previous_treatments }}</div></div>
            @endif
            @if($record->goals_for_treatment)
            <div class="field-row"><div class="field-label">Goals for Treatment</div><div class="field-value">{{ $record->goals_for_treatment }}</div></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Section 4: Mental Status --}}
    <div class="section">
        <div class="section-title">Mental Status</div>
        <div class="section-body">
            @foreach($categories as $key => $cat)
                @php $entry = $record->mental_status[$key] ?? []; $selected = $entry['selected'] ?? []; $other = $entry['other'] ?? ''; @endphp
                <div class="cat-row">
                    <div class="cat-label">{{ $cat['label'] }}</div>
                    <div>
                        @if(count($selected) > 0)
                            @foreach($selected as $opt)<span class="chip">{{ $opt }}</span>@endforeach
                            @if($other)<span style="font-size:8.5px;color:#555;">({{ $other }})</span>@endif
                        @else
                            <span class="no-sel">Not assessed</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Section 5: Substance Use History --}}
    @php
        $substanceRows = array_filter($record->substance_use ?? [], fn($r) =>
            ($r['primary'] ?? false) || ($r['current_use'] ?? false)
            || !empty($r['age_first_use']) || !empty($r['frequency']) || !empty($r['route']) || !empty($r['last_use_date'])
        );
    @endphp
    @if(count($substanceRows) > 0)
    <div class="section">
        <div class="section-title">Substance Use History</div>
        <div class="section-body">
            <table class="sub-table">
                <tr>
                    <th>Substance</th>
                    <th>Primary</th>
                    <th>Age First Use</th>
                    <th>Current Use</th>
                    <th>Last Use Date</th>
                    <th>Frequency</th>
                    <th>Route</th>
                    <th>Days Abstinent</th>
                </tr>
                @foreach($substanceRows as $row)
                <tr>
                    <td>{{ $row['substance'] }}</td>
                    <td style="text-align:center">{{ ($row['primary'] ?? false) ? 'Yes' : '—' }}</td>
                    <td>{{ $row['age_first_use'] ?: '—' }}</td>
                    <td style="text-align:center">{{ ($row['current_use'] ?? false) ? 'Yes' : '—' }}</td>
                    <td>{{ $row['last_use_date'] ?: '—' }}</td>
                    <td>{{ $row['frequency'] ?: '—' }}</td>
                    <td>{{ $row['route'] ?: '—' }}</td>
                    <td>{{ $row['days_abstinent'] ?: '—' }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Section 6: Medical History --}}
    @if($record->current_medications || $record->medical_conditions || $record->medication_allergies || $record->other_allergies || $record->hospitalizations)
    <div class="section">
        <div class="section-title">Medical History</div>
        <div class="section-body">
            @if($record->current_medications)
            <div class="field-row"><div class="field-label">Current Medications</div><div class="field-value">{{ $record->current_medications }}</div></div>
            @endif
            @if($record->medical_conditions)
            <div class="field-row"><div class="field-label">Medical Conditions</div><div class="field-value">{{ $record->medical_conditions }}</div></div>
            @endif
            @if($record->medication_allergies)
            <div class="field-row"><div class="field-label">Medication Allergies</div><div class="field-value">{{ $record->medication_allergies }}</div></div>
            @endif
            @if($record->other_allergies)
            <div class="field-row"><div class="field-label">Other Allergies</div><div class="field-value">{{ $record->other_allergies }}</div></div>
            @endif
            @if($record->hospitalizations)
            <div class="field-row"><div class="field-label">Hospitalizations / Surgeries</div><div class="field-value">{{ $record->hospitalizations }}</div></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Section 7: Psychiatric History --}}
    @if($record->psychiatric_diagnoses || $record->psychiatric_hospitalizations || $record->psychiatric_medications || $record->psych_provider_name)
    <div class="section">
        <div class="section-title">Psychiatric History</div>
        <div class="section-body">
            @if($record->psychiatric_diagnoses)
            <div class="field-row"><div class="field-label">Psychiatric Diagnoses</div><div class="field-value">{{ $record->psychiatric_diagnoses }}</div></div>
            @endif
            @if($record->psychiatric_hospitalizations)
            <div class="field-row"><div class="field-label">Psychiatric Hospitalizations</div><div class="field-value">{{ $record->psychiatric_hospitalizations }}</div></div>
            @endif
            @if($record->psychiatric_medications)
            <div class="field-row"><div class="field-label">Psychiatric Medications</div><div class="field-value">{{ $record->psychiatric_medications }}</div></div>
            @endif
            @if($record->psych_provider_name || $record->psych_provider_phone)
            <div class="field-row"><div class="field-label">Psychiatric Provider</div><div class="field-value">{{ $record->psych_provider_name ?? '' }}{{ $record->psych_provider_phone ? ' · ' . $record->psych_provider_phone : '' }}</div></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Section 8: Psychosocial / Legal History --}}
    @if($record->legal_status || $record->legal_history || $record->employment_history || $record->family_history || $record->trauma_history || $record->social_support || $record->cultural_considerations)
    <div class="section">
        <div class="section-title">Psychosocial / Legal History</div>
        <div class="section-body">
            @if($record->legal_status)
            <div class="field-row"><div class="field-label">Current Legal Status</div><div class="field-value">{{ $record->legal_status }}</div></div>
            @endif
            @if($record->legal_history)
            <div class="field-row"><div class="field-label">Legal History</div><div class="field-value">{{ $record->legal_history }}</div></div>
            @endif
            @if($record->employment_history)
            <div class="field-row"><div class="field-label">Employment History</div><div class="field-value">{{ $record->employment_history }}</div></div>
            @endif
            @if($record->family_history)
            <div class="field-row"><div class="field-label">Family History</div><div class="field-value">{{ $record->family_history }}</div></div>
            @endif
            @if($record->trauma_history)
            <div class="field-row"><div class="field-label">Trauma / Abuse History</div><div class="field-value">{{ $record->trauma_history }}</div></div>
            @endif
            @if($record->social_support)
            <div class="field-row"><div class="field-label">Social Support System</div><div class="field-value">{{ $record->social_support }}</div></div>
            @endif
            @if($record->cultural_considerations)
            <div class="field-row"><div class="field-label">Cultural / Spiritual Considerations</div><div class="field-value">{{ $record->cultural_considerations }}</div></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Section 9: Risk Assessment --}}
    <div class="section">
        <div class="section-title">Risk Assessment</div>
        <div class="section-body">
            <div class="grid-2">
                <div class="field-row">
                    <div class="field-label">Suicidal Ideation</div>
                    @if($record->suicidal_ideation)
                        <span class="risk-badge si-{{ $record->suicidal_ideation }}">{{ ucfirst($record->suicidal_ideation) }}</span>
                    @else
                        <div class="field-value empty">Not recorded</div>
                    @endif
                </div>
                <div class="field-row">
                    <div class="field-label">Suicide Plan</div>
                    <div class="field-value">{{ $record->suicide_plan ? 'Yes' : 'No' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Homicidal Ideation</div>
                    @if($record->homicidal_ideation)
                        <span class="risk-badge si-{{ $record->homicidal_ideation }}">{{ ucfirst($record->homicidal_ideation) }}</span>
                    @else
                        <div class="field-value empty">Not recorded</div>
                    @endif
                </div>
                <div class="field-row">
                    <div class="field-label">Overall Risk Level</div>
                    @if($record->risk_level)
                        <span class="risk-badge risk-{{ $record->risk_level }}">{{ ucfirst($record->risk_level) }}</span>
                    @else
                        <div class="field-value empty">Not recorded</div>
                    @endif
                </div>
            </div>
            @if($record->suicide_history)
            <div class="field-row" style="margin-top:6px"><div class="field-label">Suicide / Self-Harm History</div><div class="field-value">{{ $record->suicide_history }}</div></div>
            @endif
            @if($record->self_harm_history)
            <div class="field-row"><div class="field-label">Self-Harm History</div><div class="field-value">{{ $record->self_harm_history }}</div></div>
            @endif
        </div>
    </div>

    {{-- Section 10: Diagnostic Summary --}}
    @if($record->clinical_summary || $record->primary_diagnosis || $record->secondary_diagnosis || $record->asam_level || $record->level_of_care || $record->treatment_goals || $record->recommendations)
    <div class="section">
        <div class="section-title">Diagnostic Summary & Recommendations</div>
        <div class="section-body">
            @if($record->primary_diagnosis || $record->secondary_diagnosis || $record->asam_level || $record->level_of_care)
            <div class="grid-2" style="margin-bottom:6px">
                @if($record->primary_diagnosis)
                <div class="field-row"><div class="field-label">Primary Diagnosis</div><div class="field-value">{{ $record->primary_diagnosis }}</div></div>
                @endif
                @if($record->secondary_diagnosis)
                <div class="field-row"><div class="field-label">Secondary Diagnosis</div><div class="field-value">{{ $record->secondary_diagnosis }}</div></div>
                @endif
                @if($record->asam_level)
                <div class="field-row"><div class="field-label">ASAM Level of Care</div><div class="field-value">{{ $record->asam_level }}</div></div>
                @endif
                @if($record->level_of_care)
                <div class="field-row"><div class="field-label">Level of Care Recommendation</div><div class="field-value">{{ $record->level_of_care }}</div></div>
                @endif
            </div>
            @endif
            @if($record->clinical_summary)
            <div class="field-row"><div class="field-label">Clinical Summary</div><div class="field-value">{{ $record->clinical_summary }}</div></div>
            @endif
            @if($record->treatment_goals)
            <div class="field-row"><div class="field-label">Treatment Goals</div><div class="field-value">{{ $record->treatment_goals }}</div></div>
            @endif
            @if($record->recommendations)
            <div class="field-row"><div class="field-label">Recommendations & Referrals</div><div class="field-value">{{ $record->recommendations }}</div></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Signers --}}
    <div class="section">
        <div class="section-title">Signers</div>
        <div class="section-body">
            @if(count($signerNames) > 0)
                @foreach($signerNames as $name) <span class="signer-item">{{ $name }}</span> @endforeach
            @else <span class="no-val">No signers selected.</span> @endif
        </div>
    </div>

    {{-- Employee Signature --}}
    <div class="section">
        <div class="section-title">Employee Signature</div>
        <div class="section-body">
            @php $sigUri = $record->signature?->getDataUri() ?? $record->raw_signature_data; @endphp
            @if($sigUri)
                <table style="width:100%"><tr>
                    <td style="width:220px"><div class="sig-box"><img class="sig-img" src="{{ $sigUri }}" alt="Signature" /></div></td>
                    <td style="padding-left:16px;vertical-align:bottom">
                        <div class="sig-name">{{ $record->recorder?->name ?? '—' }}</div>
                        <div class="sig-meta">{{ $record->created_at->format('m/d/Y g:i A') }}</div>
                    </td>
                </tr></table>
            @else <span class="no-val">Not signed.</span> @endif
        </div>
    </div>

    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential Initial Assessment</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>
</div>
</body>
</html>
