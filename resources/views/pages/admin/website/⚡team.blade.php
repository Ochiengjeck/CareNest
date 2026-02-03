<?php

use App\Concerns\PublicWebsiteValidationRules;
use App\Models\TeamMember;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app.sidebar')]
#[Title('Manage Team Members')]
class extends Component {
    use PublicWebsiteValidationRules, WithFileUploads;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $role = '';
    public string $bio = '';
    public $photo = null;
    public ?string $current_photo = null;
    public int $sort_order = 0;

    #[Computed]
    public function members()
    {
        return TeamMember::ordered()->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $member = TeamMember::findOrFail($id);

        $this->editingId = $member->id;
        $this->name = $member->name;
        $this->role = $member->role;
        $this->bio = $member->bio ?? '';
        $this->current_photo = $member->photo;
        $this->sort_order = $member->sort_order;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate($this->teamMemberRules());

        $data = [
            'name' => $this->name,
            'role' => $this->role,
            'bio' => $this->bio ?: null,
            'sort_order' => $this->sort_order,
        ];

        if ($this->photo) {
            if ($this->current_photo) {
                Storage::disk('public')->delete($this->current_photo);
            }
            $data['photo'] = $this->photo->store('team', 'public');
        }

        if ($this->editingId) {
            TeamMember::find($this->editingId)->update($data);
        } else {
            TeamMember::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
        unset($this->members);
    }

    public function delete(int $id): void
    {
        $member = TeamMember::findOrFail($id);

        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();
        unset($this->members);
    }

    public function toggleActive(int $id): void
    {
        $member = TeamMember::findOrFail($id);
        $member->update(['is_active' => !$member->is_active]);
        unset($this->members);
    }

    public function removePhoto(): void
    {
        if ($this->current_photo && $this->editingId) {
            Storage::disk('public')->delete($this->current_photo);
            TeamMember::find($this->editingId)->update(['photo' => null]);
            $this->current_photo = null;
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->role = '';
        $this->bio = '';
        $this->photo = null;
        $this->current_photo = null;
        $this->sort_order = 0;
        $this->resetValidation();
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('Team Members')"
        :subheading="__('Manage leadership team displayed on the about page')">

        <div class="space-y-6">
            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="create" icon="plus">
                    {{ __('Add Team Member') }}
                </flux:button>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($this->members as $member)
                    <flux:card class="relative">
                        @if(!$member->is_active)
                            <div class="absolute top-2 right-2">
                                <flux:badge color="zinc">Inactive</flux:badge>
                            </div>
                        @endif

                        <div class="text-center">
                            @if($member->photo)
                                <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->name }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-4">
                            @else
                                <div class="w-24 h-24 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center mx-auto mb-4">
                                    <flux:icon.user class="size-10 text-zinc-400" />
                                </div>
                            @endif

                            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $member->name }}</h3>
                            <p class="text-sm text-accent">{{ $member->role }}</p>
                            @if($member->bio)
                                <p class="text-sm text-zinc-500 mt-2">{{ Str::limit($member->bio, 60) }}</p>
                            @endif

                            <div class="flex justify-center gap-2 mt-4">
                                <flux:button variant="ghost" size="xs" wire:click="edit({{ $member->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="xs" wire:click="toggleActive({{ $member->id }})" icon="{{ $member->is_active ? 'eye-slash' : 'eye' }}" />
                                <flux:button variant="ghost" size="xs" wire:click="delete({{ $member->id }})" wire:confirm="Are you sure you want to delete this team member?" icon="trash" class="text-red-600 hover:text-red-700" />
                            </div>
                        </div>
                    </flux:card>
                @empty
                    <div class="col-span-full">
                        <flux:card class="text-center py-12">
                            <flux:icon.users class="size-12 text-zinc-400 mx-auto mb-4" />
                            <p class="text-zinc-500">{{ __('No team members yet. Add your first one!') }}</p>
                        </flux:card>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Team Member') : __('Add Team Member') }}
                </flux:heading>

                <form wire:submit="save" class="space-y-4">
                    <flux:input
                        wire:model="name"
                        :label="__('Name')"
                        placeholder="Dr. Jane Smith"
                        required
                    />

                    <flux:input
                        wire:model="role"
                        :label="__('Role / Title')"
                        placeholder="Medical Director"
                        required
                    />

                    <flux:textarea
                        wire:model="bio"
                        :label="__('Short Bio')"
                        rows="3"
                        placeholder="A brief description of their experience..."
                    />

                    <div>
                        <flux:label>{{ __('Photo') }}</flux:label>
                        @if($current_photo)
                            <div class="flex items-center gap-4 mt-2 mb-2">
                                <img src="{{ Storage::url($current_photo) }}" alt="Current" class="w-16 h-16 rounded-full object-cover">
                                <flux:button variant="ghost" size="sm" wire:click="removePhoto" type="button">
                                    {{ __('Remove') }}
                                </flux:button>
                            </div>
                        @endif
                        <input type="file" wire:model="photo" accept="image/*" class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                        @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <flux:input
                        wire:model.number="sort_order"
                        :label="__('Sort Order')"
                        type="number"
                        min="0"
                    />

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="$set('showModal', false)" type="button">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit">
                            {{ $editingId ? __('Update') : __('Create') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </x-pages.admin.website-layout>
</flux:main>
