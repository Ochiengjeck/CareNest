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
    .header .doc-title { font-size: 11px; font-weight: bold; color: #111; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px; }
    .resident-strip { background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 9px; padding: 1px 8px 1px 0; }
    .resident-strip .label { color: #555; }
    .resident-strip .value { font-weight: bold; color: #111; }
    .section { margin-bottom: 10px; page-break-inside: avoid; }
    .section-title { background: #1e40af; color: #fff; font-size: 8.5px; font-weight: bold; padding: 4px 8px; border-radius: 3px 3px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .section-body { border: 1px solid #cbd5e1; border-top: none; padding: 8px 10px; border-radius: 0 0 3px 3px; }
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; }
    .field-item .field-label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; }
    .field-item .field-value { font-size: 9.5px; color: #111; }
    .text-block { font-size: 9.5px; line-height: 1.6; color: #111; white-space: pre-wrap; }
    .notice-box { background: #fefce8; border: 1px solid #fef08a; border-radius: 3px; padding: 8px 10px; font-size: 8.5px; line-height: 1.6; color: #713f12; }
    .chip { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 10px; padding: 2px 7px; font-size: 8px; margin: 2px 2px 2px 0; }
    .signer-item { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 2px 8px; font-size: 8px; margin: 2px 3px 2px 0; font-weight: bold; }
    .no-val { color: #aaa; font-style: italic; }
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
    .sig-img { max-height: 60px; max-width: 180px; }
    .sig-name { font-weight: bold; font-size: 9px; margin-top: 4px; }
    .sig-meta { font-size: 8px; color: #666; }
    .sig-section { margin-bottom: 8px; }
    .sig-section-label { font-size: 8px; text-transform: uppercase; color: #555; letter-spacing: 0.3px; margin-bottom: 4px; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Authorization for Release of Information</div>
    </div>

    <div class="resident-strip">
        <table>
            <tr>
                <td><span class="label">Resident: </span><span class="value">{{ $record->resident->full_name }}</span></td>
                <td><span class="label">AHCCCS ID: </span><span class="value">{{ $record->resident->ahcccs_id ?? '—' }}</span></td>
                <td><span class="label">DOB: </span><span class="value">{{ $record->resident->date_of_birth->format('m/d/Y') }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Recorded by: </span><span class="value">{{ $record->recorder?->name ?? '—' }}</span></td>
                <td><span class="label">Date: </span><span class="value">{{ $record->created_at->format('m/d/Y') }}</span></td>
                <td></td>
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
        <div class="section-title">Release To</div>
        <div class="section-body">
            <div class="field-grid">
                <div class="field-item">
                    <div class="field-label">Person / Agency</div>
                    <div class="field-value">{{ $record->recipient_person_agency ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Agency Name</div>
                    <div class="field-value">{{ $record->agency_name ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Phone</div>
                    <div class="field-value">{{ $record->recipient_phone ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Fax</div>
                    <div class="field-value">{{ $record->recipient_fax ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Email</div>
                    <div class="field-value">{{ $record->recipient_email ?? '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-label">Address</div>
                    <div class="field-value">{{ $record->recipient_address ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Notice to Recipient</div>
        <div class="section-body">
            <div class="notice-box">
                This information has been disclosed to you from records whose confidentiality is protected by federal law. Federal regulations (42 CFR Part 2) prohibit you from making any further disclosure of this information unless further disclosure is expressly permitted by the written consent of the person to whom it pertains or as otherwise permitted by 42 CFR Part 2. A general authorization for the release of medical or other information is NOT sufficient for this purpose. The Federal rules restrict any use of the information to criminally investigate or prosecute any alcohol or drug abuse patient.
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Information to Release</div>
        <div class="section-body">
            @if(!empty($record->information_released))
                @foreach($record->information_released as $item)
                    <span class="chip">{{ $item }}</span>
                @endforeach
            @else
                <span class="no-val">None specified.</span>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Purpose &amp; Expiration</div>
        <div class="section-body">
            @if($record->purpose)
                <div style="margin-bottom: 6px;">
                    <div class="field-item field-label">Purpose</div>
                    <p class="text-block">{{ $record->purpose }}</p>
                </div>
            @endif
            <div class="field-item field-label">Expiration</div>
            <div class="field-value" style="font-size: 9.5px;">
                @php
                    $expMap = ['one_year' => 'One Year', 'sixty_days' => '60 Days', 'specific_date' => 'Specific Date', 'other' => 'Other'];
                @endphp
                {{ $expMap[$record->expiration_type] ?? '—' }}
                @if($record->expiration_type === 'specific_date' && $record->expiration_date)
                    — {{ $record->expiration_date->format('m/d/Y') }}
                @elseif($record->expiration_type === 'other' && $record->expiration_other)
                    — {{ $record->expiration_other }}
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Signatures</div>
        <div class="section-body">
            <table style="width:100%">
                <tr>
                    <td style="width:50%; padding-right: 12px; vertical-align: top;">
                        <div class="sig-section-label">Employee Signature</div>
                        @php $empSig = $record->employeeSignature?->getDataUri() ?? $record->employee_raw_signature_data; @endphp
                        @if($empSig)
                            <div class="sig-box">
                                <img class="sig-img" src="{{ $empSig }}" alt="Employee Signature" />
                            </div>
                            <div class="sig-name">{{ $record->recorder?->name ?? '—' }}</div>
                            <div class="sig-meta">{{ $record->created_at->format('m/d/Y') }}</div>
                        @else
                            <span class="no-val">Not signed.</span>
                        @endif
                    </td>
                    <td style="width:50%; padding-left: 12px; vertical-align: top;">
                        <div class="sig-section-label">Resident Signature</div>
                        @if($record->resident_raw_signature_data)
                            <div class="sig-box">
                                <img class="sig-img" src="{{ $record->resident_raw_signature_data }}" alt="Resident Signature" />
                            </div>
                            <div class="sig-name">{{ $record->resident->full_name }}</div>
                        @else
                            <span class="no-val">Not signed.</span>
                        @endif
                    </td>
                </tr>
            </table>
            @if($record->witness)
                <div style="margin-top: 8px;">
                    <div class="field-item field-label">Witness</div>
                    <div style="font-size: 9.5px; font-weight: bold;">{{ $record->witness }}</div>
                </div>
            @endif
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

    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential — Authorization for Release</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>

</div>
</body>
</html>
