<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $signatureMode = 'draw';
    public $signatureUpload;

    /**
     * Mount the component.
     */
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
     * Receive signature data from the canvas JS component.
     */
    public function receiveSignature(string $dataUrl): void
    {
        $this->validate(
            ['dataUrl' => $this->signatureRules()],
            [],
            ['dataUrl' => 'signature'],
        );

        $user = Auth::user();
        $user->signature_data = $dataUrl;
        $user->signature_updated_at = now();
        $user->save();

        $this->dispatch('signature-saved');
    }

    /**
     * Upload a signature image file and convert to base64.
     */
    public function uploadSignature(): void
    {
        $this->validate(
            ['signatureUpload' => $this->signatureUploadRules()],
            [],
            ['signatureUpload' => 'signature image'],
        );

        $contents = file_get_contents($this->signatureUpload->getRealPath());
        $mime = $this->signatureUpload->getMimeType();
        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($contents);

        $user = Auth::user();
        $user->signature_data = $dataUrl;
        $user->signature_updated_at = now();
        $user->save();

        $this->signatureUpload = null;
        $this->dispatch('signature-saved');
    }

    /**
     * Remove the user's saved signature.
     */
    public function clearSignature(): void
    {
        $user = Auth::user();
        $user->signature_data = null;
        $user->signature_updated_at = now();
        $user->save();

        $this->dispatch('signature-cleared');
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
    public function hasExistingSignature(): bool
    {
        return Auth::user()->hasSignature();
    }

    #[Computed]
    public function existingSignatureDataUri(): ?string
    {
        return Auth::user()->getSignatureDataUri();
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

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- Digital Signature Section --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 mt-6">
            <flux:heading size="lg">{{ __('Digital Signature') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Your signature will be embedded in therapy and discharge report exports.') }}</flux:text>

            {{-- Current Signature Preview --}}
            @if ($this->hasExistingSignature)
                <div class="mt-4 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-2">
                        <flux:text size="sm" class="font-medium">{{ __('Current Signature') }}</flux:text>
                        <flux:button variant="danger" size="sm" wire:click="clearSignature" wire:confirm="Are you sure you want to remove your signature?">
                            {{ __('Remove') }}
                        </flux:button>
                    </div>
                    <div class="bg-white rounded border border-zinc-300 p-2 inline-block">
                        <img src="{{ $this->existingSignatureDataUri }}" alt="Your signature" style="max-height: 80px; max-width: 300px;">
                    </div>
                    @if (Auth::user()->signature_updated_at)
                        <flux:text size="sm" class="mt-2 text-zinc-500">
                            {{ __('Last updated:') }} {{ Auth::user()->signature_updated_at->diffForHumans() }}
                        </flux:text>
                    @endif
                </div>
            @endif

            {{-- Mode Toggle --}}
            <div class="mt-4 flex gap-2">
                <flux:button
                    size="sm"
                    :variant="$signatureMode === 'draw' ? 'primary' : 'ghost'"
                    wire:click="$set('signatureMode', 'draw')"
                >
                    {{ __('Draw') }}
                </flux:button>
                <flux:button
                    size="sm"
                    :variant="$signatureMode === 'upload' ? 'primary' : 'ghost'"
                    wire:click="$set('signatureMode', 'upload')"
                >
                    {{ __('Upload') }}
                </flux:button>
            </div>

            {{-- Draw Mode --}}
            @if ($signatureMode === 'draw')
                <div
                    class="mt-4"
                    x-data="signaturePad(@js($this->hasExistingSignature ? $this->existingSignatureDataUri : null))"
                    wire:ignore
                >
                    <div class="bg-white rounded-lg border border-zinc-300 dark:border-zinc-600" style="width: 100%; max-width: 400px;">
                        <canvas
                            x-ref="canvas"
                            style="width: 100%; height: 200px; cursor: crosshair; touch-action: none;"
                        ></canvas>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <flux:button size="sm" variant="ghost" x-on:click="clear()">
                            {{ __('Clear') }}
                        </flux:button>
                        <flux:button size="sm" variant="primary" x-on:click="save()" x-bind:disabled="isEmpty">
                            {{ __('Save Signature') }}
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Upload Mode --}}
            @if ($signatureMode === 'upload')
                <form wire:submit="uploadSignature" class="mt-4 space-y-3">
                    <div>
                        <input
                            type="file"
                            wire:model="signatureUpload"
                            accept="image/png,image/jpeg"
                            class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300"
                        />
                        <flux:text size="sm" class="mt-1 text-zinc-500">{{ __('PNG or JPG, max 512KB, max 800x400px') }}</flux:text>
                        @error('signatureUpload')
                            <flux:text size="sm" class="mt-1 text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    @if ($signatureUpload)
                        <div class="bg-white rounded border border-zinc-300 p-2 inline-block">
                            <img src="{{ $signatureUpload->temporaryUrl() }}" alt="Signature preview" style="max-height: 80px; max-width: 300px;">
                        </div>
                    @endif

                    <div>
                        <flux:button size="sm" variant="primary" type="submit" :disabled="!$signatureUpload">
                            {{ __('Save Signature') }}
                        </flux:button>
                    </div>
                </form>
            @endif
        </div>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>

@script
<script>
    $wire.on('signature-saved', () => {
        Flux.toast({ text: 'Signature saved successfully.', variant: 'success' });
    });
    $wire.on('signature-cleared', () => {
        Flux.toast({ text: 'Signature removed.', variant: 'success' });
    });
</script>
@endscript
