<?php

use App\Concerns\TherapySessionValidationRules;
use App\Models\TherapySession;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Document Session')]
class extends Component {
    use TherapySessionValidationRules;

    #[Locked]
    public int $sessionId;

    public string $interventions = '';
    public string $progress_notes = '';
    public string $client_plan = '';
    public string $notes = '';

    public bool $isGenerating = false;
    public string $generateField = '';

    public function mount(TherapySession $session): void
    {
        $this->sessionId = $session->id;
        $this->interventions = $session->interventions ?? '';
        $this->progress_notes = $session->progress_notes ?? '';
        $this->client_plan = $session->client_plan ?? '';
        $this->notes = $session->notes ?? '';
    }

    #[Computed]
    public function session(): TherapySession
    {
        return TherapySession::with(['therapist', 'resident'])->findOrFail($this->sessionId);
    }

    #[Computed]
    public function canUseAi(): bool
    {
        try {
            $aiManager = app(AiManager::class);
            return $aiManager->isUseCaseEnabled('therapy_reporting')
                && $aiManager->isConfigured($aiManager->getUseCaseProvider('therapy_reporting'));
        } catch (\Exception) {
            return false;
        }
    }

    public function generateInterventions(): void
    {
        $this->generateField('interventions', 'provider support and interventions used during the session');
    }

    public function generateProgressNotes(): void
    {
        $this->generateField('progress_notes', 'client\'s specific progress on treatment plan, problems, goals, action steps, objectives, and referrals');
    }

    public function generateClientPlan(): void
    {
        $this->generateField('client_plan', 'client\'s plan including new issues or problems that affect the treatment plan');
    }

