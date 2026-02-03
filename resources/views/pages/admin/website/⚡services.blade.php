<?php

use App\Concerns\PublicWebsiteValidationRules;
use App\Models\Amenity;
use App\Models\DailySchedule;
use App\Models\Service;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app.sidebar')]
#[Title('Manage Services')]
class extends Component {
    use PublicWebsiteValidationRules, WithFileUploads;

    // Service Modal
    public bool $showServiceModal = false;
    public ?int $editingServiceId = null;
    public string $title = '';
    public string $description = '';
    public string $icon = 'heart';
    public $image = null;
    public ?string $current_image = null;
    public array $features = [''];
    public int $sort_order = 0;

    // Amenity Modal
    public bool $showAmenityModal = false;
    public ?int $editingAmenityId = null;
    public string $amenity_title = '';
    public string $amenity_description = '';
    public string $amenity_icon = 'star';
    public int $amenity_sort_order = 0;

    // Schedule Modal
    public bool $showScheduleModal = false;
    public ?int $editingScheduleId = null;
    public string $schedule_time = '';
    public string $schedule_activity = '';
    public int $schedule_sort_order = 0;

    #[Computed]
    public function services()
    {
        return Service::ordered()->get();
    }

    #[Computed]
    public function amenities()
    {
        return Amenity::ordered()->get();
    }

    #[Computed]
    public function schedules()
    {
        return DailySchedule::ordered()->get();
    }

    #[Computed]
    public function serviceIcons(): array
    {
        return Service::ICONS;
    }

    #[Computed]
    public function amenityIcons(): array
    {
        return Amenity::ICONS;
    }

    // ============ SERVICE METHODS ============

    public function createService(): void
    {
        $this->resetServiceForm();
        $this->showServiceModal = true;
    }

    public function editService(int $id): void
    {
        $service = Service::findOrFail($id);

        $this->editingServiceId = $service->id;
        $this->title = $service->title;
        $this->description = $service->description;
        $this->icon = $service->icon;
        $this->current_image = $service->image_path;
        $this->features = $service->features ?? [''];
        $this->sort_order = $service->sort_order;

        if (empty($this->features)) {
            $this->features = [''];
        }

        $this->showServiceModal = true;
    }

    public function saveService(): void
    {
        $this->features = array_values(array_filter($this->features, fn($f) => trim($f) !== ''));

        $rules = $this->editingServiceId
            ? $this->serviceUpdateRules()
            : $this->serviceRules();

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'features' => !empty($this->features) ? $this->features : null,
            'sort_order' => $this->sort_order,
        ];

        if ($this->image) {
            if ($this->current_image) {
                Storage::disk('public')->delete($this->current_image);
            }
            $data['image_path'] = $this->image->store('services', 'public');
        }

        if ($this->editingServiceId) {
            Service::find($this->editingServiceId)->update($data);
        } else {
            Service::create($data);
        }

