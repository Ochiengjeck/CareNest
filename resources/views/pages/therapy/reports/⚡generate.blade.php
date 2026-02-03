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
    public bool $showAdvanced = false;
    public string $previewTab = 'document';
    public bool $documentGenerated = false;

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
    public function reportTypeConfig(): array
    {
        return [
            'individual_session' => [
                'label' => 'Session Note',
                'description' => 'Single session documentation',
                'icon' => 'document-text',
            ],
            'progress_summary' => [
                'label' => 'Progress Summary',
                'description' => 'Multi-session overview',
                'icon' => 'chart-bar',
            ],
            'therapist_caseload' => [
                'label' => 'Caseload Report',
                'description' => 'Therapist workload analysis',
                'icon' => 'clipboard-document-list',
            ],
            'resident_history' => [
                'label' => 'Therapy History',
                'description' => 'Complete resident record',
                'icon' => 'clock',
            ],
        ];
    }

    #[Computed]
    public function exportUrls(): array
    {
        return match ($this->reportType) {
            'individual_session' => $this->sessionId ? [
                'pdf' => route('therapy.reports.export.individual.pdf', $this->sessionId),
                'word' => route('therapy.reports.export.individual.word', $this->sessionId),
            ] : [],
            'progress_summary' => $this->residentId ? [
                'pdf' => route('therapy.reports.export.progress-summary.pdf', [
                    'resident_id' => $this->residentId,
                    'date_from' => $this->dateFrom,
                    'date_to' => $this->dateTo,
                ]),
                'word' => route('therapy.reports.export.progress-summary.word', [
                    'resident_id' => $this->residentId,
                    'date_from' => $this->dateFrom,
                    'date_to' => $this->dateTo,
                ]),
            ] : [],
            'therapist_caseload' => $this->therapistId ? [
                'pdf' => route('therapy.reports.export.therapist-caseload.pdf', [
                    'therapist_id' => $this->therapistId,
                    'date_from' => $this->dateFrom,
                    'date_to' => $this->dateTo,
                ]),
                'word' => route('therapy.reports.export.therapist-caseload.word', [
                    'therapist_id' => $this->therapistId,
                    'date_from' => $this->dateFrom,
                    'date_to' => $this->dateTo,
                ]),
            ] : [],
            'resident_history' => $this->residentId ? [
                'pdf' => route('therapy.reports.export.resident-history.pdf', [
                    'resident_id' => $this->residentId,
                ]),
                'word' => route('therapy.reports.export.resident-history.word', [
                    'resident_id' => $this->residentId,
                ]),
            ] : [],
            default => [],
        };
    }

    #[Computed]
    public function previewPdfUrl(): ?string
    {
        if (empty($this->exportUrls)) {
            return null;
        }
        return $this->exportUrls['pdf'] . (str_contains($this->exportUrls['pdf'], '?') ? '&' : '?') . 'preview=1';
    }

    #[Computed]
    public function canGenerate(): bool
    {
        return match ($this->reportType) {
            'individual_session' => !empty($this->sessionId),
            'progress_summary', 'resident_history' => !empty($this->residentId),
            'therapist_caseload' => !empty($this->therapistId),
            default => false,
        };
    }

    #[Computed]
    public function hasValidSelection(): bool
    {
        return match ($this->reportType) {
            'individual_session' => !empty($this->sessionId),
            'progress_summary', 'resident_history' => !empty($this->residentId),
            'therapist_caseload' => !empty($this->therapistId),
            default => false,
        };
    }

    public function setReportType(string $type): void
    {
        $this->reportType = $type;
        $this->generatedReport = '';
        $this->errorMessage = '';
        $this->documentGenerated = false;
    }

    public function setPreviewTab(string $tab): void
    {
        $this->previewTab = $tab;
    }

    public function generateReport(): void
    {
        $this->errorMessage = '';
        $this->generatedReport = '';

        if (!$this->canGenerate) {
            $this->errorMessage = 'Please select the required options for this report type.';
            return;
        }

        // Enable document preview (PDF will be generated on-demand when iframe loads)
        $this->documentGenerated = true;

        // If AI is not available, just enable the document preview without AI notes
        if (!$this->canUseAi) {
            $this->previewTab = 'document';
            return;
        }

        $this->isGenerating = true;
        $this->previewTab = 'ai_notes';

        try {
            $aiManager = app(AiManager::class);
            $prompt = $this->buildPrompt();

            $response = $aiManager->executeForUseCase('therapy_reporting', $prompt);

            if ($response->success) {
                $this->generatedReport = $response->content;
            } else {
                $this->errorMessage = $response->error ?? 'Failed to generate AI notes. Document preview is still available.';
                $this->previewTab = 'document';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'AI notes generation failed. Document preview is still available.';
            $this->previewTab = 'document';
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
        $this->documentGenerated = false;
    }
}; ?>

<flux:main>
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <flux:heading size="xl">{{ __('Therapy Reports') }}</flux:heading>
            <flux:subheading>{{ __('Generate reports and export formatted documents') }}</flux:subheading>
        </div>

        @if(!$this->canUseAi)
            <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
                <x-slot:heading>{{ __('AI Not Available') }}</x-slot:heading>
                {{ __('AI is not enabled. Documents can still be exported with raw data.') }}
            </flux:callout>
        @endif

        <div class="grid gap-8 lg:grid-cols-5">
            {{-- Left Panel: Configuration --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Report Type Selection --}}
                <flux:card class="p-0 overflow-hidden">
                    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Report Type') }}</flux:heading>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($this->reportTypeConfig as $type => $config)
                            <button
                                wire:click="setReportType('{{ $type }}')"
                                class="w-full flex items-center gap-4 p-4 text-left transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $reportType === $type ? 'bg-zinc-50 dark:bg-zinc-800/50' : '' }}"
                            >
                                <div class="flex-shrink-0 p-2 rounded-lg {{ $reportType === $type ? 'bg-accent text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400' }}">
                                    <flux:icon name="{{ $config['icon'] }}" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium {{ $reportType === $type ? 'text-accent' : '' }}">{{ $config['label'] }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $config['description'] }}</div>
                                </div>
                                @if($reportType === $type)
                                    <flux:icon name="check-circle" class="size-5 text-accent flex-shrink-0" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                </flux:card>

                {{-- Report Options --}}
                <flux:card>
                    <flux:heading size="sm" class="mb-4">{{ __('Options') }}</flux:heading>

                    <div class="space-y-4">
                        @if($reportType === 'individual_session')
                            <flux:select wire:model.live="sessionId" label="Session">
                                <flux:select.option value="">{{ __('Select a session...') }}</flux:select.option>
                                @foreach($this->sessions as $id => $label)
                                    <flux:select.option value="{{ $id }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if(in_array($reportType, ['progress_summary', 'resident_history']))
                            <flux:select wire:model.live="residentId" label="Resident">
                                <flux:select.option value="">{{ __('Select a resident...') }}</flux:select.option>
                                @foreach($this->residents as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if($reportType === 'therapist_caseload')
                            <flux:select wire:model.live="therapistId" label="Therapist">
                                <flux:select.option value="">{{ __('Select a therapist...') }}</flux:select.option>
                                @foreach($this->therapists as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if(in_array($reportType, ['progress_summary', 'therapist_caseload']))
                            <div class="grid grid-cols-2 gap-3">
                                <flux:input wire:model.live="dateFrom" type="date" label="From" />
                                <flux:input wire:model.live="dateTo" type="date" label="To" />
                            </div>
                        @endif
                    </div>
                </flux:card>

                {{-- Advanced Options (Collapsed) --}}
                <flux:card class="overflow-hidden">
                    <button
                        wire:click="$toggle('showAdvanced')"
                        class="w-full flex items-center justify-between p-0 text-left"
                    >
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Advanced Options') }}</span>
                        <flux:icon name="{{ $showAdvanced ? 'chevron-up' : 'chevron-down' }}" class="size-4 text-zinc-400" />
                    </button>

                    @if($showAdvanced)
                        <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:textarea
                                wire:model="customInstructions"
                                label="Custom Instructions"
                                placeholder="Add specific instructions for AI generation..."
                                rows="3"
                            />
                            <p class="mt-2 text-xs text-zinc-500">{{ __('Used for AI Notes generation only.') }}</p>
                        </div>
                    @endif
                </flux:card>

                {{-- Action Buttons --}}
                <div class="flex gap-3">
                    <flux:button
                        variant="primary"
                        wire:click="generateReport"
                        wire:loading.attr="disabled"
                        :disabled="!$this->canGenerate"
                        class="flex-1"
                        icon="sparkles"
                    >
                        <span wire:loading.remove wire:target="generateReport">{{ __('Generate Report') }}</span>
                        <span wire:loading wire:target="generateReport">{{ __('Generating...') }}</span>
                    </flux:button>

                    @if(!empty($this->exportUrls))
                        <flux:dropdown>
                            <flux:button variant="outline" icon="arrow-down-tray" icon-trailing="chevron-down">
                                {{ __('Export') }}
                            </flux:button>
                            <flux:menu>
                                <a href="{{ $this->exportUrls['pdf'] }}" target="_blank">
                                    <flux:menu.item icon="document-arrow-down">{{ __('Download PDF') }}</flux:menu.item>
                                </a>
                                <a href="{{ $this->exportUrls['word'] }}" target="_blank">
                                    <flux:menu.item icon="document-text">{{ __('Download Word') }}</flux:menu.item>
                                </a>
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                </div>
            </div>

            {{-- Right Panel: Preview --}}
            <div class="lg:col-span-3">
                <flux:card class="h-full min-h-[650px] flex flex-col p-0 overflow-hidden">
                    {{-- Tab Header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <div class="flex gap-1">
                            <button
                                wire:click="setPreviewTab('document')"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $previewTab === 'document' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}"
                            >
                                <span class="flex items-center gap-2">
                                    <flux:icon name="document" class="size-4" />
                                    {{ __('Document Preview') }}
                                    @if($documentGenerated)
                                        <span class="size-2 rounded-full bg-green-500"></span>
                                    @endif
                                </span>
                            </button>
                            <button
                                wire:click="setPreviewTab('ai_notes')"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $previewTab === 'ai_notes' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}"
                            >
                                <span class="flex items-center gap-2">
                                    <flux:icon name="sparkles" class="size-4" />
                                    {{ __('AI Notes') }}
                                    @if($generatedReport)
                                        <span class="size-2 rounded-full bg-green-500"></span>
                                    @endif
                                </span>
                            </button>
                        </div>

                        @if($documentGenerated || $generatedReport)
                            <flux:button variant="ghost" size="sm" wire:click="clearReport" icon="x-mark">
                                {{ __('Clear') }}
                            </flux:button>
                        @endif
                    </div>

                    {{-- Content Area --}}
                    <div class="flex-1 overflow-hidden">
                        @if($previewTab === 'document')
                            {{-- Document Preview Tab --}}
                            @if($documentGenerated && !empty($this->exportUrls))
                                <div class="h-full flex flex-col">
                                    <div class="flex-1 bg-zinc-100 dark:bg-zinc-900">
                                        <iframe
                                            src="{{ $this->previewPdfUrl }}"
                                            class="w-full h-full border-0"
                                            title="Document Preview"
                                        ></iframe>
                                    </div>
                                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-zinc-500">{{ __('Document with AI-enhanced content') }}</p>
                                            <div class="flex gap-2">
                                                <a href="{{ $this->exportUrls['pdf'] }}" target="_blank">
                                                    <flux:button variant="outline" size="sm" icon="document-arrow-down">
                                                        {{ __('PDF') }}
                                                    </flux:button>
                                                </a>
                                                <a href="{{ $this->exportUrls['word'] }}" target="_blank">
                                                    <flux:button variant="outline" size="sm" icon="document-text">
                                                        {{ __('Word') }}
                                                    </flux:button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-full py-16 text-center px-6">
                                    <div class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-4">
                                        <flux:icon name="document" class="size-8 text-zinc-400" />
                                    </div>
                                    <h3 class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('No Document Preview') }}</h3>
                                    <p class="mt-2 text-sm text-zinc-500 max-w-xs">
                                        {{ __('Select your options and click "Generate Report" to preview the formatted document.') }}
                                    </p>
                                    @if($this->canGenerate)
                                        <flux:button
                                            variant="primary"
                                            wire:click="generateReport"
                                            class="mt-6"
                                            icon="sparkles"
                                            size="sm"
                                        >
                                            {{ __('Generate Report') }}
                                        </flux:button>
                                    @endif
                                </div>
                            @endif
                        @else
                            {{-- AI Notes Tab --}}
                            <div class="h-full overflow-auto p-6">
                                @if($errorMessage)
                                    <flux:callout variant="warning" icon="exclamation-circle" class="mb-4">
                                        {{ $errorMessage }}
                                    </flux:callout>
                                @endif

                                @if($isGenerating)
                                    <div class="flex flex-col items-center justify-center h-full py-16">
                                        <div class="relative">
                                            <div class="animate-spin rounded-full h-12 w-12 border-2 border-zinc-200 dark:border-zinc-700"></div>
                                            <div class="absolute inset-0 animate-spin rounded-full h-12 w-12 border-t-2 border-accent"></div>
                                        </div>
                                        <p class="mt-4 text-sm text-zinc-500">{{ __('Generating AI notes...') }}</p>
                                    </div>
                                @elseif($generatedReport)
                                    <article class="prose prose-sm dark:prose-invert max-w-none prose-headings:font-semibold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base">
                                        {!! Str::markdown($generatedReport) !!}
                                    </article>
                                @else
                                    <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                                        <div class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-4">
                                            <flux:icon name="sparkles" class="size-8 text-zinc-400" />
                                        </div>
                                        <h3 class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('No AI Notes') }}</h3>
                                        <p class="mt-2 text-sm text-zinc-500 max-w-xs">
                                            @if($this->canUseAi)
                                                {{ __('Click "Generate Report" to create AI-powered notes.') }}
                                            @else
                                                {{ __('AI is not available. Use the Document Preview tab instead.') }}
                                            @endif
                                        </p>
                                        @if($this->canGenerate && $this->canUseAi)
                                            <flux:button
                                                variant="primary"
                                                wire:click="generateReport"
                                                class="mt-6"
                                                icon="sparkles"
                                                size="sm"
                                            >
                                                {{ __('Generate Report') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</flux:main>
