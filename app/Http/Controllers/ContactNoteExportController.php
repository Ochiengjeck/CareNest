<?php

namespace App\Http\Controllers;

use App\Models\ContactNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactNoteExportController extends Controller
{
    public function pdf(Request $request, ContactNote $contactNote)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $contactNote->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($contactNote->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $contactNote->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.contact-note.single', [
            'record'      => $contactNote,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'contact_note_'
            . Str::slug($contactNote->resident->full_name)
            . '_' . $contactNote->contact_date->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
