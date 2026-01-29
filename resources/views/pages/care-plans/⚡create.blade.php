<?php

use App\Concerns\CarePlanValidationRules;
use App\Models\CarePlan;
use App\Models\Resident;
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
                @if($this->preselectedResident)
                    <flux:subheading>{{ __('For') }} {{ $this->preselectedResident->full_name }}</flux:subheading>
                @else
                    <flux:subheading>{{ __('Create a new care plan for a resident') }}</flux:subheading>
                @endif
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Care Plan Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Care Plan Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required :placeholder="__('e.g., Daily Mobility Plan')" />
                    </div>

                    @if($preselectedResidentId)
                        <div class="sm:col-span-2">
                            <flux:input :value="$this->preselectedResident->full_name" :label="__('Resident')" disabled />
                        </div>
                    @else
                        <div class="sm:col-span-2">
                            <flux:select wire:model="resident_id" :label="__('Resident')" required>
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
