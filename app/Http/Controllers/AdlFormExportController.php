<?php

namespace App\Http\Controllers;

use App\Models\AdlTrackingForm;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdlFormExportController extends Controller
{
    public function pdf(Request $request, AdlTrackingForm $adlForm)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $adlForm->load(['resident', 'recorder', 'signature']);

        $pdf = Pdf::loadView('reports.adl.single', [
            'form'     => $adlForm,
            'facility' => system_setting('system_name', 'CareNest'),
            'items'    => AdlTrackingForm::adlItems(),
            'levels'   => AdlTrackingForm::levelLabels(),
        ])->setPaper('letter', 'portrait');

        $filename = 'adl_'
            . Str::slug($adlForm->resident->full_name) . '_'
            . $adlForm->form_date->format('Y-m-d')
            . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
