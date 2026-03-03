<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; line-height: 1.4; }
    .page { padding: 20px 24px; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 8px; margin-bottom: 10px; }
    .header h1 { font-size: 14px; font-weight: bold; color: #1e40af; }
    .header .doc-title { font-size: 12px; font-weight: bold; color: #111; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px; }
    .resident-strip { background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; padding: 6px 10px; margin-bottom: 10px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 8.5px; padding: 1px 8px 1px 0; }
    .resident-strip .label { color: #555; }
    .resident-strip .value { font-weight: bold; }
    .section { margin-bottom: 8px; page-break-inside: avoid; }
    .section-title { background: #1e40af; color: #fff; font-size: 8px; font-weight: bold; padding: 3px 8px; border-radius: 3px 3px 0 0; text-transform: uppercase; }
    .section-body { border: 1px solid #cbd5e1; border-top: none; padding: 6px 8px; border-radius: 0 0 3px 3px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px 12px; }
    .field-label { font-size: 7.5px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; }
    .field-value { font-size: 9px; color: #111; }
    .text-block { font-size: 9px; line-height: 1.5; white-space: pre-wrap; }
    .bool-badge { display: inline-block; padding: 1px 5px; border-radius: 8px; font-size: 7.5px; font-weight: bold; }
    .bool-yes { background: #dcfce7; color: #166534; }
    .bool-no { background: #fee2e2; color: #991b1b; }
    .bool-na { background: #f1f5f9; color: #64748b; }
    .signer-item { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 1px 6px; font-size: 7.5px; margin: 1px 2px 1px 0; font-weight: bold; }
    .no-val { color: #aaa; font-style: italic; }
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px; text-align: center; }
    .sig-img { max-height: 50px; max-width: 160px; }
    .sig-name { font-weight: bold; font-size: 8.5px; margin-top: 3px; }
    .sig-meta { font-size: 7.5px; color: #666; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 12px; padding-top: 5px; font-size: 7px; color: #999; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Face Sheet</div>
    </div>

    <div class="resident-strip">
        <table><tr>
            <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
            <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
            <td><span class="label">DOB: </span><span class="value">{{ $record->resident->date_of_birth->format('m/d/Y') }}</span></td>
            <td><span class="label">Admitted: </span><span class="value">{{ $record->resident->admission_date->format('m/d/Y') }}</span></td>
        </tr></table>
    </div>

    @if($record->diagnosis)
    <div class="section">
        <div class="section-title">Diagnosis</div>
        <div class="section-body"><p class="text-block">{{ $record->diagnosis }}</p></div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Facility Information</div>
        <div class="section-body">
            <div class="grid-2">
                <div><div class="field-label">Address</div><div class="field-value">{{ $record->facility_address ?? '—' }}</div></div>
                <div><div class="field-label">Phone</div><div class="field-value">{{ $record->facility_phone ?? '—' }}</div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Physical Description</div>
        <div class="section-body">
            <div class="grid-3">
                <div><div class="field-label">Place of Birth</div><div class="field-value">{{ $record->place_of_birth ?? '—' }}</div></div>
                <div><div class="field-label">Eye Color</div><div class="field-value">{{ $record->eye_color ?? '—' }}</div></div>
                <div><div class="field-label">Race</div><div class="field-value">{{ $record->race ?? '—' }}</div></div>
                <div><div class="field-label">Height</div><div class="field-value">{{ $record->height ?? '—' }}</div></div>
                <div><div class="field-label">Weight</div><div class="field-value">{{ $record->weight ?? '—' }}</div></div>
                <div><div class="field-label">Hair Color</div><div class="field-value">{{ $record->hair_color ?? '—' }}</div></div>
                <div><div class="field-label">Primary Language</div><div class="field-value">{{ $record->primary_language ?? '—' }}</div></div>
                <div><div class="field-label">Court Ordered</div><div class="field-value">
                    @if($record->court_ordered === true) <span class="bool-badge bool-yes">Yes</span>
                    @elseif($record->court_ordered === false) <span class="bool-badge bool-no">No</span>
                    @else <span class="no-val">—</span> @endif
                </div></div>
            </div>
            @if($record->identifiable_marks)
                <div style="margin-top:4px;"><div class="field-label">Identifiable Marks</div><div class="field-value">{{ $record->identifiable_marks }}</div></div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Emergency Contacts</div>
        <div class="section-body">
            <div class="grid-2">
                <div><div class="field-label">Family Emergency Contact</div><div class="field-value" style="white-space:pre-wrap;">{{ $record->family_emergency_contact ?? '—' }}</div></div>
                <div><div class="field-label">Facility Emergency Contact</div><div class="field-value">{{ $record->facility_emergency_contact ?? '—' }}</div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Allergies</div>
        <div class="section-body">
            <div class="grid-2">
                <div><div class="field-label">Medication Allergies</div><div class="field-value" style="white-space:pre-wrap;">{{ $record->medication_allergies ?? '—' }}</div></div>
                <div><div class="field-label">Other Allergies</div><div class="field-value" style="white-space:pre-wrap;">{{ $record->other_allergies ?? '—' }}</div></div>
            </div>
        </div>
    </div>

    @php
        $providers = [
            ['title'=>'Primary Care Provider','fields'=>['Name'=>$record->pcp_name,'Phone'=>$record->pcp_phone,'Address'=>$record->pcp_address]],
            ['title'=>'Other Specialist ('.($record->specialist_1_type ?: 'Type 1').')','fields'=>['Name'=>$record->specialist_1_name,'Phone'=>$record->specialist_1_phone,'Address'=>$record->specialist_1_address]],
            ['title'=>'Psychiatric Provider','fields'=>['Name'=>$record->psych_name,'Phone'=>$record->psych_phone,'Address'=>$record->psych_address]],
            ['title'=>'Other Specialist ('.($record->specialist_2_type ?: 'Type 2').')','fields'=>['Name'=>$record->specialist_2_name,'Phone'=>$record->specialist_2_phone,'Address'=>$record->specialist_2_address]],
            ['title'=>'Preferred Hospital','fields'=>['Name'=>$record->preferred_hospital,'Phone'=>$record->preferred_hospital_phone,'Address'=>$record->preferred_hospital_address]],
        ];
    @endphp
    @foreach($providers as $prov)
    <div class="section">
        <div class="section-title">{{ $prov['title'] }}</div>
        <div class="section-body">
            <div class="grid-3">
                @foreach($prov['fields'] as $lbl => $val)
                    <div><div class="field-label">{{ $lbl }}</div><div class="field-value">{{ $val ?? '—' }}</div></div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    <div class="section">
        <div class="section-title">Health Plan</div>
        <div class="section-body">
            <div class="grid-2">
                <div><div class="field-label">Health Plan</div><div class="field-value">{{ $record->health_plan ?? '—' }}</div></div>
                <div><div class="field-label">Plan ID</div><div class="field-value">{{ $record->health_plan_id ?? '—' }}</div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Case Manager</div>
        <div class="section-body">
            <div class="grid-3">
                <div><div class="field-label">Name</div><div class="field-value">{{ $record->case_manager_name ?? '—' }}</div></div>
                <div><div class="field-label">Phone</div><div class="field-value">{{ $record->case_manager_phone ?? '—' }}</div></div>
                <div><div class="field-label">Email</div><div class="field-value">{{ $record->case_manager_email ?? '—' }}</div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">SS Rep Payee</div>
        <div class="section-body">
            <div class="grid-3">
                <div><div class="field-label">Name</div><div class="field-value">{{ $record->ss_rep_payee ?? '—' }}</div></div>
                <div><div class="field-label">Phone</div><div class="field-value">{{ $record->ss_rep_phone ?? '—' }}</div></div>
                <div><div class="field-label">Email</div><div class="field-value">{{ $record->ss_rep_email ?? '—' }}</div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Diagnoses &amp; History</div>
        <div class="section-body">
            @foreach(['Mental Health Diagnoses'=>$record->mental_health_diagnoses,'Medical Diagnoses'=>$record->medical_diagnoses,'Past Surgeries'=>$record->past_surgeries] as $lbl => $val)
                @if($val)
                    <div style="margin-bottom:5px;"><div class="field-label">{{ $lbl }}</div><div class="text-block">{{ $val }}</div></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="section">
        <div class="section-title">Signers</div>
        <div class="section-body">
            @if(count($signerNames) > 0)
                @foreach($signerNames as $name) <span class="signer-item">{{ $name }}</span> @endforeach
            @else <span class="no-val">No signers selected.</span> @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Signature</div>
        <div class="section-body">
            @php $sigUri = $record->signature?->getDataUri() ?? $record->raw_signature_data; @endphp
            @if($sigUri)
                <table style="width:100%"><tr>
                    <td style="width:200px"><div class="sig-box"><img class="sig-img" src="{{ $sigUri }}" alt="Signature" /></div></td>
                    <td style="padding-left:12px;vertical-align:bottom">
                        <div class="sig-name">{{ $record->recorder?->name ?? '—' }}</div>
                        <div class="sig-meta">{{ $record->created_at->format('m/d/Y g:i A') }}</div>
                    </td>
                </tr></table>
            @else <span class="no-val">Not signed.</span> @endif
        </div>
    </div>

    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential Face Sheet</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>
</div>
</body>
</html>
