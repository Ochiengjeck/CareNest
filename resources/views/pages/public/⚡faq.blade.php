<?php

use App\Models\FaqItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.public')]
#[Title('FAQ')]
class extends Component {
    public string $activeCategory = 'all';

    #[Computed]
    public function faqItems()
    {
        return FaqItem::active()->ordered()->get();
    }

    #[Computed]
    public function categories(): array
    {
        return [
            'all' => 'All Questions',
            'general' => 'General',
            'admissions' => 'Admissions',
            'care' => 'Care & Services',
            'visiting' => 'Visiting',
            'costs' => 'Costs & Payment',
        ];
    }

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
    }
};

?>

<div>
    {{-- Hero Section --}}
    <x-public.hero
        size="small"
        title="Frequently Asked Questions"
        subtitle="Have Questions?"
        description="Find answers to common questions about our care home, services, admissions process, and visiting policies."
    />

    {{-- FAQ Section --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Category Tabs --}}
            <div class="flex flex-wrap justify-center gap-2 mb-12">
                @foreach($this->categories as $key => $label)
                    <button
                        wire:click="setCategory('{{ $key }}')"
                        type="button"
                        class="px-5 py-2.5 rounded-full text-sm font-medium transition-colors
                            {{ $activeCategory === $key
                                ? 'bg-accent text-white'
                                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                            }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- FAQ Items --}}
            <div class="space-y-4">
                @forelse($this->faqItems as $faq)
                    <div class="{{ $activeCategory !== 'all' && $activeCategory !== $faq->category ? 'hidden' : '' }}" wire:key="faq-{{ $faq->id }}">
                        <x-public.faq-item
                            :question="$faq->question"
                            :answer="$faq->answer"
                        />
                    </div>
                @empty
                    {{-- Fallback placeholder FAQs --}}
                    @php
                        $placeholderFaqs = [
                            ['category' => 'general', 'question' => 'What types of care do you offer?', 'answer' => 'We offer a comprehensive range of care services including residential care for seniors needing daily assistance, specialized memory care for those with Alzheimer\'s or dementia, short-term respite care for family caregivers, and rehabilitation services for post-surgery recovery.'],
                            ['category' => 'general', 'question' => 'How many residents do you accommodate?', 'answer' => 'Our care home accommodates up to 60 residents in a variety of room configurations including private and semi-private options. We maintain a high staff-to-resident ratio to ensure personalized attention.'],
                            ['category' => 'admissions', 'question' => 'What is the admissions process?', 'answer' => 'Our admissions process begins with an initial inquiry and tour of our facilities. We then conduct a comprehensive assessment of care needs, review medical history, and discuss preferences with the family.'],
                            ['category' => 'care', 'question' => 'What is your staff-to-resident ratio?', 'answer' => 'We maintain a high staff-to-resident ratio to ensure quality care. During the day, we typically have one caregiver for every 5-6 residents, with additional nursing staff on duty.'],
                            ['category' => 'visiting', 'question' => 'What are the visiting hours?', 'answer' => 'Our standard visiting hours are 10:00 AM to 8:00 PM daily. We understand the importance of family connections and can accommodate visits outside these hours with prior arrangement.'],
                            ['category' => 'costs', 'question' => 'How much does care cost?', 'answer' => 'Costs vary based on the level of care required and room type selected. We provide transparent pricing and a detailed breakdown of what\'s included. Contact us for a personalized quote.'],
                        ];
                    @endphp
                    @foreach($placeholderFaqs as $index => $faq)
                        <div class="{{ $activeCategory !== 'all' && $activeCategory !== $faq['category'] ? 'hidden' : '' }}" wire:key="faq-placeholder-{{ $index }}">
                            <x-public.faq-item
                                :question="$faq['question']"
                                :answer="$faq['answer']"
                            />
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- Still Have Questions --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <flux:icon.chat-bubble-left-right class="size-12 text-accent mx-auto mb-4" />
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-white mb-4">Still Have Questions?</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-8">
                    Can't find the answer you're looking for? Our friendly team is here to help. Reach out to us and we'll get back to you as soon as possible.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a
                        href="{{ route('contact') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                    >
                        <flux:icon.envelope variant="mini" class="size-5" />
                        Contact Us
                    </a>
                    @if($phone = system_setting('contact_phone'))
                        <a
                            href="tel:{{ $phone }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-medium border-2 border-accent text-accent hover:bg-accent hover:text-white transition-colors"
                        >
                            <flux:icon.phone variant="mini" class="size-5" />
                            {{ $phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <x-public.cta-section
        title="Ready to Take the Next Step?"
        description="Schedule a tour to see our facilities and meet our caring team in person."
        :primaryAction="['label' => 'Schedule a Tour', 'href' => route('contact')]"
        :secondaryAction="['label' => 'View Services', 'href' => route('services')]"
    />
</div>
