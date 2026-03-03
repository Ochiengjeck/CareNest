<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransactionRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FinancialTransactionExportController extends Controller
{
    public function pdf(Request $request, FinancialTransactionRecord $financialTransactionRecord)
    {
        abort_unless(auth()->user()->can('view-residents'), 403);

        $financialTransactionRecord->load(['resident', 'recorder', 'signature']);

        $signerNames = [];
        if (! empty($financialTransactionRecord->signers)) {
            $signerNames = \App\Models\User::whereIn('id', $financialTransactionRecord->signers)
                ->pluck('name')
                ->toArray();
        }

        $pdf = Pdf::loadView('reports.financial-transaction.single', [
            'record'      => $financialTransactionRecord,
            'facility'    => system_setting('system_name', 'CareNest'),
            'signerNames' => $signerNames,
        ])->setPaper('letter', 'portrait');

        $filename = 'financial_transaction_'
            . Str::slug($financialTransactionRecord->resident->full_name)
            . '_' . now()->format('Y-m-d')
            . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
