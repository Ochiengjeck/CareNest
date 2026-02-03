<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Discharge;
use App\Models\Resident;
use App\Services\AI\AiManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class DischargeReportService
{
    // ──────────────────────────────────────────────
    // AI Integration
    // ──────────────────────────────────────────────

    public function isAiAvailable(): bool
    {
        try {
            $aiManager = app(AiManager::class);

            return $aiManager->isEnabled()
                && $aiManager->isUseCaseEnabled('discharge_reporting')
                && $aiManager->isConfigured($aiManager->getUseCaseProvider('discharge_reporting'));
        } catch (\Exception) {
            return false;
        }
    }

    public function generateAftercare(Resident $resident): ?array
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $aiManager = app(AiManager::class);
            $context = $this->buildResidentContext($resident);

            $prompt = "Generate aftercare recommendations for a patient discharge.\n\n"
                .$context."\n\n"
                ."Based on the patient's history and current status, provide:\n"
                ."1. Next level of care recommended (brief, specific recommendation)\n"
                ."2. Barriers to discharge transition (potential challenges the patient may face)\n"
                ."3. Strengths for discharge (patient's strengths that support successful transition)\n\n"
                ."Return your response in this exact format:\n"
                ."[NEXT_LEVEL_OF_CARE]\n(your recommendation)\n\n"
                ."[BARRIERS]\n(list barriers)\n\n"
                ."[STRENGTHS]\n(list strengths)";

            $response = $aiManager->executeForUseCase('discharge_reporting', $prompt);

            if ($response->success && $response->content) {
                return $this->parseAftercare($response->content);
            }
        } catch (\Exception) {
            // Fall through to return null
        }

        return null;
    }

    public function generateClinicalSummary(Resident $resident): ?array
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $aiManager = app(AiManager::class);
            $context = $this->buildResidentContext($resident);

            $prompt = "Generate a clinical discharge summary.\n\n"
                .$context."\n\n"
                ."Provide:\n"
                ."1. Reason for admission (why the patient was initially admitted)\n"
                ."2. Course of treatment (summary of treatments and interventions during stay)\n"
                ."3. Discharge status and recommendations (current status and follow-up recommendations)\n\n"
                ."Return your response in this exact format:\n"
                ."[REASON_FOR_ADMISSION]\n(summary)\n\n"
                ."[COURSE_OF_TREATMENT]\n(summary)\n\n"
                ."[RECOMMENDATIONS]\n(recommendations)";

            $response = $aiManager->executeForUseCase('discharge_reporting', $prompt);

            if ($response->success && $response->content) {
                return $this->parseClinicalSummary($response->content);
            }
        } catch (\Exception) {
            // Fall through to return null
        }

        return null;
    }

    public function generateCrisisPlan(Resident $resident): ?string
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $aiManager = app(AiManager::class);
            $context = $this->buildResidentContext($resident);

            $prompt = "Generate a crisis safety plan for the patient.\n\n"
                .$context."\n\n"
                ."Create a comprehensive crisis plan that includes:\n"
                ."- Warning signs to watch for\n"
                ."- Coping strategies\n"
                ."- Support contacts\n"
                ."- Emergency steps\n\n"
                .'Keep the plan clear and actionable.';

            $response = $aiManager->executeForUseCase('discharge_reporting', $prompt);

            return $response->success ? $response->content : null;
        } catch (\Exception) {
            return null;
        }
    }

    public function suggestFollowUpAppointments(Resident $resident): ?array
    {
        if (! $this->isAiAvailable()) {
            return null;
        }

        try {
            $aiManager = app(AiManager::class);
            $context = $this->buildResidentContext($resident);

            $prompt = "Based on the patient's treatment needs, suggest follow-up appointments.\n\n"
                .$context."\n\n"
                ."Suggest 2-4 follow-up appointments with:\n"
                ."- Type of appointment (e.g., Primary Care, Mental Health, Therapy)\n"
                ."- Recommended timeframe (e.g., 1 week, 2 weeks, 1 month)\n"
                ."- Brief reason for the appointment\n\n"
                ."Return your response in this format:\n"
                ."[APPOINTMENT]\nType: ...\nTimeframe: ...\nReason: ...\n\n"
                .'(repeat for each appointment)';

            $response = $aiManager->executeForUseCase('discharge_reporting', $prompt);

            if ($response->success && $response->content) {
                return $this->parseAppointmentSuggestions($response->content);
            }
        } catch (\Exception) {
            // Fall through to return null
        }

        return null;
    }

    protected function buildResidentContext(Resident $resident): string
    {
        $resident->load(['carePlans', 'medications', 'incidents', 'vitals']);

        $context = "Patient: {$resident->full_name}\n"
            ."Age: {$resident->age} years old\n"
            ."Gender: {$resident->gender}\n"
            .'Admission Date: '.($resident->admission_date?->format('m/d/Y') ?? 'N/A')."\n";

        if ($resident->medical_conditions) {
            $context .= "Medical Conditions: {$resident->medical_conditions}\n";
        }

        if ($resident->allergies) {
            $context .= "Allergies: {$resident->allergies}\n";
        }

        // Care Plans
        $activePlans = $resident->carePlans->where('status', 'active');
        if ($activePlans->isNotEmpty()) {
            $context .= "\nActive Care Plans:\n";
            foreach ($activePlans->take(5) as $plan) {
                $context .= "- {$plan->type}: {$plan->title}\n";
                if ($plan->goals) {
                    $context .= '  Goals: '.substr($plan->goals, 0, 200)."\n";
                }
            }
        }

        // Current Medications
        $activeMeds = $resident->medications->where('status', 'active');
        if ($activeMeds->isNotEmpty()) {
            $context .= "\nCurrent Medications:\n";
            foreach ($activeMeds as $med) {
                $context .= "- {$med->name} ({$med->dosage} {$med->route})\n";
            }
        }

        // Recent Incidents
        $recentIncidents = $resident->incidents->sortByDesc('occurred_at')->take(3);
        if ($recentIncidents->isNotEmpty()) {
            $context .= "\nRecent Incidents:\n";
            foreach ($recentIncidents as $incident) {
                $context .= "- [{$incident->type}] {$incident->occurred_at->format('m/d/Y')}: ".substr($incident->description, 0, 100)."\n";
            }
        }

        return $context;
    }

    protected function parseAftercare(string $content): ?array
    {
        $result = [
            'next_level_of_care' => '',
            'barriers_to_transition' => '',
            'strengths_for_discharge' => '',
        ];

        if (preg_match('/\[NEXT_LEVEL_OF_CARE\]\s*(.+?)(?=\[BARRIERS\]|$)/s', $content, $m)) {
            $result['next_level_of_care'] = trim($m[1]);
        }
        if (preg_match('/\[BARRIERS\]\s*(.+?)(?=\[STRENGTHS\]|$)/s', $content, $m)) {
            $result['barriers_to_transition'] = trim($m[1]);
        }
        if (preg_match('/\[STRENGTHS\]\s*(.+?)$/s', $content, $m)) {
            $result['strengths_for_discharge'] = trim($m[1]);
        }

        return ! empty(array_filter($result)) ? $result : null;
    }

    protected function parseClinicalSummary(string $content): ?array
    {
        $result = [
            'reason_for_admission' => '',
            'course_of_treatment' => '',
            'discharge_status_recommendations' => '',
        ];

        if (preg_match('/\[REASON_FOR_ADMISSION\]\s*(.+?)(?=\[COURSE_OF_TREATMENT\]|$)/s', $content, $m)) {
            $result['reason_for_admission'] = trim($m[1]);
        }
        if (preg_match('/\[COURSE_OF_TREATMENT\]\s*(.+?)(?=\[RECOMMENDATIONS\]|$)/s', $content, $m)) {
            $result['course_of_treatment'] = trim($m[1]);
        }
        if (preg_match('/\[RECOMMENDATIONS\]\s*(.+?)$/s', $content, $m)) {
            $result['discharge_status_recommendations'] = trim($m[1]);
        }

        return ! empty(array_filter($result)) ? $result : null;
    }

    protected function parseAppointmentSuggestions(string $content): ?array
    {
        $appointments = [];

        if (preg_match_all('/\[APPOINTMENT\]\s*Type:\s*(.+?)\s*Timeframe:\s*(.+?)\s*Reason:\s*(.+?)(?=\[APPOINTMENT\]|$)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $appointments[] = [
                    'date' => '',
                    'time' => '',
                    'provider' => trim($match[1]),
                    'location' => '',
                    'phone' => '',
                    'notes' => trim($match[2]).' - '.trim($match[3]),
                ];
            }
        }

        return ! empty($appointments) ? $appointments : null;
    }

    // ──────────────────────────────────────────────
    // Facility & Agency Data
    // ──────────────────────────────────────────────

    public function getFacilityData(): array
    {
        return [
            'facility_name' => system_setting('system_name', 'CareNest'),
            'address_line_1' => system_setting('address_line_1', ''),
            'address_line_2' => system_setting('address_line_2', ''),
            'city' => system_setting('city', ''),
            'state' => system_setting('state', ''),
            'postal_code' => system_setting('postal_code', ''),
            'country' => system_setting('country', ''),
            'phone' => system_setting('phone', ''),
            'email' => system_setting('email', ''),
        ];
    }

    public function getFormattedAddress(): string
    {
        $data = $this->getFacilityData();
        $parts = array_filter([
            $data['address_line_1'],
            $data['address_line_2'],
            implode(' ', array_filter([$data['city'], $data['state'], $data['postal_code']])),
        ]);

        return ! empty($parts) ? implode(', ', $parts) : 'Address not configured';
    }

    public function getAgencyContacts(?array $selectedIds = null): Collection
    {
        $query = Agency::active()
            ->orderByDesc('is_institution')
            ->orderBy('name');

        if ($selectedIds !== null) {
            $query->whereIn('id', $selectedIds);
        }

        return $query->get();
    }

    public function getAllAgencies(): Collection
    {
        return Agency::orderByDesc('is_institution')
            ->orderBy('name')
            ->get();
    }

    public function dischargeSummaryPdf(Discharge $discharge): \Barryvdh\DomPDF\PDF
    {
        $discharge->load(['resident', 'dischargeStaff', 'creator']);

        // Use selected agencies if available, otherwise get all active agencies
        $selectedIds = $discharge->selected_agencies;
        $agencyContacts = $this->getAgencyContacts($selectedIds);

        $data = [
            'discharge' => $discharge,
            'resident' => $discharge->resident,
            'facility' => $this->getFacilityData(),
            'formatted_address' => $this->getFormattedAddress(),
            'agency_contacts' => $agencyContacts,
            'today_date' => now()->format('m/d/Y'),
        ];

        return Pdf::loadView('reports.discharge.discharge-summary', $data)
            ->setPaper('letter', 'portrait');
    }

    public function dischargeSummaryWord(Discharge $discharge): string
    {
        $discharge->load(['resident', 'dischargeStaff', 'creator']);

        $facility = $this->getFacilityData();
        $resident = $discharge->resident;

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        // Page 1
        $section = $phpWord->addSection([
            'marginTop' => 600,
            'marginBottom' => 600,
            'marginLeft' => 800,
            'marginRight' => 800,
        ]);

        $this->addHeader($section, $facility);
        $this->addResidentInfoLine($section, $resident, $discharge);
        $section->addText('DISCHARGE SUMMARY', ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => 'center', 'spaceBefore' => 200, 'spaceAfter' => 200]);

        // Provider Information
        $this->addSectionTitle($section, 'Provider Information');
        $this->addLabelValue($section, 'Name of Agency:', $discharge->agency_name ?? '');
        $this->addLabelValue($section, 'Name of Discharge Staff:', $discharge->discharge_staff_name ?? $discharge->dischargeStaff?->name ?? '');

        // Member Information
        $this->addSectionTitle($section, 'Member Information');
        $this->addLabelValue($section, "Member's Name:", $resident->full_name);
        $this->addLabelValue($section, 'Date of Birth:', $resident->date_of_birth?->format('m/d/Y') ?? 'N/A');
        $this->addLabelValue($section, 'Date of Admission:', $resident->admission_date?->format('m/d/Y') ?? 'N/A');
        $this->addLabelValue($section, 'Date of Discharge:', $discharge->discharge_date?->format('m/d/Y') ?? 'N/A');

        // Aftercare Information
        $this->addSectionTitle($section, 'Aftercare Information');
        $this->addLabelValue($section, 'Next Level of Care Recommended:', $discharge->next_level_of_care ?? '');
        $this->addLabelValue($section, 'Barriers to Discharge Transition:', $discharge->barriers_to_transition ?? '');
        $this->addLabelValue($section, 'Strengths for Discharge:', $discharge->strengths_for_discharge ?? '');

        // Clinical Summary
        $this->addSectionTitle($section, 'Reason for Admission');
        $section->addText($discharge->reason_for_admission ?? '', [], ['spaceAfter' => 100]);

        $this->addSectionTitle($section, 'Course of Treatment');
        $section->addText($discharge->course_of_treatment ?? '', [], ['spaceAfter' => 100]);

        $this->addSectionTitle($section, 'Discharge Status and Recommendations');
        $section->addText($discharge->discharge_status_recommendations ?? '', [], ['spaceAfter' => 100]);

        // Page 2
        $section->addPageBreak();
        $this->addHeader($section, $facility);
        $this->addResidentInfoLine($section, $resident, $discharge);

        $this->addSectionTitle($section, 'Discharge Condition/Reason');
        $section->addText($discharge->discharge_condition_reason ?? '', [], ['spaceAfter' => 100]);

        $this->addSectionTitle($section, 'Crisis Plan');
        $section->addText($discharge->crisis_plan ?? '', [], ['spaceAfter' => 100]);

        // Agency Contacts Table
        $this->addSectionTitle($section, 'Agency Contacts');
        $borderStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 40];
        $table = $section->addTable($borderStyle);

        $table->addRow();
        $table->addCell(4500, ['bgColor' => 'E8E8E8'])->addText('Agency Name', ['bold' => true, 'size' => 9]);
        $table->addCell(2500, ['bgColor' => 'E8E8E8'])->addText('Address', ['bold' => true, 'size' => 9]);
        $table->addCell(2000, ['bgColor' => 'E8E8E8'])->addText('Telephone', ['bold' => true, 'size' => 9]);

        $selectedIds = $discharge->selected_agencies;
        foreach ($this->getAgencyContacts($selectedIds) as $contact) {
            $table->addRow();
            $table->addCell(4500)->addText($contact->name, ['size' => 9]);
            $table->addCell(2500)->addText($contact->address ?? '', ['size' => 9]);
            $table->addCell(2000)->addText($contact->phone ?? '', ['size' => 9]);
        }

        // Future Appointments
        $this->addSectionTitle($section, 'Future Appointments');
        $appointments = $discharge->future_appointments ?? [];
        if (! empty($appointments)) {
            $table = $section->addTable($borderStyle);
            $table->addRow();
            $table->addCell(1500, ['bgColor' => 'E8E8E8'])->addText('Date', ['bold' => true, 'size' => 9]);
            $table->addCell(1000, ['bgColor' => 'E8E8E8'])->addText('Time', ['bold' => true, 'size' => 9]);
            $table->addCell(2500, ['bgColor' => 'E8E8E8'])->addText('Provider', ['bold' => true, 'size' => 9]);
            $table->addCell(2500, ['bgColor' => 'E8E8E8'])->addText('Location', ['bold' => true, 'size' => 9]);
            $table->addCell(1500, ['bgColor' => 'E8E8E8'])->addText('Phone', ['bold' => true, 'size' => 9]);

            foreach ($appointments as $apt) {
                $table->addRow();
                $table->addCell(1500)->addText($apt['date'] ?? '', ['size' => 9]);
                $table->addCell(1000)->addText($apt['time'] ?? '', ['size' => 9]);
                $table->addCell(2500)->addText($apt['provider'] ?? '', ['size' => 9]);
                $table->addCell(2500)->addText($apt['location'] ?? '', ['size' => 9]);
                $table->addCell(1500)->addText($apt['phone'] ?? '', ['size' => 9]);
            }
        } else {
            $section->addText('None scheduled', ['italic' => true]);
        }

        // Page 3
        $section->addPageBreak();
        $this->addHeader($section, $facility);
        $this->addResidentInfoLine($section, $resident, $discharge);

        // Special Needs
        $this->addSectionTitle($section, 'Special Needs');
        $section->addText($discharge->special_needs ?? 'None', [], ['spaceAfter' => 100]);

        // Medications
        $this->addSectionTitle($section, 'MEDICATIONS');
        $medications = $discharge->medications_at_discharge ?? [];
        $table = $section->addTable($borderStyle);
        $table->addRow();
        $table->addCell(4000, ['bgColor' => 'E8E8E8'])->addText('NAME', ['bold' => true, 'size' => 9]);
        $table->addCell(3000, ['bgColor' => 'E8E8E8'])->addText('DOSAGE', ['bold' => true, 'size' => 9]);
        $table->addCell(2000, ['bgColor' => 'E8E8E8'])->addText('QUANTITY', ['bold' => true, 'size' => 9]);

        if (! empty($medications)) {
            foreach ($medications as $med) {
                $table->addRow();
                $table->addCell(4000)->addText($med['name'] ?? '', ['size' => 9]);
                $table->addCell(3000)->addText($med['dosage'] ?? '', ['size' => 9]);
                $table->addCell(2000)->addText($med['quantity'] ?? '', ['size' => 9]);
            }
        } else {
            $table->addRow();
            $table->addCell(4000)->addText('', ['size' => 9]);
            $table->addCell(3000)->addText('', ['size' => 9]);
            $table->addCell(2000)->addText('', ['size' => 9]);
        }

        // Personal Possessions
        $this->addSectionTitle($section, 'PERSONAL POSSESSIONS');
        $section->addText($discharge->personal_possessions ?? 'Client maintained possession of all personal belongings during treatment.', [], ['spaceAfter' => 200]);

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'discharge_').'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }

    private function addHeader($section, array $facility): void
    {
        $section->addText(
            strtoupper($facility['facility_name']),
            ['bold' => true, 'size' => 12, 'underline' => 'single'],
            ['alignment' => 'center']
        );
        $section->addText(
            $this->getFormattedAddress(),
            ['size' => 10, 'underline' => 'single'],
            ['alignment' => 'center']
        );

        $contactLine = [];
        if ($facility['email']) {
            $contactLine[] = 'Email: '.$facility['email'];
        }
        if ($facility['phone']) {
            $contactLine[] = 'M: '.$facility['phone'];
        }

        if (! empty($contactLine)) {
            $section->addText(
                implode('   ', $contactLine),
                ['size' => 10, 'underline' => 'single'],
                ['alignment' => 'center', 'spaceAfter' => 100]
            );
        }
    }

    private function addResidentInfoLine($section, $resident, $discharge): void
    {
        $section->addText(
            "Name of Resident: {$resident->full_name}   DOB: ".($resident->date_of_birth?->format('m/d/Y') ?? 'N/A').'   AHCCCS ID#: ___',
            ['size' => 10],
            ['spaceBefore' => 100]
        );
        $section->addText(
            'Date of Intake: '.($resident->admission_date?->format('m/d/Y') ?? 'N/A')."   Today's Date: ".now()->format('m/d/Y'),
            ['size' => 10],
            ['spaceAfter' => 100]
        );
    }

    private function addSectionTitle($section, string $title): void
    {
        $section->addText($title, ['bold' => true, 'size' => 10], ['spaceBefore' => 150, 'spaceAfter' => 50]);
    }

    private function addLabelValue($section, string $label, string $value): void
    {
        $textRun = $section->addTextRun();
        $textRun->addText($label.' ', ['bold' => true, 'size' => 10]);
        $textRun->addText($value, ['size' => 10]);
    }
}
