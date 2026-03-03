<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\ShiftProgressNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftProgressNoteExportController extends Controller
{
    public function pdf(Request $request, ShiftProgressNote $shiftProgressNote)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $note = $shiftProgressNote->load(['resident', 'recorder', 'signature']);

        $pdf = Pdf::loadView('reports.shift-progress-note.single', [
            'note'     => $note,
            'facility' => system_setting('system_name', 'CareNest'),
        ])->setPaper('letter', 'portrait');

        $filename = 'shift_note_'
            .Str::slug($note->resident->full_name).'_'
            .$note->shift_date->format('Y-m-d')
            .'.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function residentPdf(Request $request, Resident $resident)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $notes = ShiftProgressNote::where('resident_id', $resident->id)
            ->with(['recorder', 'signature'])
            ->latest('shift_date')
            ->latest('id')
            ->get();

        $pdf = Pdf::loadView('reports.shift-progress-note.list', [
            'resident' => $resident,
            'notes'    => $notes,
            'facility' => system_setting('system_name', 'CareNest'),
            'exported' => now()->format('m/d/Y g:i A'),
        ])->setPaper('letter', 'portrait');

        $filename = 'shift_notes_'
            .Str::slug($resident->full_name).'_'
            .now()->format('Y-m-d')
            .'.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
