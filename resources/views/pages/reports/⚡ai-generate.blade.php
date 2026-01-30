<?php

use App\Models\Incident;
use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\Qualification;
use App\Models\Resident;
use App\Models\Shift;
use App\Models\StaffProfile;
use App\Models\Vital;
use App\Services\AI\AiManager;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('AI Report Generator')]
class extends Component {
    public string $reportType = 'resident_summary';
    public ?string $residentId = null;
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $customInstructions = '';

    public ?string $generatedReport = null;
    public ?string $reportModel = null;
    public ?int $reportTokens = null;
    public ?float $reportTime = null;
    public ?string $reportError = null;

    #[Computed]
    public function aiEnabled(): bool
    {
        return app(AiManager::class)->isEnabled();
    }

    #[Computed]
    public function reportGenerationEnabled(): bool
    {
        $config = app(AiManager::class)->getUseCaseConfig('report_generation');

        return $config['enabled'] ?? false;
    }

    #[Computed]
    public function reportTypes(): array
    {
        return [
            'resident_summary' => 'Resident Summary',
            'clinical_analysis' => 'Clinical Analysis',
            'incident_review' => 'Incident Review',
            'operational_overview' => 'Operational Overview',
            'custom' => 'Custom Report',
        ];
    }

    #[Computed]
    public function residents()
    {
        return Resident::active()->orderBy('first_name')->get();
    }

    public function generateReport(): void
    {
        $this->validate([
            'reportType' => 'required|in:'.implode(',', array_keys($this->reportTypes)),
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
        ]);

        $this->generatedReport = null;
        $this->reportError = null;
        $this->reportModel = null;
        $this->reportTokens = null;
        $this->reportTime = null;

        $prompt = $this->buildPrompt();

        $aiManager = app(AiManager::class);
        $response = $aiManager->executeForUseCase('report_generation', $prompt);

        if ($response->success) {
            $this->generatedReport = $response->content;
            $this->reportModel = $response->model;
            $this->reportTokens = ($response->promptTokens ?? 0) + ($response->completionTokens ?? 0);
            $this->reportTime = $response->responseTime;
        } else {
            $this->reportError = $response->error;
        }
    }

    private function buildPrompt(): string
    {
        $typeName = $this->reportTypes[$this->reportType] ?? 'Report';
        $prompt = "Generate a professional {$typeName} for a care home facility.\n\n";

        if ($this->dateFrom || $this->dateTo) {
            $prompt .= 'Date range: ';
            $prompt .= $this->dateFrom ? "from {$this->dateFrom} " : '';
            $prompt .= $this->dateTo ? "to {$this->dateTo}" : '';
            $prompt .= "\n\n";
        }

        $prompt .= $this->gatherDataContext();

        if (! empty($this->customInstructions)) {
            $prompt .= "\n\nAdditional instructions: {$this->customInstructions}";
        }

        $prompt .= "\n\nFormat the report with clear headings and sections. Use markdown formatting.";

        return $prompt;
    }

    private function gatherDataContext(): string
    {
        $context = "Current data from the care home system:\n\n";

        switch ($this->reportType) {
            case 'resident_summary':
                $context .= $this->getResidentContext();
                break;
            case 'clinical_analysis':
                $context .= $this->getClinicalContext();
                break;
            case 'incident_review':
                $context .= $this->getIncidentContext();
                break;
            case 'operational_overview':
                $context .= $this->getOperationalContext();
                break;
            case 'custom':
                $context .= $this->getResidentContext();
                $context .= $this->getClinicalContext();
                $context .= $this->getOperationalContext();
                break;
        }

        return $context;
    }

    private function getResidentContext(): string
    {
        $context = "=== RESIDENT DATA ===\n";

        if ($this->residentId) {
            $resident = Resident::with(['carePlans', 'medications', 'vitals', 'incidents'])->find($this->residentId);
            if ($resident) {
                $context .= "Focus Resident: {$resident->full_name}\n";
                $context .= "Age: {$resident->age}, Gender: {$resident->gender}\n";
                $context .= "Room: {$resident->room_number}\n";
                $context .= "Mobility: {$resident->mobility_status}, Fall Risk: {$resident->fall_risk_level}\n";
                $context .= "Medical Conditions: {$resident->medical_conditions}\n";
                $context .= "Allergies: {$resident->allergies}\n";
                $context .= "Active Care Plans: {$resident->carePlans()->where('status', 'active')->count()}\n";
                $context .= "Active Medications: {$resident->medications()->where('status', 'active')->count()}\n";
                $context .= "Recent Incidents: {$resident->incidents()->where('occurred_at', '>=', now()->subDays(30))->count()}\n\n";
            }
        } else {
            $activeCount = Resident::active()->count();
            $maleCount = Resident::active()->where('gender', 'male')->count();
            $femaleCount = Resident::active()->where('gender', 'female')->count();
            $dnrCount = Resident::active()->where('dnr_status', true)->count();
            $highFallRisk = Resident::active()->where('fall_risk_level', 'high')->count();

            $context .= "Total Active Residents: {$activeCount}\n";
            $context .= "Gender Distribution: {$maleCount} male, {$femaleCount} female\n";
            $context .= "Residents with DNR: {$dnrCount}\n";
            $context .= "High Fall Risk Residents: {$highFallRisk}\n\n";
        }

        return $context;
    }

