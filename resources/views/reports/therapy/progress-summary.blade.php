<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px 30px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header .facility-name {
            font-weight: bold;
            font-size: 13px;
            text-decoration: underline;
        }
        .header .address, .header .contact {
            font-size: 10px;
            text-decoration: underline;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 15px 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10px;
        }
        th {
            background-color: #E8E8E8;
            font-weight: bold;
            text-align: left;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin: 15px 0 5px 0;
            padding: 4px 8px;
            background-color: #2872A1;
            color: #fff;
        }
        .info-box {
            border: 2px solid #2872A1;
            padding: 10px;
            margin-bottom: 10px;
        }
        .info-box .label {
            font-weight: bold;
            color: #2872A1;
        }
        .info-row {
            margin-bottom: 3px;
        }
        .analysis-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 8px 0;
            background-color: #FAFAFA;
            white-space: pre-wrap;
        }
        .alt-row {
            background-color: #F5F5F5;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            color: #888;
            text-align: right;
        }
        .stat-grid {
            width: 100%;
            border: 2px solid #2872A1;
        }
        .stat-grid td {
            text-align: center;
            border: 1px solid #2872A1;
            padding: 8px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2872A1;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    @include('reports.therapy.partials.header')

    <div class="title">THERAPY PROGRESS SUMMARY REPORT</div>

    {{-- Client Info Box --}}
    <div class="info-box">
        <div class="info-row"><span class="label">Client:</span> {{ $resident->full_name }}</div>
        <div class="info-row"><span class="label">Client ID:</span> {{ $resident->id }} &nbsp;&nbsp; <span class="label">D.O.B:</span> {{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }}</div>
        <div class="info-row"><span class="label">Report Period:</span> {{ \Carbon\Carbon::parse($dateFrom)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('m/d/Y') }}</div>
    </div>

    {{-- Stats --}}
    <table class="stat-grid">
        <tr>
            <td>
                <div class="stat-value">{{ $sessions->count() }}</div>
                <div class="stat-label">Total Sessions</div>
            </td>
            <td>
                <div class="stat-value">{{ $sessions->pluck('service_type')->unique()->count() }}</div>
                <div class="stat-label">Service Types</div>
            </td>
            <td>
                <div class="stat-value">{{ $sessions->pluck('challenge_index')->filter()->unique()->count() }}</div>
                <div class="stat-label">Challenge Areas</div>
            </td>
            <td>
                <div class="stat-value">{{ $sessions->pluck('therapist.name')->unique()->count() }}</div>
                <div class="stat-label">Therapists</div>
            </td>
        </tr>
    </table>

    {{-- AI Analysis --}}
    @if($analysis)
        <div class="section-title">CLINICAL ANALYSIS</div>
        <div class="analysis-box">{!! nl2br(e($analysis)) !!}</div>
    @endif

    {{-- Session Timeline --}}
    <div class="section-title">SESSION TIMELINE</div>
    <table>
        <tr>
            <th style="width: 12%;">Date</th>
            <th style="width: 14%;">Service Type</th>
            <th style="width: 22%;">Topic</th>
            <th style="width: 52%;">Key Progress</th>
        </tr>
        @foreach($sessions as $i => $session)
            <tr class="{{ $i % 2 ? 'alt-row' : '' }}">
                <td>{{ $session->session_date->format('m/d/Y') }}</td>
                <td>{{ $session->service_type_label }}</td>
                <td>{{ $session->session_topic }}</td>
                <td>{{ $session->progress_notes ? Str::limit($session->progress_notes, 200) : '-' }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Challenge Areas Summary --}}
    @php
        $challenges = $sessions->pluck('challenge_index')->filter()->countBy();
    @endphp
    @if($challenges->isNotEmpty())
        <div class="section-title">CHALLENGE AREAS ADDRESSED</div>
        <table>
            <tr>
                <th>Challenge Area</th>
                <th style="width: 20%;">Sessions</th>
            </tr>
            @foreach($challenges as $challenge => $count)
                <tr>
                    <td>{{ \App\Services\TherapyReportService::challengeDisplay($challenge) }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="footer">
        Generated: {{ $generated_at }} by {{ $generated_by }}
    </div>
</body>
</html>
