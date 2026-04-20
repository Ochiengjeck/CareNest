# CareNest Pitch Deck

---

## Slide 1 — Title

# CareNest
### AI-Enhanced Care Home Management
> *Smarter care. Safer residents. Empowered staff.*

---

## Slide 2 — The Problem

Care homes are overwhelmed by fragmented, paper-based operations:

- Medications, vitals, incidents, and care plans are tracked manually — creating dangerous gaps
- Staff training is inconsistent and nearly impossible to audit
- Managers lack real-time visibility into resident wellbeing
- Regulatory inspections expose missing documentation trails
- Disconnected tools mean duplicated work and costly oversights

---

## Slide 3 — The Solution

**CareNest is an all-in-one platform that digitizes, automates, and intelligently assists every aspect of care home operations.**

- A unified dashboard tailored to every role in the facility
- AI that writes reports, analyzes documents, and flags critical incidents
- Structured digital workflows that replace error-prone paper processes
- A built-in mentorship platform for continuous staff development
- A fully managed public website for family-facing communications

---

## Slide 4 — Key Features

| Clinical | Operations | Learning |
|---|---|---|
| Medications & administration logs | Resident admissions & discharge | Staff mentorship platform |
| Vitals with abnormal-value alerts | Care plans & therapy sessions | Weekly topic scheduling |
| Incident reporting & review workflow | Shift scheduling & staff directory | Lesson library & session tracking |
| PDF & Word discharge report exports | Role-based access control (21 permissions) | Teaching session documentation |

---

## Slide 5 — AI at the Core

CareNest doesn't just store data — it understands it.

- **Groq (Llama 3.3 / Qwen 3)** — instant report generation, incident summarization, care assistance chatbot
- **Google Gemini** — multimodal document analysis: upload a referral letter, get structured resident intake data
- AI-generated therapy progress reports and full resident history summaries
- Authenticated staff chatbot + public-facing family chatbot (rate-limited)
- No AI vendor lock-in — provider and model are configurable per use case from the admin panel

---

## Slide 6 — Role-Based Access

Five roles. Every user sees exactly what they need — nothing more.

| Role | Responsibilities in CareNest |
|---|---|
| **System Admin** | Full control, audit logs, AI configuration, user & role management |
| **Care Home Manager** | Operational oversight, mentorship management, reports, website CMS |
| **Nurse** | Medications, vitals, care plans, incident management |
| **Caregiver** | Daily care tasks, activity logging |
| **Therapist** | Session documentation, AI-generated progress reports |

Users can hold multiple roles and receive direct permission overrides without changing their role.

---

## Slide 7 — The Mentorship Platform

A product-within-a-product for continuous staff development — built into the same login.

- Weekly topic schedule: 3 time slots per day × 7 days (10 AM, 2 PM, 6 PM)
- Topics include attachments, personal notes, and a shared lessons library
- Any authenticated staff member can teach; managers track coverage and engagement
- CSV bulk import for scheduling entire weeks in seconds
- Reports: sessions by mentor, category coverage, topic frequency over time

**Categories:** Mental Health · Substance Use Disorder · Employment/Education · Physical Health · Financial/Housing · Psycho-Social/Family · Spirituality

---

## Slide 8 — Public-Facing Website (CMS Built-In)

Families research care homes online before making a decision.

- CareNest includes a fully managed public website out of the box
- Manage services, gallery, team profiles, testimonials, FAQs, and contact submissions — all from the admin panel
- No separate CMS, no third-party integrations, no extra subscriptions
- Dynamic theming: 6 built-in color templates + custom brand color

---

## Slide 9 — Security & Compliance

Built for regulated environments from day one.

- **Two-factor authentication** via Laravel Fortify (TOTP)
- **21 granular permissions** across 9 groups — least-privilege by design
- **Full audit log** on every action: who changed what, and when
- Encrypted API keys and sensitive fields (signatures, raw data) at rest
- Soft deletes on all resident-facing data — nothing is permanently lost
- `created_by` / `updated_by` / `reviewed_by` fields on every critical record for full traceability

---

## Slide 10 — Tech Stack

```
Backend      Laravel 12 + PHP 8.2
Frontend     Livewire v4 + Flux UI + Tailwind CSS v4
Auth         Laravel Fortify (2FA, TOTP)
AI           Groq API (Llama/Qwen) + Google Gemini 2.0/2.5
Storage      Cloudflare R2 (S3-compatible)
Exports      PDF (DomPDF) + Word (PHPWord)
Permissions  Spatie Laravel Permission
Database     SQLite (dev) / any SQL database (production)
```

Deployable on any PHP host. Vercel-ready static asset build. Queue and session backed by the database — no Redis required to get started.

---

## Slide 11 — Market & Opportunity

- The UK alone has **~15,000 registered care homes**; the US has over **65,000 nursing and assisted-living facilities**
- Most still rely on paper-based or fragmented point-solution software
- CQC (UK) and CMS (US) inspections increasingly require **digital evidence trails**
- No dominant all-in-one solution combining clinical workflows + AI + mentorship + public CMS
- Natural SaaS pricing model: **per facility per month**, with tiers by resident capacity

---

## Slide 12 — Call to Action

CareNest is production-ready and actively deployed.

**What we're looking for:**
- Pilot care homes willing to replace their current tools
- NHS / Medicaid integration partners
- Angel or seed investment to accelerate sales and compliance certifications

**Why now:**
- AI capabilities have reached the point where report generation is genuinely useful, not a gimmick
- Regulatory pressure on digital documentation is increasing every year
- The window to establish a market-leading platform is open

---

*Demo available — full role-based walkthrough on request.*

**Contact:** deniskem.dev@gmail.com
