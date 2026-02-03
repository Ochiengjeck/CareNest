<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Discharge Summary - {{ $resident->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            margin: 20px 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .facility-name {
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
        }
        .facility-address {
            text-decoration: underline;
        }
        .facility-contact {
            text-decoration: underline;
            margin-bottom: 10px;
        }
        .resident-info {
            margin-bottom: 5px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            margin: 10px 0;
        }
        .section-title {
            font-weight: bold;
            background-color: #e8e8e8;
            padding: 4px 8px;
            margin: 10px 0 5px 0;
            border: 1px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.info-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            vertical-align: top;
        }
        table.info-table td.label {
            font-weight: bold;
            width: 40%;
            background-color: #f5f5f5;
        }
        .text-box {
            border: 1px solid #000;
            padding: 8px;
            min-height: 60px;
            margin-bottom: 10px;
            white-space: pre-wrap;
        }
        .text-box.large {
            min-height: 80px;
        }
        .page-break {
            page-break-before: always;
        }
        .agency-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: left;
            padding: 4px 8px;
            border: 1px solid #000;
        }
        .agency-table td {
            padding: 4px 8px;
            border: 1px solid #000;
        }
        .medications-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: left;
            padding: 4px 8px;
            border: 1px solid #000;
        }
        .medications-table td {
            padding: 4px 8px;
            border: 1px solid #000;
            height: 25px;
        }
    </style>
</head>
<body>
    {{-- PAGE 1 --}}
    @include('reports.discharge.partials.header')

    <div class="resident-info">
        <strong>Name of Resident:</strong> {{ $resident->full_name }} &nbsp;&nbsp;
        <strong>DOB:</strong> {{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp;
        <strong>AHCCCS ID#:</strong> ___
    </div>
    <div class="resident-info">
        <strong>Date of Intake:</strong> {{ $resident->admission_date?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp;
        <strong>Today's Date:</strong> {{ $today_date }}
    </div>

    <div class="title">DISCHARGE SUMMARY</div>

    <div class="section-title">Provider Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Name of Agency:</td>
            <td>{{ $discharge->agency_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Name of Discharge Staff:</td>
            <td>{{ $discharge->discharge_staff_name ?? $discharge->dischargeStaff?->name ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">Member Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Member's Name:</td>
            <td>{{ $resident->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Date of Birth:</td>
            <td>{{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Admission:</td>
            <td>{{ $resident->admission_date?->format('m/d/Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Discharge:</td>
            <td>{{ $discharge->discharge_date?->format('m/d/Y') ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Aftercare Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Next Level of Care Recommended:</td>
            <td>{{ $discharge->next_level_of_care ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Barriers to Discharge Transition:</td>
            <td>{{ $discharge->barriers_to_transition ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Strengths for Discharge:</td>
            <td>{{ $discharge->strengths_for_discharge ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">Reason for Admission:</div>
    <div class="text-box large">{{ $discharge->reason_for_admission ?? '' }}</div>

    <div class="section-title">Course of Treatment:</div>
    <div class="text-box large">{{ $discharge->course_of_treatment ?? '' }}</div>

    <div class="section-title">Discharge Status and Recommendations:</div>
    <div class="text-box large">{{ $discharge->discharge_status_recommendations ?? '' }}</div>

    {{-- PAGE 2 --}}
    <div class="page-break"></div>
    @include('reports.discharge.partials.header')

    <div class="resident-info">
        <strong>Name of Resident:</strong> {{ $resident->full_name }} &nbsp;&nbsp;
        <strong>DOB:</strong> {{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp;
        <strong>AHCCCS ID#:</strong> ___
    </div>
    <div class="resident-info">
        <strong>Date of Intake:</strong> {{ $resident->admission_date?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp;
        <strong>Today's Date:</strong> {{ $today_date }}
    </div>

    <div class="title">DISCHARGE SUMMARY</div>

    <div class="section-title">Discharge Condition/Reason:</div>
    <div class="text-box large">{{ $discharge->discharge_condition_reason ?? '' }}</div>

    <div class="section-title">Crisis Plan:</div>
    <div class="text-box large">{{ $discharge->crisis_plan ?? '' }}</div>

    <div class="section-title">Agency Contacts</div>
    <table class="agency-table">
        <thead>
            <tr>
                <th style="width: 50%;">Agency Name</th>
                <th style="width: 25%;">Address</th>
                <th style="width: 25%;">Telephone</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agency_contacts as $contact)
                <tr>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->address ?? '' }}</td>
                    <td>{{ $contact->phone ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Future Appointments</div>
    <table class="agency-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Provider</th>
                <th>Location</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            @forelse($discharge->future_appointments ?? [] as $appointment)
                <tr>
                    <td>{{ $appointment['date'] ?? '' }}</td>
                    <td>{{ $appointment['time'] ?? '' }}</td>
                    <td>{{ $appointment['provider'] ?? '' }}</td>
                    <td>{{ $appointment['location'] ?? '' }}</td>
                    <td>{{ $appointment['phone'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- PAGE 3 --}}
    <div class="page-break"></div>
    @include('reports.discharge.partials.header')

    <div class="resident-info">
        <strong>Name of Resident:</strong> {{ $resident->full_name }} &nbsp;&nbsp;
        <strong>DOB:</strong> {{ $resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp;
        <strong>AHCCCS ID#:</strong> ___
    </div>
    <div class="resident-info">
        <strong>Date of Intake:</strong> {{ $resident->admission_date?->format('m/d/Y') ?? 'N/A' }} &nbsp;&nbsp;
        <strong>Today's Date:</strong> {{ $today_date }}
    </div>

    <div class="title">DISCHARGE SUMMARY</div>

    <div class="section-title">Special Needs:</div>
    <div class="text-box">{{ $discharge->special_needs ?? 'None' }}</div>

    <div class="section-title">MEDICATIONS</div>
    <table class="medications-table">
        <thead>
            <tr>
                <th style="width: 45%;">NAME</th>
                <th style="width: 35%;">DOSAGE</th>
                <th style="width: 20%;">QUANTITY</th>
            </tr>
        </thead>
        <tbody>
            @forelse($discharge->medications_at_discharge ?? [] as $med)
                <tr>
                    <td>{{ $med['name'] ?? '' }}</td>
                    <td>{{ $med['dosage'] ?? '' }}</td>
                    <td>{{ $med['quantity'] ?? '' }}</td>
                </tr>
            @empty
                @for($i = 0; $i < 6; $i++)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    <div class="section-title">PERSONAL POSSESSIONS</div>
    <div class="text-box">{{ $discharge->personal_possessions ?? 'Client maintained possession of all personal belongings during treatment.' }}</div>
</body>
</html>
