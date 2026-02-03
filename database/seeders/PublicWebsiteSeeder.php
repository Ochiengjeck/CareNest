<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use App\Models\GalleryImage;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class PublicWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTestimonials();
        $this->seedTeamMembers();
        $this->seedFaqItems();
    }

    protected function seedTestimonials(): void
    {
        $testimonials = [
            [
                'quote' => 'The staff here treat my mother like she\'s their own family. The care and attention to detail is remarkable. We couldn\'t have asked for a better place.',
                'author_name' => 'Sarah Thompson',
                'author_relation' => 'Daughter of Resident',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'quote' => 'After Dad\'s stroke, we were worried about finding the right care. This team made his recovery journey so much easier with their professional yet warm approach.',
                'author_name' => 'Michael Chen',
                'author_relation' => 'Son of Resident',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'quote' => 'The activities and social programs have given my husband a new lease on life. He looks forward to every day now. Thank you for bringing back his smile.',
                'author_name' => 'Eleanor Richards',
                'author_relation' => 'Wife of Resident',
                'is_featured' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['author_name' => $testimonial['author_name']],
                $testimonial
            );
        }
    }

    protected function seedTeamMembers(): void
    {
        $members = [
            [
                'name' => 'Dr. James Wilson',
                'role' => 'Medical Director',
                'bio' => '20+ years in geriatric medicine',
                'sort_order' => 1,
            ],
            [
                'name' => 'Sarah Mitchell',
                'role' => 'Director of Nursing',
                'bio' => 'RN with specialized dementia care certification',
                'sort_order' => 2,
            ],
            [
                'name' => 'David Park',
                'role' => 'Operations Manager',
                'bio' => 'Ensuring smooth daily operations',
                'sort_order' => 3,
            ],
            [
                'name' => 'Emily Rodriguez',
                'role' => 'Activities Director',
                'bio' => 'Creating engaging programs for residents',
                'sort_order' => 4,
            ],
        ];

        foreach ($members as $member) {
            TeamMember::updateOrCreate(
                ['name' => $member['name']],
                $member
            );
        }
    }

    protected function seedFaqItems(): void
    {
        $faqs = [
            // General
            [
                'category' => 'general',
                'question' => 'What types of care do you offer?',
                'answer' => 'We offer a comprehensive range of care services including residential care for seniors needing daily assistance, specialized memory care for those with Alzheimer\'s or dementia, short-term respite care for family caregivers, and rehabilitation services for post-surgery recovery. Each service is tailored to meet individual needs.',
                'sort_order' => 1,
            ],
            [
                'category' => 'general',
                'question' => 'How many residents do you accommodate?',
                'answer' => 'Our care home accommodates up to 60 residents in a variety of room configurations including private and semi-private options. We maintain a high staff-to-resident ratio to ensure personalized attention and quality care for everyone.',
                'sort_order' => 2,
            ],
            [
                'category' => 'general',
                'question' => 'Is the care home licensed and regulated?',
                'answer' => 'Yes, we are fully licensed and regulated by the relevant health authorities. We undergo regular inspections and maintain compliance with all safety, health, and quality standards. Our certifications are available for viewing upon request.',
                'sort_order' => 3,
            ],
            // Admissions
            [
                'category' => 'admissions',
                'question' => 'What is the admissions process?',
                'answer' => 'Our admissions process begins with an initial inquiry and tour of our facilities. We then conduct a comprehensive assessment of care needs, review medical history, and discuss preferences with the family. Once a care plan is agreed upon, we handle the paperwork and coordinate a smooth move-in date.',
                'sort_order' => 1,
            ],
            [
                'category' => 'admissions',
                'question' => 'Is there a waiting list?',
                'answer' => 'Availability varies depending on the type of care needed and room preference. We do sometimes have a waiting list for certain room types. We encourage families to contact us early in their planning process so we can discuss current availability and add you to our list if needed.',
                'sort_order' => 2,
            ],
            [
                'category' => 'admissions',
                'question' => 'Can I bring personal furniture and belongings?',
                'answer' => 'Absolutely! We encourage residents to personalize their rooms with familiar items, photographs, and small pieces of furniture (space permitting). This helps create a comfortable, home-like environment. Our team can advise on what items work best in our room configurations.',
                'sort_order' => 3,
            ],
            // Care
            [
                'category' => 'care',
                'question' => 'What is your staff-to-resident ratio?',
                'answer' => 'We maintain a high staff-to-resident ratio to ensure quality care. During the day, we typically have one caregiver for every 5-6 residents, with additional nursing staff on duty. Night coverage ensures 24/7 availability for any resident needs.',
                'sort_order' => 1,
            ],
            [
                'category' => 'care',
                'question' => 'How do you handle medical emergencies?',
                'answer' => 'We have trained staff on-site 24/7 and established protocols for medical emergencies. We maintain relationships with local hospitals and can coordinate emergency transport when needed. Families are notified immediately of any health concerns or incidents.',
                'sort_order' => 2,
            ],
            [
                'category' => 'care',
                'question' => 'Can residents continue seeing their own doctors?',
                'answer' => 'Yes, residents can continue with their existing healthcare providers. We coordinate with external doctors, specialists, and therapists to ensure continuity of care. We can also arrange for our affiliated physicians to provide on-site care if preferred.',
                'sort_order' => 3,
            ],
            [
                'category' => 'care',
                'question' => 'What activities do you offer?',
                'answer' => 'We offer a diverse activities program including arts and crafts, music therapy, gentle exercise classes, gardening, games, movie nights, and social events. We also organize special celebrations for holidays and birthdays. Activities are designed to engage residents at various ability levels.',
                'sort_order' => 4,
            ],
            // Visiting
            [
                'category' => 'visiting',
                'question' => 'What are the visiting hours?',
                'answer' => 'Our standard visiting hours are 10:00 AM to 8:00 PM daily. We understand the importance of family connections and can accommodate visits outside these hours with prior arrangement. We simply ask that visits respect other residents\' rest times.',
                'sort_order' => 1,
            ],
            [
                'category' => 'visiting',
                'question' => 'Can family members join for meals?',
                'answer' => 'Yes! Family members are welcome to join their loved ones for meals. We simply ask for advance notice so we can make appropriate arrangements. There may be a small charge for guest meals. Special occasions and celebrations can also be hosted in our private dining area.',
                'sort_order' => 2,
            ],
            [
                'category' => 'visiting',
                'question' => 'Can I take my loved one out for day trips?',
                'answer' => 'Yes, residents are free to go on outings with family members. We ask that you inform the nursing staff before leaving and upon return, and provide an estimated return time. For residents with specific care needs, our team can provide guidance on what to prepare for the outing.',
                'sort_order' => 3,
            ],
            // Costs
            [
                'category' => 'costs',
                'question' => 'How much does care cost?',
                'answer' => 'Costs vary based on the level of care required and room type selected. We provide transparent pricing and a detailed breakdown of what\'s included. We encourage families to schedule a consultation where we can discuss specific needs and provide an accurate quote.',
                'sort_order' => 1,
            ],
            [
                'category' => 'costs',
                'question' => 'What payment options are available?',
                'answer' => 'We accept various payment methods including private pay, long-term care insurance, and certain government assistance programs for eligible residents. Our admissions team can help navigate payment options and assist with insurance documentation.',
                'sort_order' => 2,
            ],
            [
                'category' => 'costs',
                'question' => 'What is included in the monthly fee?',
                'answer' => 'Our monthly fee typically includes accommodation, all meals and snacks, basic personal care assistance, housekeeping, laundry, activities programming, and 24/7 nursing oversight. Additional services like specialized therapy or one-on-one care may incur extra charges.',
                'sort_order' => 3,
            ],
        ];

        foreach ($faqs as $faq) {
            FaqItem::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
