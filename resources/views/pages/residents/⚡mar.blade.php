<?php

use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\Resident;
use App\Models\Signature;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Medication Administration Record')]
class extends Component {
    #[Locked]
    public int $residentId;

    public int $month;
    public int $year;

    // Modal state
    public bool $showAdminModal = false;
    public bool $isViewMode = false;
    public ?int $selectedMedId = null;
    public ?int $selectedDay = null;
    public ?string $selectedSlot = null;
    public ?int $existingLogId = null;

    // Form fields
    public ?int $adminUserId = null;
    public string $adminStatus = 'given';
    public string $adminNotes = '';
    public ?string $rawSignatureData = null;

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-medications'), 403);

        $this->residentId  = $resident->id;
        $this->month       = (int) now()->format('n');
        $this->year        = (int) now()->format('Y');
        $this->adminUserId = auth()->id();
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    #[Computed]
    public function medications()
    {
        return Medication::where('resident_id', $this->residentId)
            ->whereIn('status', ['active', 'on_hold'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function logsMatrix(): array
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        $logs = MedicationLog::whereIn('medication_id', $this->medications->pluck('id'))
            ->whereBetween('administered_at', [$start, $end])
            ->get();

        $matrix = [];
        foreach ($logs as $log) {
            $day  = (int) Carbon::parse($log->administered_at)->format('j');
            $slot = $log->slot_time ?? Carbon::parse($log->administered_at)->format('H:i');
            $matrix[$log->medication_id][$day][$slot] = $log;
        }

        return $matrix;
    }

    #[Computed]
    public function allUsers()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function daysInMonth(): int
    {
        return Carbon::createFromDate($this->year, $this->month)->daysInMonth;
    }

    #[Computed]
    public function monthLabel(): string
    {
        return Carbon::createFromDate($this->year, $this->month)->format('F Y');
    }

    #[Computed]
    public function facility(): string
    {
        return system_setting('facility_name', config('app.name'));
    }

    public function prevMonth(): void
    {
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
    }

    public function nextMonth(): void
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
    }

    public function openModal(int $medId, int $day, string $slot): void
    {
        $this->selectedMedId  = $medId;
        $this->selectedDay    = $day;
        $this->selectedSlot   = $slot;

        $existingLog = $this->logsMatrix[$medId][$day][$slot] ?? null;

        if ($existingLog) {
            $this->existingLogId    = $existingLog->id;
            $this->adminUserId      = $existingLog->administered_by ?? auth()->id();
            $this->adminStatus      = $existingLog->status;
            $this->adminNotes       = $existingLog->notes ?? '';
            $this->rawSignatureData = $existingLog->raw_signature_data;
            $this->isViewMode       = true;
        } else {
            $this->existingLogId    = null;
            $this->adminUserId      = auth()->id();
            $this->adminStatus      = 'given';
            $this->adminNotes       = '';
            $this->rawSignatureData = null;
            $this->isViewMode       = false;
        }

        $this->showAdminModal = true;
    }

    public function closeModal(): void
    {
        $this->showAdminModal   = false;
        $this->isViewMode       = false;
        $this->selectedMedId    = null;
        $this->selectedDay      = null;
        $this->selectedSlot     = null;
        $this->existingLogId    = null;
        $this->adminUserId      = auth()->id();
        $this->adminStatus      = 'given';
        $this->adminNotes       = '';
        $this->rawSignatureData = null;
    }

    public function setSignatureData(string $data): void
    {
        $this->rawSignatureData = $data;
    }

    public function saveAdministration(): void
    {
        $this->validate([
            'adminUserId' => ['required', 'exists:users,id'],
            'adminStatus' => ['required', 'string', 'in:given,refused,withheld,missed,hospital,home_pass,on_hold,unavailable,discontinued'],
            'adminNotes'  => ['nullable', 'string', 'max:5000'],
        ]);

        $selectedUser = $this->allUsers->firstWhere('id', $this->adminUserId);
        $initials = collect(explode(' ', $selectedUser?->name ?? ''))
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->implode('');

        $administeredAt = Carbon::createFromDate($this->year, $this->month, $this->selectedDay)
            ->setTimeFromTimeString($this->selectedSlot ?? '08:00');

        $data = [
            'medication_id'      => $this->selectedMedId,
            'resident_id'        => $this->residentId,
            'administered_at'    => $administeredAt,
            'status'             => $this->adminStatus,
            'notes'              => $this->adminNotes ?: null,
            'administered_by'    => $this->adminUserId,
            'initials'           => $initials,
            'slot_time'          => $this->selectedSlot,
            'raw_signature_data' => $this->rawSignatureData,
        ];

        if ($this->existingLogId) {
            MedicationLog::findOrFail($this->existingLogId)->update($data);
        } else {
            MedicationLog::create($data);
        }

        $statusLabels = [
            'given'        => 'Given',
            'hospital'     => 'Hospital',
            'home_pass'    => 'Home Pass',
            'refused'      => 'Refused',
            'on_hold'      => 'On Hold',
            'unavailable'  => 'Unavailable',
            'withheld'     => 'Withheld',
            'missed'       => 'Missed',
            'discontinued' => 'Discontinued',
        ];
        $toastLabel = $statusLabels[$this->adminStatus] ?? 'Saved';

        $this->closeModal();

        $this->js("Flux.toast('" . $toastLabel . " — administration recorded.', { variant: 'success' })");
    }
}; ?>

<flux:main>

@php
$statusMap = [
    'given'        => ['label' => 'Given',                      'code' => 'G',  'bg' => 'bg-teal-500',   'text' => 'text-white'],
    'hospital'     => ['label' => 'Hospital',                   'code' => 'H',  'bg' => 'bg-slate-400',  'text' => 'text-white'],
    'home_pass'    => ['label' => 'Home Pass',                  'code' => 'HP', 'bg' => 'bg-sky-400',    'text' => 'text-white'],
    'refused'      => ['label' => 'Refused Med',                'code' => 'RM', 'bg' => 'bg-red-500',    'text' => 'text-white'],
    'on_hold'      => ['label' => 'On Hold / Provider Orders',  'code' => 'HO', 'bg' => 'bg-amber-400',  'text' => 'text-zinc-800'],
    'unavailable'  => ['label' => 'Unavailable',                'code' => 'UN', 'bg' => 'bg-violet-500', 'text' => 'text-white'],
    'withheld'     => ['label' => 'Withheld',                   'code' => 'WT', 'bg' => 'bg-blue-500',   'text' => 'text-white'],
    'missed'       => ['label' => 'Missed',                     'code' => 'M',  'bg' => 'bg-rose-600',   'text' => 'text-white'],
    'discontinued' => ['label' => 'Discontinued',               'code' => 'D',  'bg' => 'bg-zinc-400',   'text' => 'text-white'],
];
@endphp

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" size="sm" :href="route('residents.show', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Medication Administration Record') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }} &mdash; {{ $this->facility }}</flux:subheading>
            </div>
        </div>

        {{-- Month navigator + actions --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <flux:button variant="outline" size="sm" wire:click="prevMonth" icon="chevron-left" />
                <span class="min-w-36 text-center text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ $this->monthLabel }}
                </span>
                <flux:button variant="outline" size="sm" wire:click="nextMonth" icon="chevron-right" />
            </div>
            @if($this->resident->isActive())
                <flux:button variant="primary" size="sm" :href="route('medications.create', ['resident_id' => $this->residentId])" wire:navigate icon="plus">
                    {{ __('Add Medication') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Flash message --}}
    @if(session('status'))
        <flux:callout icon="check-circle" color="green">
            <flux:callout.text>{{ session('status') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- No medications state --}}
    @if($this->medications->isEmpty())
        <flux:card class="py-12 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.beaker class="h-6 w-6 text-zinc-400" />
            </div>
            <flux:heading size="sm" class="mb-1">{{ __('No active medications') }}</flux:heading>
            <flux:subheading class="mb-4">{{ __('There are no active or on-hold medications for this resident.') }}</flux:subheading>
            <flux:button variant="primary" size="sm" :href="route('medications.create', ['resident_id' => $this->residentId])" wire:navigate icon="plus">
                {{ __('Add Medication') }}
            </flux:button>
        </flux:card>
    @endif

    {{-- Medication MAR cards --}}
    @foreach($this->medications as $med)
    @php
        $times = $med->scheduled_times;
        $medLogs = $this->logsMatrix[$med->id] ?? [];
    @endphp
    <div x-data="{ open: true }" class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        {{-- Medication header --}}
        <div class="flex items-center justify-between gap-4 px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 min-w-0">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{ $med->name }}</span>
                <span class="text-sm text-zinc-500">{{ $med->dosage }}</span>
                <flux:badge size="sm" color="zinc">{{ $med->route_label }}</flux:badge>
                <flux:badge size="sm" color="{{ $med->status_color }}">{{ ucfirst(str_replace('_', ' ', $med->status)) }}</flux:badge>
                <span class="text-xs text-zinc-400">{{ $med->frequency }}</span>
                <div class="flex gap-1">
                    @foreach($times as $t)
                        <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 px-2 py-0.5 text-xs font-mono text-blue-700 dark:text-blue-300">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
            <button @click="open = !open" class="flex-shrink-0 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- MAR grid --}}
        <div x-show="open" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="overflow-x-auto">
                <table class="border-collapse" style="min-width: max-content;">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800 border-b border-r border-zinc-200 dark:border-zinc-700 px-3 py-2 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide min-w-20">
                                {{ __('Time') }}
                            </th>
                            @for($d = 1; $d <= $this->daysInMonth; $d++)
                            <th class="border-b border-r border-zinc-200 dark:border-zinc-700 px-1 py-2 text-center text-xs font-semibold {{ $d === (int) now()->format('j') && $this->month === (int) now()->format('n') && $this->year === (int) now()->format('Y') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-zinc-500' }} min-w-9">
                                {{ $d }}
                            </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($times as $slot)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="sticky left-0 z-10 bg-white dark:bg-zinc-900 border-b border-r border-zinc-200 dark:border-zinc-700 px-3 py-1.5 text-xs font-mono font-semibold text-zinc-600 dark:text-zinc-400">
                                {{ $slot }}
                            </td>
                            @for($d = 1; $d <= $this->daysInMonth; $d++)
                            @php
                                $log = $medLogs[$d][$slot] ?? null;
                                $isFuture = Carbon::createFromDate($this->year, $this->month, $d)->isFuture();
                            @endphp
                            <td class="border-b border-r border-zinc-200 dark:border-zinc-700 p-1 text-center">
                                @if($log)
                                    @php $st = $statusMap[$log->status] ?? ['code' => '?', 'bg' => 'bg-zinc-300', 'text' => 'text-zinc-800']; @endphp
                                    <button
                                        wire:click="openModal({{ $med->id }}, {{ $d }}, '{{ $slot }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-md {{ $st['bg'] }} {{ $st['text'] }} text-[10px] font-bold leading-none hover:opacity-80 active:scale-95 transition-all cursor-pointer"
                                        title="{{ $st['label'] }} — {{ $log->initials ?? '' }}"
                                    >{{ $st['code'] }}</button>
                                @elseif(!$isFuture)
                                    <button
                                        wire:click="openModal({{ $med->id }}, {{ $d }}, '{{ $slot }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-md border-2 border-dashed border-zinc-300 dark:border-zinc-600 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all cursor-pointer group"
                                        title="{{ __('Record administration') }}"
                                    >
                                        <svg class="w-3 h-3 text-zinc-300 dark:text-zinc-600 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700"></span>
                                    </span>
                                @endif
                            </td>
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endforeach

    {{-- Legend --}}
    @if($this->medications->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-3">
        <p class="mb-2 text-xs font-semibold text-zinc-500 uppercase tracking-wide">{{ __('Legend') }}</p>
        <div class="flex flex-wrap gap-2">
            @foreach($statusMap as $key => $st)
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $st['bg'] }} {{ $st['text'] }} text-[9px] font-bold">{{ $st['code'] }}</span>
                    <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $st['label'] }}</span>
                </div>
            @endforeach
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded border-2 border-dashed border-zinc-300 dark:border-zinc-600"></span>
                <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Not recorded') }}</span>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Administration modal --}}