        $this->resetServiceForm();
        $this->showServiceModal = false;
        unset($this->services);
    }

    public function deleteService(int $id): void
    {
        $service = Service::findOrFail($id);

        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }

        $service->delete();
        unset($this->services);
    }

    public function toggleServiceActive(int $id): void
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);
        unset($this->services);
    }

    public function addFeature(): void
    {
        if (count($this->features) < 10) {
            $this->features[] = '';
        }
    }

    public function removeFeature(int $index): void
    {
        if (count($this->features) > 1) {
            unset($this->features[$index]);
            $this->features = array_values($this->features);
        }
    }

    protected function resetServiceForm(): void
    {
        $this->editingServiceId = null;
        $this->title = '';
        $this->description = '';
        $this->icon = 'heart';
        $this->image = null;
        $this->current_image = null;
        $this->features = [''];
        $this->sort_order = 0;
        $this->resetValidation();
    }

    // ============ AMENITY METHODS ============

    public function createAmenity(): void
    {
        $this->resetAmenityForm();
        $this->showAmenityModal = true;
    }

    public function editAmenity(int $id): void
    {
        $amenity = Amenity::findOrFail($id);

        $this->editingAmenityId = $amenity->id;
        $this->amenity_title = $amenity->title;
        $this->amenity_description = $amenity->description;
        $this->amenity_icon = $amenity->icon;
        $this->amenity_sort_order = $amenity->sort_order;

        $this->showAmenityModal = true;
    }

    public function saveAmenity(): void
    {
        $this->validate($this->amenityRules());

        $data = [
            'title' => $this->amenity_title,
            'description' => $this->amenity_description,
            'icon' => $this->amenity_icon,
            'sort_order' => $this->amenity_sort_order,
        ];

        if ($this->editingAmenityId) {
            Amenity::find($this->editingAmenityId)->update($data);
        } else {
            Amenity::create($data);
        }

        $this->resetAmenityForm();
        $this->showAmenityModal = false;
        unset($this->amenities);
    }

    public function deleteAmenity(int $id): void
    {
        Amenity::findOrFail($id)->delete();
        unset($this->amenities);
    }

    public function toggleAmenityActive(int $id): void
    {
        $amenity = Amenity::findOrFail($id);
        $amenity->update(['is_active' => !$amenity->is_active]);
        unset($this->amenities);
    }

    protected function resetAmenityForm(): void
    {
        $this->editingAmenityId = null;
        $this->amenity_title = '';
        $this->amenity_description = '';
        $this->amenity_icon = 'star';
        $this->amenity_sort_order = 0;
        $this->resetValidation();
    }

    // ============ SCHEDULE METHODS ============

    public function createSchedule(): void
    {
        $this->resetScheduleForm();
        $this->showScheduleModal = true;
    }

    public function editSchedule(int $id): void
    {
        $schedule = DailySchedule::findOrFail($id);

        $this->editingScheduleId = $schedule->id;
        $this->schedule_time = $schedule->time;
        $this->schedule_activity = $schedule->activity;
        $this->schedule_sort_order = $schedule->sort_order;

        $this->showScheduleModal = true;
    }

    public function saveSchedule(): void
    {
        $this->validate($this->dailyScheduleRules());

        $data = [
            'time' => $this->schedule_time,
            'activity' => $this->schedule_activity,
            'sort_order' => $this->schedule_sort_order,
        ];

        if ($this->editingScheduleId) {
            DailySchedule::find($this->editingScheduleId)->update($data);
        } else {
            DailySchedule::create($data);
        }

        $this->resetScheduleForm();
        $this->showScheduleModal = false;
        unset($this->schedules);
    }

    public function deleteSchedule(int $id): void
    {
        DailySchedule::findOrFail($id)->delete();
        unset($this->schedules);
    }

    public function toggleScheduleActive(int $id): void
    {
        $schedule = DailySchedule::findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);
        unset($this->schedules);
    }

    protected function resetScheduleForm(): void
    {
        $this->editingScheduleId = null;
        $this->schedule_time = '';
        $this->schedule_activity = '';
        $this->schedule_sort_order = 0;
        $this->resetValidation();
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('Services Page')"
        :subheading="__('Manage services, amenities, and daily schedule')">

        <div class="space-y-10">
            {{-- ============ SERVICES SECTION ============ --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Care Services') }}</h3>
                        <p class="text-sm text-zinc-500">{{ __('Main services displayed on the services page') }}</p>
                    </div>
                    <flux:button variant="primary" wire:click="createService" icon="plus" size="sm">
                        {{ __('Add Service') }}
                    </flux:button>
                </div>

                <div class="space-y-3">
                    @forelse($this->services as $service)
                        <flux:card class="relative overflow-hidden">
                            <div class="flex flex-col sm:flex-row gap-4">
                                {{-- Image --}}
                                <div class="w-full sm:w-32 shrink-0">
                                    <div class="aspect-video bg-zinc-100 dark:bg-zinc-800 rounded-lg overflow-hidden">
                                        @if($service->image_path)
                                            <img src="{{ Storage::url($service->image_path) }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <flux:icon :name="$service->icon" class="size-8 text-zinc-400" />
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <flux:icon :name="$service->icon" class="size-4 text-accent" />
                                                <h4 class="font-semibold text-zinc-900 dark:text-white">{{ $service->title }}</h4>
                                                @if(!$service->is_active)
                                                    <flux:badge color="zinc" size="sm">Inactive</flux:badge>
                                                @endif
                                            </div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-1">{{ $service->description }}</p>
                                        </div>

                                        <div class="flex gap-1 shrink-0">
                                            <flux:button variant="ghost" size="xs" wire:click="editService({{ $service->id }})" icon="pencil" />
                                            <flux:button variant="ghost" size="xs" wire:click="toggleServiceActive({{ $service->id }})" icon="{{ $service->is_active ? 'eye-slash' : 'eye' }}" />
                                            <flux:button variant="ghost" size="xs" wire:click="deleteService({{ $service->id }})" wire:confirm="Delete this service?" icon="trash" class="text-red-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </flux:card>
                    @empty
                        <flux:card class="text-center py-8">
                            <flux:icon.rectangle-stack class="size-10 text-zinc-400 mx-auto mb-2" />
                            <p class="text-zinc-500 text-sm">{{ __('No services yet.') }}</p>
                        </flux:card>
                    @endforelse
                </div>
            </div>

            <flux:separator />

            {{-- ============ AMENITIES SECTION ============ --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Amenities & Facilities') }}</h3>
                        <p class="text-sm text-zinc-500">{{ __('Amenities grid displayed on the services page') }}</p>
                    </div>
                    <flux:button variant="primary" wire:click="createAmenity" icon="plus" size="sm">
                        {{ __('Add Amenity') }}
                    </flux:button>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @forelse($this->amenities as $amenity)
                        <flux:card class="relative group">
                            @if(!$amenity->is_active)
                                <div class="absolute top-2 right-2 z-10">
                                    <flux:badge color="zinc" size="sm">Inactive</flux:badge>
                                </div>
                            @endif

                            <div class="text-center">
                                <flux:icon :name="$amenity->icon" class="size-8 text-accent mx-auto mb-2" />
                                <h4 class="font-semibold text-zinc-900 dark:text-white text-sm">{{ $amenity->title }}</h4>
                                <p class="text-xs text-zinc-500 mt-1 line-clamp-2">{{ $amenity->description }}</p>
                            </div>

                            <div class="flex justify-center gap-1 mt-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:button variant="ghost" size="xs" wire:click="editAmenity({{ $amenity->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="xs" wire:click="toggleAmenityActive({{ $amenity->id }})" icon="{{ $amenity->is_active ? 'eye-slash' : 'eye' }}" />
                                <flux:button variant="ghost" size="xs" wire:click="deleteAmenity({{ $amenity->id }})" wire:confirm="Delete this amenity?" icon="trash" class="text-red-600" />
                            </div>
                        </flux:card>
                    @empty
                        <div class="col-span-full">
                            <flux:card class="text-center py-8">
                                <flux:icon.building-office class="size-10 text-zinc-400 mx-auto mb-2" />
                                <p class="text-zinc-500 text-sm">{{ __('No amenities yet.') }}</p>
                            </flux:card>
                        </div>
                    @endforelse
                </div>
            </div>

            <flux:separator />

            {{-- ============ DAILY SCHEDULE SECTION ============ --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Daily Life Schedule') }}</h3>
                        <p class="text-sm text-zinc-500">{{ __('Daily schedule displayed on the services page') }}</p>
                    </div>
                    <flux:button variant="primary" wire:click="createSchedule" icon="plus" size="sm">
                        {{ __('Add Schedule Item') }}
                    </flux:button>
                </div>

                <flux:card>
                    @forelse($this->schedules as $schedule)
                        <div class="flex items-center gap-4 py-3 {{ !$loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }} group">
                            <div class="w-24 shrink-0 text-sm font-semibold text-accent">
                                {{ $schedule->time }}
                            </div>
                            <div class="flex-1 text-zinc-700 dark:text-zinc-300 text-sm {{ !$schedule->is_active ? 'opacity-50' : '' }}">
                                {{ $schedule->activity }}
                                @if(!$schedule->is_active)
                                    <flux:badge color="zinc" size="sm" class="ml-2">Inactive</flux:badge>
                                @endif
                            </div>
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:button variant="ghost" size="xs" wire:click="editSchedule({{ $schedule->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="xs" wire:click="toggleScheduleActive({{ $schedule->id }})" icon="{{ $schedule->is_active ? 'eye-slash' : 'eye' }}" />
                                <flux:button variant="ghost" size="xs" wire:click="deleteSchedule({{ $schedule->id }})" wire:confirm="Delete this schedule item?" icon="trash" class="text-red-600" />
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <flux:icon.clock class="size-10 text-zinc-400 mx-auto mb-2" />
                            <p class="text-zinc-500 text-sm">{{ __('No schedule items yet.') }}</p>
                        </div>
                    @endforelse
                </flux:card>
            </div>
        </div>

        {{-- ============ SERVICE MODAL ============ --}}
        <flux:modal wire:model="showServiceModal" class="max-w-2xl">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingServiceId ? __('Edit Service') : __('Add Service') }}
                </flux:heading>

                <form wire:submit="saveService" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <flux:input
                            wire:model="title"
                            :label="__('Title')"
                            placeholder="Residential Care"
                            required
                        />

                        <div>
                            <flux:label>{{ __('Icon') }}</flux:label>
                            <flux:select wire:model="icon" class="mt-1">
                                @foreach($this->serviceIcons as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    <flux:textarea
                        wire:model="description"
                        :label="__('Description')"
                        rows="3"
                        placeholder="Describe this service..."
                        required
                    />

                    <div>
                        <flux:label>{{ __('Image') }}</flux:label>
                        @if($current_image)
                            <div class="mt-2 mb-2">
                                <img src="{{ Storage::url($current_image) }}" alt="Current" class="w-32 h-24 object-cover rounded-lg">
                            </div>
                        @endif
                        <input type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300">
                        @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-zinc-500 mt-1">{{ __('Max 2MB. JPG, PNG, or WebP.') }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <flux:label>{{ __('Features') }}</flux:label>
                            @if(count($features) < 10)
                                <flux:button variant="ghost" size="xs" wire:click.prevent="addFeature" icon="plus">
                                    {{ __('Add') }}
                                </flux:button>
                            @endif
                        </div>
                        <div class="space-y-2">
                            @foreach($features as $index => $feature)
                                <div class="flex gap-2">
                                    <flux:input
                                        wire:model="features.{{ $index }}"
                                        placeholder="Feature description..."
                                        class="flex-1"
                                    />
                                    @if(count($features) > 1)
                                        <flux:button variant="ghost" size="sm" wire:click.prevent="removeFeature({{ $index }})" icon="x-mark" class="text-red-500" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-zinc-500 mt-1">{{ __('Key features or benefits (max 10)') }}</p>
                    </div>

                    <flux:input
                        wire:model.number="sort_order"
                        :label="__('Sort Order')"
                        type="number"
                        min="0"
                    />

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="$set('showServiceModal', false)" type="button">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit">
                            {{ $editingServiceId ? __('Update') : __('Create') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>

        {{-- ============ AMENITY MODAL ============ --}}
        <flux:modal wire:model="showAmenityModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingAmenityId ? __('Edit Amenity') : __('Add Amenity') }}
                </flux:heading>

                <form wire:submit="saveAmenity" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <flux:input
                            wire:model="amenity_title"
                            :label="__('Title')"
                            placeholder="Private Rooms"
                            required
                        />

                        <div>
                            <flux:label>{{ __('Icon') }}</flux:label>
                            <flux:select wire:model="amenity_icon" class="mt-1">
                                @foreach($this->amenityIcons as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    <flux:textarea
                        wire:model="amenity_description"
                        :label="__('Description')"
                        rows="2"
                        placeholder="Brief description..."
                        required
                    />

                    <flux:input
                        wire:model.number="amenity_sort_order"
                        :label="__('Sort Order')"
                        type="number"
                        min="0"
                    />

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="$set('showAmenityModal', false)" type="button">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit">
                            {{ $editingAmenityId ? __('Update') : __('Create') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>

        {{-- ============ SCHEDULE MODAL ============ --}}
        <flux:modal wire:model="showScheduleModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingScheduleId ? __('Edit Schedule Item') : __('Add Schedule Item') }}
                </flux:heading>

                <form wire:submit="saveSchedule" class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <flux:input
                            wire:model="schedule_time"
                            :label="__('Time')"
                            placeholder="7:00 AM"
                            required
                        />

                        <flux:input
                            wire:model.number="schedule_sort_order"
                            :label="__('Sort Order')"
                            type="number"
                            min="0"
                        />
                    </div>

                    <flux:input
                        wire:model="schedule_activity"
                        :label="__('Activity')"
                        placeholder="Morning wake-up and personal care assistance"
                        required
                    />

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="$set('showScheduleModal', false)" type="button">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit">
                            {{ $editingScheduleId ? __('Update') : __('Create') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </x-pages.admin.website-layout>
</flux:main>
