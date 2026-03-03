<?php

namespace App\Http\Controllers;

use App\Models\FaceSheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaceSheetExportController extends Controller
{
    public function pdf(Request $request, FaceSheet $faceSheet)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $faceSheet->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($faceSheet->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $faceSheet->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.face-sheet.single', [
            'record'      => $faceSheet,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'face_sheet_'
            . Str::slug($faceSheet->resident->full_name)
            . '_' . now()->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