    private function getClinicalContext(): string
    {
        $context = "=== CLINICAL DATA ===\n";

        $activeMeds = Medication::active()->count();
        $totalLogs = MedicationLog::count();
        $givenLogs = MedicationLog::where('status', 'given')->count();
        $complianceRate = $totalLogs > 0 ? round(($givenLogs / $totalLogs) * 100, 1) : 0;

        $context .= "Active Medications: {$activeMeds}\n";
        $context .= "Medication Compliance Rate: {$complianceRate}% ({$givenLogs}/{$totalLogs} doses given)\n";

        $abnormalVitals = Vital::query()
            ->latest('recorded_at')
            ->limit(50)
            ->get()
            ->filter(fn ($v) => $v->hasAbnormalValues())
            ->count();

        $context .= "Recent Abnormal Vitals: {$abnormalVitals} (out of last 50 readings)\n";

        $openIncidents = Incident::open()->count();
        $criticalIncidents = Incident::where('severity', 'critical')->where('status', '!=', 'closed')->count();

        $context .= "Open Incidents: {$openIncidents}\n";
        $context .= "Critical Open Incidents: {$criticalIncidents}\n\n";

        return $context;
    }

    private function getIncidentContext(): string
    {
        $context = "=== INCIDENT DATA ===\n";

        $totalIncidents = Incident::count();
        $openIncidents = Incident::open()->count();

        $byType = Incident::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $bySeverity = Incident::selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        $context .= "Total Incidents: {$totalIncidents}\n";
        $context .= "Open Incidents: {$openIncidents}\n\n";

        $context .= "By Type:\n";
        foreach ($byType as $type => $count) {
            $context .= "  - {$type}: {$count}\n";
        }

        $context .= "\nBy Severity:\n";
        foreach ($bySeverity as $severity => $count) {
            $context .= "  - {$severity}: {$count}\n";
        }

        $recentCritical = Incident::where('severity', 'critical')
            ->latest('occurred_at')
            ->limit(3)
            ->get();

        if ($recentCritical->count() > 0) {
            $context .= "\nRecent Critical Incidents:\n";
            foreach ($recentCritical as $incident) {
                $context .= "  - {$incident->title} ({$incident->occurred_at->format('M d, Y')}): {$incident->status}\n";
            }
        }

        $context .= "\n";

        return $context;
    }

    private function getOperationalContext(): string
    {
        $context = "=== OPERATIONAL DATA ===\n";

        $activeStaff = StaffProfile::active()->count();
        $staffByDept = StaffProfile::active()
            ->whereNotNull('department')
            ->selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->pluck('count', 'department')
            ->toArray();

        $context .= "Active Staff: {$activeStaff}\n";
        $context .= "Staff by Department:\n";
        foreach ($staffByDept as $dept => $count) {
            $context .= "  - {$dept}: {$count}\n";
        }

        $shiftsToday = Shift::today()->count();
        $shiftsThisWeek = Shift::where('shift_date', '>=', now()->startOfWeek())
            ->where('shift_date', '<=', now()->endOfWeek())
            ->count();

        $context .= "\nShifts Today: {$shiftsToday}\n";
        $context .= "Shifts This Week: {$shiftsThisWeek}\n";

        $expiredQuals = Qualification::where('expiry_date', '<', now())->count();
        $expiringSoon = Qualification::expiringSoon(30)->count();

        $context .= "\nExpired Qualifications: {$expiredQuals}\n";
        $context .= "Qualifications Expiring in 30 Days: {$expiringSoon}\n\n";

        return $context;
    }

