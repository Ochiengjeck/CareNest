<?php

namespace App\Http\Controllers;

use App\Models\SafetyPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SafetyPlanExportController extends Controller
{
    public function pdf(Request $request, SafetyPlan $safetyPlan)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $safetyPlan->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($safetyPlan->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $safetyPlan->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.safety-plan.single', [
            'record'      => $safetyPlan,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'safety_plan_'
            . Str::slug($safetyPlan->resident->full_name)
            . '_' . now()->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
