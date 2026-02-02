<?php

use App\Concerns\CarePlanValidationRules;
use App\Models\CarePlan;
use App\Models\Resident;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Create Care Plan')]
class extends Component {
    use CarePlanValidationRules;

    #[Locked]
    public ?int $preselectedResidentId = null;

    public string $resident_id = '';
    public string $title = '';
    public string $type = 'general';
    public string $status = 'draft';
    public string $start_date = '';
    public ?string $review_date = null;
    public string $description = '';
    public string $goals = '';
    public string $interventions = '';
    public string $notes = '';

    public bool $isGenerating = false;

    public function mount(?Resident $resident = null): void
    {
        $this->start_date = now()->format('Y-m-d');

        if ($resident && $resident->exists) {
            $this->preselectedResidentId = $resident->id;
            $this->resident_id = (string) $resident->id;
        }
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name.' (Room '.$r->room_number.')'])
            ->toArray();
    }

    #[Computed]
    public function preselectedResident(): ?Resident
    {
        if ($this->preselectedResidentId) {
            return Resident::find($this->preselectedResidentId);
        }

        return null;
    }

    #[Computed]
    public function canUseAi(): bool
    {
        try {
            $aiManager = app(AiManager::class);
            return $aiManager->isUseCaseEnabled('care_assistant')
                && $aiManager->isConfigured($aiManager->getUseCaseProvider('care_assistant'));
        } catch (\Exception) {
            return false;
        }
    }

    public function aiSuggest(): void
    {
        if (!$this->canUseAi || !$this->resident_id) {
            return;
        }

        $this->isGenerating = true;

        try {
            $aiManager = app(AiManager::class);
            $resident = Resident::find($this->resident_id);

            if (!$resident) {
                return;
            }

            $existingPlans = CarePlan::where('resident_id', $resident->id)
                ->active()
                ->pluck('title', 'type')
                ->map(fn ($title, $type) => "{$title} ({$type})")
                ->implode(', ');

            $prompt = "You are a care home assistant. Generate a care plan suggestion for a resident.\n\n";
            $prompt .= "Resident Details:\n";
            $prompt .= "- Name: {$resident->full_name}\n";
            $prompt .= "- Age: {$resident->age} years old\n";
            $prompt .= "- Gender: {$resident->gender}\n";
            $prompt .= "- Mobility Status: {$resident->mobility_status}\n";
            $prompt .= "- Fall Risk Level: {$resident->fall_risk_level}\n";

            if ($resident->medical_conditions) {
                $prompt .= "- Medical Conditions: {$resident->medical_conditions}\n";
            }
            if ($resident->allergies) {
                $prompt .= "- Allergies: {$resident->allergies}\n";
            }
            if ($resident->dietary_requirements) {
                $prompt .= "- Dietary Requirements: {$resident->dietary_requirements}\n";
            }

            if ($existingPlans) {
                $prompt .= "\nExisting active care plans (avoid duplicating): {$existingPlans}\n";
            }

            $prompt .= "\nBased on this resident's profile, suggest a care plan that addresses their most important needs.\n";
            $prompt .= "Respond ONLY with a JSON object (no markdown, no code fences, just raw JSON) with these keys:\n";
            $prompt .= '- "title": a concise care plan title' . "\n";
            $prompt .= '- "type": one of general, nutrition, mobility, mental_health, personal_care, medication, social' . "\n";
            $prompt .= '- "description": 2-3 sentences describing the purpose and scope' . "\n";
            $prompt .= '- "goals": 2-4 specific, measurable goals' . "\n";
            $prompt .= '- "interventions": 3-5 specific actions and strategies to achieve the goals' . "\n";

            $response = $aiManager->executeForUseCase('care_assistant', $prompt);

            if ($response->success) {
                $content = trim($response->content);
                $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
                $content = preg_replace('/\s*```$/m', '', $content);

                $data = json_decode($content, true);

                if (is_array($data)) {
                    $this->title = is_string($data['title'] ?? '') ? $data['title'] : '';
                    if (isset($data['type']) && in_array($data['type'], ['general', 'nutrition', 'mobility', 'mental_health', 'personal_care', 'medication', 'social'])) {
                        $this->type = $data['type'];
                    }
                    $this->description = $this->arrayToString($data['description'] ?? '');
                    $this->goals = $this->arrayToString($data['goals'] ?? '');
                    $this->interventions = $this->arrayToString($data['interventions'] ?? '');
                }
            }
        } catch (\Exception) {
            // Silent fail - user can fill manually
        } finally {
            $this->isGenerating = false;
        }
    }

    protected function arrayToString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            // Handle nested arrays by flattening
            $flattened = [];
            array_walk_recursive($value, function ($item) use (&$flattened) {
                if (is_string($item)) {
                    $flattened[] = $item;
                }
            });

            return implode("\n", $flattened);
        }

        return '';
    }

    public function save(): void
    {
        $validated = $this->validate($this->carePlanRules(residentRequired: true));

        $validated['created_by'] = auth()->id();

        $carePlan = CarePlan::create($validated);

        session()->flash('status', 'Care plan created successfully.');
        $this->redirect(route('care-plans.show', $carePlan), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="$preselectedResidentId ? route('residents.show', $preselectedResidentId) : route('care-plans.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Create Care Plan') }}</flux:heading>
                <flux:subheading>{{ __('Create a new care plan for a resident') }}</flux:subheading>
            </div>
        </div>

        {{-- Preselected Resident Card --}}
        @if($this->preselectedResident)
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    @if($this->preselectedResident->photo_path)
                        <img src="{{ Storage::url($this->preselectedResident->photo_path) }}" alt="" class="size-12 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" />
                    @else
                        <flux:avatar name="{{ $this->preselectedResident->full_name }}" />
                    @endif
                    <div>
                        <flux:text class="font-medium">{{ $this->preselectedResident->full_name }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">
                            {{ $this->preselectedResident->age }} {{ __('years old') }}
                            @if($this->preselectedResident->room_number)
                                &middot; {{ __('Room') }} {{ $this->preselectedResident->room_number }}
                            @endif
                        </flux:text>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <flux:badge size="sm" :color="match($this->preselectedResident->status) {
                            'active' => 'green',
                            'discharged' => 'amber',
                            'deceased' => 'red',
                            'on_leave' => 'blue',
                            default => 'zinc',
                        }">
                            {{ str_replace('_', ' ', ucfirst($this->preselectedResident->status)) }}
                        </flux:badge>
                        @if($this->canUseAi)
                            <flux:button
                                variant="primary"
                                size="sm"
                                wire:click="aiSuggest"
                                wire:loading.attr="disabled"
                                wire:target="aiSuggest"
                                icon="sparkles"
                            >
                                <span wire:loading.remove wire:target="aiSuggest">{{ __('AI Suggest') }}</span>
                                <span wire:loading wire:target="aiSuggest">{{ __('Generating...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- Care Plan Details --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Care Plan Details') }}</flux:heading>
                    @if($this->canUseAi && !$preselectedResidentId && $resident_id)
                        <flux:button
                            variant="primary"
                            size="sm"
                            wire:click="aiSuggest"
                            wire:loading.attr="disabled"
                            wire:target="aiSuggest"
                            icon="sparkles"
                        >
                            <span wire:loading.remove wire:target="aiSuggest">{{ __('AI Suggest') }}</span>
                            <span wire:loading wire:target="aiSuggest">{{ __('Generating...') }}</span>
                        </flux:button>
                    @endif
                </div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required :placeholder="__('e.g., Daily Mobility Plan')" />
                    </div>

                    @if(!$preselectedResidentId)
                        <div class="sm:col-span-2">
                            <flux:select wire:model.live="resident_id" :label="__('Resident')" required>
                                <flux:select.option value="">{{ __('Select a resident...') }}</flux:select.option>
                                @foreach($this->residents as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @error('resident_id')
                                <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                            @enderror
                        </div>
                    @endif

                    <flux:select wire:model="type" :label="__('Type')" required>
                        <flux:select.option value="general">{{ __('General') }}</flux:select.option>
                        <flux:select.option value="nutrition">{{ __('Nutrition') }}</flux:select.option>
                        <flux:select.option value="mobility">{{ __('Mobility') }}</flux:select.option>
                        <flux:select.option value="mental_health">{{ __('Mental Health') }}</flux:select.option>
                        <flux:select.option value="personal_care">{{ __('Personal Care') }}</flux:select.option>
                        <flux:select.option value="medication">{{ __('Medication') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('Social') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="under_review">{{ __('Under Review') }}</flux:select.option>
                        <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="start_date" :label="__('Start Date')" type="date" required />
                    <flux:input wire:model="review_date" :label="__('Review Date')" type="date" />
                </div>
            </flux:card>

            {{-- Description & Goals --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Description & Goals') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="description" :label="__('Description')" rows="3" :placeholder="__('Describe the purpose and scope of this care plan...')" />
                <flux:textarea wire:model="goals" :label="__('Goals')" rows="3" :placeholder="__('What are the desired outcomes for the resident...')" />
            </flux:card>

            {{-- Interventions & Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Interventions & Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="interventions" :label="__('Interventions')" rows="3" :placeholder="__('Actions and strategies to achieve the goals...')" />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" :placeholder="__('Any additional notes...')" />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="$preselectedResidentId ? route('residents.show', $preselectedResidentId) : route('care-plans.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Create Care Plan') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
