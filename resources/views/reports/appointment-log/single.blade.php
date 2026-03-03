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
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; }
    .field-label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .field-value { font-size: 9.5px; color: #111; }
    .text-block { font-size: 9.5px; line-height: 1.6; white-space: pre-wrap; }
    .no-val { color: #aaa; font-style: italic; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Appointment Tracking Log</div>
    </div>

    <div class="resident-strip">
        <table><tr>
            <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
            <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
            <td><span class="label">DOB: </span><span class="value">{{ $record->resident->date_of_birth->format('m/d/Y') }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Recorded by: </span><span class="value">{{ $record->recorder?->name ?? '—' }}</span></td>
            <td><span class="label">Created: </span><span class="value">{{ $record->created_at->format('m/d/Y') }}</span></td>
            <td></td>
        </tr></table>
    </div>

    <div class="section">
        <div class="section-title">Appointment Details</div>
        <div class="section-body">
            <div class="field-grid">
                <div class="field-item">
                    <div class="field-label">Appointment Date</div>
                    <div class="field-value">{{ $record->appointment_date->format('l, F j, Y') }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Time</div>
                    <div class="field-value">{{ $record->time_slot ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Contact Number</div>
                    <div class="field-value">{{ $record->contact_number ?? '—' }}</div>
                </div>
            </div>
            @if($record->address)
                <div style="margin-top: 8px;">
                    <div class="field-label">Address</div>
                    <div class="text-block">{{ $record->address }}</div>
                </div>
            @endif
            @if($record->reason)
                <div style="margin-top: 8px;">
                    <div class="field-label">Reason for Appointment</div>
                    <div class="text-block">{{ $record->reason }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <span>{{ $facility }} &mdash; Appointment Tracking Log</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>
</div>
</body>
</html>
