<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; line-height: 1.4; }
    .page { padding: 24px 28px; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 12px; }
    .header h1 { font-size: 15px; font-weight: bold; color: #1e40af; letter-spacing: 0.5px; }
    .header .doc-title { font-size: 12px; font-weight: bold; color: #111; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }
    .resident-strip { background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 9px; padding: 1px 8px 1px 0; }
    .resident-strip .label { color: #555; font-weight: normal; }
    .resident-strip .value { font-weight: bold; color: #111; }
    .section { margin-bottom: 10px; page-break-inside: avoid; }
    .section-title { background: #1e40af; color: #fff; font-size: 8.5px; font-weight: bold; padding: 4px 8px; border-radius: 3px 3px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .section-body { border: 1px solid #cbd5e1; border-top: none; padding: 8px 10px; border-radius: 0 0 3px 3px; }
    .field-row { display: flex; gap: 16px; margin-bottom: 6px; }
    .field-item { flex: 1; }
    .field-label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .field-value { font-size: 9.5px; color: #111; }
    .text-block { font-size: 9.5px; line-height: 1.6; color: #111; white-space: pre-wrap; }
    .bool-badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; }
    .bool-yes { background: #dcfce7; color: #166534; }
    .bool-no { background: #fee2e2; color: #991b1b; }
    .bool-na { background: #f1f5f9; color: #64748b; }
    .bool-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px solid #f1f5f9; }
    .bool-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .bool-label { font-size: 9px; color: #374151; }
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
        <div class="doc-title">Staffing Note</div>
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
                <td><span class="label">Note Date: </span><span class="value">{{ $record->note_date->format('m/d/Y') }}</span></td>
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
        <div class="section-title">Session Details</div>
        <div class="section-body">
            <div class="field-row">
                <div class="field-item">
                    <div class="field-label">Note Date</div>
                    <div class="field-value">{{ $record->note_date->format('m/d/Y') }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Begin Time</div>
                    <div class="field-value">{{ $record->begin_time ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">End Time</div>
                    <div class="field-value">{{ $record->end_time ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Participant</div>
                    <div class="field-value">{{ $record->participant ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Clinical Questions</div>
        <div class="section-body">
            @if($record->presenting_issues)
                <div style="margin-bottom: 8px;">
                    <div class="field-label">Presenting Issues</div>
                    <p class="text-block" style="margin-top: 2px;">{{ $record->presenting_issues }}</p>
                </div>
            @endif
            @php
                $boolNull = fn($v) => $v === null ? 'N/A' : ($v ? 'Yes' : 'No');
                $boolClass = fn($v) => $v === null ? 'bool-na' : ($v ? 'bool-yes' : 'bool-no');
            @endphp
            <div class="bool-row">
                <span class="bool-label">Conducted within 30 days?</span>
                <span class="bool-badge {{ $boolClass($record->conducted_within_30_days) }}">{{ $boolNull($record->conducted_within_30_days) }}</span>
            </div>
            <div class="bool-row">
                <span class="bool-label">Treatment plan requested?</span>
                <span class="bool-badge {{ $boolClass($record->treatment_plan_requested) }}">{{ $boolNull($record->treatment_plan_requested) }}</span>
            </div>
            <div class="bool-row">
                <span class="bool-label">Step down discussed?</span>
                <span class="bool-badge {{ $boolClass($record->step_down_discussed) }}">{{ $boolNull($record->step_down_discussed) }}</span>
            </div>
            @if($record->goals_addressed)
                <div style="margin-top: 8px;">
                    <div class="field-label">Goals Addressed</div>
                    <p class="text-block" style="margin-top: 2px;">{{ $record->goals_addressed }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Notes</div>
        <div class="section-body">
            @foreach([
                'Note Summary' => $record->note_summary,
                'Barriers' => $record->barriers,
                'Not Conducted Reason' => $record->not_conducted_reason,
                'Recommendations' => $record->recommendations,
            ] as $label => $value)
                @if($value)
                    <div style="margin-bottom: 8px;">
                        <div class="field-label">{{ $label }}</div>
                        <p class="text-block" style="margin-top: 2px;">{{ $value }}</p>
                    </div>
                @endif
            @endforeach
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
                <table style="width:100%">
                    <tr>
                        <td style="width:220px">
                            <div class="sig-box">
                                <img class="sig-img" src="{{ $sigUri }}" alt="Signature" />
                            </div>
                        </td>
                        <td style="padding-left:16px;vertical-align:bottom">
                            <div class="sig-name">{{ $record->recorder?->name ?? '—' }}</div>
                            <div class="sig-meta">{{ $record->created_at->format('m/d/Y g:i A') }}</div>
                        </td>
                    </tr>
                </table>
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
