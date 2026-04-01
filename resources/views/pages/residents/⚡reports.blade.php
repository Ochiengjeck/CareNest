<?php

use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Resident Reports')]
class extends Component {
    #[Locked]
    public int $residentId;

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->residentId = $resident->id;
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    protected function reportItems(): array
    {
        $residentId = $this->residentId;

        return [
            // Row 1
            [
                'key'         => 'shift_progress_note',
                'label'       => 'Shift Progress Note',
                'icon'        => 'clipboard-document-list',
                'description' => 'Daily shift observations and resident care notes',
                'status'      => 'available',
                'route_name'  => 'residents.progress-notes',
                'route_params' => [$residentId],
            ],
            [
                'key'         => 'discharge_summary',
                'label'       => 'Discharge Summary',
                'icon'        => 'document-text',
                'description' => 'Discharge documentation with AI-generated summary and PDF/Word export',
                'status'      => 'available',
                'route_name'  => 'residents.discharge',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'adl',
                'label'        => 'Activities of Daily Living',
                'icon'         => 'list-bullet',
                'description'  => 'Assessment of independence in eating, bathing, dressing, and mobility',
                'status'       => 'available',
                'route_name'   => 'residents.adl.index',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'financial_transaction',
                'label'        => 'Financial Transaction Record',
                'icon'         => 'banknotes',
                'description'  => 'Track care fees, personal allowances, and resident expenses',
                'status'       => 'available',
                'route_name'   => 'residents.financial-transactions.index',
                'route_params' => [$residentId],
            ],

            // Row 2
            [
                'key'          => 'staffing_note',
                'label'        => 'Staffing Note',
                'icon'         => 'chat-bubble-bottom-center-text',
                'description'  => 'Handover notes and care communications between staff',
                'status'       => 'available',
                'route_name'   => 'residents.staffing-notes.index',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'authorization',
                'label'        => 'Authorization for Release of Information',
                'icon'         => 'document-check',
                'description'  => 'Consent forms authorising disclosure of resident information',
                'status'       => 'available',
                'route_name'   => 'residents.authorizations.index',
                'route_params' => [$residentId],
            ],
            [
                'key'         => 'incident_report',
                'label'       => 'Incident Report Form',
                'icon'        => 'exclamation-triangle',
                'description' => 'Report falls, medication errors, injuries, and behavioural events',
                'status'      => 'available',
                'route_name'  => 'incidents.create',
                'route_params' => [],
            ],
            [
                'key'          => 'contact_note',
                'label'        => 'Contact Note',
                'icon'         => 'phone-arrow-up-right',
                'description'  => 'Log family calls, social worker visits, and care team meetings',
                'status'       => 'available',
                'route_name'   => 'residents.contact-notes.index',
                'route_params' => [$residentId],
            ],

            // Row 3
            [
                'key'          => 'bhp_progress_note',
                'label'        => 'BHP Progress Note',
                'icon'         => 'sparkles',
                'description'  => 'Behavioural Health Professional structured progress documentation',
                'status'       => 'available',
                'route_name'   => 'residents.bhp-progress-notes.index',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'asam_checklist',
                'label'        => 'ASAM Criteria Checklist',
                'icon'         => 'clipboard-document-check',
                'description'  => 'ASAM multi-dimensional addiction medicine assessment',
                'status'       => 'available',
                'route_name'   => 'residents.asam-checklists.index',
                'route_params' => [$residentId],
            ],
            [
                'key'         => 'discharge_planning',
                'label'       => 'Discharge Planning',
                'icon'        => 'map-pin',
                'description' => 'Pre-discharge planning: barriers, strengths, future appointments, and agencies',
                'status'      => 'available',
                'route_name'  => 'residents.discharge',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'initial_assessment',
                'label'        => 'Initial Assessment',
                'icon'         => 'magnifying-glass-circle',
                'description'  => 'Comprehensive care assessment completed at admission',
                'status'       => 'available',
                'route_name'   => 'residents.initial-assessments.index',
                'route_params' => [$residentId],
            ],

            // Row 4
            [
                'key'         => 'nursing_assessment',
                'label'       => 'Nursing Assessment',
                'icon'        => 'heart',
                'description' => 'Record and review clinical vitals and nursing observations',
                'status'      => 'available',
                'route_name'  => 'vitals.create',
                'route_params' => [],
            ],
            [
                'key'         => 'bhtp',
                'label'       => 'Behavioral Health Treatment Plan',
                'icon'        => 'academic-cap',
                'description' => 'Mental health and behavioural care plans for this resident',
                'status'      => 'available',
                'route_name'  => 'care-plans.index',
                'route_params' => [],
            ],
            [
                'key'          => 'face_sheet',
                'label'        => 'Face Sheet',
                'icon'         => 'identification',
                'description'  => 'One-page printable summary of key resident information',
                'status'       => 'available',
                'route_name'   => 'residents.face-sheets.index',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'safety_plan',
                'label'        => 'Safety Plan',
                'icon'         => 'shield-check',
                'description'  => 'Formal risk management plan for falls, wandering, and self-harm',
                'status'       => 'available',
                'route_name'   => 'residents.safety-plans.index',
                'route_params' => [$residentId],
            ],

            // Row 5
            [
                'key'         => 'resident_intakes',
                'label'       => 'Resident Intakes',
                'icon'        => 'user-plus',
                'description' => 'Admission details, medical history, and emergency contacts',
                'status'      => 'available',
                'route_name'  => 'residents.edit',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'mental_status',
                'label'        => 'Mental Status',
                'icon'         => 'face-smile',
                'description'  => 'Cognitive and behavioural mental status examination',
                'status'       => 'available',
                'route_name'   => 'residents.mental-status.index',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'treatment_refusal',
                'label'        => 'Refusal of Medical Treatment',
                'icon'         => 'no-symbol',
                'description'  => 'Document when a resident refuses medication or treatment',
                'status'       => 'available',
                'route_name'   => 'residents.treatment-refusals.index',
                'route_params' => [$residentId],
            ],
            [
                'key'          => 'appointment_log',
                'label'        => 'Appointment Tracking Log',
                'icon'         => 'calendar-days',
                'description'  => 'Schedule and track medical, therapy, and specialist appointments',
                'status'       => 'available',
                'route_name'   => 'residents.appointment-logs.index',
                'route_params' => [$residentId],
            ],
        ];
    }
}; ?>

