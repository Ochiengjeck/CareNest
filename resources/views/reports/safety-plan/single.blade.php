<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; line-height: 1.4; }
    .page { padding: 24px 28px; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 12px; }
    .header h1 { font-size: 15px; font-weight: bold; color: #1e40af; }
    .header .doc-title { font-size: 12px; font-weight: bold; color: #111; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }
    .resident-strip { background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 9px; padding: 1px 8px 1px 0; }
    .resident-strip .label { color: #555; }
    .resident-strip .value { font-weight: bold; }
    .section { margin-bottom: 10px; page-break-inside: avoid; }
    .section-title { background: #1e40af; color: #fff; font-size: 8.5px; font-weight: bold; padding: 4px 8px; border-radius: 3px 3px 0 0; text-transform: uppercase; }
    .section-body { border: 1px solid #cbd5e1; border-top: none; padding: 8px 10px; border-radius: 0 0 3px 3px; }
    .list-item { padding: 3px 0; border-bottom: 1px solid #f1f5f9; font-size: 9.5px; }
    .list-item:last-child { border-bottom: none; }
    .list-num { font-size: 8px; color: #1e40af; font-weight: bold; margin-right: 4px; }
    .person-row { display: flex; gap: 12px; padding: 4px 0; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
    .person-row:last-child { border-bottom: none; }
    .field-label { font-size: 7.5px; color: #666; text-transform: uppercase; }
    .alert-box { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 3px; padding: 6px 10px; font-size: 9px; font-weight: bold; color: #92400e; margin-top: 6px; }
    .text-block { font-size: 9.5px; line-height: 1.6; white-space: pre-wrap; }
    .signer-item { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 2px 8px; font-size: 8px; margin: 2px 3px 2px 0; font-weight: bold; }
    .no-val { color: #aaa; font-style: italic; }
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
    .sig-img { max-height: 60px; max-width: 180px; }
    .sig-name { font-weight: bold; font-size: 9px; margin-top: 4px; }
    .sig-meta { font-size: 8px; color: #666; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Safety Plan</div>
    </div>

    <div class="resident-strip">
        <table><tr>
            <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
            <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
            <td><span class="label">DOB: </span><span class="value">{{ $record->resident->date_of_birth->format('m/d/Y') }}</span></td>
            <td><span class="label">Date: </span><span class="value">{{ $record->created_at->format('m/d/Y') }}</span></td>
        </tr></table>
    </div>

    @if($record->diagnosis)
    <div class="section">
        <div class="section-title">Diagnosis</div>
        <div class="section-body"><p class="text-block">{{ $record->diagnosis }}</p></div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Step 1 — Warning Signs</div>
        <div class="section-body">
            @php $signs = array_filter($record->warning_signs ?? []); @endphp
            @if(count($signs) > 0)
                @foreach($signs as $i => $sign)
                    <div class="list-item"><span class="list-num">{{ $i+1 }}.</span>{{ $sign }}</div>
                @endforeach
            @else <span class="no-val">None recorded.</span> @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Step 2 — Internal Coping Strategies</div>
        <div class="section-body">
            @php $strategies = array_filter($record->coping_strategies ?? []); @endphp
            @if(count($strategies) > 0)
                @foreach($strategies as $i => $s)
                    <div class="list-item"><span class="list-num">{{ $i+1 }}.</span>{{ $s }}</div>
                @endforeach
            @else <span class="no-val">None recorded.</span> @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Step 3 — People &amp; Places for Distraction</div>
        <div class="section-body">
            @if(!empty($record->distraction_people))
                <div style="font-size:8px;font-weight:bold;color:#374151;margin-bottom:4px;">People</div>
                @foreach($record->distraction_people as $p)
                    @if($p['name'] || $p['phone'])
                    <div class="person-row">
                        <div><div class="field-label">Name</div>{{ $p['name'] ?? '—' }}</div>
                        <div><div class="field-label">Phone</div>{{ $p['phone'] ?? '—' }}</div>
                        <div><div class="field-label">Relationship</div>{{ $p['relationship'] ?? '—' }}</div>
                    </div>
                    @endif
                @endforeach
            @endif
            @php $places = array_filter($record->distraction_places ?? []); @endphp
            @if(count($places) > 0)
                <div style="font-size:8px;font-weight:bold;color:#374151;margin-top:6px;margin-bottom:4px;">Places</div>
                @foreach($places as $i => $place)
                    <div class="list-item"><span class="list-num">{{ $i+1 }}.</span>{{ $place }}</div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Step 4 — Help &amp; Crisis Resources</div>
        <div class="section-body">
            @if(!empty($record->help_people))
                <div style="font-size:8px;font-weight:bold;color:#374151;margin-bottom:4px;">Personal Contacts</div>
                @foreach($record->help_people as $p)
                    @if($p['name'] || $p['phone'])
                    <div class="person-row">
                        <div><div class="field-label">Name</div>{{ $p['name'] ?? '—' }}</div>
                        <div><div class="field-label">Phone</div>{{ $p['phone'] ?? '—' }}</div>
                        <div><div class="field-label">Relationship</div>{{ $p['relationship'] ?? '—' }}</div>
                    </div>
                    @endif
                @endforeach
            @endif
            @if(!empty($record->crisis_professionals))
                <div style="font-size:8px;font-weight:bold;color:#374151;margin-top:6px;margin-bottom:4px;">Crisis Professionals</div>
                @foreach($record->crisis_professionals as $cp)
                    @if($cp['facility_name'] || $cp['clinician_name'])
                    <div class="person-row">
                        <div><div class="field-label">Facility</div>{{ $cp['facility_name'] ?? '—' }}</div>
                        <div><div class="field-label">Phone</div>{{ $cp['phone'] ?? '—' }}</div>
                        <div><div class="field-label">Clinician</div>{{ $cp['clinician_name'] ?? '—' }}</div>
                    </div>
                    @endif
                @endforeach
            @endif
            <div class="alert-box">National Suicide &amp; Crisis Lifeline: 988 &nbsp;|&nbsp; Emergency Services: 911</div>
        </div>
    </div>

    @if($record->environment_safety)
    <div class="section">
        <div class="section-title">Environment Safety</div>
        <div class="section-body"><p class="text-block">{{ $record->environment_safety }}</p></div>
    </div>
    @endif

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
        <span>{{ $facility }} &mdash; Confidential Safety Plan</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>
</div>
</body>
</html>
