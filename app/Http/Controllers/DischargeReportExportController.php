<?php

namespace App\Http\Controllers;

use App\Models\Discharge;
use App\Services\DischargeReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DischargeReportExportController extends Controller
{
    public function __construct(private DischargeReportService $reportService) {}

    public function dischargeSummaryPdf(Request $request, Discharge $discharge)
    {
        $discharge->load('resident');

        $pdf = $this->reportService->dischargeSummaryPdf($discharge);

        $filename = 'discharge_summary_'.Str::slug($discharge->resident->full_name).'_'.$discharge->discharge_date->format('Y-m-d').'.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function dischargeSummaryWord(Request $request, Discharge $discharge)
    {
        $discharge->load('resident');

        $tempPath = $this->reportService->dischargeSummaryWord($discharge);

        $filename = 'discharge_summary_'.Str::slug($discharge->resident->full_name).'_'.$discharge->discharge_date->format('Y-m-d').'.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
