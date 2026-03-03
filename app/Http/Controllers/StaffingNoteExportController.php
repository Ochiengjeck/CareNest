<?php

namespace App\Http\Controllers;

use App\Models\StaffingNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffingNoteExportController extends Controller
{
    public function pdf(Request $request, StaffingNote $staffingNote)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $staffingNote->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($staffingNote->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $staffingNote->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.staffing-note.single', [
            'record'      => $staffingNote,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'staffing_note_'
            . Str::slug($staffingNote->resident->full_name)
            . '_' . $staffingNote->note_date->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
