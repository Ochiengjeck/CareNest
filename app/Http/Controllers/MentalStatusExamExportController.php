<?php

namespace App\Http\Controllers;

use App\Models\MentalStatusExam;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MentalStatusExamExportController extends Controller
{
    public function pdf(Request $request, MentalStatusExam $mentalStatusExam)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $mentalStatusExam->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($mentalStatusExam->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $mentalStatusExam->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.mental-status.single', [
            'record'      => $mentalStatusExam,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
            'categories'  => MentalStatusExam::categories(),
        ])->setPaper('letter', 'portrait');

        $filename = 'mental_status_'
            . Str::slug($mentalStatusExam->resident->full_name)
            . '_' . $mentalStatusExam->exam_date->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
