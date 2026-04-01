<?php

namespace App\Http\Controllers;

use App\Models\InitialAssessment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InitialAssessmentExportController extends Controller
{
    public function pdf(Request $request, InitialAssessment $initialAssessment)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $initialAssessment->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($initialAssessment->signers)) {
            $signerNames = User::whereIn('id', $initialAssessment->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.initial-assessment.single', [
            'record'      => $initialAssessment,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
            'categories'  => InitialAssessment::mentalStatusCategories(),
        ])->setPaper('letter', 'portrait');

        $filename = 'initial_assessment_'
            . Str::slug($initialAssessment->resident->full_name)
            . '_' . ($initialAssessment->assessment_date?->format('Y-m-d') ?? now()->format('Y-m-d'))
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
