<?php

use App\Models\Testimonial;
use App\Models\GalleryImage;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.public')]
#[Title('Welcome')]
class extends Component {
    #[Computed]
    public function stats(): array
    {
        return app(SettingsService::class)->get('public_stats', [
            'years' => '20',
            'residents' => '150',
            'staff' => '50',
            'satisfaction' => '98',
        ]);
    }

    #[Computed]
    public function testimonials()
    {
        return Testimonial::active()->featured()->ordered()->take(3)->get();
    }

    #[Computed]
    public function galleryImages()
    {
        return GalleryImage::active()->featured()->ordered()->take(5)->get();
    }
};

?>

<div>
    {{-- Hero Section --}}
    <x-public.hero
        :title="system_setting('system_name', 'CareNest') . ' Care Home'"
        subtitle="Welcome to Our Family"
        :description="system_setting('system_tagline', 'Where compassion meets excellence. We provide professional, personalized care in a warm, homely environment that your loved ones deserve.')"
        :primaryAction="['label' => 'Schedule a Visit', 'href' => route('contact')]"
        :secondaryAction="['label' => 'Our Services', 'href' => route('services')]"
    />

    {{-- Stats Bar --}}
    <section class="relative -mt-16 z-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 p-6 lg:p-8 rounded-2xl bg-white dark:bg-zinc-800 shadow-xl border border-zinc-200 dark:border-zinc-700">
                <x-public.stat-counter :value="$this->stats['years'] ?? '20'" suffix="+" label="Years of Care" />
                <x-public.stat-counter :value="$this->stats['residents'] ?? '150'" suffix="+" label="Residents Served" />
                <x-public.stat-counter :value="$this->stats['staff'] ?? '50'" suffix="+" label="Caring Staff" />
                <x-public.stat-counter :value="$this->stats['satisfaction'] ?? '98'" suffix="%" label="Family Satisfaction" />
            </div>
        </div>
    </section>

    {{-- Services Overview --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Our Care Services"
                subtitle="What We Offer"
            >
                Comprehensive care services tailored to meet the unique needs of each resident, ensuring comfort, dignity, and quality of life.
            </x-public.section-heading>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-public.feature-card
                    icon="home"
                    title="Residential Care"
                    description="Full-time residential care with 24/7 support, nutritious meals, and a comfortable living environment for seniors who need daily assistance."
                />
                <x-public.feature-card
                    icon="heart"
                    title="Memory Care"
                    description="Specialized care for residents with Alzheimer's and dementia, featuring secure environments and trained staff in memory care techniques."
                />
                <x-public.feature-card
                    icon="clock"
                    title="Respite Care"
                    description="Short-term care options providing relief for family caregivers while ensuring your loved one receives quality care and attention."
                />
                <x-public.feature-card
                    icon="arrow-path"
                    title="Rehabilitation"
                    description="Post-surgery and recovery programs with physical therapy support to help residents regain independence and mobility."
                />
                <x-public.feature-card
                    icon="clipboard-document-check"
                    title="Therapy Programs"
                    description="Occupational, speech, and physical therapy services provided by licensed therapists to maintain and improve quality of life."
                />
                <x-public.feature-card
                    icon="user-group"
                    title="Social Activities"
                    description="Engaging daily activities, outings, and events designed to promote social interaction, mental stimulation, and joy."
                />
            </div>

            <div class="mt-10 text-center">
                <a
                    href="{{ route('services') }}"
                    class="inline-flex items-center gap-2 text-accent font-semibold hover:underline"
                >
                    View All Services
                    <flux:icon.arrow-right variant="mini" class="size-5" />
                </a>
            </div>
        </div>
    </section>

    {{-- Why Choose Us --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Why Choose Us"
                subtitle="Our Commitment"
            >
                We're dedicated to providing exceptional care that treats every resident like family.
            </x-public.section-heading>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-public.feature-card
                    icon="shield-check"
                    title="24/7 Professional Care"
                    description="Round-the-clock care from trained nurses and caregivers ensuring safety and immediate response to any needs."
                />
                <x-public.feature-card
                    icon="home-modern"
                    title="Homely Environment"
                    description="Beautifully designed spaces that feel like home, with comfortable rooms, gardens, and communal areas."
                />
                <x-public.feature-card
                    icon="users"
                    title="Experienced Staff"
                    description="Compassionate team with years of experience in elderly care, ongoing training, and genuine dedication."
                />
                <x-public.feature-card
                    icon="chart-bar"
                    title="Personalized Care Plans"
                    description="Individualized care plans developed with families to address specific needs, preferences, and health requirements."
                />
                <x-public.feature-card
                    icon="calendar"
                    title="Engaging Activities"
                    description="Daily programs including arts, music, gardening, and social events to keep residents active and happy."
                />
                <x-public.feature-card
                    icon="phone"
                    title="Family Communication"
                    description="Regular updates and open communication with families, including digital tools to stay connected with loved ones."
                />
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="What Families Say"
                subtitle="Testimonials"
            >
                Hear from families who have trusted us with the care of their loved ones.
            </x-public.section-heading>

            <div class="mt-12 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($this->testimonials as $testimonial)
                    <x-public.testimonial-card
                        :quote="$testimonial->quote"
                        :author="$testimonial->author_name"
                        :relation="$testimonial->author_relation"
                        :image="$testimonial->author_image ? Storage::url($testimonial->author_image) : null"
                    />
                @empty
                    {{-- Fallback placeholder testimonials --}}
                    <x-public.testimonial-card
                        quote="The staff here treat my mother like she's their own family. The care and attention to detail is remarkable. We couldn't have asked for a better place."
                        author="Sarah Thompson"
                        relation="Daughter of Resident"
                    />
                    <x-public.testimonial-card
                        quote="After Dad's stroke, we were worried about finding the right care. This team made his recovery journey so much easier with their professional yet warm approach."
                        author="Michael Chen"
                        relation="Son of Resident"
                    />
                    <x-public.testimonial-card
                        quote="The activities and social programs have given my husband a new lease on life. He looks forward to every day now. Thank you for bringing back his smile."
                        author="Eleanor Richards"
                        relation="Wife of Resident"
                    />
                @endforelse
            </div>
        </div>
    </section>

    {{-- Gallery Preview --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Our Facilities"
                subtitle="Take a Look"
            >
                Explore our comfortable, well-maintained facilities designed with your loved ones in mind.
            </x-public.section-heading>

            <div class="mt-12 grid grid-cols-2 lg:grid-cols-4 gap-4">
                @if($this->galleryImages->count())
                    @foreach($this->galleryImages as $index => $image)
                        <div class="{{ $index === 0 ? 'col-span-2 lg:col-span-2 lg:row-span-2' : '' }}">
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-zinc-200 dark:bg-zinc-700">
                                <img
                                    src="{{ Storage::url($image->image_path) }}"
                                    alt="{{ $image->alt_text ?? $image->title }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                        </div>
                    @endforeach
                @else
                    <x-public.image-placeholder aspect="4/3" text="Living Room" icon="home" class="col-span-2 lg:col-span-2 lg:row-span-2" />
                    <x-public.image-placeholder aspect="4/3" text="Bedroom Suite" icon="home" />
                    <x-public.image-placeholder aspect="4/3" text="Garden Area" icon="sun" />
                    <x-public.image-placeholder aspect="4/3" text="Dining Hall" icon="cake" />
                    <x-public.image-placeholder aspect="4/3" text="Activity Room" icon="puzzle-piece" />
                @endif
            </div>

            <div class="mt-10 text-center">
                <a
                    href="{{ route('gallery') }}"
                    class="inline-flex items-center gap-2 text-accent font-semibold hover:underline"
                >
                    View Full Gallery
                    <flux:icon.arrow-right variant="mini" class="size-5" />
                </a>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <x-public.cta-section
        title="Ready to Learn More?"
        description="Schedule a visit to tour our facilities and meet our caring team. We'd love to show you around."
        :primaryAction="['label' => 'Schedule a Visit', 'href' => route('contact')]"
        :secondaryAction="['label' => 'Call Us Today', 'href' => 'tel:' . system_setting('contact_phone', '')]"
    />
</div>