    public function clearReport(): void
    {
        $this->generatedReport = null;
        $this->reportModel = null;
        $this->reportTokens = null;
        $this->reportTime = null;
        $this->reportError = null;
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <flux:button variant="ghost" size="sm" :href="route('reports.index')" wire:navigate icon="arrow-left" class="mb-2">
                {{ __('All Reports') }}
            </flux:button>
            <flux:heading size="xl">{{ __('AI Report Generator') }}</flux:heading>
            <flux:subheading>{{ __('Generate AI-powered reports from your care home data') }}</flux:subheading>
        </div>

        @if(!$this->aiEnabled)
            {{-- AI Disabled --}}
            <flux:card>
                <div class="py-12 text-center">
                    <flux:icon name="cpu-chip" class="mx-auto size-12 text-zinc-400" />
                    <flux:heading size="sm" class="mt-4">{{ __('AI Integration Disabled') }}</flux:heading>
                    <flux:subheading class="mt-2">
                        {{ __('Enable AI integration in') }}
                        <flux:link :href="route('admin.settings.ai')" wire:navigate>{{ __('AI settings') }}</flux:link>
                        {{ __('to generate reports.') }}
                    </flux:subheading>
                </div>
            </flux:card>
        @elseif(!$this->reportGenerationEnabled)
            {{-- Report Generation Disabled --}}
            <flux:card>
                <div class="py-12 text-center">
                    <flux:icon name="document-text" class="mx-auto size-12 text-zinc-400" />
                    <flux:heading size="sm" class="mt-4">{{ __('Report Generation Disabled') }}</flux:heading>
                    <flux:subheading class="mt-2">
                        {{ __('The report generation AI use case is disabled. Enable it in') }}
                        <flux:link :href="route('admin.settings.ai')" wire:navigate>{{ __('AI settings') }}</flux:link>.
                    </flux:subheading>
                </div>
            </flux:card>
        @else
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Left: Form --}}
                <div>
                    <flux:card>
                        <form wire:submit="generateReport" class="space-y-4">
                            <flux:select wire:model.live="reportType" :label="__('Report Type')">
                                @foreach($this->reportTypes as $value => $label)
                                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model="residentId" :label="__('Specific Resident (optional)')">
                                <flux:select.option value="">{{ __('All Residents') }}</flux:select.option>
                                @foreach($this->residents as $resident)
                                    <flux:select.option :value="$resident->id">{{ $resident->full_name }} (Room {{ $resident->room_number }})</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:input wire:model="dateFrom" type="date" :label="__('Date From')" />
                            <flux:input wire:model="dateTo" type="date" :label="__('Date To')" />

                            <flux:textarea wire:model="customInstructions" :label="__('Custom Instructions')" rows="3"
                                :placeholder="__('Any specific focus areas or formatting preferences...')" />

                            <flux:button variant="primary" type="submit" class="w-full" icon="sparkles"
                                wire:loading.attr="disabled" wire:target="generateReport">
                                <span wire:loading.remove wire:target="generateReport">{{ __('Generate Report') }}</span>
                                <span wire:loading wire:target="generateReport">{{ __('Generating...') }}</span>
                            </flux:button>
                        </form>
                    </flux:card>
                </div>

                {{-- Right: Generated Report --}}
                <div class="lg:col-span-2">
                    @if($reportError)
                        <flux:card>
                            <flux:callout variant="danger" icon="exclamation-triangle">
                                <flux:callout.heading>{{ __('Generation Failed') }}</flux:callout.heading>
                                <flux:callout.text>{{ $reportError }}</flux:callout.text>
                            </flux:callout>
                        </flux:card>
                    @elseif($generatedReport)
                        <flux:card>
                            <div class="mb-4 flex items-center justify-between">
                                <flux:heading size="sm">{{ __('Generated Report') }}</flux:heading>
                                <flux:button variant="ghost" size="sm" wire:click="clearReport" icon="trash">
                                    {{ __('Clear') }}
                                </flux:button>
                            </div>

                            {{-- Report content --}}
                            <div class="prose prose-sm max-w-none dark:prose-invert">
                                {!! Str::markdown($generatedReport) !!}
                            </div>

                            {{-- AI Metadata --}}
                            <flux:separator class="my-4" />
                            <div class="flex flex-wrap gap-4 text-xs text-zinc-500">
                                @if($reportModel)
                                    <span><strong>{{ __('Model') }}:</strong> {{ $reportModel }}</span>
                                @endif
                                @if($reportTokens)
                                    <span><strong>{{ __('Tokens') }}:</strong> {{ $reportTokens }}</span>
                                @endif
                                @if($reportTime)
                                    <span><strong>{{ __('Time') }}:</strong> {{ round($reportTime, 2) }}s</span>
                                @endif
                            </div>
                        </flux:card>
                    @else
                        {{-- Empty State --}}
                        <flux:card>
                            <div class="py-12 text-center">
                                <flux:icon name="document-text" class="mx-auto size-12 text-zinc-400" />
                                <flux:heading size="sm" class="mt-4">{{ __('No report generated yet') }}</flux:heading>
                                <flux:subheading class="mt-2">
                                    {{ __('Select a report type and click Generate to create an AI-powered report.') }}
                                </flux:subheading>
                            </div>
                        </flux:card>
                    @endif
                </div>
            </div>
        @endif
    </div>
</flux:main>
