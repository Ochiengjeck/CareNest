<?php

namespace App\Http\Controllers;

use App\Models\AsamChecklist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AsamChecklistExportController extends Controller
{
    public function pdf(Request $request, AsamChecklist $asamChecklist)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $asamChecklist->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($asamChecklist->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $asamChecklist->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.asam-checklist.single', [
            'record'      => $asamChecklist,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
            'dimensions'  => AsamChecklist::dimensions(),
        ])->setPaper('letter', 'portrait');

        $filename = 'asam_checklist_'
            . Str::slug($asamChecklist->resident->full_name)
            . '_' . now()->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
