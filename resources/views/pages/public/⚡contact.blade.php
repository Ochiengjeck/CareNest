<?php

use App\Models\ContactSubmission;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new
#[Layout('layouts.public')]
#[Title('Contact Us')]
class extends Component {
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('required|string|max:100')]
    public string $subject = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $message = '';

    public bool $submitted = false;

    #[Computed]
    public function visitingHours(): array
    {
        return app(SettingsService::class)->get('public_visiting_hours', [
            'weekday' => '10:00 AM - 8:00 PM',
            'saturday' => '10:00 AM - 8:00 PM',
            'sunday' => '10:00 AM - 8:00 PM',
        ]);
    }

    #[Computed]
    public function officeHours(): array
    {
        return app(SettingsService::class)->get('public_office_hours', [
            'weekday' => '8:00 AM - 6:00 PM',
            'saturday' => '9:00 AM - 4:00 PM',
            'sunday' => '10:00 AM - 2:00 PM',
        ]);
    }

    public function submit(): void
    {
        $this->validate();

        // Save to database
        ContactSubmission::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'subject' => $this->subject,
            'message' => $this->message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->submitted = true;

        // Reset form
        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
    }
};

?>

<div>
    {{-- Hero Section --}}
    <x-public.hero
        size="small"
        title="Contact Us"
        subtitle="Get in Touch"
        description="Have questions or ready to schedule a visit? We'd love to hear from you. Reach out using any of the methods below."
    />

    {{-- Contact Section --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-12">
                {{-- Contact Info --}}
                <div class="lg:col-span-1">
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Contact Information</h2>

                    <div class="space-y-6">
                        @if($address = system_setting('contact_address'))
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                                    <flux:icon.map-pin class="size-6 text-accent" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-1">Address</h3>
                                    <p class="text-zinc-600 dark:text-zinc-400">{{ $address }}</p>
                                </div>
                            </div>
                        @endif

                        @if($phone = system_setting('contact_phone'))
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                                    <flux:icon.phone class="size-6 text-accent" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-1">Phone</h3>
                                    <a href="tel:{{ $phone }}" class="text-zinc-600 dark:text-zinc-400 hover:text-accent transition-colors">{{ $phone }}</a>
                                </div>
                            </div>
                        @endif

                        @if($email = system_setting('contact_email'))
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                                    <flux:icon.envelope class="size-6 text-accent" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-zinc-900 dark:text-white mb-1">Email</h3>
                                    <a href="mailto:{{ $email }}" class="text-zinc-600 dark:text-zinc-400 hover:text-accent transition-colors">{{ $email }}</a>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                                <flux:icon.clock class="size-6 text-accent" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white mb-1">Visiting Hours</h3>
                                <div class="text-zinc-600 dark:text-zinc-400 space-y-1">
                                    <p><span class="text-zinc-500">Mon-Fri:</span> {{ $this->visitingHours['weekday'] ?? '10:00 AM - 8:00 PM' }}</p>
                                    <p><span class="text-zinc-500">Saturday:</span> {{ $this->visitingHours['saturday'] ?? '10:00 AM - 8:00 PM' }}</p>
                                    <p><span class="text-zinc-500">Sunday:</span> {{ $this->visitingHours['sunday'] ?? '10:00 AM - 8:00 PM' }}</p>
                                </div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-500 mt-1">Extended hours by appointment</p>
                            </div>
                        </div>
                    </div>

                    {{-- Office Hours --}}
                    <div class="mt-8 p-6 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                        <h3 class="font-semibold text-zinc-900 dark:text-white mb-3">Office Hours</h3>
                        <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <div class="flex justify-between">
                                <span>Monday - Friday</span>
                                <span>{{ $this->officeHours['weekday'] ?? '8:00 AM - 6:00 PM' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Saturday</span>
                                <span>{{ $this->officeHours['saturday'] ?? '9:00 AM - 4:00 PM' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Sunday</span>
                                <span>{{ $this->officeHours['sunday'] ?? '10:00 AM - 2:00 PM' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div class="lg:col-span-2">
                    <div class="p-6 lg:p-8 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">Send Us a Message</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">Fill out the form below and we'll get back to you within 24 hours.</p>

                        @if($submitted)
                            <div class="p-6 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-center">
                                <flux:icon.check-circle class="size-12 text-green-600 dark:text-green-400 mx-auto mb-3" />
                                <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">Message Sent Successfully!</h3>
                                <p class="text-green-700 dark:text-green-300">Thank you for reaching out. We'll get back to you as soon as possible.</p>
                                <button
                                    wire:click="$set('submitted', false)"
                                    type="button"
                                    class="mt-4 text-green-700 dark:text-green-300 underline hover:no-underline"
                                >
                                    Send another message
                                </button>
                            </div>
                        @else
                            <form wire:submit="submit" class="space-y-5">
                                <div class="grid sm:grid-cols-2 gap-5">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            wire:model="name"
                                            type="text"
                                            id="name"
                                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-accent focus:border-accent transition-colors"
                                            placeholder="Your name"
                                        >
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            wire:model="email"
                                            type="email"
                                            id="email"
                                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-accent focus:border-accent transition-colors"
                                            placeholder="you@example.com"
                                        >
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-5">
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                            Phone Number
                                        </label>
                                        <input
                                            wire:model="phone"
                                            type="tel"
                                            id="phone"
                                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-accent focus:border-accent transition-colors"
                                            placeholder="(optional)"
                                        >
                                        @error('phone')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                            Subject <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            wire:model="subject"
                                            id="subject"
                                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-accent focus:border-accent transition-colors"
                                        >
                                            <option value="">Select a subject</option>
                                            <option value="Schedule a Tour">Schedule a Tour</option>
                                            <option value="Admissions Inquiry">Admissions Inquiry</option>
                                            <option value="Services Information">Services Information</option>
                                            <option value="Pricing & Payment">Pricing & Payment</option>
                                            <option value="General Question">General Question</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        @error('subject')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                        Message <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        wire:model="message"
                                        id="message"
                                        rows="5"
                                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-accent focus:border-accent transition-colors resize-none"
                                        placeholder="How can we help you?"
                                    ></textarea>
                                    @error('message')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-500">
                                        <span class="text-red-500">*</span> Required fields
                                    </p>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-medium text-white bg-accent hover:bg-accent-content transition-colors disabled:opacity-50"
                                        wire:loading.attr="disabled"
                                    >
                                        <span wire:loading.remove>Send Message</span>
                                        <span wire:loading>Sending...</span>
                                        <flux:icon.paper-airplane variant="mini" class="size-5" wire:loading.remove />
                                        <flux:icon.arrow-path variant="mini" class="size-5 animate-spin" wire:loading />
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Map Section --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Find Us"
                subtitle="Location"
            >
                We're conveniently located and easy to find. Here's how to reach us.
            </x-public.section-heading>

            <div class="mt-12">
                {{-- Map Placeholder --}}
                <div class="relative h-80 lg:h-96 rounded-2xl overflow-hidden bg-zinc-200 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-6">
                        <flux:icon.map class="size-16 text-zinc-400 dark:text-zinc-600 mb-4" />
                        <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Map Coming Soon</h3>
                        <p class="text-zinc-500 dark:text-zinc-500 max-w-sm">
                            An interactive map will be added here. For now, please use the address above or contact us for directions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <x-public.cta-section
        title="We'd Love to Meet You"
        description="Schedule a visit to tour our facilities and see why families trust us with their loved ones' care."
        :primaryAction="['label' => 'Call Us Today', 'href' => 'tel:' . system_setting('contact_phone', '')]"
    />
</div>
