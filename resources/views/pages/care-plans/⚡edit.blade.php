<?php

use App\Concerns\CarePlanValidationRules;
use App\Models\CarePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Care Plan')]
class extends Component {
    use CarePlanValidationRules;

    #[Locked]
    public int $carePlanId;

    #[Locked]
    public int $residentId;

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

    public function mount(CarePlan $carePlan): void
    {
        $this->carePlanId = $carePlan->id;
        $this->residentId = $carePlan->resident_id;
        $this->resident_id = (string) $carePlan->resident_id;
        $this->title = $carePlan->title;
        $this->type = $carePlan->type;
        $this->status = $carePlan->status;
        $this->start_date = $carePlan->start_date->format('Y-m-d');
        $this->review_date = $carePlan->review_date?->format('Y-m-d');
        $this->description = $carePlan->description ?? '';
        $this->goals = $carePlan->goals ?? '';
        $this->interventions = $carePlan->interventions ?? '';
        $this->notes = $carePlan->notes ?? '';
    }

    #[Computed]
    public function carePlan(): CarePlan
    {
        return CarePlan::with('resident')->findOrFail($this->carePlanId);
    }

    #[Computed]
    public function residentIsInactive(): bool
    {
        return $this->carePlan->resident && $this->carePlan->resident->isInactive();
    }

    public function save(): void
    {
        $validated = $this->validate($this->carePlanRules());

        $carePlan = CarePlan::findOrFail($this->carePlanId);

        // Keep the original resident_id
        $validated['resident_id'] = $this->residentId;

        // Restrict status for inactive residents
        if ($this->residentIsInactive && !in_array($validated['status'], ['archived'])) {
            $this->addError('status', __('Only "Archived" status is allowed for a :status resident.', ['status' => $carePlan->resident->status]));
            return;
        }

        $carePlan->update($validated);

        session()->flash('status', 'Care plan updated successfully.');
        $this->redirect(route('care-plans.show', $carePlan), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('care-plans.show', $carePlanId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Edit Care Plan') }}</flux:heading>
                <flux:subheading>{{ $title }}</flux:subheading>
            </div>
        </div>

        {{-- Resident Card --}}
        @if($this->carePlan->resident)
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    @if($this->carePlan->resident->photo_path)
                        <img src="{{ Storage::url($this->carePlan->resident->photo_path) }}" alt="" class="size-12 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" />
                    @else
                        <flux:avatar name="{{ $this->carePlan->resident->full_name }}" />
                    @endif
                    <div>
                        <flux:text class="font-medium">{{ $this->carePlan->resident->full_name }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">
                            {{ $this->carePlan->resident->age }} {{ __('years old') }}
                            @if($this->carePlan->resident->room_number)
                                &middot; {{ __('Room') }} {{ $this->carePlan->resident->room_number }}
                            @endif
                        </flux:text>
                    </div>
                    <flux:badge size="sm" :color="match($this->carePlan->resident->status) {
                        'active' => 'green',
                        'discharged' => 'amber',
                        'deceased' => 'red',
                        'on_leave' => 'blue',
                        default => 'zinc',
                    }" class="ml-auto">
                        {{ str_replace('_', ' ', ucfirst($this->carePlan->resident->status)) }}
                    </flux:badge>
                </div>
            </flux:card>
        @endif

        @if($this->residentIsInactive)
            <flux:callout icon="exclamation-triangle" color="amber">
                <flux:callout.heading>{{ __('Resident is :status', ['status' => $this->carePlan->resident->status]) }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('This care plan can only be archived. New care plans cannot be created for this resident.') }}
                </flux:callout.text>
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- Care Plan Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Care Plan Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required />
                    </div>

                    <flux:select wire:model="type" :label="__('Type')" required>
                        <flux:select.option value="general">{{ __('General') }}</flux:select.option>
                        <flux:select.option value="nutrition">{{ __('Nutrition') }}</flux:select.option>
                        <flux:select.option value="mobility">{{ __('Mobility') }}</flux:select.option>
                        <flux:select.option value="mental_health">{{ __('Mental Health') }}</flux:select.option>
                        <flux:select.option value="personal_care">{{ __('Personal Care') }}</flux:select.option>
                        <flux:select.option value="medication">{{ __('Medication') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('Social') }}</flux:select.option>
                    </flux:select>

                    @if($this->residentIsInactive)
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                        </flux:select>
                    @else
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                            <flux:select.option value="under_review">{{ __('Under Review') }}</flux:select.option>
                            <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                        </flux:select>
                    @endif

                    <flux:input wire:model="start_date" :label="__('Start Date')" type="date" required />
                    <flux:input wire:model="review_date" :label="__('Review Date')" type="date" />
                </div>
            </flux:card>

            {{-- Description & Goals --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Description & Goals') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="description" :label="__('Description')" rows="3" />
                <flux:textarea wire:model="goals" :label="__('Goals')" rows="3" />
            </flux:card>

            {{-- Interventions & Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Interventions & Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="interventions" :label="__('Interventions')" rows="3" />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('care-plans.show', $carePlanId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
