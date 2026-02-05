<?php

use App\Models\Agency;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Agency')]
class extends Component {
    #[Locked]
    public int $agencyId;

    public string $name = '';
    public string $phone = '';
    public string $address = '';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(Agency $agency): void
    {
        $this->agencyId = $agency->id;
        $this->name = $agency->name;
        $this->phone = $agency->phone ?? '';
        $this->address = $agency->address ?? '';
        $this->notes = $agency->notes ?? '';
        $this->is_active = $agency->is_active;
    }

    #[Computed]
    public function agency(): Agency
    {
        return Agency::findOrFail($this->agencyId);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function updateAgency(): void
    {
        $validated = $this->validate();

        $this->agency->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        $this->dispatch('agency-updated');
    }

    public function deleteAgency(): void
    {
        if (!$this->agency->is_institution) {
            $this->agency->delete();
            session()->flash('status', 'Agency deleted successfully.');
            $this->redirect(route('admin.agencies.index'), navigate: true);
        }
    }
}; ?>

<flux:main>
    <x-pages.admin.settings-layout :heading="__('Edit Agency')" :subheading="$this->agency->name">
        <div class="space-y-8 max-w-2xl">
            @if($this->agency->is_institution)
                <flux:callout icon="information-circle" color="blue">
                    <flux:callout.heading>{{ __('Institution Agency') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('This is the primary institution agency. It cannot be deleted or deactivated, but you can update its information.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            <form wire:submit="updateAgency" class="space-y-6">
                <flux:card>
                    <flux:heading size="sm" class="mb-4">{{ __('Agency Information') }}</flux:heading>

                    <div class="space-y-4">
                        <flux:input
                            wire:model="name"
                            :label="__('Name')"
                            :description="__('The agency or organization name')"
                            required
                        />

                        <flux:input
                            wire:model="phone"
                            :label="__('Phone')"
                            :description="__('Contact phone number')"
                            placeholder="e.g., 1-800-555-0123"
                        />

                        <flux:input
                            wire:model="address"
                            :label="__('Address')"
                            :description="__('Physical address (optional)')"
                        />

                        <flux:textarea
                            wire:model="notes"
                            :label="__('Notes')"
                            :description="__('Additional information about this agency')"
                            rows="3"
                        />
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading size="sm" class="mb-4">{{ __('Status') }}</flux:heading>

                    @if($this->agency->is_institution)
                        <div class="flex items-center gap-2 text-zinc-500">
                            <flux:icon name="lock-closed" class="size-4" />
                            <span>{{ __('Institution agencies are always active') }}</span>
                        </div>
                    @else
                        <flux:switch
                            wire:model="is_active"
                            :label="__('Active')"
                            :description="__('Inactive agencies will not appear in discharge forms')"
                        />
                    @endif
                </flux:card>

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        {{ __('Save Changes') }}
                    </flux:button>
                    <x-action-message on="agency-updated">{{ __('Saved.') }}</x-action-message>
                </div>
            </form>

            @unless($this->agency->is_institution)
                <flux:card>
                    <flux:heading size="sm" class="mb-2 text-red-600">{{ __('Danger Zone') }}</flux:heading>
                    <flux:subheading class="mb-4">
                        {{ __('Deleting an agency will remove it permanently. This action cannot be undone.') }}
                    </flux:subheading>

                    <flux:button
                        variant="danger"
                        wire:click="deleteAgency"
                        wire:confirm="{{ __('Are you sure you want to delete this agency?') }}"
                        icon="trash"
                    >
                        {{ __('Delete Agency') }}
                    </flux:button>
                </flux:card>
            @endunless

            <flux:button variant="ghost" :href="route('admin.agencies.index')" wire:navigate icon="arrow-left">
                {{ __('Back to Agencies') }}
            </flux:button>
        </div>
    </x-pages.admin.settings-layout>
</flux:main>
