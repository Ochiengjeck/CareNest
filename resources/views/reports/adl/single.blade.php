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

    /* ADL Table */
    .adl-table { width: 100%; border-collapse: collapse; }
    .adl-table th { background: #f1f5f9; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; color: #555; padding: 5px 6px; border: 1px solid #e2e8f0; text-align: center; }
    .adl-table th.adl-name { text-align: left; width: 130px; }
    .adl-table th.initials-col { width: 55px; text-align: left; }
    .adl-table td { font-size: 9px; padding: 4px 6px; border: 1px solid #e2e8f0; text-align: center; vertical-align: middle; }
    .adl-table td.adl-name { text-align: left; font-weight: 500; color: #333; }
    .adl-table td.initials-col { text-align: left; color: #555; }
    .adl-table tr:nth-child(even) td { background: #f8fafc; }
    .adl-check { font-size: 11px; font-weight: bold; color: #1e40af; }
    .adl-footer { font-size: 7.5px; color: #888; font-style: italic; margin-top: 6px; }

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
        <div class="doc-title">Activities of Daily Living</div>
    </div>

    {{-- Resident strip --}}
    <div class="resident-strip">
        <table>
            <tr>
                <td><span class="label">Resident: </span><span class="value">{{ $form->resident->full_name }}</span></td>
                <td><span class="label">DOB: </span><span class="value">{{ $form->resident->date_of_birth->format('m/d/Y') }}</span></td>
                <td><span class="label">Admitted: </span><span class="value">{{ $form->resident->admission_date->format('m/d/Y') }}</span></td>
                <td><span class="label">Room: </span><span class="value">{{ $form->resident->room_number ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Form Date: </span><span class="value">{{ $form->form_date->format('m/d/Y') }}</span></td>
                <td><span class="label">Recorded by: </span><span class="value">{{ $form->recorder?->name ?? '—' }}</span></td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    {{-- ADL Table --}}
    @php
        $entries = $form->entries ?? [];
    @endphp
    <div class="section">
        <div class="section-title">ADL Tracking</div>
        <div class="section-body" style="padding: 0;">
            <table class="adl-table">
                <thead>
                    <tr>
                        <th class="adl-name">ADL</th>
                        <th>No Assistance</th>
                        <th>Some Assistance</th>
                        <th>Complete Assistance</th>
                        <th>Not Applicable</th>
                        <th>Refused</th>
                        <th class="initials-col">Initials</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $key => $label)
                        @php
                            $entry    = $entries[$key] ?? [];
                            $selected = $entry['level'] ?? '';
                            $initials = $entry['initials'] ?? '';
                        @endphp
                        <tr>
                            <td class="adl-name">{{ $label }}</td>
                            @foreach (['no_assistance', 'some_assistance', 'complete_assistance', 'not_applicable', 'refused'] as $level)
                                <td>
                                    @if ($selected === $level)
                                        <span class="adl-check">&#10003;</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="initials-col">{{ $initials ?: '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="adl-footer" style="padding: 6px 8px;">Staff members are to initial once ADLs is completed on each shift.</p>
        </div>
    </div>

    {{-- Signature --}}
    <div class="section">
        <div class="section-title">Signature</div>
        <div class="section-body">
            @php $sigUri = $form->signature?->getDataUri() ?? $form->raw_signature_data; @endphp
            @if($sigUri)
                <table style="width:100%">
                    <tr>
                        <td style="width:220px">
                            <div class="sig-box">
                                <img class="sig-img" src="{{ $sigUri }}" alt="Signature" />
                            </div>
                        </td>
                        <td style="padding-left:16px;vertical-align:bottom">
                            <div class="sig-name">{{ $form->recorder?->name ?? '—' }}</div>
                            <div class="sig-meta">{{ $form->created_at->format('m/d/Y g:i A') }}</div>
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
