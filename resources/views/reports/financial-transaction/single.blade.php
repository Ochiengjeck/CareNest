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

    /* Transactions table */
    .tx-table { width: 100%; border-collapse: collapse; }
    .tx-table th { background: #f1f5f9; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; color: #555; padding: 5px 6px; border: 1px solid #e2e8f0; text-align: right; }
    .tx-table th.left { text-align: left; }
    .tx-table td { font-size: 9px; padding: 4px 6px; border: 1px solid #e2e8f0; vertical-align: top; text-align: right; }
    .tx-table td.left { text-align: left; }
    .tx-table tr:nth-child(even) td { background: #f8fafc; }
    .balance-positive { color: #166534; font-weight: bold; }
    .balance-negative { color: #991b1b; font-weight: bold; }

    /* Signers */
    .signer-list { margin: 0; padding: 0; list-style: none; }
    .signer-item { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 20px; padding: 2px 8px; font-size: 8px; margin: 2px 3px 2px 0; font-weight: bold; }
    .no-val { color: #aaa; font-style: italic; }

    /* Diagnosis */
    .diagnosis-text { font-size: 9.5px; line-height: 1.6; color: #111; white-space: pre-wrap; }

    /* Signature */
    .sig-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
    .sig-img { max-height: 60px; max-width: 180px; }
    .sig-name { font-weight: bold; font-size: 9px; margin-top: 4px; }
    .sig-meta { font-size: 8px; color: #666; }

    /* Footer */
    .footer { border-top: 1px solid #e2e8f0; margin-top: 16px; padding-top: 6px; font-size: 7.5px; color: #999; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Resident Financial Record</div>
    </div>

    {{-- Resident strip --}}
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
                <td><span class="label">Date: </span><span class="value">{{ $record->created_at->format('m/d/Y') }}</span></td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    {{-- Diagnosis --}}
    <div class="section">
        <div class="section-title">Diagnosis</div>
        <div class="section-body">
            @if($record->diagnosis)
                <p class="diagnosis-text">{{ $record->diagnosis }}</p>
            @else
                <span class="no-val">No diagnosis recorded.</span>
            @endif
        </div>
    </div>

    {{-- Transactions table --}}
    @php
        $entries  = $record->entries ?? [];
        $running  = 0;
        $balances = [];
        foreach ($entries as $entry) {
            $running   += (float)($entry['deposit'] ?? 0) - (float)($entry['money_spent'] ?? 0);
            $balances[] = $running;
        }
    @endphp
    <div class="section">
        <div class="section-title">Transactions</div>
        <div class="section-body" style="padding: 0;">
            @if(count($entries) > 0)
                <table class="tx-table">
                    <thead>
                        <tr>
                            <th class="left">Date</th>
                            <th>Deposit ($)</th>
                            <th>Money Spent ($)</th>
                            <th>Balance ($)</th>
                            <th class="left">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $i => $entry)
                            @php $bal = $balances[$i]; @endphp
                            <tr>
                                <td class="left">{{ \Carbon\Carbon::parse($entry['date'])->format('m/d/Y') }}</td>
                                <td>{{ number_format((float)($entry['deposit'] ?? 0), 2) }}</td>
                                <td>{{ number_format((float)($entry['money_spent'] ?? 0), 2) }}</td>
                                <td class="{{ $bal >= 0 ? 'balance-positive' : 'balance-negative' }}">
                                    {{ number_format($bal, 2) }}
                                </td>
                                <td class="left">{{ $entry['description'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-val" style="padding: 8px 10px;">No transactions recorded.</p>
            @endif
        </div>
    </div>

    {{-- Signers --}}
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

    {{-- Signature --}}
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

    {{-- Footer --}}
    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential Resident Financial Record</span>
        <span>Generated {{ now()->format('m/d/Y g:i A') }}</span>
    </div>

</div>
</body>
</html>
