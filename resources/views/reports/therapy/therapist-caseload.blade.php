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
        .stat-grid {
            width: 100%;
            border: 2px solid #2872A1;
        }
        .stat-grid td {
            text-align: center;
            border: 1px solid #2872A1;
            padding: 10px 8px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #2872A1;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
        }
        .bar-container {
            background-color: #E8E8E8;
            height: 14px;
            width: 100%;
            border-radius: 2px;
        }
        .bar-fill {
            background-color: #2872A1;
            height: 14px;
            border-radius: 2px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            color: #888;
            text-align: right;
        }
        .alt-row {
            background-color: #F5F5F5;
        }
    </style>
</head>
<body>
    @include('reports.therapy.partials.header')

    <div class="title">THERAPIST CASELOAD REPORT</div>

    {{-- Therapist Info --}}
    @php
        $therapist->load('staffProfile');
    @endphp
    <div class="info-box">
        <div class="info-row"><span class="label">Therapist:</span> {{ $therapist->name }}</div>
        <div class="info-row"><span class="label">Position:</span> {{ $therapist->staffProfile?->position ?? 'N/A' }}</div>
        <div class="info-row"><span class="label">Report Period:</span> {{ \Carbon\Carbon::parse($dateFrom)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('m/d/Y') }}</div>
    </div>

    {{-- Statistics Grid --}}
    <div class="section-title">CASELOAD STATISTICS</div>
    @php
        $scheduled = $sessions->where('status', 'scheduled')->count();
        $noShow = $sessions->where('status', 'no_show')->count();
        $cancelledOnly = $sessions->where('status', 'cancelled')->count();
    @endphp
    <table class="stat-grid">
        <tr>
            <td>
                <div class="stat-value">{{ $sessions->count() }}</div>
                <div class="stat-label">Total Sessions</div>
            </td>
            <td>
                <div class="stat-value" style="color: #22863A;">{{ $completed }}</div>
                <div class="stat-label">Completed</div>
            </td>
            <td>
                <div class="stat-value" style="color: #DC2626;">{{ $cancelledOnly }}</div>
                <div class="stat-label">Cancelled</div>
            </td>
            <td>
                <div class="stat-value" style="color: #D97706;">{{ $noShow }}</div>
                <div class="stat-label">No Show</div>
            </td>
            <td>
                <div class="stat-value" style="color: #2563EB;">{{ $uniqueResidents }}</div>
                <div class="stat-label">Unique Residents</div>
            </td>
        </tr>
    </table>

    {{-- AI Analysis --}}
    @if($analysis)
        <div class="section-title">PERFORMANCE ANALYSIS</div>
        <div class="analysis-box">{!! nl2br(e($analysis)) !!}</div>
    @endif

    {{-- Service Type Distribution --}}
    <div class="section-title">SERVICE TYPE DISTRIBUTION</div>
    @php
        $byType = $sessions->groupBy('service_type');
        $total = $sessions->count() ?: 1;
    @endphp
    <table>
        <tr>
            <th style="width: 25%;">Service Type</th>
            <th style="width: 10%;">Count</th>
            <th style="width: 10%;">%</th>
            <th style="width: 55%;">Distribution</th>
        </tr>
        @foreach($byType as $type => $typeSessions)
            @php $pct = round($typeSessions->count() / $total * 100); @endphp
            <tr>
                <td>{{ $typeSessions->first()->service_type_label }}</td>
                <td>{{ $typeSessions->count() }}</td>
                <td>{{ $pct }}%</td>
                <td>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ $pct }}%;"></div>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Resident Breakdown --}}
    <div class="section-title">RESIDENT BREAKDOWN</div>
    @php
        $byResident = $sessions->groupBy('resident_id');
    @endphp
    <table>
        <tr>
            <th>Resident</th>
            <th style="width: 10%;">Sessions</th>
            <th style="width: 30%;">Service Types</th>
            <th style="width: 15%;">Last Session</th>
        </tr>
        @foreach($byResident as $residentId => $residentSessions)
            <tr>
                <td>{{ $residentSessions->first()->resident->full_name }}</td>
                <td>{{ $residentSessions->count() }}</td>
                <td>{{ $residentSessions->pluck('service_type_label')->unique()->implode(', ') }}</td>
                <td>{{ $residentSessions->sortByDesc('session_date')->first()->session_date->format('m/d/Y') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="footer">
        Generated: {{ $generated_at }} by {{ $generated_by }}
    </div>
</body>
</html>
