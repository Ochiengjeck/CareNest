<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 9px; color: #111; line-height: 1.4; }
    .page { padding: 22px 24px; }

    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 8px; margin-bottom: 10px; }
    .header h1 { font-size: 14px; font-weight: bold; color: #1e40af; }
    .header .doc-title { font-size: 11px; font-weight: bold; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px; }
    .header .meta { font-size: 8px; color: #666; margin-top: 3px; }

    .resident-strip { background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; padding: 6px 10px; margin-bottom: 10px; }
    .resident-strip table { width: 100%; }
    .resident-strip td { font-size: 8.5px; }
    .label { color: #555; }
    .value { font-weight: bold; }

    table.notes { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.notes th { background: #1e40af; color: #fff; font-size: 8px; font-weight: bold; padding: 5px 6px; text-align: left; text-transform: uppercase; letter-spacing: 0.4px; }
    table.notes td { font-size: 8.5px; padding: 5px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    table.notes tr:nth-child(even) td { background: #f8fafc; }

    .badge { display: inline-block; border-radius: 20px; padding: 1px 7px; font-size: 7.5px; font-weight: bold; }
    .badge-day     { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .badge-evening { background: #e0f2fe; color: #0c4a6e; border: 1px solid #7dd3fc; }
    .badge-night   { background: #ede9fe; color: #3730a3; border: 1px solid #c4b5fd; }
    .badge-shift   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .badge-signed  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .badge-unsigned{ background: #f1f5f9; color: #6b7280; border: 1px solid #d1d5db; }

    .pill { display: inline-block; background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; border-radius: 10px; padding: 1px 5px; font-size: 7.5px; margin: 1px 2px 1px 0; }
    .none { color: #aaa; font-style: italic; }

    .footer { border-top: 1px solid #e2e8f0; margin-top: 12px; padding-top: 5px; font-size: 7px; color: #999; display: flex; justify-content: space-between; }

    @page { margin: 15mm 12mm; }
</style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>{{ strtoupper($facility) }}</h1>
        <div class="doc-title">Shift Progress Notes — Resident Report</div>
        <div class="meta">Exported {{ $exported }} &bull; {{ $notes->count() }} {{ Str::plural('note', $notes->count()) }}</div>
    </div>

    <div class="resident-strip">
        <table>
            <tr>
                <td><span class="label">Resident: </span><span class="value">{{ $resident->full_name }}</span></td>
                <td><span class="label">DOB: </span><span class="value">{{ $resident->date_of_birth->format('m/d/Y') }}</span></td>
                <td><span class="label">Admitted: </span><span class="value">{{ $resident->admission_date->format('m/d/Y') }}</span></td>
                <td><span class="label">Room: </span><span class="value">{{ $resident->room_number ?? '—' }}</span></td>
                <td><span class="label">Status: </span><span class="value">{{ ucfirst($resident->status) }}</span></td>
            </tr>
        </table>
    </div>

    @php
        $moodLabels     = ['appropriate'=>'Appropriate','anxious'=>'Anxious','worry'=>'Worry','sad'=>'Sad','depressed'=>'Depressed','irritable'=>'Irritable','angry'=>'Angry','fearful'=>'Fearful','other'=>'Other'];
        $behaviorLabels = ['appropriate'=>'Appropriate','verbal_aggression'=>'Verbal Aggression','physical_aggression'=>'Physical Aggression','internal_stimuli'=>'Internal Stimuli','isolation'=>'Isolation','obsession'=>'Obsession','manipulative'=>'Manipulative','impulsive'=>'Impulsive','poor_boundaries'=>'Poor Boundaries','sexual_maladaptive'=>'Sexual Maladaptive','other'=>'Other'];
        $yn = fn($v) => match($v) { true=>'Yes', false=>'No', default=>'—' };
        $ts = fn($v) => match($v) { 'yes'=>'Yes','no'=>'No','refused'=>'Refused',default=>'—' };
    @endphp

    @if($notes->isEmpty())
        <p style="color:#999;font-style:italic;margin-top:16px;text-align:center">No shift progress notes on record.</p>
    @else
        <table class="notes">
            <thead>
                <tr>
                    <th style="width:80px">Date</th>
                    <th style="width:80px">Shift</th>
                    <th style="width:100px">Mood</th>
                    <th style="width:110px">Behaviors</th>
                    <th style="width:50px">Med</th>
                    <th style="width:50px">ADLs</th>
                    <th style="width:70px">Recorded By</th>
                    <th style="width:45px">Signed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notes as $note)
                    <tr>
                        <td>
                            <strong>{{ $note->shift_date->format('m/d/Y') }}</strong>
                            @if($note->shift_start_time)
                                <div style="font-size:7.5px;color:#666">
                                    {{ \Carbon\Carbon::parse($note->shift_start_time)->format('g:i A') }}
                                    @if($note->shift_end_time)– {{ \Carbon\Carbon::parse($note->shift_end_time)->format('g:i A') }}@endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $type = $note->shift_type_label;
                                $cls  = str_contains($type,'Day') ? 'badge-day' : (str_contains($type,'Evening') ? 'badge-evening' : (str_contains($type,'Night') ? 'badge-night' : 'badge-shift'));
                            @endphp
                            <span class="badge {{ $cls }}">{{ $type }}</span>
                        </td>
                        <td>
                            @foreach(array_slice($note->mood ?? [], 0, 3) as $k)
                                <span class="pill">{{ $moodLabels[$k] ?? $k }}</span>
                            @endforeach
                            @if(count($note->mood ?? []) > 3)
                                <span style="font-size:7.5px;color:#6b7280">+{{ count($note->mood) - 3 }} more</span>
                            @endif
                            @if(empty($note->mood)) <span class="none">—</span> @endif
                        </td>
                        <td>
                            @foreach(array_slice($note->behaviors ?? [], 0, 2) as $k)
                                <span class="pill">{{ $behaviorLabels[$k] ?? $k }}</span>
                            @endforeach
                            @if(count($note->behaviors ?? []) > 2)
                                <span style="font-size:7.5px;color:#6b7280">+{{ count($note->behaviors) - 2 }} more</span>
                            @endif
                            @if(empty($note->behaviors)) <span class="none">—</span> @endif
                        </td>
                        <td>{{ $ts($note->medication_administered) }}</td>
                        <td>{{ $yn($note->adls_completed) }}</td>
                        <td style="font-size:8px">{{ $note->recorder?->name ?? '—' }}</td>
                        <td>
                            @if($note->signature)
                                <span class="badge badge-signed">Signed</span>
                            @else
                                <span class="badge badge-unsigned">No</span>
                            @endif
                        </td>
                    </tr>
                    @if($note->note_summary)
                        <tr>
                            <td colspan="8" style="background:#fafafa;padding:3px 8px;border-bottom:1px solid #e2e8f0">
                                <span style="color:#555;font-size:7.5px"><em>Note: </em>{{ Str::limit($note->note_summary, 180) }}</span>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <span>{{ $facility }} &mdash; Confidential Clinical Record</span>
        <span>Generated {{ $exported }}</span>
    </div>

</div>
</body>
</html>
