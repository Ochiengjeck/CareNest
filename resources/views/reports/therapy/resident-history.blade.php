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
        .alt-row {
            background-color: #F5F5F5;
        }
        .doc-box {
            border: 1px solid #ccc;
            padding: 8px;
            margin: 5px 0;
            background-color: #FAFAFA;
        }
        .doc-box .doc-label {
            font-weight: bold;
            font-size: 9px;
            color: #2872A1;
            margin-bottom: 3px;
        }
        .doc-box .doc-content {
            font-size: 9px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            color: #888;
            text-align: right;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @include('reports.therapy.partials.header')

    <div class="title">RESIDENT THERAPY HISTORY</div>

    {{-- Client Profile --}}
    <div class="info-box">
        <div class="info-row"><span class="label">Client:</span> {{ $resident->full_name }} &nbsp;&nbsp; <span class="label">Client ID:</span> {{ $resident->id }}</div>
        <div class="info-row"><span class="label">D.O.B:</span> {{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp; <span class="label">Gender:</span> {{ ucfirst($resident->gender ?? 'N/A') }}</div>
        <div class="info-row"><span class="label">Room:</span> {{ $resident->room_number ?? 'N/A' }} &nbsp;&nbsp; <span class="label">Admission Date:</span> {{ $resident->admission_date?->format('m/d/Y') ?? 'N/A' }}</div>
        @if($resident->medical_conditions)
            <div class="info-row"><span class="label">Medical Conditions:</span> {{ $resident->medical_conditions }}</div>
        @endif
    </div>

    {{-- Engagement Overview --}}
    @php
        $completedCount = $sessions->where('status', 'completed')->count();
        $cancelledCount = $sessions->where('status', 'cancelled')->count();
        $noShowCount = $sessions->where('status', 'no_show')->count();
        $scheduledCount = $sessions->where('status', 'scheduled')->count();
        $therapists = $sessions->pluck('therapist.name')->unique();
        $firstSession = $sessions->first();
        $lastSession = $sessions->last();
    @endphp

    <div class="section-title">ENGAGEMENT OVERVIEW</div>
    <table class="stat-grid">
        <tr>
            <td>
                <div class="stat-value">{{ $sessions->count() }}</div>
                <div class="stat-label">Total Sessions</div>
            </td>
            <td>
                <div class="stat-value" style="color: #22863A;">{{ $completedCount }}</div>
                <div class="stat-label">Completed</div>
            </td>
            <td>
                <div class="stat-value" style="color: #DC2626;">{{ $cancelledCount }}</div>
                <div class="stat-label">Cancelled</div>
            </td>
            <td>
                <div class="stat-value" style="color: #D97706;">{{ $noShowCount }}</div>
                <div class="stat-label">No Show</div>
            </td>
        </tr>
    </table>

    @if($firstSession && $lastSession)
        <table>
            <tr>
                <th style="width: 20%;">First Session</th>
                <th style="width: 20%;">Last Session</th>
                <th>Therapists Involved</th>
            </tr>
            <tr>
                <td>{{ $firstSession->session_date->format('m/d/Y') }}</td>
                <td>{{ $lastSession->session_date->format('m/d/Y') }}</td>
                <td>{{ $therapists->implode(', ') }}</td>
            </tr>
        </table>
    @endif

    {{-- Challenge Areas --}}
    @php
        $challenges = $sessions->pluck('challenge_index')->filter()->countBy();
    @endphp
    @if($challenges->isNotEmpty())
        <div class="section-title">CHALLENGE AREAS ADDRESSED</div>
        <table>
            <tr>
                <th>Challenge Area</th>
                <th style="width: 15%;">Sessions</th>
            </tr>
            @foreach($challenges as $challenge => $count)
                <tr>
                    <td>{{ \App\Services\TherapyReportService::challengeDisplay($challenge) }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- AI Analysis --}}
    @if($analysis)
        <div class="section-title">CLINICAL ANALYSIS</div>
        <div class="analysis-box">{!! nl2br(e($analysis)) !!}</div>
    @endif

    {{-- Complete Session Log --}}
    <div class="section-title">COMPLETE SESSION LOG</div>
    <table>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 12%;">Date</th>
            <th style="width: 12%;">Time</th>
            <th style="width: 14%;">Type</th>
            <th style="width: 17%;">Therapist</th>
            <th style="width: 28%;">Topic</th>
            <th style="width: 12%;">Status</th>
        </tr>
        @foreach($sessions as $i => $session)
            <tr class="{{ $i % 2 ? 'alt-row' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $session->session_date->format('m/d/Y') }}</td>
                <td>{{ $session->formatted_time_range }}</td>
                <td>{{ $session->service_type_label }}</td>
                <td>{{ $session->therapist->name }}</td>
                <td>{{ $session->session_topic }}</td>
                <td>{{ $session->status_label }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Session Documentation Summaries --}}
    @php
        $documentedSessions = $sessions->filter(fn($s) => $s->status === 'completed' && $s->progress_notes);
    @endphp
    @if($documentedSessions->isNotEmpty())
        <div class="page-break"></div>
        <div class="section-title">SESSION DOCUMENTATION</div>

        @foreach($documentedSessions as $session)
            <div class="doc-box">
                <div class="doc-label">
                    {{ $session->session_date->format('m/d/Y') }} - {{ $session->session_topic }}
                    ({{ $session->service_type_label }})
                </div>

                @if($session->interventions)
                    <div class="doc-label" style="margin-top: 4px;">Interventions:</div>
                    <div class="doc-content">{{ Str::limit($session->interventions, 300) }}</div>
                @endif

                @if($session->progress_notes)
                    <div class="doc-label" style="margin-top: 4px;">Progress:</div>
                    <div class="doc-content">{{ Str::limit($session->progress_notes, 300) }}</div>
                @endif

                @if($session->client_plan)
                    <div class="doc-label" style="margin-top: 4px;">Plan:</div>
                    <div class="doc-content">{{ Str::limit($session->client_plan, 300) }}</div>
                @endif
            </div>
        @endforeach
    @endif

    <div class="footer">
        Generated: {{ $generated_at }} by {{ $generated_by }}
    </div>
</body>
</html>
