# CareNest — Engagement Model Proposal for KenorCare

**Document Version:** 1.0
**Date:** 2026-05-10
**Audience:** KenorCare leadership and CareNest Product Partner
**Companion Documents:** `docs/carenest-tor.md`, `docs/kenorcare-partnership-agreement.md`

> Presentation source. Each `## Slide N` section corresponds to one slide and is detailed enough to double as speaker notes. Lift directly into PowerPoint, Google Slides, or Keynote.

---

## Slide 1 — Title & Purpose

### CareNest — Three Paths to Operational Readiness

**Subtitle:** Engagement Model Proposal for KenorCare

**Date:** 2026-05-10

**Why this deck**

- CareNest is ready to move from build planning to commercial commitment.
- KenorCare has indicated interest in a long-term partnership; this deck presents that path alongside two alternatives so the decision is made on full information.
- The recommendation at the end is **Strategic Partnership**, but One-Off Build and Monthly Billing are presented as legitimate fallbacks.

**What's covered:** system snapshot, the three models with figures and responsibilities, side-by-side comparison, timeline, and a recommended next step.

---

## Slide 2 — CareNest at a Glance

CareNest is a Laravel 12 + Livewire 4 platform purpose-built for behavioral health facility operations — unifying resident records, clinical documentation, staffing, therapy, mentorship, public website, reporting, and supervised AI assistance into a single role-based system.

### Functional domains (from TOR §5.1)

- Public website & external communication (CMS, contact, public chatbot)
- User, role, and system administration (5 roles, 21 permissions)
- Resident management (admission, discharge, readmission, longitudinal records)
- Care planning and daily care documentation (care plans, ADL, shift notes)
- Clinical and behavioral documentation (meds, vitals, incidents, 12+ clinical forms)
- Financial and service records (resident-linked transactions)
- Staff and workforce management (directory, qualifications, shift scheduling)
- Therapy management (assignments, sessions, exports)
- Mentorship and learning (weekly topics, lessons, AI mentor)
- Reporting and exports (PDF/Word for clinical, therapy, discharge)
- AI-assisted capabilities (Groq + Gemini, configurable per use case)

### Stack

PHP 8.2 · Laravel 12 · Livewire 4 · Flux UI · Tailwind 4 · Spatie Permissions · DOMPDF · PHPWord · Groq + Google Gemini

### By the numbers

| Metric | Count |
| :---- | :---- |
| Eloquent models | 28 |
| Functional domains | 9 |
| Roles | 5 |
| Permissions | 21 |
| AI providers | 2 |
| Validation trait domains | 18 |

---

## Slide 3 — The Three Engagement Models

| Model | Headline | Best when KenorCare wants… | Primary risk |
| :---- | :---- | :---- | :---- |
| **One-Off Build** | Pay once, own it forever | Full ownership and control of the codebase | High upfront cash outlay |
| **Monthly Billing (SaaS)** | Pay-as-you-go subscription | Predictable opex, no infrastructure burden | Cumulative cost over multi-year horizon |
| **Strategic Partnership** | Revenue share on commercialization | Zero cash outlay, ongoing upside, shared mission | Returns depend on commercial traction |

> **Leaning option:** Strategic Partnership. The Terms of Reference (`carenest-tor.md`) and a draft Partnership Agreement (`kenorcare-partnership-agreement.md`) are already prepared to support this path.

---

## Slide 4 — Model 1: One-Off Build

### Headline figure: **USD $55,000 – $75,000 (fixed price, full TOR scope)**

Optional ongoing support retainer: **USD $1,500 / month** post-handover.

### Payment schedule (milestone-based)

| Milestone | % | Trigger |
| :---- | :---- | :---- |
| Kickoff | 30% | Contract signed, requirements locked |
| Clinical & Resident modules | 30% | Residents, care plans, medications, vitals, incidents delivered |
| Therapy, Mentorship & AI | 30% | Therapy module, mentorship platform, AI integration delivered |
| Go-live & acceptance | 10% | Deployed, UAT signed off, 30-day warranty start |

### What's included

- Full scope from TOR §5.1 (all 9 functional domains)
- Source code handover with documentation
- One production deployment + staging environment
- 30-day post-launch warranty (defect fixes only)
- Admin training (2 sessions)

### Responsibilities

- **KenorCare:** funds the build, signs off milestones, provides domain feedback during UAT, owns the deployed instance, code, and data after go-live, takes over hosting and AI provider costs.
- **Product Partner:** delivers the platform, deploys once, hands over code and documentation, exits after warranty unless retainer signed.

### Pros

- Predictable, capped total cost.
- Full ownership of code and data.
- No ongoing royalty or revenue share.

