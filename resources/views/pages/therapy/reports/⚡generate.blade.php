<?php

use App\Models\Resident;
use App\Models\TherapySession;
use App\Models\User;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Generate Therapy Report')]
class extends Component {
    #[Url]
    public string $reportType = 'individual_session';

    #[Url]
    public string $sessionId = '';

    #[Url]
    public string $residentId = '';

    #[Url]
    public string $therapistId = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public string $customInstructions = '';
    public string $generatedReport = '';
    public bool $isGenerating = false;
    public string $errorMessage = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        if (request()->has('session')) {
            $this->sessionId = (string) request()->get('session');
            $this->reportType = 'individual_session';
        }
    }

    #[Computed]
    public function canUseAi(): bool
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

    #[Computed]
    public function sessions(): array
    {
        return TherapySession::query()
            ->completed()
            ->with(['resident', 'therapist'])
            ->latest('session_date')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn ($s) => [
                $s->id => $s->session_date->format('M d, Y') . ' - ' . $s->resident->full_name . ' (' . $s->session_topic . ')'
            ])
            ->toArray();
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name])
            ->toArray();
    }

    #[Computed]
    public function therapists(): array
    {
        return User::role('therapist')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function reportTypes(): array
    {
        return [
            'individual_session' => 'Individual Session Note',
            'progress_summary' => 'Progress Summary (Multi-Session)',
            'therapist_caseload' => 'Therapist Caseload Report',
            'resident_history' => 'Resident Therapy History',
        ];
    }

    public function generateReport(): void
    {
        $this->errorMessage = '';
        $this->generatedReport = '';

        if (!$this->canUseAi) {
            $this->errorMessage = 'AI therapy reporting is not enabled or configured. Please contact your administrator.';
            return;
        }

        // Validate inputs based on report type
        if ($this->reportType === 'individual_session' && !$this->sessionId) {
            $this->errorMessage = 'Please select a session for individual session reports.';
            return;
        }

        if (in_array($this->reportType, ['progress_summary', 'resident_history']) && !$this->residentId) {
            $this->errorMessage = 'Please select a resident for this report type.';
            return;
        }

        if ($this->reportType === 'therapist_caseload' && !$this->therapistId) {
            $this->errorMessage = 'Please select a therapist for caseload reports.';
            return;
        }

        $this->isGenerating = true;

        try {
            $aiManager = app(AiManager::class);
            $prompt = $this->buildPrompt();

            $response = $aiManager->executeForUseCase('therapy_reporting', $prompt);

            if ($response->success) {
                $this->generatedReport = $response->content;
            } else {
                $this->errorMessage = $response->error ?? 'Failed to generate report. Please try again.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while generating the report. Please try again.';
        } finally {
            $this->isGenerating = false;
        }
    }

    protected function buildPrompt(): string
    {
        return match ($this->reportType) {
            'individual_session' => $this->buildIndividualSessionPrompt(),
            'progress_summary' => $this->buildProgressSummaryPrompt(),
            'therapist_caseload' => $this->buildCaseloadPrompt(),
            'resident_history' => $this->buildResidentHistoryPrompt(),
            default => '',
        };
    }

    protected function buildIndividualSessionPrompt(): string
    {
        $session = TherapySession::with(['resident', 'therapist'])->find($this->sessionId);

        if (!$session) {
            return '';
        }

        $prompt = "Generate a professional, comprehensive therapy session note in a clinical format.\n\n";
        $prompt .= "SESSION DETAILS:\n";
        $prompt .= "- Client Name: {$session->resident->full_name}\n";
        $prompt .= "- Date of Birth: " . ($session->resident->date_of_birth?->format('m/d/Y') ?? 'N/A') . "\n";
        $prompt .= "- Service Date: {$session->session_date->format('m/d/Y')}\n";
        $prompt .= "- Start Time: " . Carbon\Carbon::parse($session->start_time)->format('g:i A') . "\n";
        $prompt .= "- End Time: " . Carbon\Carbon::parse($session->end_time)->format('g:i A') . "\n";
        $prompt .= "- Service Type: {$session->service_type_label}\n";
        $prompt .= "- Treatment Plan Index: " . ($session->challenge_label ?? 'N/A') . "\n";
        $prompt .= "- Session Topic: {$session->session_topic}\n";
        $prompt .= "- Therapist: {$session->therapist->name}\n\n";

        if ($session->interventions) {
            $prompt .= "PROVIDER SUPPORT & INTERVENTIONS:\n{$session->interventions}\n\n";
        }

        if ($session->progress_notes) {
            $prompt .= "CLIENT'S PROGRESS NOTES:\n{$session->progress_notes}\n\n";
        }

        if ($session->client_plan) {
            $prompt .= "CLIENT'S PLAN:\n{$session->client_plan}\n\n";
        }

        if ($session->resident->medical_conditions) {
            $prompt .= "CLIENT'S MEDICAL CONDITIONS:\n{$session->resident->medical_conditions}\n\n";
        }

        $prompt .= "INSTRUCTIONS:\n";
        $prompt .= "Generate a formal therapy session note that:\n";
        $prompt .= "1. Summarizes the session objectives and therapeutic approach\n";
        $prompt .= "2. Documents specific interventions used and client engagement\n";
        $prompt .= "3. Describes client's progress, responses, and behavioral observations\n";
        $prompt .= "4. Includes recommendations for continued care\n";
        $prompt .= "5. Uses professional clinical terminology\n";
        $prompt .= "6. Maintains a third-person narrative style\n\n";

        if ($this->customInstructions) {
            $prompt .= "ADDITIONAL INSTRUCTIONS:\n{$this->customInstructions}\n\n";
        }

        $prompt .= "Format the report with clear sections using markdown headers.";

        return $prompt;
    }

    protected function buildProgressSummaryPrompt(): string
    {
        $resident = Resident::find($this->residentId);
        $sessions = TherapySession::query()
            ->forResident($this->residentId)
            ->completed()
            ->inDateRange($this->dateFrom, $this->dateTo)
            ->with('therapist')
            ->orderBy('session_date')
            ->get();

        if (!$resident || $sessions->isEmpty()) {
            return '';
        }

        $prompt = "Generate a comprehensive therapy progress summary report.\n\n";
        $prompt .= "CLIENT INFORMATION:\n";
        $prompt .= "- Name: {$resident->full_name}\n";
        $prompt .= "- Date of Birth: " . ($resident->date_of_birth?->format('m/d/Y') ?? 'N/A') . "\n";
        $prompt .= "- Report Period: {$this->dateFrom} to {$this->dateTo}\n";
        $prompt .= "- Total Sessions: {$sessions->count()}\n\n";

        if ($resident->medical_conditions) {
            $prompt .= "MEDICAL CONDITIONS:\n{$resident->medical_conditions}\n\n";
        }

        $prompt .= "SESSION HISTORY:\n";
        foreach ($sessions as $session) {
            $prompt .= "---\n";
            $prompt .= "Date: {$session->session_date->format('m/d/Y')} | Type: {$session->service_type_label}\n";
            $prompt .= "Topic: {$session->session_topic}\n";
            if ($session->progress_notes) {
                $prompt .= "Progress: " . Str::limit($session->progress_notes, 300) . "\n";
            }
        }

        $prompt .= "\n\nINSTRUCTIONS:\n";
        $prompt .= "Generate a progress summary report that:\n";
        $prompt .= "1. Provides an executive summary of the client's therapy journey\n";
        $prompt .= "2. Identifies patterns in engagement and progress\n";
        $prompt .= "3. Highlights key achievements and milestones\n";
        $prompt .= "4. Notes areas requiring continued focus\n";
        $prompt .= "5. Includes recommendations for future treatment\n\n";

        if ($this->customInstructions) {
            $prompt .= "ADDITIONAL INSTRUCTIONS:\n{$this->customInstructions}\n\n";
        }

        $prompt .= "Format with clear sections using markdown.";

        return $prompt;
    }

    protected function buildCaseloadPrompt(): string
    {
        $therapist = User::find($this->therapistId);
        $sessions = TherapySession::query()
            ->forTherapist($this->therapistId)
            ->inDateRange($this->dateFrom, $this->dateTo)
            ->with('resident')
            ->get();

        if (!$therapist) {
            return '';
        }

        $completedCount = $sessions->where('status', 'completed')->count();
        $uniqueResidents = $sessions->pluck('resident_id')->unique()->count();

        $prompt = "Generate a therapist caseload and performance report.\n\n";
        $prompt .= "THERAPIST: {$therapist->name}\n";
        $prompt .= "REPORT PERIOD: {$this->dateFrom} to {$this->dateTo}\n\n";
        $prompt .= "STATISTICS:\n";
        $prompt .= "- Total Sessions: {$sessions->count()}\n";
        $prompt .= "- Completed Sessions: {$completedCount}\n";
        $prompt .= "- Unique Residents Served: {$uniqueResidents}\n";
        $prompt .= "- Cancelled/No-Show: " . $sessions->whereIn('status', ['cancelled', 'no_show'])->count() . "\n\n";

        $prompt .= "SERVICE TYPE BREAKDOWN:\n";
        $byType = $sessions->groupBy('service_type');
        foreach ($byType as $type => $typeSessions) {
            $prompt .= "- " . ($typeSessions->first()->service_type_label ?? $type) . ": " . $typeSessions->count() . "\n";
        }

        $prompt .= "\n\nINSTRUCTIONS:\n";
        $prompt .= "Generate a professional caseload report that:\n";
        $prompt .= "1. Summarizes the therapist's productivity and caseload\n";
        $prompt .= "2. Analyzes session distribution and patterns\n";
        $prompt .= "3. Highlights workload balance and utilization\n";
        $prompt .= "4. Provides recommendations for caseload management\n\n";

        if ($this->customInstructions) {
            $prompt .= "ADDITIONAL INSTRUCTIONS:\n{$this->customInstructions}\n\n";
        }

        return $prompt;
    }

    protected function buildResidentHistoryPrompt(): string
    {
        $resident = Resident::find($this->residentId);
        $sessions = TherapySession::query()
            ->forResident($this->residentId)
            ->with('therapist')
            ->orderBy('session_date')
            ->get();

        if (!$resident) {
            return '';
        }

        $prompt = "Generate a comprehensive therapy history report for a resident.\n\n";
        $prompt .= "CLIENT INFORMATION:\n";
        $prompt .= "- Name: {$resident->full_name}\n";
        $prompt .= "- Date of Birth: " . ($resident->date_of_birth?->format('m/d/Y') ?? 'N/A') . "\n";
        $prompt .= "- Admission Date: " . ($resident->admission_date?->format('m/d/Y') ?? 'N/A') . "\n";
        $prompt .= "- Total Therapy Sessions: {$sessions->count()}\n";
        $prompt .= "- Completed Sessions: " . $sessions->where('status', 'completed')->count() . "\n\n";

        if ($resident->medical_conditions) {
            $prompt .= "MEDICAL CONDITIONS:\n{$resident->medical_conditions}\n\n";
        }

        $prompt .= "COMPLETE SESSION HISTORY:\n";
        foreach ($sessions as $session) {
            $prompt .= "---\n";
            $prompt .= "Date: {$session->session_date->format('m/d/Y')} | Status: {$session->status_label}\n";
            $prompt .= "Type: {$session->service_type_label} | Therapist: {$session->therapist->name}\n";
            $prompt .= "Topic: {$session->session_topic}\n";
            if ($session->challenge_label) {
                $prompt .= "Focus Area: {$session->challenge_label}\n";
            }
        }

        $prompt .= "\n\nINSTRUCTIONS:\n";
        $prompt .= "Generate a comprehensive therapy history report that:\n";
        $prompt .= "1. Provides a complete overview of the client's therapy engagement\n";
        $prompt .= "2. Tracks treatment focus areas over time\n";
        $prompt .= "3. Summarizes therapeutic approaches used\n";
        $prompt .= "4. Identifies long-term patterns and trends\n";
        $prompt .= "5. Assesses overall treatment compliance and engagement\n\n";

        if ($this->customInstructions) {
            $prompt .= "ADDITIONAL INSTRUCTIONS:\n{$this->customInstructions}\n\n";
        }

        return $prompt;
    }

    public function clearReport(): void
    {
        $this->generatedReport = '';
        $this->errorMessage = '';
    }
}; ?>

<flux:main>
    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Generate Therapy Report') }}</flux:heading>
            <flux:subheading>{{ __('Use AI to generate professional therapy session reports') }}</flux:subheading>
        </div>

        @if(!$this->canUseAi)
            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-start gap-4">
                    <flux:icon name="exclamation-triangle" class="h-6 w-6 text-amber-600 dark:text-amber-400 flex-shrink-0" />
                    <div>
                        <flux:heading size="sm">{{ __('AI Not Available') }}</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            {{ __('AI therapy reporting is not enabled or configured. Please contact your administrator to enable this feature in System Settings > AI Configuration.') }}
                        </p>
                    </div>
                </div>
            </flux:card>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Report Options --}}
            <div class="lg:col-span-1 space-y-6">
                <flux:card>
                    <flux:heading size="sm" class="mb-4">{{ __('Report Type') }}</flux:heading>

                    <div class="space-y-4">
                        <flux:select wire:model.live="reportType" label="Report Type">
                            @foreach($this->reportTypes as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        @if($reportType === 'individual_session')
                            <flux:select wire:model="sessionId" label="Select Session">
                                <flux:select.option value="">{{ __('Choose a session...') }}</flux:select.option>
                                @foreach($this->sessions as $id => $label)
                                    <flux:select.option value="{{ $id }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if(in_array($reportType, ['progress_summary', 'resident_history']))
                            <flux:select wire:model="residentId" label="Select Resident">
                                <flux:select.option value="">{{ __('Choose a resident...') }}</flux:select.option>
                                @foreach($this->residents as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if($reportType === 'therapist_caseload')
                            <flux:select wire:model="therapistId" label="Select Therapist">
                                <flux:select.option value="">{{ __('Choose a therapist...') }}</flux:select.option>
                                @foreach($this->therapists as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if(in_array($reportType, ['progress_summary', 'therapist_caseload']))
                            <flux:input
                                wire:model="dateFrom"
                                type="date"
                                label="From Date"
                            />

                            <flux:input
                                wire:model="dateTo"
                                type="date"
                                label="To Date"
                            />
                        @endif
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading size="sm" class="mb-4">{{ __('Custom Instructions') }}</flux:heading>

                    <flux:textarea
                        wire:model="customInstructions"
                        placeholder="Add any specific instructions for the AI (e.g., focus on specific aspects, include particular details, formatting preferences)..."
                        rows="4"
                    />
                </flux:card>

                <flux:button
                    variant="primary"
                    wire:click="generateReport"
                    wire:loading.attr="disabled"
                    :disabled="!$this->canUseAi"
                    class="w-full"
                    icon="sparkles"
                >
                    <span wire:loading.remove wire:target="generateReport">{{ __('Generate Report') }}</span>
                    <span wire:loading wire:target="generateReport">{{ __('Generating...') }}</span>
                </flux:button>
            </div>

            {{-- Generated Report --}}
            <div class="lg:col-span-2">
                <flux:card class="min-h-[500px]">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="sm">{{ __('Generated Report') }}</flux:heading>
                        @if($generatedReport)
                            <flux:button variant="ghost" size="sm" wire:click="clearReport" icon="x-mark">
                                {{ __('Clear') }}
                            </flux:button>
                        @endif
                    </div>

                    @if($errorMessage)
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 mb-4">
                            <div class="flex items-start gap-3">
                                <flux:icon name="exclamation-circle" class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0" />
                                <p class="text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</p>
                            </div>
                        </div>
                    @endif

                    @if($isGenerating)
                        <div class="flex flex-col items-center justify-center py-16">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 theme-accent-spinner"></div>
                            <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Generating your report...') }}</p>
                        </div>
                    @elseif($generatedReport)
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! Str::markdown($generatedReport) !!}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <flux:icon name="document-text" class="h-16 w-16 text-zinc-300 dark:text-zinc-600" />
                            <h3 class="mt-4 text-lg font-medium">{{ __('No Report Generated') }}</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 max-w-sm">
                                {{ __('Select a report type and the required options, then click "Generate Report" to create your AI-powered therapy report.') }}
                            </p>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>
    </div>
</flux:main>
