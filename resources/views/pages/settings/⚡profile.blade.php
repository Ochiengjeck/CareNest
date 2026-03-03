<?php

use App\Concerns\ProfileValidationRules;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';

    // Signature creation modal
    public bool $showSignatureModal = false;
    public string $newSigName = '';
    public string $newSigPenColor = '#000000';
    public string $newSigMode = 'draw';
    public $newSigUpload;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Open the signature creation modal.
     */
    public function openSignatureModal(): void
    {
        $this->showSignatureModal = true;
    }

    /**
     * Close the signature creation modal and reset its state.
     */
    public function closeSignatureModal(): void
    {
        $this->showSignatureModal = false;
        $this->reset(['newSigName', 'newSigPenColor', 'newSigMode', 'newSigUpload']);
        $this->newSigPenColor = '#000000';
    }

    /**
     * Save a drawn signature from the canvas.
     * $penColor is passed directly from Alpine to avoid wire:ignore sync issues.
     */
    public function saveDrawnSignature(string $dataUrl, string $penColor = '#000000'): void
    {
        $this->newSigPenColor = $penColor;

        // Validate Livewire properties normally
        $this->validate([
            'newSigName'     => $this->signatureNameRules(),
            'newSigPenColor' => $this->signaturePenColorRules(),
        ], [], [
            'newSigName'     => 'signature name',
            'newSigPenColor' => 'pen color',
        ]);

        // dataUrl is a method argument, not a Livewire property — use Validator directly
        Validator::make(
            ['signature' => $dataUrl],
            ['signature' => $this->signatureRules()],
            [],
            ['signature' => 'signature drawing'],
        )->validate();

        $user = Auth::user();
        $isFirst = ! $user->signatures()->exists();

        $user->signatures()->create([
            'name' => $this->newSigName,
            'pen_color' => $this->newSigPenColor,
            'signature_data' => $dataUrl,
            'is_active' => $isFirst,
        ]);

        $this->closeSignatureModal();
        unset($this->signatures);
        $this->dispatch('signature-saved');
    }

    /**
     * Save an uploaded signature image.
     */
    public function saveUploadedSignature(): void
    {
        $this->validate([
            'newSigName' => $this->signatureNameRules(),
            'newSigUpload' => $this->signatureUploadRules(),
        ], [], [
            'newSigName' => 'signature name',
            'newSigUpload' => 'signature image',
        ]);

        $contents = file_get_contents($this->newSigUpload->getRealPath());
        $mime = $this->newSigUpload->getMimeType();
        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($contents);

        $user = Auth::user();
        $isFirst = ! $user->signatures()->exists();

        $user->signatures()->create([
            'name' => $this->newSigName,
            'pen_color' => '#000000',
            'signature_data' => $dataUrl,
            'is_active' => $isFirst,
        ]);

        $this->closeSignatureModal();
        unset($this->signatures);
        $this->dispatch('signature-saved');
    }

    /**
     * Set a signature as the active one (used in report exports).
     */
    public function activateSignature(int $id): void
    {
        $user = Auth::user();

        $sig = $user->signatures()->find($id);
        if (! $sig) {
            return;
        }

        $user->signatures()->update(['is_active' => false]);
        $sig->update(['is_active' => true]);

        unset($this->signatures);
        $this->dispatch('signature-activated');
    }

    /**
     * Deactivate a signature (keeps it saved but unused in exports).
     */
    public function deactivateSignature(int $id): void
    {
        $user = Auth::user();

        $sig = $user->signatures()->find($id);
        if ($sig) {
            $sig->update(['is_active' => false]);
            unset($this->signatures);
            $this->dispatch('signature-deactivated');
        }
    }

    /**
     * Delete a signature permanently.
     */
    public function deleteSignature(int $id): void
    {
        $user = Auth::user();

        $sig = $user->signatures()->find($id);
        if (! $sig) {
            return;
        }

        $wasActive = $sig->is_active;
        $sig->delete();

        // If it was active, promote the most recent remaining signature
        if ($wasActive) {
            $next = $user->signatures()->latest()->first();
            $next?->update(['is_active' => true]);
        }

        unset($this->signatures);
        $this->dispatch('signature-deleted');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function signatures()
    {
        return Auth::user()->signatures()->latest()->get();
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<div>
    @include('partials.settings-heading')

    <div class="max-w-2xl space-y-6">

        {{-- Card 1: Profile Information --}}
        <flux:card>
            <form wire:submit="updateProfileInformation">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <flux:icon.user class="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('Profile Information') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">{{ __('Update your name and email address') }}</flux:text>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <x-action-message class="text-sm text-green-600 dark:text-green-400" on="profile-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                        <flux:button variant="primary" size="sm" type="submit" data-test="update-profile-button">
                            {{ __('Save Changes') }}
                        </flux:button>
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                    <div>
                        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                        @if ($this->hasUnverifiedEmail)
                            <div class="mt-3">
                                <flux:text size="sm">
                                    {{ __('Your email address is unverified.') }}
                                    <flux:link class="cursor-pointer text-sm" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </flux:text>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text size="sm" class="mt-2 font-medium !text-green-600 dark:!text-green-400">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </flux:text>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </flux:card>

        {{-- Card 2: Digital Signatures --}}
        <flux:card>
            <div class="mb-5 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-900/20">
                        <flux:icon.pencil-square class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('Digital Signatures') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">{{ __('Only the active signature is embedded in report exports.') }}</flux:text>
                    </div>
                </div>
                <flux:button variant="primary" size="sm" icon="plus" wire:click="openSignatureModal" class="shrink-0">
                    {{ __('New Signature') }}
                </flux:button>
            </div>

            @if ($this->signatures->isEmpty())
                <div class="rounded-lg border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-600">
                    <flux:icon.pencil-square class="mx-auto mb-3 size-10 text-zinc-400" />
                    <flux:heading size="sm" class="text-zinc-500">{{ __('No signatures yet') }}</flux:heading>
                    <flux:text size="sm" class="mt-1 text-zinc-400">{{ __('Create your first digital signature to use in report exports.') }}</flux:text>
                    <flux:button variant="primary" size="sm" class="mt-4" icon="plus" wire:click="openSignatureModal">
                        {{ __('Create Signature') }}
                    </flux:button>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->signatures as $sig)
                        <div class="relative flex flex-col gap-3 rounded-lg border p-4 {{ $sig->is_active ? 'border-blue-400 ring-1 ring-blue-400 dark:border-blue-500 dark:ring-blue-500' : 'border-zinc-200 dark:border-zinc-700' }} bg-white dark:bg-zinc-800">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        class="inline-block size-3 shrink-0 rounded-full border border-zinc-300"
                                        style="background-color: {{ $sig->pen_color }}"
                                        title="{{ __('Pen color: :color', ['color' => $sig->pen_color]) }}"
                                    ></span>
                                    <flux:heading size="sm" class="truncate">{{ $sig->name }}</flux:heading>
                                </div>
                                @if ($sig->is_active)
                                    <flux:badge size="sm" color="blue" class="shrink-0">{{ __('Active') }}</flux:badge>
                                @endif
                            </div>

                            <div class="flex items-center justify-center rounded border border-zinc-200 bg-zinc-50 p-2 dark:bg-white" style="min-height: 72px;">
                                <img
                                    src="{{ $sig->getDataUri() }}"
                                    alt="{{ $sig->name }}"
                                    style="max-height: 64px; max-width: 100%; object-fit: contain;"
                                >
                            </div>

                            <flux:text size="sm" class="text-zinc-400">
                                {{ __('Created') }} {{ $sig->created_at->diffForHumans() }}
                            </flux:text>

                            <div class="flex items-center gap-2 border-t border-zinc-100 pt-1 dark:border-zinc-700">
                                @if (! $sig->is_active)
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="check-circle"
                                        wire:click="activateSignature({{ $sig->id }})"
                                        class="flex-1"
                                    >
                                        {{ __('Set Active') }}
                                    </flux:button>
                                @else
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pause-circle"
                                        wire:click="deactivateSignature({{ $sig->id }})"
                                        class="flex-1"
                                    >
                                        {{ __('Deactivate') }}
                                    </flux:button>
                                @endif

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="deleteSignature({{ $sig->id }})"
                                    wire:confirm="{{ __('Delete this signature? This cannot be undone.') }}"
                                    class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>

        {{-- Card 3: Danger Zone --}}
        @if ($this->showDeleteUser)
            <flux:card class="border-red-200 dark:border-red-800">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="text-red-600 dark:text-red-400">{{ __('Danger Zone') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">{{ __('Irreversible account actions') }}</flux:text>
                    </div>
                </div>
                <livewire:pages::settings.delete-user-form />
            </flux:card>
        @endif

    </div>

    {{-- Create Signature Modal --}}
    <flux:modal wire:model="showSignatureModal" name="create-signature" class="w-full max-w-lg">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Create Signature') }}</flux:heading>
                <flux:subheading>{{ __('Draw or upload your signature, then give it a name.') }}</flux:subheading>
            </div>

            {{-- Signature Name --}}
            <flux:input
                wire:model="newSigName"
                :label="__('Signature Name')"
                placeholder="{{ __('e.g. Professional Signature') }}"
                required
            />
            @error('newSigName')
                <flux:text size="sm" class="-mt-3 text-red-500">{{ $message }}</flux:text>
            @enderror

            {{-- Mode Toggle --}}
            <div>
                <flux:label>{{ __('Input Method') }}</flux:label>
                <div class="mt-1.5 flex gap-2">
                    <flux:button
                        size="sm"
                        :variant="$newSigMode === 'draw' ? 'primary' : 'ghost'"
                        wire:click="$set('newSigMode', 'draw')"
                        icon="pencil"
                    >
                        {{ __('Draw') }}
                    </flux:button>
                    <flux:button
                        size="sm"
                        :variant="$newSigMode === 'upload' ? 'primary' : 'ghost'"
                        wire:click="$set('newSigMode', 'upload')"
                        icon="arrow-up-tray"
                    >
                        {{ __('Upload') }}
                    </flux:button>
                </div>
            </div>

            {{-- Draw Mode --}}
            @if ($newSigMode === 'draw')
                <div
                    x-data="signatureCreator(@js($newSigPenColor))"
                    wire:ignore
                >
                    {{-- Pen Color Picker --}}
                    <div class="mb-3 flex items-center gap-3">
                        <flux:label>{{ __('Pen Color') }}</flux:label>
                        <div class="flex items-center gap-2">
                            <input
                                type="color"
                                x-model="penColor"
                                class="h-8 w-10 cursor-pointer rounded border border-zinc-300 bg-white p-0.5 dark:border-zinc-600 dark:bg-zinc-800"
                                title="{{ __('Pick pen color') }}"
                            >
                            <span class="font-mono text-sm text-zinc-500" x-text="penColor"></span>
                        </div>
                        {{-- Preset colors --}}
                        <div class="flex gap-1.5">
                            @foreach (['#000000', '#1e40af', '#15803d', '#7c3aed', '#b91c1c'] as $preset)
                                <button
                                    type="button"
                                    class="size-6 rounded-full border-2 border-white ring-1 ring-zinc-300 transition-shadow hover:ring-zinc-500"
                                    style="background-color: {{ $preset }}"
                                    x-on:click="penColor = '{{ $preset }}'"
                                    title="{{ $preset }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Canvas --}}
                    <div class="rounded-lg border-2 border-zinc-300 bg-white dark:border-zinc-600" style="width: 100%;">
                        <canvas
                            x-ref="canvas"
                            style="width: 100%; height: 200px; cursor: crosshair; touch-action: none; display: block;"
                        ></canvas>
                    </div>
                    <flux:text size="sm" class="mt-1.5 text-zinc-400">{{ __('Draw your signature in the box above.') }}</flux:text>

                    {{-- Canvas Actions --}}
                    <div class="mt-3 flex items-center justify-between">
                        <flux:button size="sm" variant="ghost" icon="arrow-path" x-on:click="clear()">
                            {{ __('Clear') }}
                        </flux:button>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="ghost" x-on:click="$wire.closeSignatureModal()">
                                {{ __('Cancel') }}
                            </flux:button>
                            <flux:button
                                size="sm"
                                variant="primary"
                                x-on:click="save()"
                                x-bind:disabled="isEmpty || saving"
                                x-bind:icon="saving ? 'arrow-path' : 'check'"
                                x-bind:class="saving ? 'animate-spin-icon' : ''"
                            >
                                <span x-text="saving ? '{{ __('Saving…') }}' : '{{ __('Save Signature') }}'"></span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Upload Mode --}}
            @if ($newSigMode === 'upload')
                <div class="space-y-3">
                    <div>
                        <flux:label>{{ __('Signature Image') }}</flux:label>
                        <input
                            type="file"
                            wire:model="newSigUpload"
                            accept="image/png,image/jpeg"
                            class="mt-1.5 block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300"
                        />
                        <flux:text size="sm" class="mt-1 text-zinc-400">{{ __('PNG or JPG · max 512 KB · max 800×400 px') }}</flux:text>
                        @error('newSigUpload')
                            <flux:text size="sm" class="mt-1 text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    @if ($newSigUpload)
                        <div class="inline-block rounded border border-zinc-200 bg-white p-2">
                            <img src="{{ $newSigUpload->temporaryUrl() }}" alt="Preview" style="max-height: 80px; max-width: 300px;">
                        </div>
                    @endif

                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" wire:click="closeSignatureModal" wire:loading.attr="disabled">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button
                            size="sm"
                            variant="primary"
                            wire:click="saveUploadedSignature"
                            wire:loading.attr="disabled"
                            :disabled="! $newSigUpload"
                        >
                            <span wire:loading.remove wire:target="saveUploadedSignature">
                                <flux:icon.check class="mr-1 inline-block size-4" />{{ __('Save Signature') }}
                            </span>
                            <span wire:loading wire:target="saveUploadedSignature">
                                {{ __('Saving…') }}
                            </span>
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</div>

@script
<script>
    $wire.on('signature-saved', () => {
        Flux.toast({ text: 'Signature saved successfully.', variant: 'success' });
    });
    $wire.on('signature-deleted', () => {
        Flux.toast({ text: 'Signature deleted.', variant: 'success' });
    });
    $wire.on('signature-activated', () => {
        Flux.toast({ text: 'Signature set as active.', variant: 'success' });
    });
    $wire.on('signature-deactivated', () => {
        Flux.toast({ text: 'Signature deactivated.', variant: 'success' });
    });
</script>
@endscript
