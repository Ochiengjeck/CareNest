<?php

use App\Concerns\PublicWebsiteValidationRules;
use App\Models\GalleryImage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app.sidebar')]
#[Title('Manage Gallery')]
class extends Component {
    use PublicWebsiteValidationRules, WithFileUploads;

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Url]
    public string $categoryFilter = '';

    public string $title = '';
    public $image = null;
    public ?string $current_image = null;
    public string $category = 'rooms';
    public string $alt_text = '';
    public int $sort_order = 0;

    #[Computed]
    public function images()
    {
        return GalleryImage::query()
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->ordered()
            ->get();
    }

    #[Computed]
    public function categories(): array
    {
        return [
            'rooms' => 'Rooms & Suites',
            'common' => 'Common Areas',
            'activities' => 'Activities',
            'gardens' => 'Gardens & Outdoors',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $image = GalleryImage::findOrFail($id);

        $this->editingId = $image->id;
        $this->title = $image->title;
        $this->current_image = $image->image_path;
        $this->category = $image->category;
        $this->alt_text = $image->alt_text ?? '';
        $this->sort_order = $image->sort_order;

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = $this->editingId
            ? $this->galleryImageUpdateRules()
            : $this->galleryImageRules();

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'category' => $this->category,
            'alt_text' => $this->alt_text ?: null,
            'sort_order' => $this->sort_order,
        ];

        if ($this->image) {
            if ($this->current_image) {
                Storage::disk('public')->delete($this->current_image);
            }
            $data['image_path'] = $this->image->store('gallery', 'public');
        }

        if ($this->editingId) {
            GalleryImage::find($this->editingId)->update($data);
        } else {
            GalleryImage::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
        unset($this->images);
    }

    public function delete(int $id): void
    {
        $image = GalleryImage::findOrFail($id);

        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();
        unset($this->images);
    }

    public function toggleActive(int $id): void
    {
        $image = GalleryImage::findOrFail($id);
        $image->update(['is_active' => !$image->is_active]);
        unset($this->images);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->image = null;
        $this->current_image = null;
        $this->category = 'rooms';
        $this->alt_text = '';
        $this->sort_order = 0;
        $this->resetValidation();
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('Gallery')"
        :subheading="__('Manage images displayed on the gallery page')">

        <div class="space-y-6">
            <div class="flex flex-wrap justify-between gap-4">
                <flux:select wire:model.live="categoryFilter" class="w-48">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($this->categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:button variant="primary" wire:click="create" icon="plus">
                    {{ __('Add Image') }}
                </flux:button>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($this->images as $image)
                    <flux:card class="relative overflow-hidden">
                        @if(!$image->is_active)
                            <div class="absolute top-2 right-2 z-10">
                                <flux:badge color="zinc">Inactive</flux:badge>
                            </div>
                        @endif

                        <div class="aspect-video bg-zinc-100 dark:bg-zinc-800 -mx-4 -mt-4 mb-4">
                            @if($image->image_path)
                                <img src="{{ Storage::url($image->image_path) }}" alt="{{ $image->alt_text ?? $image->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <flux:icon.photo class="size-12 text-zinc-400" />
                                </div>
                            @endif
                        </div>

                        <h3 class="font-semibold text-zinc-900 dark:text-white truncate">{{ $image->title }}</h3>
                        <p class="text-sm text-zinc-500">{{ $this->categories[$image->category] ?? $image->category }}</p>

                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xs text-zinc-400">Order: {{ $image->sort_order }}</span>
                            <div class="flex gap-1">
                                <flux:button variant="ghost" size="xs" wire:click="edit({{ $image->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="xs" wire:click="toggleActive({{ $image->id }})" icon="{{ $image->is_active ? 'eye-slash' : 'eye' }}" />
                                <flux:button variant="ghost" size="xs" wire:click="delete({{ $image->id }})" wire:confirm="Are you sure you want to delete this image?" icon="trash" class="text-red-600 hover:text-red-700" />
                            </div>
                        </div>
                    </flux:card>
                @empty
                    <div class="col-span-full">
                        <flux:card class="text-center py-12">
                            <flux:icon.photo class="size-12 text-zinc-400 mx-auto mb-4" />
                            <p class="text-zinc-500">{{ __('No gallery images yet. Add your first one!') }}</p>
                        </flux:card>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Image') : __('Add Image') }}
                </flux:heading>

                <form wire:submit="save" class="space-y-4">
                    <flux:input
                        wire:model="title"
                        :label="__('Title')"
                        placeholder="Private Suite A"
                        required
                    />

                    <div>
                        <flux:label>{{ __('Image') }} @if(!$editingId)<span class="text-red-500">*</span>@endif</flux:label>
                        @if($current_image)
                            <div class="mt-2 mb-2">
                                <img src="{{ Storage::url($current_image) }}" alt="Current" class="w-32 h-24 object-cover rounded-lg">
                            </div>
                        @endif
                        <input type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                        @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-zinc-500 mt-1">{{ __('Max 2MB. JPG, PNG, or WebP.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:select wire:model="category" :label="__('Category')">
                            @foreach($this->categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input
                            wire:model.number="sort_order"
                            :label="__('Sort Order')"
                            type="number"
                            min="0"
                        />
                    </div>

                    <flux:input
                        wire:model="alt_text"
                        :label="__('Alt Text (Accessibility)')"
                        placeholder="Description for screen readers"
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
