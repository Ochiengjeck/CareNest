<?php

use App\Concerns\MedicationValidationRules;
use App\Models\Medication;
use App\Models\Resident;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Add Medication')]
class extends Component {
    use MedicationValidationRules;

    public string $resident_id = '';
    public string $name = '';
    public string $dosage = '';
    public string $frequency = '';
    public string $route = 'oral';
    public string $prescribed_by = '';
    public string $prescribed_date = '';
    public string $start_date = '';
    public ?string $end_date = null;
    public string $status = 'active';
    public string $instructions = '';
    public string $notes = '';

    public bool $isGenerating = false;

    public function mount(): void
    {
        $this->prescribed_date = now()->format('Y-m-d');
        $this->start_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name . ' (Room ' . $r->room_number . ')'])
            ->toArray();
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

            $existingMeds = Medication::where('resident_id', $resident->id)
                ->active()
                ->pluck('name')
                ->implode(', ');

            $prompt = "You are a clinical pharmacist assistant. Suggest a medication prescription for a care home resident.\n\n";
            $prompt .= "Resident Details:\n";
            $prompt .= "- Name: {$resident->full_name}\n";
            $prompt .= "- Age: {$resident->age} years old\n";
            $prompt .= "- Gender: {$resident->gender}\n";

            if ($resident->medical_conditions) {
                $prompt .= "- Medical Conditions: {$resident->medical_conditions}\n";
            }
            if ($resident->allergies) {
                $prompt .= "- Allergies: {$resident->allergies}\n";
            }

            if ($existingMeds) {
                $prompt .= "\nCurrently prescribed medications (avoid duplicates, consider interactions): {$existingMeds}\n";
            }

            $prompt .= "\nBased on this resident's conditions and existing medications, suggest a new medication that would benefit them.\n";
            $prompt .= "Respond ONLY with a JSON object (no markdown, no code fences, just raw JSON) with these keys:\n";
            $prompt .= '- "name": the medication name (generic name)' . "\n";
            $prompt .= '- "dosage": specific dosage (e.g., "500mg", "10ml")' . "\n";
            $prompt .= '- "frequency": how often to take it (e.g., "Twice daily", "Every 8 hours")' . "\n";
            $prompt .= '- "route": one of oral, topical, injection, inhalation, sublingual, rectal, other' . "\n";
            $prompt .= '- "instructions": administration instructions and special considerations' . "\n";

            $response = $aiManager->executeForUseCase('care_assistant', $prompt);

            if ($response->success) {
                $content = trim($response->content);
                $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
                $content = preg_replace('/\s*```$/m', '', $content);

                $data = json_decode($content, true);

                if (is_array($data)) {
                    $this->name = $data['name'] ?? '';
                    $this->dosage = $data['dosage'] ?? '';
                    $this->frequency = $data['frequency'] ?? '';
                    if (isset($data['route']) && in_array($data['route'], ['oral', 'topical', 'injection', 'inhalation', 'sublingual', 'rectal', 'other'])) {
                        $this->route = $data['route'];
                    }
                    $this->instructions = is_array($data['instructions'] ?? '') ? implode("\n", $data['instructions']) : ($data['instructions'] ?? '');
                }
            }
        } catch (\Exception) {
            // Silent fail - user can fill manually
        } finally {
            $this->isGenerating = false;
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->medicationRules());

        $resident = Resident::findOrFail($validated['resident_id']);
        if ($resident->isInactive()) {
            $this->addError('resident_id', __('Cannot add medication for a :status resident.', ['status' => $resident->status]));
            return;
        }

        $validated['created_by'] = auth()->id();

        $medication = Medication::create($validated);

        session()->flash('status', 'Medication added successfully.');
        $this->redirect(route('medications.show', $medication), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('medications.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Add Medication') }}</flux:heading>
                <flux:subheading>{{ __('Create a new medication prescription') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Medication Details --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Medication Details') }}</flux:heading>
                    @if($this->canUseAi && $resident_id)
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
                        <flux:select wire:model.live="resident_id" :label="__('Resident')" required>
                            <flux:select.option value="">{{ __('Select a resident...') }}</flux:select.option>
                            @foreach($this->residents as $id => $residentName)
                                <flux:select.option value="{{ $id }}">{{ $residentName }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('resident_id')
                            <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <flux:input wire:model="name" :label="__('Medication Name')" required placeholder="e.g., Metformin, Lisinopril" />
                    </div>

                    <flux:input wire:model="dosage" :label="__('Dosage')" required placeholder="e.g., 500mg, 10ml" />
                    <flux:input wire:model="frequency" :label="__('Frequency')" required placeholder="e.g., Twice daily, Every 8 hours" />

                    <flux:select wire:model="route" :label="__('Route')" required>
                        <flux:select.option value="oral">{{ __('Oral') }}</flux:select.option>
                        <flux:select.option value="topical">{{ __('Topical') }}</flux:select.option>
                        <flux:select.option value="injection">{{ __('Injection') }}</flux:select.option>
                        <flux:select.option value="inhalation">{{ __('Inhalation') }}</flux:select.option>
                        <flux:select.option value="sublingual">{{ __('Sublingual') }}</flux:select.option>
                        <flux:select.option value="rectal">{{ __('Rectal') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="on_hold">{{ __('On Hold') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                        <flux:select.option value="discontinued">{{ __('Discontinued') }}</flux:select.option>
                    </flux:select>
                </div>
            </flux:card>

            {{-- Prescription Info --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Prescription Info') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="prescribed_by" :label="__('Prescribed By')" required placeholder="Doctor's name" />
                    <flux:input wire:model="prescribed_date" :label="__('Prescribed Date')" type="date" required />
                    <flux:input wire:model="start_date" :label="__('Start Date')" type="date" required />
                    <flux:input wire:model="end_date" :label="__('End Date')" type="date" />
                </div>
            </flux:card>

            {{-- Instructions & Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Instructions & Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="instructions" :label="__('Instructions')" rows="3" placeholder="Administration instructions, special considerations..." />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" placeholder="Any additional notes..." />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('medications.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Add Medication') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
