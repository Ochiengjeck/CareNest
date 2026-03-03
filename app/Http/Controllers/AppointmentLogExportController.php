<?php

namespace App\Http\Controllers;

use App\Models\AppointmentLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppointmentLogExportController extends Controller
{
    public function pdf(Request $request, AppointmentLog $appointmentLog)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $appointmentLog->load(['resident', 'recorder']);

        $pdf = Pdf::loadView('reports.appointment-log.single', [
            'record'   => $appointmentLog,
            'facility' => system_setting('system_name', 'CareNest'),
        ])->setPaper('letter', 'portrait');

        $filename = 'appointment_log_'
            . Str::slug($appointmentLog->resident->full_name)
            . '_' . $appointmentLog->appointment_date->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
