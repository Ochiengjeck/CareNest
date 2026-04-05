<?php

use App\Concerns\ArtMeetingValidationRules;
use App\Models\ArtMeeting;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New ART Meeting Note')]
class extends Component {
    use ArtMeetingValidationRules;

    #[Locked]
    public int $residentId;

    public string $meeting_date = '';
    public string $meeting_type = 'scheduled';
    public array $attendees = [];
    public string $new_attendee = '';
    public string $discussion_notes = '';
    public string $treatment_plan_revisions = '';
    public string $next_meeting_date = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId  = $resident->id;
        $this->meeting_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    public function addAttendee(): void
    {
        $name = trim($this->new_attendee);
        if ($name !== '' && ! in_array($name, $this->attendees)) {
            $this->attendees[] = $name;
        }
        $this->new_attendee = '';
    }

    public function removeAttendee(int $index): void
    {
        array_splice($this->attendees, $index, 1);
    }

    public function save(): void
    {
        $v = $this->validate($this->artMeetingRules());

        ArtMeeting::create([
            'resident_id'              => $this->residentId,
            'meeting_date'             => $v['meeting_date'],
            'meeting_type'             => $v['meeting_type'],
            'attendees'                => $this->attendees,
            'discussion_notes'         => $v['discussion_notes'] ?? null,
            'treatment_plan_revisions' => $v['treatment_plan_revisions'] ?? null,
            'next_meeting_date'        => $v['next_meeting_date'] ?? null,
            'recorded_by'              => auth()->id(),
            'created_by'               => auth()->id(),
        ]);

        session()->flash('status', 'ART meeting note saved successfully.');
        $this->redirect(route('residents.art-meetings.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.art-meetings.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New ART Meeting Note') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
            </div>
        </div>

        {{-- Resident info bar --}}
        <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/60 px-5 py-3 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="user" class="size-4 text-blue-400" />
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->full_name }}</span>
                </div>
                @if ($this->resident->ahcccs_id)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="identification" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">AHCCCS ID:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->ahcccs_id }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <flux:icon name="cake" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">DOB:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->date_of_birth->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="calendar" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Admitted:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            {{-- Meeting Details --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Meeting Details') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input type="date" wire:model="meeting_date" :label="__('Meeting Date')" required />
                    <div>
                        <flux:label>{{ __('Meeting Type') }}</flux:label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach (['scheduled' => 'Scheduled', 'emergency' => 'Emergency', 'discharge_planning' => 'Discharge Planning'] as $val => $label)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" wire:model.live="meeting_type" value="{{ $val }}" class="sr-only" />
                                    <span @class([
                                        'inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition',
                                        'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $meeting_type === $val,
                                        'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $meeting_type !== $val,
                                    ])>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('meeting_type') <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                    </div>
                </div>
                @error('meeting_date') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
            </flux:card>

            {{-- Attendees --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="users" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Attendees') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex gap-2">
                    <flux:input wire:model="new_attendee" :placeholder="__('Name and role, e.g. Dr. Smith (Psychiatrist)')" class="flex-1"
                        x-on:keydown.enter.prevent="$wire.addAttendee()" />
                    <flux:button type="button" wire:click="addAttendee" variant="outline" icon="plus">{{ __('Add') }}</flux:button>
                </div>
                @if (count($attendees) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($attendees as $i => $attendee)
                            <span class="flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                {{ $attendee }}
                                <button type="button" wire:click="removeAttendee({{ $i }})" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                    <flux:icon name="x-mark" class="size-3.5" />
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Discussion Notes --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Discussion Notes') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="discussion_notes" :label="__('Meeting Discussion')" rows="6"
                    :placeholder="__('Document the discussion, clinical updates, progress towards treatment goals, concerns raised...')" />
            </flux:card>

            {{-- Treatment Plan Revisions --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-magnifying-glass" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Treatment Plan Revisions') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="treatment_plan_revisions" :label="__('Revisions / Recommendations')" rows="4"
                    :placeholder="__('Any changes to the treatment plan, new goals, discontinued interventions...')" />
            </flux:card>

            {{-- Next Meeting --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="arrow-right-circle" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Next Meeting') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:input type="date" wire:model="next_meeting_date" :label="__('Next Meeting Date (optional)')" />
                @error('next_meeting_date') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.art-meetings.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Meeting Note') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
