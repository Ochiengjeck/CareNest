<?php

namespace App\Http\Controllers;

use App\Models\BhpProgressNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BhpProgressNoteExportController extends Controller
{
    public function pdf(Request $request, BhpProgressNote $bhpProgressNote)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $bhpProgressNote->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($bhpProgressNote->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $bhpProgressNote->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.bhp-progress-note.single', [
            'record'      => $bhpProgressNote,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'bhp_progress_note_'
            . Str::slug($bhpProgressNote->resident->full_name)
            . '_' . now()->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
