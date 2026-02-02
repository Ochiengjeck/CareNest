<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Audit Logs Report')]
class extends Component {
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

    public bool $showExportModal = false;
    public string $exportFormat = 'csv';

    public function mount(): void
    {
        // Default to last 30 days
        if (!$this->dateFrom && !$this->dateTo) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    protected function baseQuery()
    {
        return AuditLog::query()
            ->when($this->userFilter, fn($q) => $q->byUser((int) $this->userFilter))
            ->when($this->modelFilter, fn($q) => $q->byModel($this->modelFilter))
            ->when($this->actionFilter, fn($q) => $q->byAction($this->actionFilter))
            ->when($this->dateFrom || $this->dateTo, fn($q) => $q->inDateRange($this->dateFrom, $this->dateTo));
    }

    #[Computed]
    public function logs()
    {
        return $this->baseQuery()->with('user')->latest()->limit(100)->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->baseQuery()->count();
    }

    #[Computed]
    public function actionBreakdown(): array
    {
        return $this->baseQuery()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }

    #[Computed]
    public function modelBreakdown(): array
    {
        return $this->baseQuery()
            ->whereNotNull('auditable_type')
            ->selectRaw('auditable_type, COUNT(*) as count')
            ->groupBy('auditable_type')
            ->pluck('count', 'auditable_type')
            ->map(fn($count, $type) => ['label' => class_basename($type), 'count' => $count])
            ->values()
            ->toArray();
    }

    #[Computed]
    public function userBreakdown(): array
    {
        return $this->baseQuery()
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name' => $item->user?->name ?? 'System',
                'count' => $item->count,
            ])
            ->toArray();
    }

    #[Computed]
    public function dailyActivity(): array
    {
        return $this->baseQuery()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
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
            'App\Models\TherapySession' => 'Therapy Session',
            'App\Models\TherapistAssignment' => 'Therapist Assignment',
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
        $this->userFilter = '';
        $this->modelFilter = '';
        $this->actionFilter = '';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function hasActiveFilters(): bool
    {
        return $this->userFilter !== ''
            || $this->modelFilter !== ''
            || $this->actionFilter !== '';
    }

    public function openExportModal(): void
    {
        $this->showExportModal = true;
    }

    public function export(): mixed
    {
        $logs = $this->baseQuery()->with('user')->latest()->get();

        return match ($this->exportFormat) {
            'csv' => $this->exportCsv($logs),
            'json' => $this->exportJson($logs),
            'pdf' => $this->exportPdf($logs),
            default => null,
        };
    }

    protected function exportCsv($logs)
    {
        $filename = 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Timestamp',
                'User',
                'Action',
                'Model Type',
                'Model ID',
                'Description',
                'IP Address',
                'Changes Summary',
            ]);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at?->format('Y-m-d H:i:s') ?? '-',
                    $log->user?->name ?? 'System',
                    $log->action_label,
                    $log->model_name ?? '-',
                    $log->auditable_id ?? '-',
                    $log->description ?? '-',
                    $log->ip_address ?? '-',
                    $log->changes_summary,
                ]);
            }

            fclose($file);
        };

        $this->showExportModal = false;

        return Response::stream($callback, 200, $headers);
    }

    protected function exportJson($logs)
    {
        $filename = 'audit-logs-' . now()->format('Y-m-d-His') . '.json';

        $data = $logs->map(fn($log) => [
            'id' => $log->id,
            'timestamp' => $log->created_at?->toIso8601String(),
            'user' => $log->user?->name,
            'user_id' => $log->user_id,
            'action' => $log->action,
            'action_label' => $log->action_label,
            'model_type' => $log->auditable_type,
            'model_name' => $log->model_name,
            'model_id' => $log->auditable_id,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
        ])->toArray();

        $exportData = [
            'generated_at' => now()->toIso8601String(),
            'filters' => [
                'user_id' => $this->userFilter ?: null,
                'model' => $this->modelFilter ?: null,
                'action' => $this->actionFilter ?: null,
                'date_from' => $this->dateFrom ?: null,
                'date_to' => $this->dateTo ?: null,
            ],
            'summary' => [
                'total_records' => count($data),
                'action_breakdown' => $this->actionBreakdown,
            ],
            'logs' => $data,
        ];

        $this->showExportModal = false;

        return Response::streamDownload(function() use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    protected function exportPdf($logs)
    {
        // For PDF, we'll generate an HTML view that can be printed
        $this->showExportModal = false;

        $this->dispatch('print-report');

        return null;
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('reports.index')" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Audit Logs Report') }}</flux:heading>
                    <flux:subheading>{{ __('System activity analysis and export') }}</flux:subheading>
                </div>
            </div>

            <flux:button variant="primary" wire:click="openExportModal" icon="arrow-down-tray">
                {{ __('Export Report') }}
            </flux:button>
        </div>

        {{-- Filters --}}
        <flux:card>
            <div class="space-y-4">
                <flux:heading size="sm">{{ __('Report Filters') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <flux:select wire:model.live="userFilter" :label="__('User')">
                        <flux:select.option value="">{{ __('All Users') }}</flux:select.option>
                        @foreach($this->users as $id => $name)
                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="modelFilter" :label="__('Model Type')">
                        <flux:select.option value="">{{ __('All Models') }}</flux:select.option>
                        @foreach($this->modelTypes as $type => $label)
                            <flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="actionFilter" :label="__('Action')">
                        <flux:select.option value="">{{ __('All Actions') }}</flux:select.option>
                        @foreach($this->actions as $action => $label)
                            <flux:select.option value="{{ $action }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model.live="dateFrom" type="date" :label="__('From Date')" />

                    <flux:input wire:model.live="dateTo" type="date" :label="__('To Date')" />
                </div>

                @if($this->hasActiveFilters())
                    <div class="flex justify-end">
                        <flux:button variant="ghost" size="sm" wire:click="clearFilters" icon="x-mark">
                            {{ __('Reset Filters') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Summary Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card class="text-center">
                <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Total Events') }}</flux:heading>
                <p class="mt-2 text-3xl font-bold">{{ number_format($this->totalCount) }}</p>
            </flux:card>

            <flux:card class="text-center">
                <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Creates') }}</flux:heading>
                <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($this->actionBreakdown['created'] ?? 0) }}</p>
            </flux:card>

            <flux:card class="text-center">
                <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Updates') }}</flux:heading>
                <p class="mt-2 text-3xl font-bold theme-accent-text">{{ number_format($this->actionBreakdown['updated'] ?? 0) }}</p>
            </flux:card>

            <flux:card class="text-center">
                <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Deletes') }}</flux:heading>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ number_format($this->actionBreakdown['deleted'] ?? 0) }}</p>
            </flux:card>
        </div>

        {{-- Breakdown Cards --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Action Breakdown --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Activity by Action Type') }}</flux:heading>
                <div class="space-y-3">
                    @forelse($this->actionBreakdown as $action => $count)
                        @php
                            $color = match($action) {
                                'created' => 'bg-green-500',
                                'updated' => 'theme-accent-bar',
                                'deleted' => 'bg-red-500',
                                'restored' => 'bg-amber-500',
                                'login' => 'bg-green-400',
                                'logout' => 'bg-zinc-400',
                                'login_failed' => 'bg-red-400',
                                default => 'bg-zinc-500',
                            };
                            $percentage = $this->totalCount > 0 ? ($count / $this->totalCount) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ ucfirst(str_replace('_', ' ', $action)) }}</span>
                                <span class="font-medium">{{ number_format($count) }} ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                <div class="{{ $color }} h-full rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">{{ __('No activity data') }}</p>
                    @endforelse
                </div>
            </flux:card>

            {{-- Model Breakdown --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Activity by Model') }}</flux:heading>
                <div class="space-y-3">
                    @forelse($this->modelBreakdown as $item)
                        @php
                            $percentage = $this->totalCount > 0 ? ($item['count'] / $this->totalCount) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ $item['label'] }}</span>
                                <span class="font-medium">{{ number_format($item['count']) }} ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">{{ __('No model data') }}</p>
                    @endforelse
                </div>
            </flux:card>
        </div>

        {{-- Top Users --}}
        <flux:card>
            <flux:heading size="sm" class="mb-4">{{ __('Most Active Users') }}</flux:heading>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-zinc-700">
                            <th class="text-left py-2 font-medium">{{ __('User') }}</th>
                            <th class="text-right py-2 font-medium">{{ __('Actions') }}</th>
                            <th class="text-right py-2 font-medium">{{ __('% of Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->userBreakdown as $item)
                            <tr class="border-b dark:border-zinc-700/50">
                                <td class="py-2">{{ $item['name'] }}</td>
                                <td class="text-right py-2 font-medium">{{ number_format($item['count']) }}</td>
                                <td class="text-right py-2 text-zinc-500">
                                    {{ $this->totalCount > 0 ? number_format(($item['count'] / $this->totalCount) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-zinc-500">{{ __('No user data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>

        {{-- Recent Logs Preview --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="sm">{{ __('Recent Activity (Last 100)') }}</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('admin.logs.index')" wire:navigate icon-trailing="arrow-right">
                    {{ __('View All Logs') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Timestamp') }}</flux:table.column>
                    <flux:table.column>{{ __('User') }}</flux:table.column>
                    <flux:table.column>{{ __('Action') }}</flux:table.column>
                    <flux:table.column>{{ __('Model') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="whitespace-nowrap text-sm">
                                {{ $log->created_at?->format('M d, H:i') ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-sm">
                                {{ $log->user?->name ?? 'System' }}
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
                            <flux:table.cell class="max-w-xs truncate text-sm">
                                {{ Str::limit($log->changes_summary, 40) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">
                                {{ __('No audit logs found for the selected filters') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    {{-- Export Modal --}}
    <flux:modal wire:model="showExportModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Export Audit Logs') }}</flux:heading>
                <flux:subheading>{{ __('Choose a format to export the filtered logs') }}</flux:subheading>
            </div>

            <div class="space-y-3">
                <flux:radio.group wire:model="exportFormat" variant="cards">
                    <flux:radio value="csv" label="CSV" description="Spreadsheet format, works with Excel/Google Sheets" />
                    <flux:radio value="json" label="JSON" description="Structured data format for developers" />
                    <flux:radio value="pdf" label="Print/PDF" description="Opens print dialog for PDF export" />
                </flux:radio.group>
            </div>

            <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-sm">
                <p class="font-medium mb-1">{{ __('Export Summary') }}</p>
                <ul class="text-zinc-600 dark:text-zinc-400 space-y-1">
                    <li>{{ __('Total records') }}: <span class="font-medium">{{ number_format($this->totalCount) }}</span></li>
                    <li>{{ __('Date range') }}: {{ $dateFrom ?: 'All' }} - {{ $dateTo ?: 'All' }}</li>
                    @if($userFilter)
                        <li>{{ __('User') }}: {{ $this->users[$userFilter] ?? 'Unknown' }}</li>
                    @endif
                    @if($modelFilter)
                        <li>{{ __('Model') }}: {{ $this->modelTypes[$modelFilter] ?? $modelFilter }}</li>
                    @endif
                    @if($actionFilter)
                        <li>{{ __('Action') }}: {{ $this->actions[$actionFilter] ?? $actionFilter }}</li>
                    @endif
                </ul>
            </div>

            <div class="flex gap-3 justify-end">
                <flux:button variant="ghost" wire:click="$set('showExportModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="export" icon="arrow-down-tray">
                    {{ __('Export') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Print Styles --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-report', () => {
                window.print();
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        @media print {
            body * { visibility: hidden; }
            .flux-main, .flux-main * { visibility: visible; }
            .flux-main { position: absolute; left: 0; top: 0; width: 100%; }
            button, .flux-button, [wire\:click], flux-modal { display: none !important; }
        }
    </style>
    @endpush
</flux:main>
