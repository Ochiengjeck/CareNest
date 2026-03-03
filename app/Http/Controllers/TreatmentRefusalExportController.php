<?php

namespace App\Http\Controllers;

use App\Models\TreatmentRefusal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TreatmentRefusalExportController extends Controller
{
    public function pdf(Request $request, TreatmentRefusal $treatmentRefusal)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $treatmentRefusal->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($treatmentRefusal->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $treatmentRefusal->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.treatment-refusal.single', [
            'record'      => $treatmentRefusal,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'treatment_refusal_'
            . Str::slug($treatmentRefusal->resident->full_name)
            . '_' . $treatmentRefusal->refusal_date->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
