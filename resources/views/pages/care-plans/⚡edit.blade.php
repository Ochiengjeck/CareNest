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

    public function save(): void
    {
        $validated = $this->validate($this->carePlanRules());

        $carePlan = CarePlan::findOrFail($this->carePlanId);

        // Keep the original resident_id
        $validated['resident_id'] = $this->residentId;

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
                <flux:subheading>{{ $this->carePlan->resident?->full_name }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Care Plan Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Care Plan Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required />
                    </div>

                    <div class="sm:col-span-2">
                        <flux:input :value="$this->carePlan->resident?->full_name" :label="__('Resident')" disabled />
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
