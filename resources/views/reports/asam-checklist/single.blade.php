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
    .q-row { margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #f1f5f9; }
    .q-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .q-label { font-size: 8px; font-weight: bold; color: #374151; margin-bottom: 2px; }
    .q-value { font-size: 9px; color: #111; line-height: 1.5; white-space: pre-wrap; }
    .no-val { color: #aaa; font-style: italic; }
    .signer-item { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 2px 8px; font-size: 8px; margin: 2px 3px 2px 0; font-weight: bold; }
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
    .sig-img { max-height: 60px; max-width: 180px; }
    .sig-name { font-weight: bold; font-size: 9px; margin-top: 4px; }
    .sig-meta { font-size: 8px; color: #666; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">ASAM Criteria Checklist</div>
    </div>

    <div class="resident-strip">
        <table><tr>
            <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
            <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
            <td><span class="label">Admitted: </span><span class="value">{{ $record->resident->admission_date->format('m/d/Y') }}</span></td>
            @if($record->discharge_date)
                <td><span class="label">Discharge: </span><span class="value">{{ $record->discharge_date->format('m/d/Y') }}</span></td>
            @endif
        </tr></table>
    </div>

    @if($record->diagnosis)
    <div class="section">
        <div class="section-title">Diagnosis</div>
        <div class="section-body"><p style="font-size:9.5px;white-space:pre-wrap;">{{ $record->diagnosis }}</p></div>
    </div>
    @endif

    @foreach($dimensions as $num => $dim)
        @php $key = 'dimension_' . $num; $data = $record->$key ?? []; @endphp
        <div class="section">
            <div class="section-title">{{ $dim['title'] }}</div>
            <div class="section-body">
                @foreach($dim['questions'] as $qKey => $question)
                    <div class="q-row">
                        <div class="q-label">{{ $question }}</div>
                        <div class="q-value">{{ $data[$qKey] ?? '' ?: '—' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="section">
        <div class="section-title">Assessment Results</div>
        <div class="section-body">
            @if($record->asam_score)
                <div class="q-row">
                    <div class="q-label">ASAM Score</div>
                    <div class="q-value">{{ $record->asam_score }}</div>
                </div>
            @endif
            @if($record->level_of_care)
                <div class="q-row">
                    <div class="q-label">Level of Care</div>
                    <div class="q-value">{{ $record->level_of_care }}</div>
                </div>
            @endif
            @if($record->residential)
                <div class="q-row">
                    <div class="q-label">Residential Level</div>
                    <div class="q-value"><span class="badge">{{ $record->residential }}</span></div>
                </div>
            @endif
            @if($record->comment)
                <div class="q-row">
                    <div class="q-label">Comment</div>
                    <div class="q-value">{{ $record->comment }}</div>
                </div>
            @endif
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
        <span>{{ $facility }} &mdash; Confidential ASAM Assessment</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>
</div>
</body>
</html>
