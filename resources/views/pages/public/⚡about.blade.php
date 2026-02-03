<?php

use App\Models\CareHomeImage;
use App\Models\TeamMember;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.public')]
#[Title('About Us')]
class extends Component {
    #[Computed]
    public function teamMembers()
    {
        return TeamMember::active()->ordered()->get();
    }

    #[Computed]
    public function featuredImage()
    {
        return CareHomeImage::active()->featured()->first();
    }

    #[Computed]
    public function aboutStory(): string
    {
        return app(SettingsService::class)->get('public_about_story', '') ?? '';
    }

    #[Computed]
    public function aboutMission(): string
    {
        return app(SettingsService::class)->get('public_about_mission', '') ?? '';
    }

    #[Computed]
    public function aboutVision(): string
    {
        return app(SettingsService::class)->get('public_about_vision', '') ?? '';
    }
};

?>

<div>
    {{-- Hero Section --}}
    <x-public.hero
        size="small"
        title="About Us"
        subtitle="Our Story"
        description="Dedicated to providing compassionate, professional care since 2004. Learn about our mission, values, and the team that makes it all possible."
    />

    {{-- Our Story --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div>
                    <x-public.section-heading
                        title="Our Story"
                        subtitle="Who We Are"
                        :centered="false"
                    />

                    <div class="mt-6 space-y-4 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        @if($this->aboutStory)
                            {!! nl2br(e($this->aboutStory)) !!}
                        @else
                            <p>
                                {{ system_setting('system_name', 'CareNest') }} was founded with a simple yet profound mission: to create a place where seniors can live with dignity, comfort, and joy. What started as a small family-run care home has grown into a trusted name in elderly care.
                            </p>
                            <p>
                                Our founders believed that quality care shouldn't mean sacrificing the warmth of home. That belief continues to guide everything we do today. Every decision we make, from hiring staff to designing our facilities, is centered on one question: "Would this be good enough for our own family?"
                            </p>
                            <p>
                                Over the years, we've had the privilege of caring for hundreds of residents and becoming part of their families' lives. These relationships inspire us to continually improve and innovate in our approach to care.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="relative">
                    @if($this->featuredImage)
                        <img
                            src="{{ Storage::url($this->featuredImage->image_path) }}"
                            alt="{{ $this->featuredImage->caption ?? 'Our Care Home' }}"
                            class="w-full aspect-[4/3] object-cover rounded-2xl"
                        />
                    @else
                        <x-public.image-placeholder aspect="4/3" text="Our Care Home" icon="home-modern" />
                    @endif
                    {{-- Decorative element --}}
                    <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-accent/10 rounded-2xl -z-10"></div>
                    <div class="absolute -top-6 -right-6 w-32 h-32 bg-accent/5 rounded-2xl -z-10"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mission & Vision --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="p-8 lg:p-10 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="w-14 h-14 rounded-xl bg-accent/10 flex items-center justify-center mb-6">
                        <flux:icon.eye class="size-7 text-accent" />
                    </div>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Our Vision</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        @if($this->aboutVision)
                            {{ $this->aboutVision }}
                        @else
                            To be the most trusted and preferred care home in our community, known for excellence in care, innovation in services, and genuine compassion for every resident we serve.
                        @endif
                    </p>
                </div>

                <div class="p-8 lg:p-10 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="w-14 h-14 rounded-xl bg-accent/10 flex items-center justify-center mb-6">
                        <flux:icon.flag class="size-7 text-accent" />
                    </div>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Our Mission</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        @if($this->aboutMission)
                            {{ $this->aboutMission }}
                        @else
                            To provide exceptional, person-centered care that enhances quality of life, promotes independence, and treats every resident with the dignity and respect they deserve.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Our Values --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Our Core Values"
                subtitle="What We Stand For"
            >
                These values guide our actions and decisions every single day.
            </x-public.section-heading>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-public.feature-card
                    icon="heart"
                    title="Compassion"
                    description="We approach every interaction with empathy and kindness, understanding that our residents deserve care that comes from the heart."
                />
                <x-public.feature-card
                    icon="shield-check"
                    title="Integrity"
                    description="We maintain the highest ethical standards, being transparent with families and always doing what's right for our residents."
                />
                <x-public.feature-card
                    icon="star"
                    title="Excellence"
                    description="We continuously strive to exceed expectations in care quality, staff training, and facility standards."
                />
                <x-public.feature-card
                    icon="hand-raised"
                    title="Respect"
                    description="We honor the individuality, privacy, and autonomy of each resident, treating everyone with dignity."
                />
                <x-public.feature-card
                    icon="users"
                    title="Community"
                    description="We foster a sense of belonging and connection, creating a supportive community for residents and families."
                />
                <x-public.feature-card
                    icon="light-bulb"
                    title="Innovation"
                    description="We embrace new technologies and approaches that can improve care outcomes and enhance quality of life."
                />
            </div>
        </div>
    </section>

    {{-- Meet Our Team --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Meet Our Leadership"
                subtitle="Our Team"
            >
                Experienced professionals dedicated to providing the best possible care.
            </x-public.section-heading>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($this->teamMembers as $member)
                    <x-public.team-card
                        :name="$member->name"
                        :role="$member->role"
                        :bio="$member->bio"
                        :image="$member->photo ? Storage::url($member->photo) : null"
                    />
                @empty
                    {{-- Fallback placeholder team --}}
                    <x-public.team-card
                        name="Dr. James Wilson"
                        role="Medical Director"
                        bio="20+ years in geriatric medicine"
                    />
                    <x-public.team-card
                        name="Sarah Mitchell"
                        role="Director of Nursing"
                        bio="RN with specialized dementia care certification"
                    />
                    <x-public.team-card
                        name="David Park"
                        role="Operations Manager"
                        bio="Ensuring smooth daily operations"
                    />
                    <x-public.team-card
                        name="Emily Rodriguez"
                        role="Activities Director"
                        bio="Creating engaging programs for residents"
                    />
                @endforelse
            </div>

            @if($this->teamMembers->isEmpty())
                <p class="mt-10 text-center text-sm text-zinc-500 dark:text-zinc-500 italic">
                    * Team member information is placeholder content
                </p>
            @endif
        </div>
    </section>

    {{-- Accreditations --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Accreditations & Certifications"
                subtitle="Quality Assured"
            >
                We maintain the highest standards of care through recognized accreditations.
            </x-public.section-heading>

            <div class="mt-12 flex flex-wrap justify-center items-center gap-8 lg:gap-16">
                {{-- Placeholder badges --}}
                @for($i = 0; $i < 4; $i++)
                    <div class="w-24 h-24 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center">
                        <flux:icon.shield-check class="size-10 text-zinc-400 dark:text-zinc-600" />
                    </div>
                @endfor
            </div>

            <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-500 italic">
                * Accreditation badges are placeholder content
            </p>
        </div>
    </section>

    {{-- CTA Section --}}
    <x-public.cta-section
        title="Want to Learn More?"
        description="We'd love to tell you more about our care home and answer any questions you might have."
        :primaryAction="['label' => 'Contact Us', 'href' => route('contact')]"
        :secondaryAction="['label' => 'View Services', 'href' => route('services')]"
    />
</div>
