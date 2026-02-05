<?php

use App\Models\Agency;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Add Agency')]
class extends Component {
    public string $name = '';
    public string $phone = '';
    public string $address = '';
    public string $notes = '';
    public bool $is_active = true;

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

    public function createAgency(): void
    {
        $validated = $this->validate();

        Agency::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        session()->flash('status', 'Agency created successfully.');
        $this->redirect(route('admin.agencies.index'), navigate: true);
    }
}; ?>

<flux:main>
    <x-pages.admin.settings-layout :heading="__('Add Agency')" :subheading="__('Add a new agency or contact for discharge coordination')">
        <form wire:submit="createAgency" class="space-y-6 max-w-2xl">
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Agency Information') }}</flux:heading>

                <div class="space-y-4">
                    <flux:input
                        wire:model="name"
                        :label="__('Name')"
                        :description="__('The agency or organization name')"
                        required
                        autofocus
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

                <flux:switch
                    wire:model="is_active"
                    :label="__('Active')"
                    :description="__('Inactive agencies will not appear in discharge forms')"
                />
            </flux:card>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Create Agency') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('admin.agencies.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </x-pages.admin.settings-layout>
</flux:main>