    protected function generateField(string $field, string $description): void
    {
        if (!$this->canUseAi) {
            return;
        }

        $this->isGenerating = true;
        $this->generateField = $field;

        try {
            $aiManager = app(AiManager::class);
            $session = $this->session;

            $context = "You are documenting a therapy session. Generate professional clinical documentation for the {$description}.\n\n";
            $context .= "Session Details:\n";
            $context .= "- Client: {$session->resident->full_name}\n";
            $context .= "- Date: {$session->session_date->format('F d, Y')}\n";
            $context .= "- Time: {$session->formatted_time_range}\n";
            $context .= "- Service Type: {$session->service_type_label}\n";
            $context .= "- Session Topic: {$session->session_topic}\n";

            if ($session->challenge_label) {
                $context .= "- Treatment Plan Index: {$session->challenge_label}\n";
            }

            if ($session->resident->medical_conditions) {
                $context .= "- Client's Medical Conditions: {$session->resident->medical_conditions}\n";
            }

            // Include other fields if already filled
            if ($field !== 'interventions' && $this->interventions) {
                $context .= "\nInterventions used: {$this->interventions}\n";
            }
            if ($field !== 'progress_notes' && $this->progress_notes) {
                $context .= "\nProgress observed: {$this->progress_notes}\n";
            }

            $prompt = $context . "\n\nGenerate a professional, detailed {$description}. Use clinical terminology appropriate for healthcare records. Write in third person. Be specific and evidence-based. Keep it concise but comprehensive (2-4 paragraphs).";

            $response = $aiManager->executeForUseCase('therapy_reporting', $prompt);

            if ($response->success) {
                $this->{$field} = trim($response->content);
            }
        } catch (\Exception $e) {
            // Silent fail - user can still type manually
        } finally {
            $this->isGenerating = false;
            $this->generateField = '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->therapySessionDocumentRules());
        $validated['updated_by'] = auth()->id();

        // Mark as completed if still scheduled
        if ($this->session->status === 'scheduled') {
            $validated['status'] = 'completed';
        }

        $this->session->update($validated);

        session()->flash('status', 'Session documented successfully.');
        $this->redirect(route('therapy.sessions.show', $this->session), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.show', $this->session)" wire:navigate icon="arrow-left">
                    {{ __('Back') }}
                </flux:button>
            </div>
            <flux:heading size="xl">{{ __('Document Session') }}</flux:heading>
            <flux:subheading>
                {{ $this->session->session_date->format('F d, Y') }} - {{ $this->session->resident->full_name }}
            </flux:subheading>
        </div>

        {{-- Session Summary --}}
        <flux:card class="bg-zinc-50 dark:bg-zinc-800/50">
            <div class="grid gap-4 sm:grid-cols-4 text-sm">
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Service Type') }}</div>
                    <div class="mt-1">
                        <flux:badge :color="$this->session->service_type_color">{{ $this->session->service_type_label }}</flux:badge>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Time') }}</div>
                    <div class="mt-1 font-medium">{{ $this->session->formatted_time_range }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Topic') }}</div>
                    <div class="mt-1 font-medium">{{ Str::limit($this->session->session_topic, 30) }}</div>
                </div>
                @if($this->session->challenge_label)
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Tx Plan Index') }}</div>
                    <div class="mt-1 font-medium">{{ $this->session->challenge_label }}</div>
                </div>
                @endif
            </div>
        </flux:card>

        <form wire:submit="save" class="space-y-6">
            {{-- Provider Support & Interventions --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __('Provider Support & Interventions') }}</flux:heading>
                    @if($this->canUseAi)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            wire:click="generateInterventions"
                            wire:loading.attr="disabled"
                            wire:target="generateInterventions"
                            icon="sparkles"
                        >
                            <span wire:loading.remove wire:target="generateInterventions">{{ __('AI Assist') }}</span>
                            <span wire:loading wire:target="generateInterventions">{{ __('Generating...') }}</span>
                        </flux:button>
                    @endif
                </div>

                <flux:textarea
                    wire:model="interventions"
                    placeholder="Describe the interventions and support provided during the session. Include techniques used, therapeutic approaches, and staff observations..."
                    rows="5"
                    required
                />
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Document the therapeutic techniques, exercises, and support provided to the client during this session.') }}
                </p>
            </flux:card>

            {{-- Client's Progress --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __("Client's Specific Progress") }}</flux:heading>
                    @if($this->canUseAi)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            wire:click="generateProgressNotes"
                            wire:loading.attr="disabled"
                            wire:target="generateProgressNotes"
                            icon="sparkles"
                        >
                            <span wire:loading.remove wire:target="generateProgressNotes">{{ __('AI Assist') }}</span>
                            <span wire:loading wire:target="generateProgressNotes">{{ __('Generating...') }}</span>
                        </flux:button>
                    @endif
                </div>

                <flux:textarea
                    wire:model="progress_notes"
                    placeholder="Describe the client's specific progress on treatment plan, problems, goals, action steps, objectives, and/or referrals..."
                    rows="6"
                    required
                />
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Document the client\'s engagement, responses, progress toward goals, and any significant observations.') }}
                </p>
            </flux:card>

            {{-- Client's Plan --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __("Client's Plan") }}</flux:heading>
                    @if($this->canUseAi)
                        <flux:button
                            variant="ghost"
                            size="sm"
                            wire:click="generateClientPlan"
                            wire:loading.attr="disabled"
                            wire:target="generateClientPlan"
                            icon="sparkles"
                        >
                            <span wire:loading.remove wire:target="generateClientPlan">{{ __('AI Assist') }}</span>
                            <span wire:loading wire:target="generateClientPlan">{{ __('Generating...') }}</span>
                        </flux:button>
                    @endif
                </div>

                <flux:textarea
                    wire:model="client_plan"
                    placeholder="Document the plan moving forward, including new issues or problems that affect the treatment plan, recommendations, and next steps..."
                    rows="4"
                    required
                />
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Document recommendations, follow-up actions, and any adjustments to the treatment plan.') }}
                </p>
            </flux:card>

            {{-- Additional Notes --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Additional Notes') }}</flux:heading>

                <flux:textarea
                    wire:model="notes"
                    placeholder="Any additional notes or observations..."
                    rows="3"
                />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('therapy.sessions.show', $this->session)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Save Documentation') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
