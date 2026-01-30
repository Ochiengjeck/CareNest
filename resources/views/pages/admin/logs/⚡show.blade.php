<?php

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Audit Log Details')]
class extends Component {
    #[Locked]
    public AuditLog $auditLog;

    public function mount(AuditLog $auditLog): void
    {
        $this->auditLog = $auditLog->load('user');
    }

    public function getChangedFields(): array
    {
        if (!$this->auditLog->old_values && !$this->auditLog->new_values) {
            return [];
        }

        $oldValues = $this->auditLog->old_values ?? [];
        $newValues = $this->auditLog->new_values ?? [];
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        $changes = [];
        foreach ($allKeys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            if ($old !== $new || $this->auditLog->action === 'created' || $this->auditLog->action === 'deleted') {
                $changes[] = [
                    'field' => $key,
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }

        return $changes;
    }

    public function formatValue($value): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        $stringValue = (string) $value;
        return Str::limit($stringValue, 100);
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Audit Log Details') }}</flux:heading>
            <flux:subheading>{{ __('View detailed information about this audit entry') }}</flux:subheading>
        </div>

        <div>
            <flux:button variant="ghost" :href="route('admin.logs.index')" wire:navigate icon="arrow-left">
                {{ __('Back to Audit Logs') }}
            </flux:button>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Main Info Card --}}
            <flux:card class="lg:col-span-2">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <flux:badge size="lg" :color="$auditLog->action_color">
                            {{ $auditLog->action_label }}
                        </flux:badge>
                        @if($auditLog->model_name)
                            <flux:badge size="lg" color="zinc">{{ $auditLog->model_name }}</flux:badge>
                        @endif
                    </div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $auditLog->created_at->format('F d, Y \a\t H:i:s') }}
                    </div>
                </div>

                @if($auditLog->description)
                    <div class="mt-4 text-zinc-700 dark:text-zinc-300">
                        {{ $auditLog->description }}
                    </div>
                @endif
            </flux:card>

            {{-- User Information --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('User Information') }}</flux:heading>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        @if($auditLog->user)
                            <flux:avatar size="md" :name="$auditLog->user->name" />
                            <div>
                                <div class="font-medium">{{ $auditLog->user->name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $auditLog->user->email }}</div>
                            </div>
                        @else
                            <flux:avatar size="md" name="?" />
                            <div>
                                <div class="font-medium text-zinc-500">{{ __('System / Unknown') }}</div>
                                <div class="text-sm text-zinc-400">{{ __('Action performed without authenticated user') }}</div>
                            </div>
                        @endif
                    </div>

                    @if($auditLog->ip_address)
                        <div>
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('IP Address') }}</div>
                            <div class="text-sm font-mono">{{ $auditLog->ip_address }}</div>
                        </div>
                    @endif

                    @if($auditLog->user_agent)
                        <div>
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('User Agent') }}</div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 break-all">{{ Str::limit($auditLog->user_agent, 150) }}</div>
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Affected Resource --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Affected Resource') }}</flux:heading>

                <div class="space-y-4">
                    @if($auditLog->auditable_type)
                        <div>
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('Model Type') }}</div>
                            <div class="text-sm">{{ $auditLog->model_name }}</div>
                        </div>
                    @endif

                    @if($auditLog->auditable_id)
                        <div>
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('Record ID') }}</div>
                            <div class="text-sm font-mono">#{{ $auditLog->auditable_id }}</div>
                        </div>
                    @endif

                    @if($auditLog->auditable_route)
                        <div>
                            <flux:button variant="ghost" size="sm" :href="$auditLog->auditable_route" wire:navigate icon="arrow-top-right-on-square">
                                {{ __('View Resource') }}
                            </flux:button>
                        </div>
                    @elseif($auditLog->action === 'deleted')
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                            {{ __('This record has been deleted') }}
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Changes --}}
            @php $changes = $this->getChangedFields(); @endphp
            @if(count($changes) > 0)
                <flux:card class="lg:col-span-2">
                    <flux:heading size="sm" class="mb-4">{{ __('Changes') }}</flux:heading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Field') }}</flux:table.column>
                            <flux:table.column>{{ __('Old Value') }}</flux:table.column>
                            <flux:table.column>{{ __('New Value') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach($changes as $change)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">
                                        {{ str_replace('_', ' ', ucfirst($change['field'])) }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($change['old'] !== null)
                                            <span class="text-sm font-mono text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded">
                                                {{ $this->formatValue($change['old']) }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($change['new'] !== null)
                                            <span class="text-sm font-mono text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded">
                                                {{ $this->formatValue($change['new']) }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            @endif

            {{-- Metadata --}}
            <flux:card class="lg:col-span-2">
                <flux:heading size="sm" class="mb-4">{{ __('Metadata') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('Log ID') }}</div>
                        <div class="text-sm font-mono">#{{ $auditLog->id }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('Action') }}</div>
                        <div class="text-sm">{{ $auditLog->action }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('Recorded At') }}</div>
                        <div class="text-sm">{{ $auditLog->created_at->diffForHumans() }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-1">{{ __('Summary') }}</div>
                        <div class="text-sm">{{ $auditLog->changes_summary }}</div>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</flux:main>