### Cons

- Highest upfront capex.
- KenorCare absorbs all hosting, AI usage, and ongoing maintenance costs.
- No shared upside if CareNest is later commercialized to other facilities.
- Future enhancements require new contract or retainer.

---

## Slide 5 — Model 2: Monthly Billing (SaaS)

### Headline figures: **Setup USD $8,000 – $12,000** (one-time) + **Monthly USD $2,500 – $4,500**

Annual prepay: **10% discount**.

### Tier breakdown

| Tier | Monthly | Resident cap | AI usage | Support |
| :---- | :---- | :---- | :---- | :---- |
| **Standard** | $2,500 | Up to 50 | Standard cap | Business hours |
| **Professional** | $3,500 | Up to 150 | Expanded cap | Priority, 1 business day SLA |
| **Enterprise** | $4,500 | Unlimited / multi-facility | High cap, custom integrations | Dedicated channel, 4-hour SLA |

### What's included every month

- Hosting and infrastructure
- AI provider usage within tier cap (overages billed at cost)
- Security patches and version upgrades
- Backups and disaster recovery
- Helpdesk support (per tier)
- Minor enhancements and bug fixes

### Responsibilities

- **Product Partner:** runs everything — hosting, ops, AI keys, backups, support, security, upgrades.
- **KenorCare:** pays subscription, designates admin user(s), provides feedback, requests enhancements through formal channel.

### Term

- Month-to-month or annual (10% discount on annual prepay).
- 30-day notice for cancellation; data export provided on offboarding.

### Pros

- Low entry cost; opex rather than capex.
- No infrastructure or AI burden on KenorCare.
- Continuous improvement: new features without separate negotiation.
- Predictable monthly budget.

### Cons

- Cumulative cost over 3+ years exceeds one-off.
- KenorCare does not own the underlying code.
- Heavy customizations may require change-control or higher tier.

---

## Slide 6 — Model 3: Strategic Partnership *(leaning option)*

### Headline structure: **10% Net Revenue baseline + 15% sourced bonus = 25% on KenorCare-sourced customers**

Source: `kenorcare-partnership-agreement.md` §4.2.

### How the share works

| Customer type | KenorCare share |
| :---- | :---- |
| General Platform customer | **10%** of Net Revenue |
| KenorCare-sourced customer (introduced, referred, or campaigned by KenorCare and documented in writing) | **25%** of Net Revenue |

### Net Revenue definition (Agreement §4.4)

Gross cash revenue from Platform customers, **less**:

- Refunds, chargebacks, bad debt
- Sales / VAT / withholding tax
- Payment processor fees
- Third-party pass-through hosting, AI, messaging, storage, licensing costs
- Approved third-party referral commissions
- Pass-through travel / implementation expenses

### Payment cadence (§4.5)

- Quarterly settlement.
- Statement delivered within **30 days** of each calendar quarter end.
- Payment due within **15 days** of statement delivery.
- KenorCare may audit revenue records once per calendar year on 15 business days' notice.

### Term (§13)

- Initial term: **3 years**.
- Auto-renews in **1-year** increments unless either party gives **60 days' notice**.
- Material breach cure window: 30 days after written notice.

### Performance clause (§4.6)

If KenorCare materially stops providing advisory and marketing support for more than **60 days after written notice**, the baseline 10% may be suspended; the 25% sourced rate continues for active sourced customers.

### Why this fits KenorCare

- Zero upfront cash outlay.
- Ongoing revenue from a product KenorCare helped shape.
- Leverages clinical credibility into a commercial asset.
- 25% on customers KenorCare brings in directly rewards business development effort.

### Why this fits Product Partner

- Domain validation from a real behavioral health operator.
- Pilot environment for testing in a real workflow.
- Marketing reach into behavioral health and residential treatment market.
- Shared incentive to make the product succeed.

---

## Slide 7 — Side-by-Side Comparison

| Dimension | One-Off Build | Monthly Billing | Strategic Partnership |
| :---- | :---- | :---- | :---- |
| **Upfront cost** | $55K – $75K | $8K – $12K setup | $0 |
| **Ongoing cost / share** | $0 (or $1.5K/mo retainer) | $2.5K – $4.5K / month | 10% – 25% of Net Revenue |
| **Code ownership** | KenorCare | Product Partner | Product Partner |
| **Hosting & ops** | KenorCare | Product Partner | Product Partner |
| **AI cost burden** | KenorCare | Product Partner (within cap) | Pass-through, deducted from Net Revenue |
| **KenorCare revenue from external customers** | None | None | 10% baseline / 25% on sourced |
| **Risk to KenorCare** | Highest cash exposure | Recurring opex commitment | Returns tied to commercial traction |
| **Risk to Product Partner** | Lowest (paid in full) | Moderate (recurring obligation) | Highest (no fee until commercialization) |
| **Time to operational readiness** | 3 – 4 months | 3 – 4 months | 3 – 4 months |
| **Term commitment** | Project term + warranty | Month-to-month or annual | 3 years initial, auto-renew |
| **Best fit when…** | KenorCare wants ownership and control | KenorCare wants predictable opex and zero ops | KenorCare wants to be a commercial co-beneficiary |

