<?php

use App\Concerns\PublicWebsiteValidationRules;
use App\Models\Testimonial;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app.sidebar')]
#[Title('Manage Testimonials')]
class extends Component {
    use PublicWebsiteValidationRules, WithFileUploads;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $quote = '';
    public string $author_name = '';
    public string $author_relation = '';
    public $author_image = null;
    public ?string $current_image = null;
    public bool $is_featured = false;
    public int $sort_order = 0;

    #[Computed]
    public function testimonials()
    {
        return Testimonial::ordered()->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);

        $this->editingId = $testimonial->id;
        $this->quote = $testimonial->quote;
        $this->author_name = $testimonial->author_name;
        $this->author_relation = $testimonial->author_relation ?? '';
        $this->current_image = $testimonial->author_image;
        $this->is_featured = $testimonial->is_featured;
        $this->sort_order = $testimonial->sort_order;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate($this->testimonialRules());

        $data = [
            'quote' => $this->quote,
            'author_name' => $this->author_name,
            'author_relation' => $this->author_relation ?: null,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ];

        if ($this->author_image) {
            // Delete old image if exists
            if ($this->current_image) {
                Storage::disk('public')->delete($this->current_image);
            }
            $data['author_image'] = $this->author_image->store('testimonials', 'public');
        }

        if ($this->editingId) {
            Testimonial::find($this->editingId)->update($data);
        } else {
            Testimonial::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
        unset($this->testimonials);
    }

    public function delete(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);

        if ($testimonial->author_image) {
            Storage::disk('public')->delete($testimonial->author_image);
        }

        $testimonial->delete();
        unset($this->testimonials);
    }

    public function toggleActive(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_active' => !$testimonial->is_active]);
        unset($this->testimonials);
    }

    public function removeImage(): void
    {
        if ($this->current_image && $this->editingId) {
            Storage::disk('public')->delete($this->current_image);
            Testimonial::find($this->editingId)->update(['author_image' => null]);
            $this->current_image = null;
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->quote = '';
        $this->author_name = '';
        $this->author_relation = '';
        $this->author_image = null;
        $this->current_image = null;
        $this->is_featured = false;
        $this->sort_order = 0;
        $this->resetValidation();
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('Testimonials')"
        :subheading="__('Manage testimonials displayed on the public website')">

        <div class="space-y-6">
            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="create" icon="plus">
                    {{ __('Add Testimonial') }}
                </flux:button>
            </div>

            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Author') }}</flux:table.column>
                        <flux:table.column>{{ __('Quote') }}</flux:table.column>
                        <flux:table.column>{{ __('Featured') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($this->testimonials as $testimonial)
                            <flux:table.row :key="$testimonial->id">
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        @if($testimonial->author_image)
                                            <img src="{{ Storage::url($testimonial->author_image) }}" alt="{{ $testimonial->author_name }}" class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                                                <flux:icon.user class="size-5 text-zinc-500" />
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium">{{ $testimonial->author_name }}</div>
                                            <div class="text-sm text-zinc-500">{{ $testimonial->author_relation }}</div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="max-w-xs truncate">
                                    {{ Str::limit($testimonial->quote, 80) }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($testimonial->is_featured)
                                        <flux:badge color="amber">Featured</flux:badge>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$testimonial->is_active ? 'green' : 'zinc'">
                                        {{ $testimonial->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        <flux:button variant="ghost" size="xs" wire:click="edit({{ $testimonial->id }})" icon="pencil" />
                                        <flux:button variant="ghost" size="xs" wire:click="toggleActive({{ $testimonial->id }})" icon="{{ $testimonial->is_active ? 'eye-slash' : 'eye' }}" />
                                        <flux:button variant="ghost" size="xs" wire:click="delete({{ $testimonial->id }})" wire:confirm="Are you sure you want to delete this testimonial?" icon="trash" class="text-red-600 hover:text-red-700" />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">
                                    {{ __('No testimonials yet. Add your first one!') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Testimonial') : __('Add Testimonial') }}
                </flux:heading>

                <form wire:submit="save" class="space-y-4">
                    <flux:textarea
                        wire:model="quote"
                        :label="__('Quote')"
                        rows="4"
                        placeholder="What did they say about your care home?"
                        required
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input
                            wire:model="author_name"
                            :label="__('Author Name')"
                            placeholder="John Smith"
                            required
                        />
                        <flux:input
                            wire:model="author_relation"
                            :label="__('Relation')"
                            placeholder="Son of Resident"
                        />
                    </div>

                    <div>
                        <flux:label>{{ __('Author Photo') }}</flux:label>
                        @if($current_image)
                            <div class="flex items-center gap-4 mt-2 mb-2">
                                <img src="{{ Storage::url($current_image) }}" alt="Current" class="w-16 h-16 rounded-full object-cover">
                                <flux:button variant="ghost" size="sm" wire:click="removeImage" type="button">
                                    {{ __('Remove') }}
                                </flux:button>
                            </div>
                        @endif
                        <input type="file" wire:model="author_image" accept="image/*" class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                        @error('author_image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input
                            wire:model.number="sort_order"
                            :label="__('Sort Order')"
                            type="number"
                            min="0"
                        />
                        <div class="flex items-end pb-2">
                            <flux:switch wire:model="is_featured" :label="__('Featured on Homepage')" />
                        </div>
                    </div>

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
