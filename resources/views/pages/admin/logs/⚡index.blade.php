<?php

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Audit Logs')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $userFilter = '';

    #[Url]
    public string $modelFilter = '';

    #[Url]
    public string $actionFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function logs()
    {
        return AuditLog::query()
            ->with('user')
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->userFilter, fn($q) => $q->byUser((int) $this->userFilter))
            ->when($this->modelFilter, fn($q) => $q->byModel($this->modelFilter))
            ->when($this->actionFilter, fn($q) => $q->byAction($this->actionFilter))
            ->when($this->dateFrom || $this->dateTo, fn($q) => $q->inDateRange($this->dateFrom, $this->dateTo))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function users(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function modelTypes(): array
    {
        return [
            'App\Models\User' => 'User',
            'App\Models\Resident' => 'Resident',
            'App\Models\CarePlan' => 'Care Plan',
            'App\Models\Medication' => 'Medication',
            'App\Models\Incident' => 'Incident',
        ];
    }

    #[Computed]
    public function actions(): array
    {
        return [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Login',
            'logout' => 'Logout',
            'login_failed' => 'Login Failed',
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->userFilter = '';
        $this->modelFilter = '';
        $this->actionFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatedModelFilter(): void
    {
        $this->resetPage();
    }

    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->userFilter !== ''
            || $this->modelFilter !== ''
            || $this->actionFilter !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Audit Logs') }}</flux:heading>
            <flux:subheading>{{ __('Track all system activities and changes') }}</flux:subheading>
        </div>
            {{-- Filters --}}
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search description or user..."
                        icon="magnifying-glass"
                        class="sm:max-w-xs"
                    />

                    <flux:select wire:model.live="userFilter" class="sm:max-w-xs">
                        <flux:select.option value="">{{ __('All Users') }}</flux:select.option>
                        @foreach($this->users as $id => $name)
                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="modelFilter" class="sm:max-w-xs">
                        <flux:select.option value="">{{ __('All Models') }}</flux:select.option>
                        @foreach($this->modelTypes as $type => $label)
                            <flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="actionFilter" class="sm:max-w-xs">
                        <flux:select.option value="">{{ __('All Actions') }}</flux:select.option>
                        @foreach($this->actions as $action => $label)
                            <flux:select.option value="{{ $action }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <flux:input
                        wire:model.live="dateFrom"
                        type="date"
                        label="From"
                        class="sm:max-w-xs"
                    />

                    <flux:input
                        wire:model.live="dateTo"
                        type="date"
                        label="To"
                        class="sm:max-w-xs"
                    />

                    @if($this->hasActiveFilters())
                        <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark">
                            {{ __('Clear Filters') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Timestamp') }}</flux:table.column>
                    <flux:table.column>{{ __('User') }}</flux:table.column>
                    <flux:table.column>{{ __('Action') }}</flux:table.column>
                    <flux:table.column>{{ __('Model') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                    <flux:table.column class="w-20"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="whitespace-nowrap">
                                <span class="text-sm">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 block">{{ $log->created_at->format('H:i:s') }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($log->user)
                                    <div class="flex items-center gap-2">
                                        <flux:avatar size="xs" :name="$log->user->name" />
                                        <span class="text-sm">{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('System / Unknown') }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$log->action_color">
                                    {{ $log->action_label }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($log->model_name)
                                    <flux:badge size="sm" color="zinc">{{ $log->model_name }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="max-w-xs truncate">
                                <span class="text-sm" title="{{ $log->description }}">{{ Str::limit($log->description, 50) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="eye"
                                    :href="route('admin.logs.show', $log)"
                                    wire:navigate
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8">
                                <x-dashboard.empty-state
                                    title="No audit logs found"
                                    description="Try adjusting your filters or wait for system activity"
                                    icon="document-text"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-6">
                {{ $this->logs->links() }}
            </div>
        </div>
    </div>
</flux:main>
