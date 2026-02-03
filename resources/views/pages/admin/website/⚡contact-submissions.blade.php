<?php

use App\Models\ContactSubmission;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Contact Submissions')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $subject = '';

    #[Url]
    public string $dateRange = '';

    public bool $showDetailModal = false;
    public ?ContactSubmission $selectedSubmission = null;
    public string $adminNotes = '';

    // Statistics
    #[Computed]
    public function stats(): array
    {
        return [
            'total' => ContactSubmission::count(),
            'new' => ContactSubmission::new()->count(),
            'read' => ContactSubmission::byStatus('read')->count(),
            'replied' => ContactSubmission::byStatus('replied')->count(),
            'archived' => ContactSubmission::byStatus('archived')->count(),
            'today' => ContactSubmission::whereDate('created_at', today())->count(),
            'this_week' => ContactSubmission::recent(7)->count(),
            'this_month' => ContactSubmission::recent(30)->count(),
        ];
    }

    #[Computed]
    public function subjectStats(): array
    {
        return ContactSubmission::query()
            ->selectRaw('subject, count(*) as count')
            ->groupBy('subject')
            ->orderByDesc('count')
            ->pluck('count', 'subject')
            ->toArray();
    }

    #[Computed]
    public function submissions()
    {
        return ContactSubmission::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->status, fn($q) => $q->byStatus($this->status))
            ->when($this->subject, fn($q) => $q->bySubject($this->subject))
            ->when($this->dateRange, function ($q) {
                return match ($this->dateRange) {
                    'today' => $q->whereDate('created_at', today()),
                    'week' => $q->recent(7),
                    'month' => $q->recent(30),
                    'quarter' => $q->recent(90),
                    default => $q,
                };
            })
            ->latest()
            ->paginate(15);
    }

    public function viewSubmission(int $id): void
    {
        $this->selectedSubmission = ContactSubmission::findOrFail($id);
        $this->adminNotes = $this->selectedSubmission->admin_notes ?? '';

        // Mark as read if new
        $this->selectedSubmission->markAsRead();
        unset($this->stats);

        $this->showDetailModal = true;
    }

    public function markAsRead(int $id): void
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->update([
            'status' => ContactSubmission::STATUS_READ,
            'read_at' => now(),
        ]);
        unset($this->stats);
        unset($this->submissions);
    }

    public function markAsReplied(int $id): void
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->markAsReplied(auth()->id());

        if ($this->selectedSubmission?->id === $id) {
            $this->selectedSubmission->refresh();
        }

        unset($this->stats);
        unset($this->submissions);
    }

    public function archive(int $id): void
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->archive();

        if ($this->selectedSubmission?->id === $id) {
            $this->selectedSubmission->refresh();
        }

        unset($this->stats);
        unset($this->submissions);
    }

    public function restore(int $id): void
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->restore();

        if ($this->selectedSubmission?->id === $id) {
            $this->selectedSubmission->refresh();
        }

        unset($this->stats);
        unset($this->submissions);
    }

    public function saveNotes(): void
    {
        if ($this->selectedSubmission) {
            $this->selectedSubmission->update([
                'admin_notes' => $this->adminNotes ?: null,
            ]);

            $this->dispatch('notes-saved');
        }
    }

    public function delete(int $id): void
    {
        ContactSubmission::findOrFail($id)->delete();

        if ($this->selectedSubmission?->id === $id) {
            $this->showDetailModal = false;
            $this->selectedSubmission = null;
        }

        unset($this->stats);
        unset($this->submissions);
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'subject', 'dateRange']);
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSubject(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('Contact Submissions')"
        :subheading="__('Manage inquiries from the contact form')">

        <div class="space-y-6">
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <flux:card class="text-center">
                    <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total'] }}</div>
                    <div class="text-sm text-zinc-500">Total Submissions</div>
                </flux:card>
                <flux:card class="text-center {{ $this->stats['new'] > 0 ? 'ring-2 ring-amber-500' : '' }}">
                    <div class="text-3xl font-bold text-amber-600">{{ $this->stats['new'] }}</div>
                    <div class="text-sm text-zinc-500">New / Unread</div>
                </flux:card>
                <flux:card class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $this->stats['replied'] }}</div>
                    <div class="text-sm text-zinc-500">Replied</div>
                </flux:card>
                <flux:card class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $this->stats['this_week'] }}</div>
                    <div class="text-sm text-zinc-500">This Week</div>
                </flux:card>
            </div>

            {{-- Subject Breakdown --}}
            @if(count($this->subjectStats) > 0)
                <flux:card>
                    <flux:heading size="sm" class="mb-3">Submissions by Subject</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->subjectStats as $subjectName => $count)
                            <button
                                wire:click="$set('subject', '{{ $subjectName }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm {{ $subject === $subjectName ? 'bg-accent text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }} transition-colors"
                            >
                                {{ $subjectName }}
                                <span class="text-xs {{ $subject === $subjectName ? 'text-white/80' : 'text-zinc-500' }}">({{ $count }})</span>
                            </button>
                        @endforeach
                    </div>
                </flux:card>
            @endif

            {{-- Filters --}}
            <flux:card>
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search name, email, phone, message..."
                            icon="magnifying-glass"
                        />
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <flux:select wire:model.live="status" class="w-36">
                            <option value="">All Status</option>
                            @foreach(\App\Models\ContactSubmission::STATUSES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="subject" class="w-44">
                            <option value="">All Subjects</option>
                            @foreach(\App\Models\ContactSubmission::SUBJECTS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="dateRange" class="w-36">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">Last 90 Days</option>
                        </flux:select>

                        @if($search || $status || $subject || $dateRange)
                            <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark" size="sm">
                                Clear
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>

            {{-- Submissions Table --}}
            <flux:card class="overflow-hidden !p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                                <th class="text-left px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">Contact</th>
                                <th class="text-left px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
                                <th class="text-left px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300 hidden lg:table-cell">Message</th>
                                <th class="text-left px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">Date</th>
                                <th class="text-right px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($this->submissions as $submission)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $submission->is_new ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }}">
                                    <td class="px-4 py-3">
                                        <flux:badge :color="$submission->status_color" size="sm">
                                            {{ $submission->status_label }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $submission->name }}</div>
                                        <div class="text-zinc-500 text-xs">{{ $submission->email }}</div>
                                        @if($submission->phone)
                                            <div class="text-zinc-400 text-xs">{{ $submission->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $submission->subject }}</span>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell">
                                        <p class="text-zinc-600 dark:text-zinc-400 line-clamp-2 max-w-xs">{{ $submission->message }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-zinc-700 dark:text-zinc-300">{{ $submission->created_at->format('M d, Y') }}</div>
                                        <div class="text-zinc-400 text-xs">{{ $submission->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1">
                                            <flux:button variant="ghost" size="xs" wire:click="viewSubmission({{ $submission->id }})" icon="eye" title="View Details" />
                                            @if($submission->status !== 'replied')
                                                <flux:button variant="ghost" size="xs" wire:click="markAsReplied({{ $submission->id }})" icon="check" title="Mark as Replied" class="text-green-600" />
                                            @endif
                                            @if($submission->status !== 'archived')
                                                <flux:button variant="ghost" size="xs" wire:click="archive({{ $submission->id }})" icon="archive-box" title="Archive" />
                                            @else
                                                <flux:button variant="ghost" size="xs" wire:click="restore({{ $submission->id }})" icon="archive-box-arrow-down" title="Restore" />
                                            @endif
                                            <flux:button variant="ghost" size="xs" wire:click="delete({{ $submission->id }})" wire:confirm="Delete this submission permanently?" icon="trash" title="Delete" class="text-red-600" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <flux:icon.inbox class="size-12 text-zinc-400 mx-auto mb-3" />
                                        <p class="text-zinc-500">{{ __('No submissions found.') }}</p>
                                        @if($search || $status || $subject || $dateRange)
                                            <p class="text-sm text-zinc-400 mt-1">Try adjusting your filters.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($this->submissions->hasPages())
                    <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $this->submissions->links() }}
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Detail Modal --}}
        <flux:modal wire:model="showDetailModal" class="max-w-2xl">
            @if($selectedSubmission)
                <div class="space-y-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <flux:heading size="lg">Contact Submission</flux:heading>
                            <flux:subheading>{{ $selectedSubmission->created_at->format('F d, Y \a\t h:i A') }}</flux:subheading>
                        </div>
                        <flux:badge :color="$selectedSubmission->status_color">
                            {{ $selectedSubmission->status_label }}
                        </flux:badge>
                    </div>

                    {{-- Contact Info --}}
                    <div class="grid sm:grid-cols-2 gap-4 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                        <div>
                            <label class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Name</label>
                            <p class="text-zinc-900 dark:text-white font-medium">{{ $selectedSubmission->name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Email</label>
                            <p>
                                <a href="mailto:{{ $selectedSubmission->email }}" class="text-accent hover:underline">{{ $selectedSubmission->email }}</a>
                            </p>
                        </div>
                        @if($selectedSubmission->phone)
                            <div>
                                <label class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Phone</label>
                                <p>
                                    <a href="tel:{{ $selectedSubmission->phone }}" class="text-accent hover:underline">{{ $selectedSubmission->phone }}</a>
                                </p>
                            </div>
                        @endif
                        <div>
                            <label class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Subject</label>
                            <p class="text-zinc-900 dark:text-white">{{ $selectedSubmission->subject }}</p>
                        </div>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Message</label>
                        <div class="mt-2 p-4 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                            <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $selectedSubmission->message }}</p>
                        </div>
                    </div>

                    {{-- Admin Notes --}}
                    <div>
                        <label class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Admin Notes</label>
                        <textarea
                            wire:model="adminNotes"
                            rows="3"
                            placeholder="Add internal notes about this submission..."
                            class="mt-2 w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-accent focus:border-accent transition-colors resize-none"
                        ></textarea>
                        <div class="flex items-center gap-3 mt-2">
                            <flux:button variant="ghost" size="sm" wire:click="saveNotes" icon="check">
                                Save Notes
                            </flux:button>
                            <x-action-message on="notes-saved">{{ __('Saved!') }}</x-action-message>
                        </div>
                    </div>

                    {{-- Timeline / History --}}
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 text-sm space-y-2">
                        <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                            <flux:icon.clock class="size-4" />
                            <span>Submitted {{ $selectedSubmission->created_at->diffForHumans() }}</span>
                        </div>
                        @if($selectedSubmission->read_at)
                            <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                <flux:icon.eye class="size-4" />
                                <span>Read {{ $selectedSubmission->read_at->diffForHumans() }}</span>
                            </div>
                        @endif
                        @if($selectedSubmission->replied_at)
                            <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                <flux:icon.check-circle class="size-4" />
                                <span>Replied {{ $selectedSubmission->replied_at->diffForHumans() }}
                                    @if($selectedSubmission->repliedByUser)
                                        by {{ $selectedSubmission->repliedByUser->name }}
                                    @endif
                                </span>
                            </div>
                        @endif
                        @if($selectedSubmission->ip_address)
                            <div class="flex items-center gap-2 text-zinc-500">
                                <flux:icon.globe-alt class="size-4" />
                                <span class="text-xs">IP: {{ $selectedSubmission->ip_address }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <a
                            href="mailto:{{ $selectedSubmission->email }}?subject=Re: {{ $selectedSubmission->subject }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                        >
                            <flux:icon.envelope class="size-4" />
                            Reply via Email
                        </a>

                        @if($selectedSubmission->status !== 'replied')
                            <flux:button variant="ghost" wire:click="markAsReplied({{ $selectedSubmission->id }})" icon="check">
                                Mark as Replied
                            </flux:button>
                        @endif

                        @if($selectedSubmission->status !== 'archived')
                            <flux:button variant="ghost" wire:click="archive({{ $selectedSubmission->id }})" icon="archive-box">
                                Archive
                            </flux:button>
                        @else
                            <flux:button variant="ghost" wire:click="restore({{ $selectedSubmission->id }})" icon="archive-box-arrow-down">
                                Restore
                            </flux:button>
                        @endif

                        <flux:button variant="ghost" wire:click="delete({{ $selectedSubmission->id }})" wire:confirm="Delete this submission permanently?" icon="trash" class="text-red-600">
                            Delete
                        </flux:button>

                        <flux:button variant="ghost" wire:click="$set('showDetailModal', false)" class="ml-auto">
                            Close
                        </flux:button>
                    </div>
                </div>
            @endif
        </flux:modal>
    </x-pages.admin.website-layout>
</flux:main>
