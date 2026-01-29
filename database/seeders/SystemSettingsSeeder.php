<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'system_name', 'group' => 'general', 'value' => 'CareNest', 'type' => 'string'],
            ['key' => 'system_tagline', 'group' => 'general', 'value' => 'Care Home Management System', 'type' => 'string'],
            ['key' => 'timezone', 'group' => 'general', 'value' => 'Africa/Nairobi', 'type' => 'string'],
            ['key' => 'date_format', 'group' => 'general', 'value' => 'M d, Y', 'type' => 'string'],
            ['key' => 'time_format', 'group' => 'general', 'value' => 'h:i A', 'type' => 'string'],
            ['key' => 'language', 'group' => 'general', 'value' => 'en', 'type' => 'string'],

            // Branding
            ['key' => 'logo_path', 'group' => 'branding', 'value' => null, 'type' => 'image'],
            ['key' => 'favicon_path', 'group' => 'branding', 'value' => null, 'type' => 'image'],
            ['key' => 'primary_color', 'group' => 'branding', 'value' => '#6366f1', 'type' => 'string'],
            ['key' => 'sidebar_name', 'group' => 'branding', 'value' => 'CareNest', 'type' => 'string'],

            // Contact
            ['key' => 'address_line_1', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'address_line_2', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'city', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'state_province', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'postal_code', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'country', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'phone', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'email', 'group' => 'contact', 'value' => '', 'type' => 'string'],
            ['key' => 'website', 'group' => 'contact', 'value' => '', 'type' => 'string'],

            // Social
            ['key' => 'facebook_url', 'group' => 'social', 'value' => '', 'type' => 'string'],
            ['key' => 'twitter_url', 'group' => 'social', 'value' => '', 'type' => 'string'],
            ['key' => 'linkedin_url', 'group' => 'social', 'value' => '', 'type' => 'string'],
            ['key' => 'instagram_url', 'group' => 'social', 'value' => '', 'type' => 'string'],

            // AI Global
            ['key' => 'ai_enabled', 'group' => 'ai', 'value' => '0', 'type' => 'boolean'],
            ['key' => 'groq_api_key', 'group' => 'ai', 'value' => null, 'type' => 'string', 'is_encrypted' => true],
            ['key' => 'groq_default_model', 'group' => 'ai', 'value' => 'llama-3.3-70b-versatile', 'type' => 'string'],
            ['key' => 'gemini_api_key', 'group' => 'ai', 'value' => null, 'type' => 'string', 'is_encrypted' => true],
            ['key' => 'gemini_default_model', 'group' => 'ai', 'value' => 'gemini-2.0-flash', 'type' => 'string'],

            // AI Use Cases
            ['key' => 'ai_usecase_report_generation', 'group' => 'ai', 'type' => 'json', 'value' => json_encode([
                'enabled' => false,
                'provider' => 'groq',
                'model' => 'llama-3.3-70b-versatile',
                'temperature' => 0.3,
                'max_tokens' => 4096,
                'system_prompt' => 'You are a professional care home report writer. Generate clear, structured reports in Markdown format. Include sections for Executive Summary, Key Findings, Detailed Analysis, and Recommendations. Use tables for data comparisons and bullet points where appropriate.',
            ])],
            ['key' => 'ai_usecase_document_analysis', 'group' => 'ai', 'type' => 'json', 'value' => json_encode([
                'enabled' => false,
                'provider' => 'gemini',
                'model' => 'gemini-2.0-flash',
                'temperature' => 0.2,
                'max_tokens' => 8192,
                'system_prompt' => 'You are a document analysis assistant for a care home. Analyze the provided document and extract key information including dates, names, medical details, and action items. Present findings in a structured format.',
            ])],
            ['key' => 'ai_usecase_care_assistant', 'group' => 'ai', 'type' => 'json', 'value' => json_encode([
                'enabled' => false,
                'provider' => 'groq',
                'model' => 'llama-3.1-8b-instant',
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'system_prompt' => 'You are a helpful care assistant for care home staff. Provide guidance on care procedures, best practices, and answer questions about resident care. Be concise and practical. If a question is about a medical emergency, always advise contacting emergency services immediately.',
            ])],
            ['key' => 'ai_usecase_incident_summarization', 'group' => 'ai', 'type' => 'json', 'value' => json_encode([
                'enabled' => false,
                'provider' => 'groq',
                'model' => 'llama-3.3-70b-versatile',
                'temperature' => 0.2,
                'max_tokens' => 2048,
                'system_prompt' => 'You are an incident summarization assistant for a care home. Summarize incident reports by extracting: what happened, who was involved, when it occurred, severity level, immediate actions taken, and recommended follow-up actions. Be factual and concise.',
            ])],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                collect($setting)->except('key')->toArray()
            );
        }
    }
}
