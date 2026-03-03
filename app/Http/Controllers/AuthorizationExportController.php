<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthorizationExportController extends Controller
{
    public function pdf(Request $request, Authorization $authorization)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $authorization->load(['resident', 'recorder', 'employeeSignature']);

        $signerNames = [];
        if (! empty($authorization->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $authorization->signers)
                ->pluck('name')->toArray();
        }

        $pdf = Pdf::loadView('reports.authorization.single', [
            'record'      => $authorization,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'authorization_'
            . Str::slug($authorization->resident->full_name)
            . '_' . now()->format('Y-m-d')
            . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }
}
