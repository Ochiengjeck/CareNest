<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
        .header .address {
            font-size: 11px;
            text-decoration: underline;
        }
        .header .contact {
            font-size: 11px;
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
            margin-bottom: 0;
        }
        td, th {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 11px;
        }
        .legend-box {
            border: 2px solid #000;
            padding: 8px 10px;
            margin: 8px 0;
            font-size: 11px;
        }
        .legend-label {
            font-weight: bold;
            color: red;
        }
        .index-box {
            border: 2px solid #000;
            padding: 8px 10px;
            margin: 8px 0;
            font-weight: bold;
            font-size: 11px;
        }
        .field-label {
            font-weight: bold;
            width: 28%;
            background-color: #f9f9f9;
        }
        .sig-row td {
            height: 50px;
        }
    </style>
</head>
<body>
    {{-- Facility Header --}}
    @include('reports.therapy.partials.header')

    {{-- Title --}}
    <div class="title">THERAPY NOTE</div>

    {{-- Client Info Row --}}
    <table>
        <tr>
            <td>Client's Name: <strong>{{ $resident->full_name }}</strong></td>
            <td>Client's ID: <strong>{{ $resident->id }}</strong></td>
            <td>D.O.B: <strong>{{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }}</strong>.</td>
        </tr>
    </table>

    {{-- Service Types Legend --}}
    <div class="legend-box">
        <span class="legend-label">SERVICE TYPES:</span>
        PR=Progress Note INT=Intake/Assess GR=Group CR=Crises CO=Collateral
        CM=Case Mngt TP=Tx Planning TR=Transport MED=Medication D=Discharge IND=Crisis OR
        Counseling O=Other
    </div>

    {{-- Index of Challenges --}}
    <div class="index-box">
        <strong>INDEX OF CHALLENGES/BARRIERS:</strong>
        1. substance use disorder
        2. Mental Health
        3. Physical Health
        4. Employment/Education
        5. Financial/Housing
        6. Legal
        7. Psycho-Social/Family
        8. Spirituality
    </div>

    {{-- Session Details Table --}}
    <table>
        {{-- Row 1: Date/Time/Type/Index --}}
        <tr>
            <td style="width: 18%;">
                <strong>Service Date</strong><br>
                {{ $session->session_date->format('m/d/Y') }}
            </td>
            <td style="width: 14%;">
                <strong>Start Time</strong><br>
                {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
            </td>
            <td style="width: 14%;">
                <strong>End Time</strong><br>
                {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
            </td>
            <td style="width: 28%;">
                <strong>Service Type (see above)</strong><br>
                {{ $service_type_code }}.
            </td>
            <td style="width: 26%;">
                <strong>Tx Plan Index</strong><br>
                {{ $challenge_display }}
            </td>
        </tr>

        {{-- Row 2: Session topic --}}
        <tr>
            <td class="field-label">Session topic</td>
            <td colspan="4">{{ $session->session_topic }}</td>
        </tr>

        {{-- Row 3: Provider support & Interventions --}}
        <tr>
            <td class="field-label">Provider support &amp; Interventions</td>
            <td colspan="4">{{ $interventions }}</td>
        </tr>

        {{-- Row 4: Client's progress --}}
        <tr>
            <td class="field-label">Description of client's specific progress on treatment plan, problems, goals, action steps, objectives, and/or referrals</td>
            <td colspan="4">{{ $progress_notes }}</td>
        </tr>

        {{-- Row 5: Client's plan --}}
        <tr>
            <td class="field-label">Client's plan (including new Issues or problems that affect treatment plan)</td>
            <td colspan="4">{{ $client_plan }}</td>
        </tr>
    </table>

    {{-- Signature Block --}}
    <table style="margin-top: 15px;">
        <tr class="sig-row">
            <td style="width: 36%;">
                <strong>Name of BHT, Title:</strong><br>
                {{ $therapist->name }}, {{ $therapist_title }}.
            </td>
            <td style="width: 38%;">
                <strong>Signature, Credentials</strong>
            </td>
            <td style="width: 26%;">
                <strong>Date of Completion</strong><br>
                {{ $completion_date }}
            </td>
        </tr>
        <tr class="sig-row">
            <td>
                <strong>Name of BHP, Title:</strong>
                @if($supervisor)
                    <br>{{ $supervisor->name }}, {{ $supervisor_title }}
                @endif
            </td>
            <td>
                <strong>Signature, Credentials</strong>
            </td>
            <td>
                <strong>Date of Completion</strong>
                @if($supervisor_date)
                    <br>{{ $supervisor_date }}
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