<flux:main>
    <div class="max-w-6xl space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('residents.show', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <flux:badge :color="$this->resident->status === 'active' ? 'green' : ($this->resident->status === 'discharged' ? 'amber' : 'red')">
                {{ ucfirst($this->resident->status) }}
            </flux:badge>
            <span>&bull;</span>
            <span>{{ $this->resident->age }} years old</span>
            <span>&bull;</span>
            <span>Room {{ $this->resident->room_number ?? 'N/A' }}</span>
            <span>&bull;</span>
            <span class="text-zinc-400">19 available &bull; 1 coming soon</span>
        </div>

        {{-- Card grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->reportItems() as $item)
                @if ($item['status'] === 'available')
                    <a
                        href="{{ route($item['route_name'], $item['route_params']) }}"
                        wire:navigate
                        class="group block focus:outline-none"
                    >
                        <flux:card class="flex h-full flex-col transition duration-150 group-hover:ring-2 group-hover:ring-accent group-focus:ring-2 group-focus:ring-accent">
                            <flux:icon
                                :name="$item['icon']"
                                class="mb-3 size-8 text-accent"
                            />
                            <flux:heading size="sm" class="leading-snug">{{ $item['label'] }}</flux:heading>
                            <flux:text class="mt-1 grow text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item['description'] }}
                            </flux:text>
                            <div class="mt-3">
                                <flux:badge color="green" size="sm">{{ __('Available') }}</flux:badge>
                            </div>
                        </flux:card>
                    </a>
                @else
                    <div class="block">
                        <flux:card class="flex h-full flex-col opacity-50">
                            <flux:icon
                                :name="$item['icon']"
                                class="mb-3 size-8 text-zinc-400 dark:text-zinc-600"
                            />
                            <flux:heading size="sm" class="leading-snug text-zinc-500 dark:text-zinc-400">
                                {{ $item['label'] }}
                            </flux:heading>
                            <flux:text class="mt-1 grow text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $item['description'] }}
                            </flux:text>
                            <div class="mt-3">
                                <flux:badge color="zinc" size="sm">{{ __('Coming Soon') }}</flux:badge>
                            </div>
                        </flux:card>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</flux:main>
