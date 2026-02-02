<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\TherapySession;
use App\Models\User;
use App\Services\AI\AiManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class TherapyReportService
{
    // ──────────────────────────────────────────────
    // Helper methods
    // ──────────────────────────────────────────────

    public function getFacilityData(): array
    {
        return [
            'facility_name' => system_setting('system_name', 'CareNest'),
            'address_line_1' => system_setting('address_line_1', ''),
            'address_line_2' => system_setting('address_line_2', ''),
            'city' => system_setting('city', ''),
            'state' => system_setting('state_province', ''),
            'postal_code' => system_setting('postal_code', ''),
            'phone' => system_setting('phone', ''),
            'email' => system_setting('email', ''),
        ];
    }

    public function getFormattedAddress(): string
    {
        $facility = $this->getFacilityData();
        $parts = array_filter([
            $facility['address_line_1'],
            $facility['address_line_2'],
            implode(' ', array_filter([$facility['city'], $facility['state'], $facility['postal_code']])),
        ]);

        return implode(', ', $parts) ?: 'Address not configured';
    }

    public static function serviceTypeCode(string $type): string
    {
        return match ($type) {
            'individual' => 'INDIVIDUAL NOTE',
            'group' => 'GR',
            'intake_assessment' => 'INT',
            'crisis' => 'CR',
            'collateral' => 'CO',
            'case_management' => 'CM',
            'treatment_planning' => 'TP',
            'discharge' => 'D',
            'other' => 'O',
            default => strtoupper($type),
        };
    }

    public static function challengeDisplay(?string $challenge): string
    {
        if (! $challenge) {
            return '';
        }

        return match ($challenge) {
            'substance_use' => '(s)1. substance use disorder',
            'mental_health' => '(s)2. Mental Health',
            'physical_health' => '(s)3. Physical Health',
            'employment_education' => '(s)4. Employment/Education',
            'financial_housing' => '(s)5. Financial/Housing',
            'legal' => '(s)6. Legal',
            'psychosocial_family' => '(s)7. Psycho-Social/Family',
            'spirituality' => '(s)8. Spirituality',
            default => ucfirst(str_replace('_', ' ', $challenge)),
        };
    }

    // ──────────────────────────────────────────────
    // AI enhancement
    // ──────────────────────────────────────────────

    public function isAiAvailable(): bool
    {
        try {
            $aiManager = app(AiManager::class);

            return $aiManager->isEnabled()
                && $aiManager->isUseCaseEnabled('therapy_reporting')
                && $aiManager->isConfigured($aiManager->getUseCaseProvider('therapy_reporting'));
        } catch (\Exception) {
            return false;
        }
    }

    public function enhanceSessionContent(TherapySession $session): array
    {
        $content = [
            'interventions' => $session->interventions ?? '',
            'progress_notes' => $session->progress_notes ?? '',
            'client_plan' => $session->client_plan ?? '',
        ];

        if (! $this->isAiAvailable()) {
            return $content;
        }

        try {
            $aiManager = app(AiManager::class);

            $prompt = 'You are a clinical documentation specialist. Polish and enhance the following therapy session documentation. '
                .'Preserve ALL factual details and client quotes. Use professional clinical terminology. Maintain third-person narrative style. '
                ."Return ONLY the enhanced text for each section, separated by the exact markers [INTERVENTIONS], [PROGRESS], [PLAN].\n\n"
                ."Client: {$session->resident->full_name}\n"
                ."Session Date: {$session->session_date->format('m/d/Y')}\n"
                ."Service Type: {$session->service_type_label}\n"
                ."Topic: {$session->session_topic}\n\n"
                ."[INTERVENTIONS]\n{$content['interventions']}\n\n"
                ."[PROGRESS]\n{$content['progress_notes']}\n\n"
                ."[PLAN]\n{$content['client_plan']}";

            $response = $aiManager->executeForUseCase('therapy_reporting', $prompt);

            if ($response->success && $response->content) {
                $enhanced = $this->parseEnhancedContent($response->content);
                if ($enhanced) {
                    return $enhanced;
                }
            }
        } catch (\Exception) {
            // Fall back to original content
        }

        return $content;
    }

    protected function parseEnhancedContent(string $aiResponse): ?array
    {
        $sections = [];

        if (preg_match('/\[INTERVENTIONS\]\s*(.+?)\s*\[PROGRESS\]/s', $aiResponse, $m)) {
            $sections['interventions'] = trim($m[1]);
        }
        if (preg_match('/\[PROGRESS\]\s*(.+?)\s*\[PLAN\]/s', $aiResponse, $m)) {
            $sections['progress_notes'] = trim($m[1]);
        }
        if (preg_match('/\[PLAN\]\s*(.+)/s', $aiResponse, $m)) {
            $sections['client_plan'] = trim($m[1]);
        }

        if (count($sections) === 3) {
            return $sections;
        }

        return null;
    }

    public function generateAnalysis(string $type, array $data): ?string
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $aiManager = app(AiManager::class);

            $prompt = match ($type) {
                'progress_summary' => $this->buildProgressAnalysisPrompt($data),
                'therapist_caseload' => $this->buildCaseloadAnalysisPrompt($data),
                'resident_history' => $this->buildHistoryAnalysisPrompt($data),
                default => null,
            };

            if (! $prompt) {
                return null;
            }

            $response = $aiManager->executeForUseCase('therapy_reporting', $prompt);

            return $response->success ? $response->content : null;
        } catch (\Exception) {
            return null;
        }
    }

    protected function buildProgressAnalysisPrompt(array $data): string
    {
        $sessionsSummary = '';
        foreach ($data['sessions'] as $session) {
            $sessionsSummary .= "- {$session->session_date->format('m/d/Y')}: {$session->service_type_label} - {$session->session_topic}";
            if ($session->progress_notes) {
                $sessionsSummary .= ' | Progress: '.substr($session->progress_notes, 0, 200);
            }
            $sessionsSummary .= "\n";
        }

        return 'Generate a concise clinical progress analysis for a therapy progress summary report. '
            ."Include an executive summary (2-3 sentences), patterns and themes observed, and recommendations.\n\n"
            ."Client: {$data['resident']->full_name}\n"
            ."Period: {$data['date_from']} to {$data['date_to']}\n"
            .'Total Sessions: '.count($data['sessions'])."\n\n"
            ."Sessions:\n{$sessionsSummary}\n\n"
            ."Format your response with these exact headers:\n"
            ."EXECUTIVE SUMMARY:\n[summary]\n\n"
            ."PATTERNS AND THEMES:\n[analysis]\n\n"
            ."RECOMMENDATIONS:\n[recommendations]";
    }

    protected function buildCaseloadAnalysisPrompt(array $data): string
    {
        return "Generate a concise therapist performance and workload analysis.\n\n"
            ."Therapist: {$data['therapist']->name}\n"
            ."Period: {$data['date_from']} to {$data['date_to']}\n"
            ."Total Sessions: {$data['total']}\n"
            ."Completed: {$data['completed']}\n"
            ."Cancelled/No-Show: {$data['cancelled']}\n"
            ."Unique Residents: {$data['unique_residents']}\n\n"
            ."Format your response with these exact headers:\n"
            ."PERFORMANCE SUMMARY:\n[summary]\n\n"
            ."WORKLOAD ANALYSIS:\n[analysis]\n\n"
            ."RECOMMENDATIONS:\n[recommendations]";
    }

    protected function buildHistoryAnalysisPrompt(array $data): string
    {
        $sessionsSummary = '';
        foreach ($data['sessions']->take(30) as $session) {
            $sessionsSummary .= "- {$session->session_date->format('m/d/Y')}: {$session->service_type_label} - {$session->session_topic} ({$session->status_label})\n";
        }

        return "Generate a concise long-term therapy progress assessment.\n\n"
            ."Client: {$data['resident']->full_name}\n"
            ."Total Sessions: {$data['sessions']->count()}\n"
            .'Completed: '.$data['sessions']->where('status', 'completed')->count()."\n\n"
            ."Recent Sessions:\n{$sessionsSummary}\n\n"
            ."Format your response with these exact headers:\n"
            ."TREATMENT OVERVIEW:\n[overview]\n\n"
            ."PROGRESS ASSESSMENT:\n[assessment]\n\n"
            ."RECOMMENDATIONS:\n[recommendations]";
    }

    // ──────────────────────────────────────────────
    // PDF export methods
    // ──────────────────────────────────────────────

    public function individualSessionPdf(TherapySession $session): \Barryvdh\DomPDF\PDF
    {
        $session->load(['therapist.staffProfile', 'resident', 'supervisor.staffProfile']);

        $enhanced = $this->enhanceSessionContent($session);

        $data = [
            'session' => $session,
            'resident' => $session->resident,
            'therapist' => $session->therapist,
            'supervisor' => $session->supervisor,
            'facility' => $this->getFacilityData(),
            'formatted_address' => $this->getFormattedAddress(),
            'service_type_code' => self::serviceTypeCode($session->service_type),
            'challenge_display' => self::challengeDisplay($session->challenge_index),
            'therapist_title' => $session->therapist->staffProfile?->position ?? 'BHT',
            'supervisor_title' => $session->supervisor?->staffProfile?->position ?? 'BHP',
            'completion_date' => $session->updated_at->format('m/d/Y'),
            'supervisor_date' => $session->supervisor_signed_at?->format('m/d/Y') ?? '',
            'interventions' => $enhanced['interventions'],
            'progress_notes' => $enhanced['progress_notes'],
            'client_plan' => $enhanced['client_plan'],
        ];

        return Pdf::loadView('reports.therapy.individual-session', $data)
            ->setPaper('letter', 'portrait');
    }

    public function progressSummaryPdf(Resident $resident, string $dateFrom, string $dateTo): \Barryvdh\DomPDF\PDF
    {
        $sessions = TherapySession::query()
            ->forResident($resident->id)
            ->completed()
            ->inDateRange($dateFrom, $dateTo)
            ->with(['therapist'])
            ->orderBy('session_date')
            ->get();

        $analysis = $this->generateAnalysis('progress_summary', [
            'resident' => $resident,
            'sessions' => $sessions,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $data = [
            'resident' => $resident,
            'sessions' => $sessions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'facility' => $this->getFacilityData(),
            'formatted_address' => $this->getFormattedAddress(),
            'analysis' => $analysis,
            'generated_by' => auth()->user()->name ?? 'System',
            'generated_at' => now()->format('m/d/Y g:i A'),
        ];

        return Pdf::loadView('reports.therapy.progress-summary', $data)
            ->setPaper('letter', 'portrait');
    }

    public function therapistCaseloadPdf(User $therapist, string $dateFrom, string $dateTo): \Barryvdh\DomPDF\PDF
    {
        $sessions = TherapySession::query()
            ->forTherapist($therapist->id)
            ->inDateRange($dateFrom, $dateTo)
            ->with('resident')
            ->orderBy('session_date')
            ->get();

        $completed = $sessions->where('status', 'completed')->count();
        $cancelled = $sessions->whereIn('status', ['cancelled', 'no_show'])->count();
        $uniqueResidents = $sessions->pluck('resident_id')->unique()->count();

        $analysis = $this->generateAnalysis('therapist_caseload', [
            'therapist' => $therapist,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total' => $sessions->count(),
            'completed' => $completed,
            'cancelled' => $cancelled,
            'unique_residents' => $uniqueResidents,
        ]);

        $data = [
            'therapist' => $therapist,
            'sessions' => $sessions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'uniqueResidents' => $uniqueResidents,
            'facility' => $this->getFacilityData(),
            'formatted_address' => $this->getFormattedAddress(),
            'analysis' => $analysis,
            'generated_by' => auth()->user()->name ?? 'System',
            'generated_at' => now()->format('m/d/Y g:i A'),
        ];

        return Pdf::loadView('reports.therapy.therapist-caseload', $data)
            ->setPaper('letter', 'portrait');
    }

    public function residentHistoryPdf(Resident $resident): \Barryvdh\DomPDF\PDF
    {
        $sessions = TherapySession::query()
            ->forResident($resident->id)
            ->with(['therapist'])
            ->orderBy('session_date')
            ->get();

        $analysis = $this->generateAnalysis('resident_history', [
            'resident' => $resident,
            'sessions' => $sessions,
        ]);

        $data = [
            'resident' => $resident,
            'sessions' => $sessions,
            'facility' => $this->getFacilityData(),
            'formatted_address' => $this->getFormattedAddress(),
            'analysis' => $analysis,
            'generated_by' => auth()->user()->name ?? 'System',
            'generated_at' => now()->format('m/d/Y g:i A'),
        ];

        return Pdf::loadView('reports.therapy.resident-history', $data)
            ->setPaper('letter', 'portrait');
    }

    // ──────────────────────────────────────────────
    // Word export methods
    // ──────────────────────────────────────────────

    public function individualSessionWord(TherapySession $session): string
    {
        $session->load(['therapist.staffProfile', 'resident', 'supervisor.staffProfile']);

        $enhanced = $this->enhanceSessionContent($session);
        $facility = $this->getFacilityData();

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => 600,
            'marginBottom' => 600,
            'marginLeft' => 800,
            'marginRight' => 800,
        ]);

        // Header
        $section->addText($facility['facility_name'], ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addText($this->getFormattedAddress(), ['size' => 10, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addText(
            'Email: '.$facility['email'].' M:'.$facility['phone'],
            ['size' => 10, 'underline' => 'single'],
            ['alignment' => 'center']
        );
        $section->addTextBreak();

        // Title
        $section->addText('THERAPY NOTE', ['bold' => true, 'size' => 13], ['alignment' => 'center']);
        $section->addTextBreak();

        $borderStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $boldFont = ['bold' => true, 'size' => 10];
        $normalFont = ['size' => 10];

        // Client Info Row
        $clientTable = $section->addTable($borderStyle);
        $clientTable->addRow();
        $clientTable->addCell(3600)->addText("Client's Name: ".$session->resident->full_name, $normalFont);
        $clientTable->addCell(2800)->addText("Client's ID: ".$session->resident->id, $normalFont);
        $clientTable->addCell(2800)->addText('D.O.B: '.($session->resident->date_of_birth?->format('m/d/Y') ?? 'N/A'), $normalFont);

        $section->addTextBreak(0);

        // Service Types Legend
        $legendTable = $section->addTable(['borderSize' => 12, 'borderColor' => '000000', 'cellMargin' => 80]);
        $legendTable->addRow();
        $cell = $legendTable->addCell(9200);
        $textRun = $cell->addTextRun();
        $textRun->addText('SERVICE TYPES: ', ['bold' => true, 'color' => 'FF0000', 'size' => 10]);
        $textRun->addText(
            'PR=Progress Note INT=Intake/Assess GR=Group CR=Crises CO=Collateral CM=Case Mngt TP=Tx Planning TR=Transport MED=Medication D=Discharge IND=Crisis OR Counseling O=Other',
            ['size' => 9]
        );

        $section->addTextBreak(0);

        // Challenge Index
        $indexTable = $section->addTable(['borderSize' => 12, 'borderColor' => '000000', 'cellMargin' => 80]);
        $indexTable->addRow();
        $cell = $indexTable->addCell(9200);
        $textRun = $cell->addTextRun();
        $textRun->addText('INDEX OF CHALLENGES/BARRIERS: ', ['bold' => true, 'size' => 10]);
        $textRun->addText(
            '1. substance use disorder 2. Mental Health 3. Physical Health 4. Employment/Education 5. Financial/Housing 6. Legal 7. Psycho-Social/Family 8. Spirituality',
            ['bold' => true, 'size' => 9]
        );

        $section->addTextBreak(0);

        // Session Details Table
        $detailsTable = $section->addTable($borderStyle);

        // Row 1: Date/Time/Type/Index
        $detailsTable->addRow();
        $c1 = $detailsTable->addCell(1800);
        $c1->addText('Service Date', $boldFont);
        $c1->addText($session->session_date->format('m/d/Y'), $normalFont);

        $c2 = $detailsTable->addCell(1600);
        $c2->addText('Start Time', $boldFont);
        $c2->addText(Carbon::parse($session->start_time)->format('g:i A'), $normalFont);

        $c3 = $detailsTable->addCell(1600);
        $c3->addText('End Time', $boldFont);
        $c3->addText(Carbon::parse($session->end_time)->format('g:i A'), $normalFont);

        $c4 = $detailsTable->addCell(2200);
        $c4->addText('Service Type (see above)', $boldFont);
        $c4->addText(self::serviceTypeCode($session->service_type).'.', $normalFont);

        $c5 = $detailsTable->addCell(2000);
        $c5->addText('Tx Plan Index', $boldFont);
        $c5->addText(self::challengeDisplay($session->challenge_index), $normalFont);

        // Row 2: Session topic
        $detailsTable->addRow();
        $detailsTable->addCell(1800)->addText('Session topic', $boldFont);
        $detailsTable->addCell(7400, ['gridSpan' => 4])->addText($session->session_topic, $normalFont);

        // Row 3: Interventions
        $detailsTable->addRow();
        $detailsTable->addCell(1800)->addText('Provider support & Interventions', $boldFont);
        $detailsTable->addCell(7400, ['gridSpan' => 4])->addText($enhanced['interventions'], $normalFont);

        // Row 4: Progress
        $detailsTable->addRow();
        $detailsTable->addCell(1800)->addText("Description of client's specific progress on treatment plan, problems, goals, action steps, objectives, and/or referrals", $boldFont);
        $detailsTable->addCell(7400, ['gridSpan' => 4])->addText($enhanced['progress_notes'], $normalFont);

        // Row 5: Client plan
        $detailsTable->addRow();
        $detailsTable->addCell(1800)->addText("Client's plan (including new Issues or problems that affect treatment plan)", $boldFont);
        $detailsTable->addCell(7400, ['gridSpan' => 4])->addText($enhanced['client_plan'], $normalFont);

        $section->addTextBreak();

        // Signature Block
        $sigTable = $section->addTable($borderStyle);

        $therapistTitle = $session->therapist->staffProfile?->position ?? 'BHT';
        $supervisorTitle = $session->supervisor?->staffProfile?->position ?? 'BHP';

        $sigTable->addRow(600);
        $sigTable->addCell(3200)->addText("Name of BHT, Title:\n{$session->therapist->name}, {$therapistTitle}.", $boldFont);
        $sigTable->addCell(3600)->addText('Signature, Credentials', $boldFont);
        $sigTable->addCell(2400)->addText("Date of Completion\n".$session->updated_at->format('m/d/Y'), $boldFont);

        $sigTable->addRow(600);
        $supervisorName = $session->supervisor ? "{$session->supervisor->name}, {$supervisorTitle}" : '';
        $sigTable->addCell(3200)->addText('Name of BHP, Title: '.$supervisorName, $boldFont);
        $sigTable->addCell(3600)->addText('Signature, Credentials', $boldFont);
        $sigTable->addCell(2400)->addText("Date of Completion\n".($session->supervisor_signed_at?->format('m/d/Y') ?? ''), $boldFont);

        $tempFile = tempnam(sys_get_temp_dir(), 'therapy_').'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }

    public function progressSummaryWord(Resident $resident, string $dateFrom, string $dateTo): string
    {
        $sessions = TherapySession::query()
            ->forResident($resident->id)
            ->completed()
            ->inDateRange($dateFrom, $dateTo)
            ->with(['therapist'])
            ->orderBy('session_date')
            ->get();

        $analysis = $this->generateAnalysis('progress_summary', [
            'resident' => $resident,
            'sessions' => $sessions,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $facility = $this->getFacilityData();

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection(['marginTop' => 600, 'marginBottom' => 600, 'marginLeft' => 800, 'marginRight' => 800]);

        // Header
        $section->addText($facility['facility_name'], ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addText($this->getFormattedAddress(), ['size' => 10, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addTextBreak();
        $section->addText('THERAPY PROGRESS SUMMARY REPORT', ['bold' => true, 'size' => 13], ['alignment' => 'center']);
        $section->addTextBreak();

        $borderStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $boldFont = ['bold' => true, 'size' => 10];
        $normalFont = ['size' => 10];
        $headerFont = ['bold' => true, 'size' => 11];

        // Client info
        $infoTable = $section->addTable($borderStyle);
        $infoTable->addRow();
        $infoTable->addCell(4600)->addText("Client: {$resident->full_name}", $boldFont);
        $infoTable->addCell(4600)->addText('Client ID: '.$resident->id, $normalFont);
        $infoTable->addRow();
        $infoTable->addCell(4600)->addText('D.O.B: '.($resident->date_of_birth?->format('m/d/Y') ?? 'N/A'), $normalFont);
        $infoTable->addCell(4600)->addText("Period: {$dateFrom} to {$dateTo}", $normalFont);
        $infoTable->addRow();
        $infoTable->addCell(9200, ['gridSpan' => 2])->addText("Total Completed Sessions: {$sessions->count()}", $boldFont);

        $section->addTextBreak();

        // AI Analysis
        if ($analysis) {
            $section->addText('ANALYSIS', $headerFont);
            $section->addText($analysis, $normalFont);
            $section->addTextBreak();
        }

        // Session Timeline
        $section->addText('SESSION TIMELINE', $headerFont);
        $section->addTextBreak(0);

        $timelineTable = $section->addTable($borderStyle);
        $timelineTable->addRow();
        $timelineTable->addCell(1400, ['bgColor' => 'E8E8E8'])->addText('Date', $boldFont);
        $timelineTable->addCell(1600, ['bgColor' => 'E8E8E8'])->addText('Service Type', $boldFont);
        $timelineTable->addCell(2400, ['bgColor' => 'E8E8E8'])->addText('Topic', $boldFont);
        $timelineTable->addCell(3800, ['bgColor' => 'E8E8E8'])->addText('Key Progress', $boldFont);

        foreach ($sessions as $session) {
            $timelineTable->addRow();
            $timelineTable->addCell(1400)->addText($session->session_date->format('m/d/Y'), $normalFont);
            $timelineTable->addCell(1600)->addText($session->service_type_label, $normalFont);
            $timelineTable->addCell(2400)->addText($session->session_topic, $normalFont);
            $timelineTable->addCell(3800)->addText(
                $session->progress_notes ? substr($session->progress_notes, 0, 150).'...' : '-',
                $normalFont
            );
        }

        $section->addTextBreak();
        $section->addText('Generated: '.now()->format('m/d/Y g:i A').' by '.(auth()->user()->name ?? 'System'), ['size' => 8, 'color' => '888888']);

        $tempFile = tempnam(sys_get_temp_dir(), 'progress_').'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }

    public function therapistCaseloadWord(User $therapist, string $dateFrom, string $dateTo): string
    {
        $sessions = TherapySession::query()
            ->forTherapist($therapist->id)
            ->inDateRange($dateFrom, $dateTo)
            ->with('resident')
            ->orderBy('session_date')
            ->get();

        $completed = $sessions->where('status', 'completed')->count();
        $cancelled = $sessions->where('status', 'cancelled')->count();
        $noShow = $sessions->where('status', 'no_show')->count();
        $uniqueResidents = $sessions->pluck('resident_id')->unique()->count();

        $analysis = $this->generateAnalysis('therapist_caseload', [
            'therapist' => $therapist,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total' => $sessions->count(),
            'completed' => $completed,
            'cancelled' => $cancelled + $noShow,
            'unique_residents' => $uniqueResidents,
        ]);

        $facility = $this->getFacilityData();

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection(['marginTop' => 600, 'marginBottom' => 600, 'marginLeft' => 800, 'marginRight' => 800]);

        $section->addText($facility['facility_name'], ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addText($this->getFormattedAddress(), ['size' => 10, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addTextBreak();
        $section->addText('THERAPIST CASELOAD REPORT', ['bold' => true, 'size' => 13], ['alignment' => 'center']);
        $section->addTextBreak();

        $borderStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $boldFont = ['bold' => true, 'size' => 10];
        $normalFont = ['size' => 10];
        $headerFont = ['bold' => true, 'size' => 11];

        // Therapist info
        $therapist->load('staffProfile');
        $infoTable = $section->addTable($borderStyle);
        $infoTable->addRow();
        $infoTable->addCell(4600)->addText("Therapist: {$therapist->name}", $boldFont);
        $infoTable->addCell(4600)->addText('Position: '.($therapist->staffProfile?->position ?? 'N/A'), $normalFont);
        $infoTable->addRow();
        $infoTable->addCell(9200, ['gridSpan' => 2])->addText("Report Period: {$dateFrom} to {$dateTo}", $normalFont);

        $section->addTextBreak();

        // Stats
        $section->addText('CASELOAD STATISTICS', $headerFont);
        $statsTable = $section->addTable($borderStyle);
        $statsTable->addRow();
        $statsTable->addCell(3066, ['bgColor' => 'E8E8E8'])->addText('Total Sessions', $boldFont);
        $statsTable->addCell(3066, ['bgColor' => 'E8E8E8'])->addText('Completed', $boldFont);
        $statsTable->addCell(3066, ['bgColor' => 'E8E8E8'])->addText('Unique Residents', $boldFont);
        $statsTable->addRow();
        $statsTable->addCell(3066)->addText((string) $sessions->count(), ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $statsTable->addCell(3066)->addText((string) $completed, ['bold' => true, 'size' => 14, 'color' => '22863A'], ['alignment' => 'center']);
        $statsTable->addCell(3066)->addText((string) $uniqueResidents, ['bold' => true, 'size' => 14, 'color' => '2563EB'], ['alignment' => 'center']);

        $section->addTextBreak();

        // AI Analysis
        if ($analysis) {
            $section->addText('ANALYSIS', $headerFont);
            $section->addText($analysis, $normalFont);
            $section->addTextBreak();
        }

        // Resident breakdown
        $section->addText('RESIDENT BREAKDOWN', $headerFont);
        $residentTable = $section->addTable($borderStyle);
        $residentTable->addRow();
        $residentTable->addCell(3000, ['bgColor' => 'E8E8E8'])->addText('Resident', $boldFont);
        $residentTable->addCell(1500, ['bgColor' => 'E8E8E8'])->addText('Sessions', $boldFont);
        $residentTable->addCell(2200, ['bgColor' => 'E8E8E8'])->addText('Service Types', $boldFont);
        $residentTable->addCell(2500, ['bgColor' => 'E8E8E8'])->addText('Last Session', $boldFont);

        $byResident = $sessions->groupBy('resident_id');
        foreach ($byResident as $residentSessions) {
            $r = $residentSessions->first()->resident;
            $residentTable->addRow();
            $residentTable->addCell(3000)->addText($r->full_name, $normalFont);
            $residentTable->addCell(1500)->addText((string) $residentSessions->count(), $normalFont);
            $residentTable->addCell(2200)->addText($residentSessions->pluck('service_type_label')->unique()->implode(', '), ['size' => 9]);
            $residentTable->addCell(2500)->addText($residentSessions->sortByDesc('session_date')->first()->session_date->format('m/d/Y'), $normalFont);
        }

        $section->addTextBreak();
        $section->addText('Generated: '.now()->format('m/d/Y g:i A').' by '.(auth()->user()->name ?? 'System'), ['size' => 8, 'color' => '888888']);

        $tempFile = tempnam(sys_get_temp_dir(), 'caseload_').'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }

    public function residentHistoryWord(Resident $resident): string
    {
        $sessions = TherapySession::query()
            ->forResident($resident->id)
            ->with(['therapist'])
            ->orderBy('session_date')
            ->get();

        $analysis = $this->generateAnalysis('resident_history', [
            'resident' => $resident,
            'sessions' => $sessions,
        ]);

        $facility = $this->getFacilityData();

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection(['marginTop' => 600, 'marginBottom' => 600, 'marginLeft' => 800, 'marginRight' => 800]);

        $section->addText($facility['facility_name'], ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addText($this->getFormattedAddress(), ['size' => 10, 'underline' => 'single'], ['alignment' => 'center']);
        $section->addTextBreak();
        $section->addText('RESIDENT THERAPY HISTORY', ['bold' => true, 'size' => 13], ['alignment' => 'center']);
        $section->addTextBreak();

        $borderStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $boldFont = ['bold' => true, 'size' => 10];
        $normalFont = ['size' => 10];
        $headerFont = ['bold' => true, 'size' => 11];

        // Client profile
        $profileTable = $section->addTable($borderStyle);
        $profileTable->addRow();
        $profileTable->addCell(4600)->addText("Client: {$resident->full_name}", $boldFont);
        $profileTable->addCell(4600)->addText('Client ID: '.$resident->id, $normalFont);
        $profileTable->addRow();
        $profileTable->addCell(4600)->addText('D.O.B: '.($resident->date_of_birth?->format('m/d/Y') ?? 'N/A'), $normalFont);
        $profileTable->addCell(4600)->addText('Admission: '.($resident->admission_date?->format('m/d/Y') ?? 'N/A'), $normalFont);
        if ($resident->medical_conditions) {
            $profileTable->addRow();
            $profileTable->addCell(9200, ['gridSpan' => 2])->addText('Medical Conditions: '.$resident->medical_conditions, $normalFont);
        }

        $section->addTextBreak();

        // Engagement overview
        $section->addText('ENGAGEMENT OVERVIEW', $headerFont);
        $completedCount = $sessions->where('status', 'completed')->count();
        $cancelledCount = $sessions->where('status', 'cancelled')->count();
        $noShowCount = $sessions->where('status', 'no_show')->count();
        $therapists = $sessions->pluck('therapist.name')->unique()->implode(', ');

        $overviewTable = $section->addTable($borderStyle);
        $overviewTable->addRow();
        $overviewTable->addCell(2300, ['bgColor' => 'E8E8E8'])->addText('Total Sessions', $boldFont);
        $overviewTable->addCell(2300, ['bgColor' => 'E8E8E8'])->addText('Completed', $boldFont);
        $overviewTable->addCell(2300, ['bgColor' => 'E8E8E8'])->addText('Cancelled', $boldFont);
        $overviewTable->addCell(2300, ['bgColor' => 'E8E8E8'])->addText('No Show', $boldFont);
        $overviewTable->addRow();
        $overviewTable->addCell(2300)->addText((string) $sessions->count(), ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $overviewTable->addCell(2300)->addText((string) $completedCount, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $overviewTable->addCell(2300)->addText((string) $cancelledCount, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $overviewTable->addCell(2300)->addText((string) $noShowCount, ['bold' => true, 'size' => 12], ['alignment' => 'center']);

        if ($therapists) {
            $section->addText("Therapists: {$therapists}", $normalFont);
        }

        $section->addTextBreak();

        // AI Analysis
        if ($analysis) {
            $section->addText('ANALYSIS', $headerFont);
            $section->addText($analysis, $normalFont);
            $section->addTextBreak();
        }

        // Complete session log
        $section->addText('COMPLETE SESSION LOG', $headerFont);
        $logTable = $section->addTable($borderStyle);
        $logTable->addRow();
        $logTable->addCell(600, ['bgColor' => 'E8E8E8'])->addText('#', $boldFont);
        $logTable->addCell(1300, ['bgColor' => 'E8E8E8'])->addText('Date', $boldFont);
        $logTable->addCell(1200, ['bgColor' => 'E8E8E8'])->addText('Time', $boldFont);
        $logTable->addCell(1400, ['bgColor' => 'E8E8E8'])->addText('Type', $boldFont);
        $logTable->addCell(1800, ['bgColor' => 'E8E8E8'])->addText('Therapist', $boldFont);
        $logTable->addCell(2000, ['bgColor' => 'E8E8E8'])->addText('Topic', $boldFont);
        $logTable->addCell(900, ['bgColor' => 'E8E8E8'])->addText('Status', $boldFont);

        $smallFont = ['size' => 9];
        foreach ($sessions as $i => $session) {
            $logTable->addRow();
            $logTable->addCell(600)->addText((string) ($i + 1), $smallFont);
            $logTable->addCell(1300)->addText($session->session_date->format('m/d/Y'), $smallFont);
            $logTable->addCell(1200)->addText($session->formatted_time_range, $smallFont);
            $logTable->addCell(1400)->addText($session->service_type_label, $smallFont);
            $logTable->addCell(1800)->addText($session->therapist->name, $smallFont);
            $logTable->addCell(2000)->addText($session->session_topic, $smallFont);
            $logTable->addCell(900)->addText($session->status_label, $smallFont);
        }

        $section->addTextBreak();
        $section->addText('Generated: '.now()->format('m/d/Y g:i A').' by '.(auth()->user()->name ?? 'System'), ['size' => 8, 'color' => '888888']);

        $tempFile = tempnam(sys_get_temp_dir(), 'history_').'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }
}
