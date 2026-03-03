<?php

use App\Concerns\StaffDocumentValidationRules;
use App\Models\StaffDocument;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.app.sidebar')]
#[Title('Staff Documents')]
class extends Component {
    use StaffDocumentValidationRules, WithFileUploads;

    #[Locked]
    public int $userId;

    public ?string $activeCategory = null;
    public bool $showUploadModal = false;

    // Upload form fields
    public string $docTitle = '';
    public string $docDescription = '';
    public ?int $docYear = null;
    public $docFile = null;
    public string $docExpiry = '';
    public string $docNotes = '';
    public string $docStatus = 'completed';

    public function mount(User $user): void
    {
        abort_unless(auth()->user()->can('view-staff'), 403);
        $this->userId = $user->id;
    }

    #[Computed]
    public function member(): User
    {
        return User::with(['roles', 'staffProfile'])->findOrFail($this->userId);
    }

    #[Computed]
    public function allDocuments()
    {
        return StaffDocument::where('user_id', $this->userId)->latest()->get();
    }

    #[Computed]
    public function categorySummary(): array
    {
        $docs = $this->allDocuments;
        $summary = [];

        foreach (StaffDocument::categories() as $key => $cat) {
            $catDocs = $docs->where('category', $key);
            $summary[$key] = [
                'count' => $catDocs->count(),
                'latest_status' => $catDocs->first()?->status,
            ];
        }

        return $summary;
    }

    #[Computed]
    public function activeDocuments()
    {
        if (! $this->activeCategory) {
            return collect();
        }

        return $this->allDocuments->where('category', $this->activeCategory)->values();
    }

    public function setActiveCategory(?string $category): void
    {
        if ($this->activeCategory === $category) {
            $this->activeCategory = null;
        } else {
            $this->activeCategory = $category;
        }
        unset($this->activeDocuments);
    }

    public function openUploadModal(?string $category = null): void
    {
        if ($category) {
            $this->activeCategory = $category;
        }
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->docTitle = '';
        $this->docDescription = '';
        $this->docYear = null;
        $this->docFile = null;
        $this->docExpiry = '';
        $this->docNotes = '';
        $this->docStatus = 'completed';
        $this->resetValidation();
    }

    public function uploadDocument(): void
    {
        $this->validate($this->staffDocumentRules());

        $file = $this->docFile;
        $extension = strtolower($file->getClientOriginalExtension());
        $storedPath = $file->store('staff/documents/'.$this->userId, 'public');

        StaffDocument::create([
            'user_id' => $this->userId,
            'category' => $this->activeCategory,
            'title' => $this->docTitle,
            'description' => $this->docDescription ?: null,
            'document_year' => $this->docYear ?: null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'status' => $this->docStatus,
            'expires_at' => $this->docExpiry ?: null,
            'notes' => $this->docNotes ?: null,
            'uploaded_by' => auth()->id(),
        ]);

        unset($this->allDocuments, $this->categorySummary, $this->activeDocuments);

        $this->closeUploadModal();

        Flux::toast(heading: 'Document uploaded', text: 'The document has been uploaded successfully.', variant: 'success');
    }

    public function deleteDocument(int $id): void
    {
        $doc = StaffDocument::where('id', $id)->where('user_id', $this->userId)->firstOrFail();
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        unset($this->allDocuments, $this->categorySummary, $this->activeDocuments);

        Flux::toast(heading: 'Document deleted', text: 'The document has been removed.', variant: 'success');
    }

