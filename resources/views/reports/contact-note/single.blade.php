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
    .field-label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .field-value { font-size: 9.5px; }
    .field-row { display: flex; gap: 16px; margin-bottom: 6px; }
    .field-item { flex: 1; }
    .text-block { font-size: 9.5px; line-height: 1.6; white-space: pre-wrap; }
    .chip { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 10px; padding: 2px 7px; font-size: 8px; margin: 2px 2px 2px 0; }
    .chip-red { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
    .chip-green { background: #dcfce7; border-color: #86efac; color: #166534; }
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
        <div class="doc-title">Contact Report</div>
    </div>

    <div class="resident-strip">
        <table>
            <tr>
                <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
                <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
                <td><span class="label">DOB: </span><span class="value">{{ $record->resident->date_of_birth->format('m/d/Y') }}</span></td>
                <td><span class="label">Admitted: </span><span class="value">{{ $record->resident->admission_date->format('m/d/Y') }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Recorded by: </span><span class="value">{{ $record->recorder?->name ?? '—' }}</span></td>
                <td><span class="label">Contact Date: </span><span class="value">{{ $record->contact_date->format('m/d/Y') }}</span></td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Diagnosis</div>
        <div class="section-body">
            @if($record->diagnosis)
                <p class="text-block">{{ $record->diagnosis }}</p>
            @else
                <span class="no-val">No diagnosis recorded.</span>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Contact Details</div>
        <div class="section-body">
            <div class="field-row">
                <div class="field-item">
                    <div class="field-label">Contact Date</div>
                    <div class="field-value">{{ $record->contact_date->format('m/d/Y') }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Contact Time</div>
                    <div class="field-value">{{ $record->contact_time ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Contact Name</div>
                    <div class="field-value">{{ $record->contact_name ?? '—' }}</div>
                </div>
            </div>
            <div style="margin-top: 6px;">
                <div class="field-label">Person Contacted</div>
                <div style="margin-top: 3px;">
                    @if(!empty($record->person_contacted))
                        @foreach($record->person_contacted as $p)
                            <span class="chip">{{ $p }}</span>
                        @endforeach
                    @else
                        <span class="no-val">—</span>
                    @endif
                </div>
            </div>
            <div style="margin-top: 6px;">
                <div class="field-label">Mode of Contact</div>
                <div style="margin-top: 3px;">
                    @if(!empty($record->mode_of_contact))
                        @foreach($record->mode_of_contact as $m)
                            <span class="chip">{{ $m }}</span>
                        @endforeach
                        @if($record->mode_other)
                            <span style="font-size:9px;">({{ $record->mode_other }})</span>
                        @endif
                    @else
                        <span class="no-val">—</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
        <div class="section-body">
            @if($record->contact_summary)
                <p class="text-block" style="margin-bottom: 8px;">{{ $record->contact_summary }}</p>
            @endif
            <div>
                <span class="field-label">Emergency Issue: </span>
                @if($record->emergency_issue === true)
                    <span class="chip chip-red">Yes — Emergency</span>
                @elseif($record->emergency_issue === false)
                    <span class="chip chip-green">No</span>
                @else
                    <span class="no-val">N/A</span>
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Signers</div>
        <div class="section-body">
            @if(count($signerNames) > 0)
                @foreach($signerNames as $name)
                    <span class="signer-item">{{ $name }}</span>
                @endforeach
            @else
                <span class="no-val">No signers selected.</span>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Signature</div>
        <div class="section-body">
            @php $sigUri = $record->signature?->getDataUri() ?? $record->raw_signature_data; @endphp
            @if($sigUri)
                <table style="width:100%"><tr>
                    <td style="width:220px">
                        <div class="sig-box"><img class="sig-img" src="{{ $sigUri }}" alt="Signature" /></div>
                    </td>
                    <td style="padding-left:16px;vertical-align:bottom">
                        <div class="sig-name">{{ $record->recorder?->name ?? '—' }}</div>
                        <div class="sig-meta">{{ $record->created_at->format('m/d/Y g:i A') }}</div>
                    </td>
                </tr></table>
            @else
                <span class="no-val">Not signed.</span>
            @endif
        </div>
    </div>

    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential Clinical Record</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>
</div>
</body>
</html>