---

## Slide 8 — Responsibilities Map

### Product Partner — across all three models

Architecture, code, deployment, security controls, AI providers and prompts, audit logging, document export pipelines, technical documentation, release management.

### KenorCare — varies by model

| Activity | One-Off | Monthly | Partnership |
| :---- | :---- | :---- | :---- |
| Funding the build | Milestone payments | Setup + monthly | None |
| Domain feedback during build | Required (UAT) | Required (UAT) | Required (Agreement §3.2) |
| Workflow validation | UAT only | UAT + ongoing | Continuous (§3.2) |
| Pilot participation | Optional | Optional | Expected (§3.2) |
| Marketing & introductions | Not required | Not required | Expected (§3.2 + §8) |
| Designating admin user(s) | Required | Required | Required |
| Monthly review meetings | Not required | Optional | Required (§3.3) |
| Hosting & AI cost burden | KenorCare | Product Partner | Product Partner (pass-through in Net Revenue) |
| Code ownership at end of term | KenorCare | Product Partner | Product Partner |

### Joint responsibilities (Partnership only — Agreement §3.3)

- Meet at least once per month during active development or commercialization periods.
- Review roadmap priorities, pilot findings, market feedback, commercialization status.
- Act in good faith with commercially reasonable effort.
- Escalate material legal, compliance, product safety, or reputational concerns promptly.

---

## Slide 9 — Timeline & Milestones

### Build phase — applies to all three models (~3 – 4 months)

| Month | Deliverables |
| :---- | :---- |
| **Month 1** | Foundation: auth, roles & permissions, system settings, residents, care plans |
| **Month 2** | Clinical: medications + administration, vitals, incidents; staff directory and shift scheduling |
| **Month 3** | Therapy module, mentorship platform, public website CMS, AI integration, PDF/Word exports |
| **Month 4** | UAT, hardening, pilot inside KenorCare, go-live |

### Partnership-specific post-launch milestones

| Time | Event |
| :---- | :---- |
| Month 5+ | Pilot inside KenorCare environment; first case studies and testimonials |
| Quarter end + 30 days | First revenue share statement issued |
| Quarter end + 45 days | First payment due (if revenue exists) |
| Year 1 end | First annual review of partnership performance |
| Year 3 end | Initial term ends; auto-renews unless 60-day notice given |

### Critical path dependencies

- Domain validation sessions in Month 1 unlock clinical form design in Month 2.
- AI prompt review in Month 3 requires KenorCare clinical sign-off before go-live.
- Pilot in Month 4 requires KenorCare to designate operational test users.

---

## Slide 10 — Recommendation & Next Steps

### Recommendation: **Strategic Partnership**

This model best matches KenorCare's stated interest, minimizes cash exposure, recognizes the genuine commercial value of KenorCare's clinical authority, and creates aligned incentives for both parties to succeed.

### Why not the others (in short)

- **One-off** asks KenorCare to bear the full build cost and all ongoing infrastructure burden — a strong fit only if ownership of the code is itself the strategic goal.
- **Monthly billing** is a clean SaaS option but treats KenorCare purely as a customer rather than a partner — it doesn't capture the marketing and credibility value KenorCare brings.

### Hybrid is also possible

- **Partnership + accelerator contribution:** KenorCare contributes a modest one-time amount (e.g., USD $10K – $20K) to accelerate build, in exchange for a slightly higher revenue share floor or an early-stage exclusivity window. Open for executive discussion.

### Concrete next steps

1. **Confirm preferred model in writing** within 14 days.
2. **Finalize agreement placeholders** in `kenorcare-partnership-agreement.md` (legal names, addresses, governing law, BAA requirement, dispute venue) per §20 of the agreement — or sign one-off / SaaS contract.
3. **Kickoff meeting** within 14 days of signature; align on Month 1 deliverables.
4. **Build begins**; first joint review meeting at the end of Month 1.

### Reference documents

- Full system scope and acceptance criteria → `docs/carenest-tor.md`
- Full partnership terms, IP, confidentiality, and revenue share definitions → `docs/kenorcare-partnership-agreement.md`
