<?php

use App\Concerns\DischargeValidationRules;
use App\Models\Agency;
use App\Models\Discharge;
use App\Models\Resident;
use App\Models\User;
use App\Services\DischargeReportService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Discharge Resident')]
class extends Component {
    use DischargeValidationRules;

    #[Locked]
    public int $residentId;

    public int $currentStep = 1;
    public int $totalSteps = 3;

    // Step 1: Provider & Clinical Summary
    public string $agency_name = '';
    public ?int $discharge_staff_id = null;
    public string $discharge_staff_name = '';
    public string $discharge_date = '';
    public string $next_level_of_care = '';
    public string $barriers_to_transition = '';
    public string $strengths_for_discharge = '';
    public string $reason_for_admission = '';
    public string $course_of_treatment = '';
    public string $discharge_status_recommendations = '';

    // Step 2: Condition, Crisis Plan & Appointments
    public string $discharge_condition_reason = '';
    public string $crisis_plan = '';
    public array $future_appointments = [];
    public array $selected_agencies = [];

    // Step 3: Special Needs, Medications & Possessions
    public string $special_needs = 'None';
    public array $medications_at_discharge = [];
    public string $personal_possessions = 'Client maintained possession of all personal belongings during treatment.';

    // AI tracking
    public bool $aiUsedForAftercare = false;
    public bool $aiUsedForClinical = false;
    public bool $aiUsedForCrisis = false;
    public bool $aiUsedForAppointments = false;

    // AI loading states
    public bool $generatingAftercare = false;
    public bool $generatingClinical = false;
    public bool $generatingCrisis = false;
    public bool $generatingAppointments = false;

