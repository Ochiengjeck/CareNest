<?php

use App\Concerns\PublicWebsiteValidationRules;
use App\Models\CareHomeImage;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app.sidebar')]
#[Title('Website Content Settings')]
class extends Component {
    use PublicWebsiteValidationRules, WithFileUploads;

    public array $stats = [];
    public array $visiting_hours = [];
    public array $office_hours = [];
    public string $about_story = '';
    public string $about_mission = '';
    public string $about_vision = '';

    // Care Home Images
    public $newImage = null;
    public string $newCaption = '';

    #[Computed]
    public function careHomeImages()
    {
        return CareHomeImage::ordered()->get();
    }

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        $this->stats = $settings->get('public_stats', [
            'years' => '20',
            'residents' => '150',
            'staff' => '50',
            'satisfaction' => '98',
        ]);

        $this->visiting_hours = $settings->get('public_visiting_hours', [
            'weekday' => '10:00 AM - 8:00 PM',
            'saturday' => '10:00 AM - 8:00 PM',
            'sunday' => '10:00 AM - 8:00 PM',
        ]);

        $this->office_hours = $settings->get('public_office_hours', [
            'weekday' => '8:00 AM - 6:00 PM',
            'saturday' => '9:00 AM - 4:00 PM',
            'sunday' => '10:00 AM - 2:00 PM',
        ]);