    public function downloadDocument(int $id)
    {
        $doc = StaffDocument::where('id', $id)->where('user_id', $this->userId)->firstOrFail();

        return Storage::disk('public')->download($doc->file_path, $doc->title.'.'.$doc->file_type);
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('staff.show', $this->userId)" wire:navigate icon="arrow-left" />
                <flux:avatar :name="$this->member->name" size="lg" />
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $this->member->name }}</flux:heading>
                        @if($this->member->staffProfile)
                            <flux:badge :color="$this->member->staffProfile->status_color">
                                {{ $this->member->staffProfile->status_label }}
                            </flux:badge>
                        @endif
                    </div>
                    <flux:subheading>{{ __('Employment Documents') }}</flux:subheading>
                </div>
            </div>
        </div>

        {{-- Category Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (StaffDocument::categories() as $catKey => $cat)
                @php
                    $summary = $this->categorySummary[$catKey] ?? ['count' => 0, 'latest_status' => null];
                    $isActive = $this->activeCategory === $catKey;
                    $isPersonal = $catKey === 'personal_information';
                @endphp

                @if ($isPersonal)
                    <a href="{{ route('staff.edit', $this->userId) }}" wire:navigate>
                        <flux:card class="cursor-pointer transition-all hover:shadow-md {{ $isActive ? 'ring-2 ring-accent' : '' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon :icon="$cat['icon']" class="size-5 text-zinc-600 dark:text-zinc-400" />
                                    </div>
                                    <div>
                                        <flux:text class="font-medium text-sm">{{ $cat['label'] }}</flux:text>
                                        <flux:text class="text-xs text-zinc-500">{{ $cat['description'] }}</flux:text>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <flux:badge size="sm" color="blue" icon="arrow-top-right-on-square">View Profile</flux:badge>
                            </div>
                        </flux:card>
                    </a>
                @else
                    <div wire:click="setActiveCategory('{{ $catKey }}')" class="cursor-pointer">
                        <flux:card class="h-full transition-all hover:shadow-md {{ $isActive ? 'ring-2 ring-accent' : '' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon :icon="$cat['icon']" class="size-5 text-zinc-600 dark:text-zinc-400" />
                                    </div>
                                    <div>
                                        <flux:text class="font-medium text-sm leading-tight">{{ $cat['label'] }}</flux:text>
                                        <flux:text class="text-xs text-zinc-500 leading-tight">{{ $cat['description'] }}</flux:text>
                                    </div>
                                </div>
                                @if ($summary['count'] > 0)
                                    <flux:badge size="sm" color="zinc">{{ $summary['count'] }}</flux:badge>
                                @endif
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                @if ($summary['count'] === 0)
                                    <flux:text class="text-xs text-zinc-400">{{ __('No documents') }}</flux:text>
                                @else
                                    @php
                                        $statusColors = ['completed' => 'green', 'pending' => 'amber', 'expired' => 'red', 'requires_update' => 'orange'];
                                        $statusLabels = ['completed' => 'Completed', 'pending' => 'Pending', 'expired' => 'Expired', 'requires_update' => 'Requires Update'];
                                        $latestStatus = $summary['latest_status'];
                                    @endphp
                                    <div class="size-2 rounded-full bg-{{ $statusColors[$latestStatus] ?? 'zinc' }}-500"></div>
                                    <flux:text class="text-xs text-zinc-500">{{ $statusLabels[$latestStatus] ?? '' }}</flux:text>
                                @endif
                            </div>
                        </flux:card>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Expanded Panel --}}
        @if ($this->activeCategory && $this->activeCategory !== 'personal_information')
            @php
                $activeCat = StaffDocument::categories()[$this->activeCategory];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon :icon="$activeCat['icon']" class="size-4 text-zinc-600 dark:text-zinc-400" />
                        </div>
                        <flux:heading size="sm">{{ $activeCat['label'] }}</flux:heading>
                    </div>
                    @can('manage-staff')
                        <flux:button
                            wire:click="openUploadModal('{{ $this->activeCategory }}')"
                            icon="arrow-up-tray"
                            size="sm"
                        >
                            {{ __('Upload Document') }}
                        </flux:button>
                    @endcan
                </div>

                <flux:separator />

                @if ($this->activeDocuments->count() > 0)
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Document') }}</flux:table.column>
                            <flux:table.column>{{ __('Year') }}</flux:table.column>
                            <flux:table.column>{{ __('Type') }}</flux:table.column>
                            <flux:table.column>{{ __('Size') }}</flux:table.column>
                            <flux:table.column>{{ __('Expiry') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column class="w-24">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->activeDocuments as $doc)
                                <flux:table.row :key="$doc->id">
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <flux:icon :icon="$doc->file_icon" class="size-4 text-zinc-400 shrink-0" />
                                            <div>
                                                <flux:text class="font-medium text-sm">{{ $doc->title }}</flux:text>
                                                @if ($doc->description)
                                                    <flux:text class="text-xs text-zinc-500">{{ $doc->description }}</flux:text>
                                                @endif
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $doc->document_year ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="zinc">{{ strtoupper($doc->file_type) }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $doc->file_size_formatted }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($doc->expires_at)
                                            <span @class([
                                                'text-red-600 dark:text-red-400 font-medium text-sm' => $doc->expires_at->isPast(),
                                                'text-amber-600 dark:text-amber-400 font-medium text-sm' => !$doc->expires_at->isPast() && $doc->expires_at->diffInDays() <= 30,
                                                'text-sm' => $doc->expires_at->diffInDays() > 30,
                                            ])>
                                                {{ $doc->expires_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <flux:text class="text-zinc-400 text-sm">-</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" :color="$doc->status_color">{{ $doc->status_label }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-1">
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="arrow-down-tray"
                                                wire:click="downloadDocument({{ $doc->id }})"
                                                title="{{ __('Download') }}"
                                            />
                                            @can('manage-staff')
                                                <flux:button
                                                    variant="ghost"
                                                    size="sm"
                                                    icon="trash"
                                                    wire:click="deleteDocument({{ $doc->id }})"
                                                    wire:confirm="Are you sure you want to delete this document? This cannot be undone."
                                                    title="{{ __('Delete') }}"
                                                />
                                            @endcan
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @else
                    <div class="flex flex-col items-center py-10 text-center">
                        <flux:icon icon="folder-open" class="size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                        <flux:text class="text-zinc-500">{{ __('No documents in this category yet.') }}</flux:text>
                        @can('manage-staff')
                            <flux:button
                                wire:click="openUploadModal('{{ $this->activeCategory }}')"
                                icon="arrow-up-tray"
                                size="sm"
                                class="mt-4"
                            >
                                {{ __('Upload First Document') }}
                            </flux:button>
                        @endcan
                    </div>
                @endif
            </flux:card>
        @endif
    </div>

    {{-- Upload Modal --}}
    <flux:modal wire:model="showUploadModal" class="max-w-lg" @close="closeUploadModal">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Upload Document') }}</flux:heading>
            @if ($this->activeCategory)
                <flux:subheading>{{ StaffDocument::categories()[$this->activeCategory]['label'] ?? '' }}</flux:subheading>
            @endif

            <flux:separator />

            <form wire:submit="uploadDocument" class="space-y-4">
                <flux:input
                    wire:model="docTitle"
                    :label="__('Title')"
                    placeholder="e.g. Signed Employment Contract 2024"
                    required
                />

                <flux:textarea
                    wire:model="docDescription"
                    :label="__('Description')"
                    rows="2"
                    placeholder="Brief description of this document..."
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="docYear"
                        :label="__('Document Year')"
                        type="number"
                        placeholder="{{ now()->year }}"
                        min="2000"
                        max="2099"
                    />
                    <flux:input
                        wire:model="docExpiry"
                        :label="__('Expiry Date')"
                        type="date"
                    />
                </div>

                <flux:select wire:model="docStatus" :label="__('Status')" required>
                    <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                    <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                    <flux:select.option value="requires_update">{{ __('Requires Update') }}</flux:select.option>
                </flux:select>

                <div>
                    <flux:label>{{ __('File') }} <span class="text-red-500">*</span></flux:label>
                    <input
                        type="file"
                        wire:model="docFile"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="mt-1 block w-full text-sm text-zinc-600 dark:text-zinc-400
                               file:mr-3 file:rounded-md file:border-0
                               file:bg-zinc-100 file:px-3 file:py-1.5
                               file:text-sm file:font-medium
                               file:text-zinc-700 hover:file:bg-zinc-200
                               dark:file:bg-zinc-700 dark:file:text-zinc-200
                               dark:hover:file:bg-zinc-600"
                    />
                    @error('docFile')
                        <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                    <flux:text class="mt-1 text-xs text-zinc-400">PDF, DOC, DOCX, JPG, PNG — max 10 MB</flux:text>
                </div>

                <flux:textarea
                    wire:model="docNotes"
                    :label="__('Notes')"
                    rows="2"
                    placeholder="Any additional notes..."
                />

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="closeUploadModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="arrow-up-tray">
                        {{ __('Upload') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    @script
    <script>
        $wire.on('close-modal', () => {
            $wire.closeUploadModal();
        });
    </script>
    @endscript
</flux:main>
