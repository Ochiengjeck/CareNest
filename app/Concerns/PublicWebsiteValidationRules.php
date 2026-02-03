<?php

namespace App\Concerns;

trait PublicWebsiteValidationRules
{
    protected function statsRules(): array
    {
        return [
            'stats.years' => ['required', 'string', 'max:10'],
            'stats.residents' => ['required', 'string', 'max:10'],
            'stats.staff' => ['required', 'string', 'max:10'],
            'stats.satisfaction' => ['required', 'string', 'max:10'],
        ];
    }

    protected function visitingHoursRules(): array
    {
        return [
            'visiting_hours.weekday' => ['required', 'string', 'max:50'],
            'visiting_hours.saturday' => ['required', 'string', 'max:50'],
            'visiting_hours.sunday' => ['required', 'string', 'max:50'],
        ];
    }

    protected function officeHoursRules(): array
    {
        return [
            'office_hours.weekday' => ['required', 'string', 'max:50'],
            'office_hours.saturday' => ['required', 'string', 'max:50'],
            'office_hours.sunday' => ['required', 'string', 'max:50'],
        ];
    }

    protected function aboutContentRules(): array
    {
        return [
            'about_story' => ['required', 'string', 'max:2000'],
            'about_mission' => ['required', 'string', 'max:1000'],
            'about_vision' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function testimonialRules(): array
    {
        return [
            'quote' => ['required', 'string', 'max:1000'],
            'author_name' => ['required', 'string', 'max:100'],
            'author_relation' => ['nullable', 'string', 'max:100'],
            'author_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function teamMemberRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function faqItemRules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string', 'in:general,admissions,care,visiting,costs'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function galleryImageRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category' => ['required', 'string', 'in:rooms,common,activities,gardens'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function galleryImageUpdateRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category' => ['required', 'string', 'in:rooms,common,activities,gardens'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function careHomeImageRules(): array
    {
        return [
            'newImage' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'newCaption' => ['nullable', 'string', 'max:200'],
        ];
    }

    protected function serviceRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'icon' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'features' => ['nullable', 'array', 'max:10'],
            'features.*' => ['required', 'string', 'max:200'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function serviceUpdateRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'icon' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'features' => ['nullable', 'array', 'max:10'],
            'features.*' => ['required', 'string', 'max:200'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function amenityRules(): array
    {
        return [
            'amenity_title' => ['required', 'string', 'max:100'],
            'amenity_description' => ['required', 'string', 'max:500'],
            'amenity_icon' => ['required', 'string', 'max:50'],
            'amenity_sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function dailyScheduleRules(): array
    {
        return [
            'schedule_time' => ['required', 'string', 'max:20'],
            'schedule_activity' => ['required', 'string', 'max:255'],
            'schedule_sort_order' => ['integer', 'min:0'],
        ];
    }
}