        $this->about_story = $settings->get('public_about_story', '') ?? '';
        $this->about_mission = $settings->get('public_about_mission', '') ?? '';
        $this->about_vision = $settings->get('public_about_vision', '') ?? '';
    }

    public function saveStats(): void
    {
        $this->validate($this->statsRules());

        $settings = app(SettingsService::class);
        $settings->set('public_stats', $this->stats, 'public_website', 'json');

        $this->dispatch('stats-saved');
    }

    public function saveVisitingHours(): void
    {
        $this->validate($this->visitingHoursRules());

        $settings = app(SettingsService::class);
        $settings->set('public_visiting_hours', $this->visiting_hours, 'public_website', 'json');

        $this->dispatch('visiting-hours-saved');
    }

    public function saveOfficeHours(): void
    {
        $this->validate($this->officeHoursRules());

        $settings = app(SettingsService::class);
        $settings->set('public_office_hours', $this->office_hours, 'public_website', 'json');

        $this->dispatch('office-hours-saved');
    }

    public function saveAboutContent(): void
    {
        $this->validate($this->aboutContentRules());

        $settings = app(SettingsService::class);
        $settings->setMany([
            'public_about_story' => $this->about_story,
            'public_about_mission' => $this->about_mission,
            'public_about_vision' => $this->about_vision,
        ], 'public_website');

        $this->dispatch('about-saved');
    }

    public function uploadImage(): void
    {
        // Check max limit
        if ($this->careHomeImages->count() >= 5) {
            $this->addError('newImage', 'Maximum 5 images allowed.');
            return;
        }

        $this->validate($this->careHomeImageRules());

        $imagePath = $this->newImage->store('about', 'public');

        // Auto-feature if this is the first image
        $isFirst = $this->careHomeImages->count() === 0;

        CareHomeImage::create([
            'image_path' => $imagePath,
            'caption' => $this->newCaption ?: null,
            'is_featured' => $isFirst,
            'sort_order' => $this->careHomeImages->count(),
        ]);

        $this->newImage = null;
        $this->newCaption = '';
        unset($this->careHomeImages);

        $this->dispatch('image-uploaded');
    }

    public function setFeatured(int $id): void
    {
        // Unfeature all images first
        CareHomeImage::query()->update(['is_featured' => false]);

        // Set the selected one as featured
        CareHomeImage::findOrFail($id)->update(['is_featured' => true]);

        unset($this->careHomeImages);
    }

    public function deleteImage(int $id): void
    {
        // Cannot delete if it's the last image
        if ($this->careHomeImages->count() <= 1) {
            $this->addError('newImage', 'At least one image is required.');
            return;
        }

        $image = CareHomeImage::findOrFail($id);
        $wasFeatured = $image->is_featured;

        // Delete the file
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        // If deleted image was featured, auto-feature the first remaining image
        if ($wasFeatured) {
            CareHomeImage::first()?->update(['is_featured' => true]);
        }

        unset($this->careHomeImages);
    }

    public function updateCaption(int $id, string $caption): void
    {
        CareHomeImage::findOrFail($id)->update([
            'caption' => $caption ?: null,
        ]);

        unset($this->careHomeImages);
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('Content Settings')"
        :subheading="__('Manage statistics, hours, and about page content')">

        <div class="space-y-8 max-w-3xl">
            {{-- Statistics --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Homepage Statistics') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('These numbers are displayed on the homepage stats bar.') }}</flux:subheading>

                <form wire:submit="saveStats" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input
                            wire:model="stats.years"
                            :label="__('Years of Care')"
                            placeholder="20"
                        />
                        <flux:input
                            wire:model="stats.residents"
                            :label="__('Residents Served')"
                            placeholder="150"
                        />
                        <flux:input
                            wire:model="stats.staff"
                            :label="__('Staff Members')"
                            placeholder="50"
                        />
                        <flux:input
                            wire:model="stats.satisfaction"
                            :label="__('Satisfaction %')"
                            placeholder="98"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save Statistics') }}</flux:button>
                        <x-action-message on="stats-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- Visiting Hours --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Visiting Hours') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Displayed on the contact page and footer.') }}</flux:subheading>

                <form wire:submit="saveVisitingHours" class="space-y-4">
                    <flux:input
                        wire:model="visiting_hours.weekday"
                        :label="__('Monday - Friday')"
                        placeholder="10:00 AM - 8:00 PM"
                    />
                    <flux:input
                        wire:model="visiting_hours.saturday"
                        :label="__('Saturday')"
                        placeholder="10:00 AM - 8:00 PM"
                    />
                    <flux:input
                        wire:model="visiting_hours.sunday"
                        :label="__('Sunday')"
                        placeholder="10:00 AM - 8:00 PM"
                    />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save Visiting Hours') }}</flux:button>
                        <x-action-message on="visiting-hours-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- Office Hours --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Office Hours') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Admin office hours displayed on the contact page.') }}</flux:subheading>

                <form wire:submit="saveOfficeHours" class="space-y-4">
                    <flux:input
                        wire:model="office_hours.weekday"
                        :label="__('Monday - Friday')"
                        placeholder="8:00 AM - 6:00 PM"
                    />
                    <flux:input
                        wire:model="office_hours.saturday"
                        :label="__('Saturday')"
                        placeholder="9:00 AM - 4:00 PM"
                    />
                    <flux:input
                        wire:model="office_hours.sunday"
                        :label="__('Sunday')"
                        placeholder="10:00 AM - 2:00 PM"
                    />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save Office Hours') }}</flux:button>
                        <x-action-message on="office-hours-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- About Page Content --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('About Page Content') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Story, mission, and vision displayed on the about page.') }}</flux:subheading>

                <form wire:submit="saveAboutContent" class="space-y-4">
                    <flux:textarea
                        wire:model="about_story"
                        :label="__('Our Story')"
                        rows="4"
                        placeholder="Tell your care home's story..."
                    />
                    <flux:textarea
                        wire:model="about_mission"
                        :label="__('Our Mission')"
                        rows="3"
                        placeholder="What is your mission?"
                    />
                    <flux:textarea
                        wire:model="about_vision"
                        :label="__('Our Vision')"
                        rows="3"
                        placeholder="What is your vision?"
                    />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save About Content') }}</flux:button>
                        <x-action-message on="about-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- Care Home Images --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Care Home Images') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Images displayed in the "Our Story" section. The featured image appears prominently on the About page.') }}</flux:subheading>

                {{-- Current Images --}}
                @if($this->careHomeImages->count() > 0)
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        @foreach($this->careHomeImages as $image)
                            <div class="relative group rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 {{ $image->is_featured ? 'ring-2 ring-accent' : '' }}">
                                {{-- Featured Badge --}}
                                @if($image->is_featured)
                                    <div class="absolute top-2 left-2 z-10">
                                        <flux:badge color="amber" size="sm" icon="star">Featured</flux:badge>
                                    </div>
                                @endif

                                {{-- Image --}}
                                <div class="aspect-video bg-zinc-100 dark:bg-zinc-800">
                                    <img
                                        src="{{ Storage::url($image->image_path) }}"
                                        alt="{{ $image->caption ?? 'Care Home' }}"
                                        class="w-full h-full object-cover"
                                    />
                                </div>

                                {{-- Actions Overlay --}}
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    @if(!$image->is_featured)
                                        <flux:button
                                            wire:click="setFeatured({{ $image->id }})"
                                            variant="filled"
                                            size="sm"
                                            icon="star"
                                            title="Set as Featured"
                                        />
                                    @endif
                                    @if($this->careHomeImages->count() > 1)
                                        <flux:button
                                            wire:click="deleteImage({{ $image->id }})"
                                            wire:confirm="Are you sure you want to delete this image?"
                                            variant="danger"
                                            size="sm"
                                            icon="trash"
                                            title="Delete"
                                        />
                                    @endif
                                </div>

                                {{-- Caption --}}
                                <div class="p-3">
                                    <input
                                        type="text"
                                        value="{{ $image->caption }}"
                                        placeholder="Add caption..."
                                        class="w-full text-sm bg-transparent border-0 border-b border-zinc-200 dark:border-zinc-700 focus:border-accent focus:ring-0 px-0 py-1 text-zinc-700 dark:text-zinc-300 placeholder-zinc-400"
                                        wire:blur="updateCaption({{ $image->id }}, $event.target.value)"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 mb-6 rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700">
                        <flux:icon.photo class="size-12 text-zinc-400 mx-auto mb-3" />
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('No images uploaded yet.') }}</p>
                        <p class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('Upload your first care home image below.') }}</p>
                    </div>
                @endif

                {{-- Upload Form --}}
                @if($this->careHomeImages->count() < 5)
                    <form wire:submit="uploadImage" class="space-y-4 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <flux:label>{{ __('Upload Image') }}</flux:label>
                                <input
                                    type="file"
                                    wire:model="newImage"
                                    accept="image/*"
                                    class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-accent/10 file:text-accent hover:file:bg-accent/20 dark:file:bg-accent/20 dark:file:text-accent"
                                />
                                @error('newImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                <p class="text-xs text-zinc-500 mt-1">{{ __('Max 2MB. JPG, PNG, or WebP.') }}</p>
                            </div>

                            <div class="flex-1">
                                <flux:input
                                    wire:model="newCaption"
                                    :label="__('Caption (optional)')"
                                    placeholder="Describe this image..."
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-500">
                                {{ $this->careHomeImages->count() }}/5 {{ __('images uploaded') }}
                            </p>
                            <div class="flex items-center gap-4">
                                <flux:button variant="primary" type="submit" icon="arrow-up-tray">
                                    {{ __('Upload Image') }}
                                </flux:button>
                                <x-action-message on="image-uploaded">{{ __('Uploaded!') }}</x-action-message>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 text-sm">
                        <flux:icon.exclamation-triangle class="size-5 inline-block mr-2" />
                        {{ __('Maximum 5 images reached. Delete an existing image to upload a new one.') }}
                    </div>
                @endif

                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-4">
                    {{ __('Tip: Click the star icon to set an image as featured. The featured image will appear in the "Our Story" section on the About page.') }}
                </p>
            </flux:card>
        </div>
    </x-pages.admin.website-layout>
</flux:main>