<div
    x-data="{
        drawing: false,
        lastX: 0, lastY: 0,
        getPos(e, canvas) {
            const rect = canvas.getBoundingClientRect();
            const src = e.touches ? e.touches[0] : e;
            return { x: src.clientX - rect.left, y: src.clientY - rect.top };
        },
        startDraw(e, canvas) {
            this.drawing = true;
            const p = this.getPos(e, canvas);
            this.lastX = p.x; this.lastY = p.y;
        },
        draw(e, canvas) {
            if (!this.drawing) return;
            e.preventDefault();
            const ctx = canvas.getContext('2d');
            const p = this.getPos(e, canvas);
            ctx.beginPath();
            ctx.moveTo(this.lastX, this.lastY);
            ctx.lineTo(p.x, p.y);
            ctx.strokeStyle = '#1e40af';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
            this.lastX = p.x; this.lastY = p.y;
        },
        stopDraw(canvas) {
            if (!this.drawing) return;
            this.drawing = false;
            $wire.setSignatureData(canvas.toDataURL());
        },
        clearPad(canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            $wire.setSignatureData(null);
        },
        loadSignature(canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            const existing = $wire.rawSignatureData;
            if (existing) {
                const img = new Image();
                img.onload = () => ctx.drawImage(img, 0, 0);
                img.src = existing;
            }
        },
        warnUnsigned: false,
        trySave() {
            if (!$wire.rawSignatureData) {
                this.warnUnsigned = true;
            } else {
                $wire.saveAdministration();
            }
        },
        confirmSaveAnyway() {
            this.warnUnsigned = false;
            $wire.saveAdministration();
        },
        init() {
            this.$watch('$wire.showAdminModal', (val) => {
                if (val) {
                    this.warnUnsigned = false;
                    this.$nextTick(() => this.loadSignature(this.$refs.sigCanvas));
                }
            });
            this.$watch('$wire.isViewMode', (val) => {
                if (!val) {
                    this.warnUnsigned = false;
                    this.$nextTick(() => this.loadSignature(this.$refs.sigCanvas));
                }
            });
        }
    }"
    x-show="$wire.showAdminModal"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$wire.closeModal()"></div>

    {{-- Modal panel --}}
    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
         @click.stop>

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
            <div>
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">
                    @php
                        $selMed = $this->medications->firstWhere('id', $this->selectedMedId);
                    @endphp
                    {{ $selMed?->name ?? __('Medication') }}
                </h3>
                <p class="text-xs text-zinc-500 mt-0.5">
                    @if($this->selectedDay && $this->selectedSlot)
                        {{ Carbon::createFromDate($this->year, $this->month, $this->selectedDay)->format('M j, Y') }}
                        &mdash; {{ $this->selectedSlot }}
                    @endif
                    @if($selMed)
                        &mdash; {{ $selMed->dosage }}
                    @endif
                </p>
            </div>
            <button wire:click="closeModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- VIEW MODE --}}
        @if($this->isViewMode && $this->existingLogId)
        @php
            $viewLog = \App\Models\MedicationLog::with('administeredBy')->find($this->existingLogId);
            $viewSt  = $statusMap[$viewLog?->status] ?? ['label' => ucfirst($viewLog?->status), 'code' => '?', 'bg' => 'bg-zinc-300', 'text' => 'text-zinc-800'];
            $viewSigUri = $viewLog?->raw_signature_data;
        @endphp
        <div class="px-5 py-4 space-y-4">

            {{-- Status + administered by --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">{{ __('Status') }}</p>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $viewSt['bg'] }} {{ $viewSt['text'] }}">
                        {{ $viewSt['code'] }} &mdash; {{ $viewSt['label'] }}
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">{{ __('Administered by') }}</p>
                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                        {{ $viewLog?->administeredBy?->name ?? '—' }}
                        @if($viewLog?->initials)
                            <span class="ml-1 text-xs font-mono text-zinc-400">({{ $viewLog->initials }})</span>
                        @endif
                    </p>
                    <p class="text-xs text-zinc-400">{{ $viewLog?->administered_at?->format('M j, Y g:i A') }}</p>
                </div>
            </div>

            {{-- Notes --}}
            @if($viewLog?->notes)
            <div>
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">{{ __('Notes') }}</p>
                <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $viewLog->notes }}</p>
            </div>
            @endif

            {{-- Signature --}}
            <div>
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-2">{{ __('Signature') }}</p>
                @if($viewSigUri)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-3 inline-block">
                        <img src="{{ $viewSigUri }}" alt="Signature" class="max-h-16 max-w-full">
                    </div>
                @else
                    <p class="text-sm text-zinc-400 italic">{{ __('Not signed') }}</p>
                @endif
            </div>

        </div>

        {{-- View mode footer --}}
        <div class="flex justify-between gap-3 px-5 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
            <flux:button variant="ghost" wire:click="closeModal">{{ __('Close') }}</flux:button>
            <flux:button variant="outline" wire:click="$set('isViewMode', false)" icon="pencil">{{ __('Edit') }}</flux:button>
        </div>

        {{-- EDIT MODE --}}
        @else
        <div class="px-5 py-4 space-y-4">

            {{-- Administered by --}}
            <div>
                <flux:label>{{ __('Administered by') }}</flux:label>
                <flux:select wire:model="adminUserId" class="mt-1">
                    @foreach($this->allUsers as $u)
                        @php
                            $uInitials = collect(explode(' ', $u->name))->map(fn($w) => strtoupper(substr($w,0,1)))->implode('');
                        @endphp
                        <flux:select.option value="{{ $u->id }}">{{ $u->name }} ({{ $uInitials }})</flux:select.option>
                    @endforeach
                </flux:select>
                @error('adminUserId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="adminStatus" class="mt-1">
                    @foreach($statusMap as $key => $st)
                        <flux:select.option value="{{ $key }}">{{ $st['code'] }} — {{ $st['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('adminStatus')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="adminNotes" rows="2" placeholder="{{ __('Optional notes...') }}" class="mt-1" />
            </div>

            {{-- Signature pad --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <flux:label>{{ __('Signature') }}</flux:label>
                    <button type="button"
                        @click="clearPad($refs.sigCanvas); $wire.setSignatureData(null)"
                        class="text-xs text-zinc-400 hover:text-red-500 transition-colors">
                        {{ __('Clear') }}
                    </button>
                </div>
                <div class="rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800 overflow-hidden">
                    <canvas
                        x-ref="sigCanvas"
                        width="440"
                        height="100"
                        class="w-full touch-none cursor-crosshair"
                        @mousedown="startDraw($event, $el)"
                        @mousemove="draw($event, $el)"
                        @mouseup="stopDraw($el)"
                        @mouseleave="stopDraw($el)"
                        @touchstart.prevent="startDraw($event, $el)"
                        @touchmove.prevent="draw($event, $el)"
                        @touchend="stopDraw($el)"
                    ></canvas>
                </div>
                <p class="mt-1 text-xs text-zinc-400">{{ __('Draw your signature above') }}</p>
            </div>

            {{-- Unsigned warning --}}
            <div x-show="warnUnsigned" x-transition class="rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-600 p-3">
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-300">{{ __('No signature provided') }}</p>
                        <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-400">{{ __('A signature is required for a complete record. Do you want to save without signing?') }}</p>
                        <div class="mt-2 flex gap-2">
                            <button type="button" @click="confirmSaveAnyway()"
                                class="rounded-md bg-amber-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-700 transition-colors">
                                {{ __('Save without signature') }}
                            </button>
                            <button type="button" @click="warnUnsigned = false"
                                class="rounded-md border border-amber-400 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                                {{ __('Go back to sign') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Edit mode footer --}}
        <div class="flex justify-between gap-3 px-5 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
            <div>
                @if($this->existingLogId)
                    <flux:button variant="ghost" wire:click="$set('isViewMode', true)" @click="warnUnsigned = false">{{ __('Back to view') }}</flux:button>
                @endif
            </div>
            <div class="flex gap-3">
                <flux:button variant="ghost" wire:click="closeModal" @click="warnUnsigned = false">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" @click="trySave()" wire:loading.attr="disabled" wire:target="saveAdministration">
                    <span wire:loading.remove wire:target="saveAdministration">{{ __('Save & Sign') }}</span>
                    <span wire:loading wire:target="saveAdministration">{{ __('Saving...') }}</span>
                </flux:button>
            </div>
        </div>
        @endif
        </div>

    </div>
</div>

</flux:main>
