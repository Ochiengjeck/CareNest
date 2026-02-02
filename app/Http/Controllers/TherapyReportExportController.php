<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\TherapySession;
use App\Models\User;
use App\Services\TherapyReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TherapyReportExportController extends Controller
{
    public function __construct(private TherapyReportService $reportService) {}

    // ──────────────────────────────────────────────
    // Individual Session Exports
    // ──────────────────────────────────────────────

    public function individualSessionPdf(TherapySession $session)
    {
        $pdf = $this->reportService->individualSessionPdf($session);

        $filename = 'therapy_note_'.Str::slug($session->resident->full_name).'_'.$session->session_date->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function individualSessionWord(TherapySession $session)
    {
        $tempPath = $this->reportService->individualSessionWord($session);

        $filename = 'therapy_note_'.Str::slug($session->resident->full_name).'_'.$session->session_date->format('Y-m-d').'.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    // ──────────────────────────────────────────────
    // Progress Summary Exports
    // ──────────────────────────────────────────────

    public function progressSummaryPdf(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $resident = Resident::findOrFail($request->resident_id);
        $pdf = $this->reportService->progressSummaryPdf($resident, $request->date_from, $request->date_to);

        $filename = 'progress_summary_'.Str::slug($resident->full_name).'_'.$request->date_from.'_to_'.$request->date_to.'.pdf';

        return $pdf->download($filename);
    }

    public function progressSummaryWord(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $resident = Resident::findOrFail($request->resident_id);
        $tempPath = $this->reportService->progressSummaryWord($resident, $request->date_from, $request->date_to);

        $filename = 'progress_summary_'.Str::slug($resident->full_name).'_'.$request->date_from.'_to_'.$request->date_to.'.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    // ──────────────────────────────────────────────
    // Therapist Caseload Exports
    // ──────────────────────────────────────────────

    public function therapistCaseloadPdf(Request $request)
    {
        $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $therapist = User::findOrFail($request->therapist_id);
        $pdf = $this->reportService->therapistCaseloadPdf($therapist, $request->date_from, $request->date_to);

        $filename = 'caseload_'.Str::slug($therapist->name).'_'.$request->date_from.'_to_'.$request->date_to.'.pdf';

        return $pdf->download($filename);
    }

    public function therapistCaseloadWord(Request $request)
    {
        $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $therapist = User::findOrFail($request->therapist_id);
        $tempPath = $this->reportService->therapistCaseloadWord($therapist, $request->date_from, $request->date_to);

        $filename = 'caseload_'.Str::slug($therapist->name).'_'.$request->date_from.'_to_'.$request->date_to.'.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    // ──────────────────────────────────────────────
    // Resident History Exports
    // ──────────────────────────────────────────────

    public function residentHistoryPdf(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
        ]);

        $resident = Resident::findOrFail($request->resident_id);
        $pdf = $this->reportService->residentHistoryPdf($resident);

        $filename = 'therapy_history_'.Str::slug($resident->full_name).'.pdf';

        return $pdf->download($filename);
    }

    public function residentHistoryWord(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
        ]);

        $resident = Resident::findOrFail($request->resident_id);
        $tempPath = $this->reportService->residentHistoryWord($resident);

        $filename = 'therapy_history_'.Str::slug($resident->full_name).'.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