    public function mount(Resident $resident): void
    {
        if ($resident->status !== 'active') {
            session()->flash('error', 'Only active residents can be discharged.');
            $this->redirect(route('residents.show', $resident), navigate: true);
            return;
        }

        $this->residentId = $resident->id;
        $this->discharge_date = now()->format('Y-m-d');
        $this->agency_name = system_setting('system_name', '');

        // Auto-populate discharge staff with current user
        $this->discharge_staff_id = auth()->id();
        $this->discharge_staff_name = auth()->user()->name;

        // Pre-populate medications from active prescriptions
        $activeMeds = $resident->medications()->where('status', 'active')->get();
        foreach ($activeMeds as $med) {
            $this->medications_at_discharge[] = [
                'name' => $med->name,
                'dosage' => $med->dosage . ' ' . $med->route,
                'quantity' => '',
            ];
        }

        // Add empty row if no medications
        if (empty($this->medications_at_discharge)) {
            $this->medications_at_discharge[] = ['name' => '', 'dosage' => '', 'quantity' => ''];
        }

        // Add one empty appointment row
        $this->future_appointments[] = [
            'date' => '',
            'time' => '',
            'provider' => '',
            'provider_id' => null,
            'location' => '',
            'agency_id' => null,
            'phone' => '',
            'notes' => '',
        ];

        // Pre-select all active agencies
        $this->selected_agencies = Agency::active()->pluck('id')->toArray();
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    #[Computed]
    public function staffUsers()
    {
        return User::permission('manage-residents')->orderBy('name')->get();
    }

    #[Computed]
    public function activeAgencies()
    {
        return Agency::active()
            ->orderByDesc('is_institution')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function isAdmin(): bool
    {
        return auth()->user()->can('manage-settings');
    }

    #[Computed]
    public function aiAvailable(): bool
    {
        try {
            return app(DischargeReportService::class)->isAiAvailable();
        } catch (\Exception) {
            return false;
        }
    }

    // Auto-fill phone when provider is selected
    public function updatedFutureAppointments($value, $key): void
    {
        // Parse key like "0.provider_id" or "0.agency_id"
        if (preg_match('/(\d+)\.(provider_id|agency_id)/', $key, $matches)) {
            $index = (int) $matches[1];
            $field = $matches[2];

            if ($field === 'provider_id' && $value) {
                $user = User::find($value);
                if ($user) {
                    $this->future_appointments[$index]['provider'] = $user->name;
                    // Try to get phone from staff profile if available
                    if ($user->staffProfile && $user->staffProfile->phone) {
                        $this->future_appointments[$index]['phone'] = $user->staffProfile->phone;
                    }
                }
            }

            if ($field === 'agency_id' && $value) {
                $agency = Agency::find($value);
                if ($agency) {
                    $this->future_appointments[$index]['location'] = $agency->name;
                    if ($agency->phone) {
                        $this->future_appointments[$index]['phone'] = $agency->phone;
                    }
                }
            }
        }
    }

    // AI Generation Methods
    public function generateAftercare(): void
    {
        if (!$this->aiAvailable) {
            $this->dispatch('notify', type: 'error', message: 'AI is not available.');
            return;
        }

        $this->generatingAftercare = true;

        try {
            $service = app(DischargeReportService::class);
            $result = $service->generateAftercare($this->resident);

            if ($result) {
                $this->next_level_of_care = $result['next_level_of_care'] ?? $this->next_level_of_care;
                $this->barriers_to_transition = $result['barriers_to_transition'] ?? $this->barriers_to_transition;
                $this->strengths_for_discharge = $result['strengths_for_discharge'] ?? $this->strengths_for_discharge;
                $this->aiUsedForAftercare = true;
                $this->dispatch('notify', type: 'success', message: 'Aftercare information generated.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Failed to generate aftercare information.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error generating aftercare: ' . $e->getMessage());
        } finally {
            $this->generatingAftercare = false;
        }
    }

    public function generateClinicalSummary(): void
    {
        if (!$this->aiAvailable) {
            $this->dispatch('notify', type: 'error', message: 'AI is not available.');
            return;
        }

        $this->generatingClinical = true;

        try {
            $service = app(DischargeReportService::class);
            $result = $service->generateClinicalSummary($this->resident);

            if ($result) {
                $this->reason_for_admission = $result['reason_for_admission'] ?? $this->reason_for_admission;
                $this->course_of_treatment = $result['course_of_treatment'] ?? $this->course_of_treatment;
                $this->discharge_status_recommendations = $result['discharge_status_recommendations'] ?? $this->discharge_status_recommendations;
                $this->aiUsedForClinical = true;
                $this->dispatch('notify', type: 'success', message: 'Clinical summary generated.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Failed to generate clinical summary.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error generating summary: ' . $e->getMessage());
        } finally {
            $this->generatingClinical = false;
        }
    }

    public function generateCrisisPlan(): void
    {
        if (!$this->aiAvailable) {
            $this->dispatch('notify', type: 'error', message: 'AI is not available.');
            return;
        }

        $this->generatingCrisis = true;

        try {
            $service = app(DischargeReportService::class);
            $result = $service->generateCrisisPlan($this->resident);

            if ($result) {
                $this->crisis_plan = $result;
                $this->aiUsedForCrisis = true;
                $this->dispatch('notify', type: 'success', message: 'Crisis plan generated.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Failed to generate crisis plan.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error generating crisis plan: ' . $e->getMessage());
        } finally {
            $this->generatingCrisis = false;
        }
    }

    public function suggestAppointments(): void
    {
        if (!$this->aiAvailable) {
            $this->dispatch('notify', type: 'error', message: 'AI is not available.');
            return;
        }

        $this->generatingAppointments = true;

        try {
            $service = app(DischargeReportService::class);
            $result = $service->suggestFollowUpAppointments($this->resident);

            if ($result && !empty($result)) {
                // Add suggested appointments
                foreach ($result as $suggestion) {
                    $this->future_appointments[] = [
                        'date' => $suggestion['date'] ?? '',
                        'time' => $suggestion['time'] ?? '',
                        'provider' => $suggestion['provider'] ?? '',
                        'provider_id' => null,
                        'location' => $suggestion['location'] ?? '',
                        'agency_id' => null,
                        'phone' => $suggestion['phone'] ?? '',
                        'notes' => $suggestion['notes'] ?? '',
                    ];
                }
                $this->aiUsedForAppointments = true;
                $this->dispatch('notify', type: 'success', message: 'Appointment suggestions added.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Failed to generate appointment suggestions.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error generating suggestions: ' . $e->getMessage());
        } finally {
            $this->generatingAppointments = false;
        }
    }

    public function nextStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => $this->dischargeStep1Rules(),
            2 => $this->dischargeStep2Rules(),
            3 => $this->dischargeStep3Rules(),
            default => [],
        };

        $this->validate($rules);
        $this->currentStep = min($this->currentStep + 1, $this->totalSteps);
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function addAppointment(): void
    {
        $this->future_appointments[] = [
            'date' => '',
            'time' => '',
            'provider' => '',
            'provider_id' => null,
            'location' => '',
            'agency_id' => null,
            'phone' => '',
            'notes' => '',
        ];
    }

    public function removeAppointment(int $index): void
    {
        unset($this->future_appointments[$index]);
        $this->future_appointments = array_values($this->future_appointments);
        if (empty($this->future_appointments)) {
            $this->future_appointments[] = [
                'date' => '',
                'time' => '',
                'provider' => '',
                'provider_id' => null,
                'location' => '',
                'agency_id' => null,
                'phone' => '',
                'notes' => '',
            ];
        }
    }

    public function addMedication(): void
    {
        $this->medications_at_discharge[] = ['name' => '', 'dosage' => '', 'quantity' => ''];
    }

    public function removeMedication(int $index): void
    {
        unset($this->medications_at_discharge[$index]);
        $this->medications_at_discharge = array_values($this->medications_at_discharge);
        if (empty($this->medications_at_discharge)) {
            $this->medications_at_discharge[] = ['name' => '', 'dosage' => '', 'quantity' => ''];
        }
    }

    public function toggleAgency(int $agencyId): void
    {
        if (in_array($agencyId, $this->selected_agencies)) {
            $this->selected_agencies = array_values(array_diff($this->selected_agencies, [$agencyId]));
        } else {
            $this->selected_agencies[] = $agencyId;
        }
    }

    public function discharge(bool $exportReport = false): void
    {
        $this->validate($this->allDischargeRules());

        // Filter out empty appointments and medications
        $appointments = array_filter($this->future_appointments, fn($a) => !empty($a['date']) || !empty($a['provider']));
        $medications = array_filter($this->medications_at_discharge, fn($m) => !empty($m['name']));

        // Check if admin override occurred
        $adminOverride = $this->isAdmin && $this->discharge_staff_id !== auth()->id();

        // Check if AI was used
        $aiUsed = $this->aiUsedForAftercare || $this->aiUsedForClinical || $this->aiUsedForCrisis || $this->aiUsedForAppointments;

        $discharge = Discharge::create([
            'resident_id' => $this->residentId,
            'agency_name' => $this->agency_name,
            'discharge_staff_id' => $this->discharge_staff_id,
            'discharge_staff_name' => $this->discharge_staff_name ?: $this->staffUsers->find($this->discharge_staff_id)?->name,
            'discharge_date' => $this->discharge_date,
            'next_level_of_care' => $this->next_level_of_care,
            'barriers_to_transition' => $this->barriers_to_transition,
            'strengths_for_discharge' => $this->strengths_for_discharge,
            'reason_for_admission' => $this->reason_for_admission,
            'course_of_treatment' => $this->course_of_treatment,
            'discharge_status_recommendations' => $this->discharge_status_recommendations,
            'discharge_condition_reason' => $this->discharge_condition_reason,
            'crisis_plan' => $this->crisis_plan,
            'future_appointments' => !empty($appointments) ? array_values($appointments) : null,
            'selected_agencies' => !empty($this->selected_agencies) ? $this->selected_agencies : null,
            'special_needs' => $this->special_needs,
            'medications_at_discharge' => !empty($medications) ? array_values($medications) : null,
            'personal_possessions' => $this->personal_possessions,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'admin_override_by' => $adminOverride ? auth()->id() : null,
            'admin_override_at' => $adminOverride ? now() : null,
            'ai_generated_at' => $aiUsed ? now() : null,
        ]);

        // Update resident status
        $this->resident->update([
            'status' => 'discharged',
            'discharge_date' => $this->discharge_date,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('status', 'Resident discharged successfully.');

        if ($exportReport) {
            $this->redirect(route('residents.discharge.export.pdf', $discharge), navigate: false);
        } else {
            $this->redirect(route('residents.show', $this->resident), navigate: true);
        }
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('residents.show', $this->resident)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Discharge Resident') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }} - {{ __('Complete discharge documentation') }}</flux:subheading>
            </div>
        </div>

        {{-- Step Indicator --}}
        <div class="flex items-center">
            @foreach([1 => 'Provider & Clinical', 2 => 'Crisis Plan & Appointments', 3 => 'Medications & Possessions'] as $step => $label)
                <div class="flex items-center {{ $step < $totalSteps ? 'flex-1' : '' }}">
                    <button
                        type="button"
                        wire:click="goToStep({{ $step }})"
                        @class([
                            'flex items-center justify-center size-9 rounded-full text-sm font-medium shrink-0 transition-colors',
                            'bg-zinc-800 text-white dark:bg-white dark:text-zinc-900' => $currentStep === $step,
                            'bg-green-500 text-white' => $currentStep > $step,
                            'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $currentStep < $step,
                            'cursor-pointer hover:opacity-80' => $step < $currentStep,
                            'cursor-default' => $step >= $currentStep,
                        ])
                        @if($step > $currentStep) disabled @endif
                    >
                        @if($currentStep > $step)
                            <flux:icon name="check" variant="mini" class="size-4" />
                        @else
                            {{ $step }}
                        @endif
                    </button>
                    <span @class([
                        'ml-2 text-sm font-medium hidden sm:block',
                        'text-zinc-900 dark:text-zinc-100' => $currentStep >= $step,
                        'text-zinc-400 dark:text-zinc-500' => $currentStep < $step,
                    ])>{{ __($label) }}</span>
                    @if($step < $totalSteps)
                        <div @class([
                            'flex-1 h-px mx-4',
                            'bg-green-500' => $currentStep > $step,
                            'bg-zinc-200 dark:bg-zinc-700' => $currentStep <= $step,
                        ])></div>
                    @endif
                </div>
            @endforeach
        </div>

        <form wire:submit="discharge" class="space-y-6">
            {{-- Step 1: Provider & Clinical Summary --}}
            @if($currentStep === 1)
                {{-- Member Information (Read-only) --}}
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Member Information') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:label>{{ __("Member's Name") }}</flux:label>
                            <div class="mt-1 font-medium">{{ $this->resident->full_name }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Date of Birth') }}</flux:label>
                            <div class="mt-1 font-medium">{{ $this->resident->date_of_birth?->format('m/d/Y') ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Date of Admission') }}</flux:label>
                            <div class="mt-1 font-medium">{{ $this->resident->admission_date?->format('m/d/Y') ?? 'N/A' }}</div>
                        </div>
                        <flux:input wire:model="discharge_date" :label="__('Date of Discharge')" type="date" required />
                    </div>
                </flux:card>

                {{-- Provider Information --}}
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Provider Information') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="agency_name" :label="__('Name of Agency')" />
                        <div>
                            @if($this->isAdmin)
                                <flux:select wire:model="discharge_staff_id" :label="__('Discharge Staff')">
                                    <flux:select.option value="">{{ __('Select staff member...') }}</flux:select.option>
                                    @foreach($this->staffUsers as $user)
                                        <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <p class="text-xs text-zinc-500 mt-1">{{ __('Admin: You can change the discharge staff.') }}</p>
                            @else
                                <flux:label>{{ __('Discharge Staff') }}</flux:label>
                                <div class="mt-1 flex items-center gap-2">
                                    <flux:avatar size="sm" name="{{ auth()->user()->name }}" />
                                    <span class="font-medium">{{ auth()->user()->name }}</span>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">{{ __('Auto-assigned to current user.') }}</p>
                            @endif
                        </div>
                    </div>
                </flux:card>

                {{-- Aftercare Information --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Aftercare Information') }}</flux:heading>
                        @if($this->aiAvailable)
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="generateAftercare"
                                wire:loading.attr="disabled"
                                wire:target="generateAftercare"
                                type="button"
                                icon="sparkles"
                            >
                                <span wire:loading.remove wire:target="generateAftercare">{{ __('Generate with AI') }}</span>
                                <span wire:loading wire:target="generateAftercare">{{ __('Generating...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                    @if($aiUsedForAftercare)
                        <flux:badge size="sm" color="purple" icon="sparkles">{{ __('AI assisted') }}</flux:badge>
                    @endif
                    <flux:separator />
                    <flux:input wire:model="next_level_of_care" :label="__('Next Level of Care Recommended')" />
                    <flux:textarea wire:model="barriers_to_transition" :label="__('Barriers to Discharge Transition')" rows="3" />
                    <flux:textarea wire:model="strengths_for_discharge" :label="__('Strengths for Discharge')" rows="3" />
                </flux:card>

                {{-- Clinical Summary --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Clinical Summary') }}</flux:heading>
                        @if($this->aiAvailable)
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="generateClinicalSummary"
                                wire:loading.attr="disabled"
                                wire:target="generateClinicalSummary"
                                type="button"
                                icon="sparkles"
                            >
                                <span wire:loading.remove wire:target="generateClinicalSummary">{{ __('Generate with AI') }}</span>
                                <span wire:loading wire:target="generateClinicalSummary">{{ __('Generating...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                    @if($aiUsedForClinical)
                        <flux:badge size="sm" color="purple" icon="sparkles">{{ __('AI assisted') }}</flux:badge>
                    @endif
                    <flux:separator />
                    <flux:textarea wire:model="reason_for_admission" :label="__('Reason for Admission')" rows="4" />
                    <flux:textarea wire:model="course_of_treatment" :label="__('Course of Treatment')" rows="4" />
                    <flux:textarea wire:model="discharge_status_recommendations" :label="__('Discharge Status and Recommendations')" rows="4" />
                </flux:card>
            @endif

            {{-- Step 2: Condition, Crisis Plan & Appointments --}}
            @if($currentStep === 2)
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Discharge Condition') }}</flux:heading>
                    <flux:separator />
                    <flux:textarea wire:model="discharge_condition_reason" :label="__('Discharge Condition/Reason')" rows="4" />
                </flux:card>

                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Crisis Plan') }}</flux:heading>
                        @if($this->aiAvailable)
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="generateCrisisPlan"
                                wire:loading.attr="disabled"
                                wire:target="generateCrisisPlan"
                                type="button"
                                icon="sparkles"
                            >
                                <span wire:loading.remove wire:target="generateCrisisPlan">{{ __('Generate with AI') }}</span>
                                <span wire:loading wire:target="generateCrisisPlan">{{ __('Generating...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                    @if($aiUsedForCrisis)
                        <flux:badge size="sm" color="purple" icon="sparkles">{{ __('AI assisted') }}</flux:badge>
                    @endif
                    <flux:separator />
                    <flux:textarea wire:model="crisis_plan" :label="__('Crisis Plan')" rows="4" />
                </flux:card>

                {{-- Agency Contacts with Toggle --}}
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Agency Contacts') }}</flux:heading>
                    <flux:subheading>{{ __('Select which agencies to include on the discharge summary.') }}</flux:subheading>
                    <flux:separator />
                    <div class="space-y-2">
                        @foreach($this->activeAgencies as $agency)
                            <label
                                wire:click="toggleAgency({{ $agency->id }})"
                                @class([
                                    'flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
                                    'border-green-500 bg-green-50 dark:bg-green-900/20' => in_array($agency->id, $selected_agencies),
                                    'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' => !in_array($agency->id, $selected_agencies),
                                ])
                            >
                                <flux:checkbox
                                    :checked="in_array($agency->id, $selected_agencies)"
                                    readonly
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $agency->name }}</span>
                                        @if($agency->is_institution)
                                            <flux:badge size="sm" color="blue">{{ __('Institution') }}</flux:badge>
                                        @endif
                                    </div>
                                    @if($agency->phone)
                                        <div class="text-sm text-zinc-500">{{ $agency->phone }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-zinc-500">
                        {{ __(':count of :total agencies selected', ['count' => count($selected_agencies), 'total' => $this->activeAgencies->count()]) }}
                    </p>
                </flux:card>

                {{-- Future Appointments --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Future Appointments') }}</flux:heading>
                        <div class="flex gap-2">
                            @if($this->aiAvailable)
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="suggestAppointments"
                                    wire:loading.attr="disabled"
                                    wire:target="suggestAppointments"
                                    type="button"
                                    icon="sparkles"
                                >
                                    <span wire:loading.remove wire:target="suggestAppointments">{{ __('Suggest') }}</span>
                                    <span wire:loading wire:target="suggestAppointments">{{ __('...') }}</span>
                                </flux:button>
                            @endif
                            <flux:button variant="ghost" size="sm" wire:click="addAppointment" type="button" icon="plus">
                                {{ __('Add') }}
                            </flux:button>
                        </div>
                    </div>
                    @if($aiUsedForAppointments)
                        <flux:badge size="sm" color="purple" icon="sparkles">{{ __('AI suggestions added') }}</flux:badge>
                    @endif
                    <flux:separator />
                    <div class="space-y-4">
                        @foreach($future_appointments as $index => $appointment)
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-zinc-500">{{ __('Appointment') }} #{{ $index + 1 }}</span>
                                    <flux:button variant="ghost" size="sm" wire:click="removeAppointment({{ $index }})" type="button" icon="trash" />
                                </div>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <flux:input
                                        wire:model="future_appointments.{{ $index }}.date"
                                        label="{{ __('Date') }}"
                                        type="date"
                                    />
                                    <flux:input
                                        wire:model="future_appointments.{{ $index }}.time"
                                        label="{{ __('Time') }}"
                                        type="time"
                                    />
                                    <flux:input
                                        wire:model="future_appointments.{{ $index }}.phone"
                                        label="{{ __('Phone') }}"
                                    />
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <flux:select
                                            wire:model.live="future_appointments.{{ $index }}.provider_id"
                                            label="{{ __('Provider') }}"
                                        >
                                            <flux:select.option value="">{{ __('Select or type below...') }}</flux:select.option>
                                            @foreach($this->staffUsers as $user)
                                                <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:input
                                            wire:model="future_appointments.{{ $index }}.provider"
                                            placeholder="{{ __('Or enter provider name') }}"
                                            class="mt-2"
                                        />
                                    </div>
                                    <div>
                                        <flux:select
                                            wire:model.live="future_appointments.{{ $index }}.agency_id"
                                            label="{{ __('Location') }}"
                                        >
                                            <flux:select.option value="">{{ __('Select or type below...') }}</flux:select.option>
                                            @foreach($this->activeAgencies as $agency)
                                                <flux:select.option value="{{ $agency->id }}">{{ $agency->name }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:input
                                            wire:model="future_appointments.{{ $index }}.location"
                                            placeholder="{{ __('Or enter location') }}"
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                                <flux:textarea
                                    wire:model="future_appointments.{{ $index }}.notes"
                                    label="{{ __('Notes') }}"
                                    rows="2"
                                    placeholder="{{ __('Additional notes about this appointment...') }}"
                                />
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif

            {{-- Step 3: Special Needs, Medications & Possessions --}}
            @if($currentStep === 3)
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Special Needs') }}</flux:heading>
                    <flux:separator />
                    <flux:textarea wire:model="special_needs" rows="3" />
                </flux:card>

                {{-- Medications at Discharge --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Medications at Discharge') }}</flux:heading>
                        <flux:button variant="ghost" size="sm" wire:click="addMedication" type="button" icon="plus">
                            {{ __('Add') }}
                        </flux:button>
                    </div>
                    <flux:separator />
                    <div class="space-y-3">
                        @foreach($medications_at_discharge as $index => $med)
                            <div class="grid gap-3 sm:grid-cols-4 items-end p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <flux:input wire:model="medications_at_discharge.{{ $index }}.name" label="{{ __('Name') }}" />
                                <flux:input wire:model="medications_at_discharge.{{ $index }}.dosage" label="{{ __('Dosage') }}" />
                                <flux:input wire:model="medications_at_discharge.{{ $index }}.quantity" label="{{ __('Quantity') }}" />
                                <div class="flex justify-end">
                                    <flux:button variant="ghost" size="sm" wire:click="removeMedication({{ $index }})" type="button" icon="trash" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Personal Possessions') }}</flux:heading>
                    <flux:separator />
                    <flux:textarea wire:model="personal_possessions" rows="3" />
                </flux:card>
            @endif

            {{-- Navigation Buttons --}}
            <div class="flex justify-between">
                <div>
                    @if($currentStep > 1)
                        <flux:button variant="ghost" wire:click="previousStep" type="button" icon="arrow-left">
                            {{ __('Previous') }}
                        </flux:button>
                    @else
                        <flux:button variant="ghost" :href="route('residents.show', $this->resident)" wire:navigate>
                            {{ __('Cancel') }}
                        </flux:button>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($currentStep < $totalSteps)
                        <flux:button variant="primary" wire:click="nextStep" type="button" icon-trailing="arrow-right">
                            {{ __('Next') }}
                        </flux:button>
                    @else
                        <flux:button variant="ghost" wire:click="discharge(true)" type="button" icon="document-arrow-down">
                            {{ __('Discharge & Export') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit" icon="check">
                            {{ __('Discharge Resident') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</flux:main>
