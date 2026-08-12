# **Terms of Reference (ToR)**

## **CareNest Care Home Management System**

**Document Version:** 1.0  
**Date:** 2026-05-10  
**System:** CareNest  
**Prepared From:** Current repository implementation and architecture notes

## **1\. Background**

CareNest is a Laravel 12 and Livewire 4 platform designed to support behavioral health facilities  operations through a unified digital system. The repository shows an implementation that combines resident management, clinical documentation, medication workflows, staffing, therapy operations, reporting, mentorship, a public-facing website, and AI-assisted features.

The system is intended to reduce fragmented record-keeping, improve care coordination, strengthen accountability, and provide operational visibility across administrative, clinical, and learning workflows.

## **2\. Purpose of This ToR**

This TOR defines the scope, objectives, requirements, deliverables, stakeholder responsibilities, and acceptance criteria for the CareNest system. It can be used as a reference for implementation planning, project governance, enhancement work, stakeholder alignment, and future procurement or partnership discussions.

## **3\. Project Goal**

To deliver and maintain a secure, role-based care management platform that supports day-to-day behavioral health facilities operations, structured clinical documentation, staff coordination, therapy services, institutional learning, and management reporting, with selective AI assistance embedded in approved workflows.

## **4\. Objectives**

***The system shall:***

1. Centralize resident, clinical, staff, therapy, and operational records.  
2. Improve care continuity through structured digital documentation and resident lifecycle tracking.  
3. Strengthen accountability through role-based access control and audit logging.  
4. Support clinical and operational reporting, including printable/exportable records.  
5. Provide public-facing communication tools for prospective residents, families, and visitors.  
6. Enable supervised AI use cases such as chat assistance, report generation, and guided knowledge access.  
7. Support training and mentorship workflows for staff development.

## **5\. Scope of Work**

### **5.1 In Scope**

The current scope of CareNest includes the following functional domains.

#### **A. Public Website and External Communication**

- Public pages for home, about, services, gallery, FAQ, and contact.  
- Management of testimonials, team members, FAQ items, gallery, services, and contact submissions.  
- Public contact capture and organization-managed website content.  
- Public chatbot for visitor questions, protected with rate limiting.

#### **B. User, Role, and System Administration**

- User administration.  
- Role and permission management.  
- System settings management, including branding, contact details, and AI settings.  
- Agency management.  
- Audit log review.

#### **C. Resident Management**

- Resident registration and profile management.  
- Admission, discharge, and readmission workflows.  
- Resident detail views and longitudinal records.  
- Support for demographic, care, and administrative data.

#### D. Care Planning and Daily Care Documentation

- Care plan creation, review, and update workflows.  
- Care plan goals and related care documentation.  
- Shift progress notes and daily resident tracking.  
- ADL tracking forms.

#### E. Clinical and Behavioral Documentation

- Medication management and medication administration record workflows.  
- Vitals recording and review.  
- Incident reporting and follow-up.  
- Clinical and behavioral forms including:  
- initial assessments  
- nursing assessments  
- authorizations  
- contact notes  
- BHP progress notes  
- ASAM checklists  
- face sheets  
- safety plans  
- mental status exams  
- treatment refusals  
- appointment logs  
- observation notes

#### **F. Financial and Service Records**

- Resident-linked financial transaction records.  
- Resident-linked service and authorization documentation where applicable.

#### **G. Staff and Workforce Management**

- Staff directory and staff profile management.  
- Staff qualification and document tracking.  
- Shift scheduling and shift detail management.

#### **H. Therapy Management**

- Therapist assignment to residents.  
- Session scheduling, documentation, and review.  
- Therapy session reporting and exports.  
- Therapy-specific reporting with PDF and Word generation.

#### **I. Mentorship and Learning**

- Mentorship dashboard and weekly topic views.  
- Teaching session creation, tracking, and review.  
- Topic, lesson, attachment, and AI settings management.  
- AI mentor interaction endpoint for guided support.  
- Mentorship reporting.

#### **J. Reporting and Exports**

- Central reports area for resident, clinical, staff, audit, and AI-generated reports.  
- PDF export support for multiple resident and clinical records.  
- Word export support for therapy and discharge-related reporting.

#### **K. AI-Assisted Capabilities**

- Authenticated internal chatbot for staff users.  
- Public chatbot for external website visitors.  
- Configurable AI providers and models.  
- AI settings for report generation, document analysis, care assistance, and incident summarization.  
- AI mentor support inside the mentorship module.

### 5.2 Out of Scope for the Current Phase

Unless separately approved, the following are out of scope for the current TOR baseline:

- Full billing and insurance claim processing.  
- Family/resident self-service portal.  
- Medical device integrations.  
- External EHR interoperability.  
- Advanced inventory and procurement automation.  
- Mobile application delivery beyond the current web-first platform direction.

## 6\. Key Stakeholders

- System Owner / Sponsor  
- Behavioral health facility Management  
- Clinical Leadership  
- Nurses and Caregivers  
- Therapists  
- Administrative Staff  
- Compliance and Audit Personnel  
- IT / System Administrators  
- Trainers / Mentorship Leads  
- Residents and families as indirect beneficiaries

## 7\. User Roles Reflected in the System

Repository configuration indicates role-based access using defined permissions. Core roles currently evidenced include:

- system\_admin  
- Facility \_manager  
- nurse  
- Behavioral health Technician  
- therapist

The permission model covers user management, residents, staff, medications, care plans, therapy, incidents reports, settings, audit logs, and mentorship.

## 8\. Functional Requirements

The system shall:

1. Require authentication for internal operational modules.  
2. Enforce role- and permission-based authorization per module and action.  
3. Maintain resident records with admission, discharge, and readmission support.  
4. Support structured care plans and care goals.  
5. Record medication orders, administration events, and related clinical notes.  
6. Capture vitals, incidents, and resident-specific clinical documentation.  
7. Allow staff scheduling and staff record maintenance.  
8. Support therapist assignment, therapy session capture, and therapy reporting.  
9. Provide mentorship content, lesson management, and teaching session workflows.  
10. Produce operational and clinical reports, including exportable documents.  
11. Support configurable AI workflows with administrative control over providers and prompts.  
12. Maintain auditable administrative oversight of sensitive actions.

## **9\. Non-Functional Requirements**

### **9.1 Security and Privacy**

- Role-based access control must be enforced consistently.  
- Sensitive settings such as AI API keys must be protected and encrypted where applicable.  
- Clinical and resident information must be accessible only to authorized users.  
- Audit trails should be available for critical administrative activity.

### **9.2 Availability and Reliability**

- Core operational workflows must remain available during standard care home working hours.  
- Failures in optional AI services must not block essential record-keeping workflows.

### **9.3 Usability**

- The interface should support rapid data entry for care, clinical, and reporting staff.  
- Public pages should remain easy to navigate for non-technical visitors and families.

### **9.4 Maintainability**

- The platform shall remain aligned with Laravel and Livewire conventions.  
- Code quality shall be maintained through formatting and automated test practices already defined in the repository.

### **9.5 Portability and Deployment**

- The system shall support modern web deployment patterns already reflected in the repository.  
- Build and test workflows shall remain reproducible through Composer and npm scripts.

## **10\. Technical Baseline**

The current repository indicates the following technical stack and implementation baseline:

- Backend: PHP 8.2, Laravel 12  
- Frontend: Livewire 4, Flux UI, Vite, Tailwind CSS 4  
- Authorization: Spatie Laravel Permission  
- Document generation: DOMPDF and PHPWord  
- AI providers currently configured in code/settings: Groq and Gemini  
- Optional mobile direction: NativePHP Mobile package present in the repository

## **11\. Deliverables**

The system delivery under this TOR shall include:

1. A working web-based care management application.  
2. Role-based internal modules for administration, residents, clinical records, staffing, therapy, reports, and mentorship.  
3. Public website pages and external communication features.  
4. Configurable AI integration points with administrative controls.  
5. Exportable PDF and Word document outputs where implemented.  
6. Technical and operational documentation sufficient for handover and support.

## **12\. Responsibilities**

### System Owner / Sponsor

- Approve scope, priorities, and release decisions.  
- Confirm policy, compliance, and operational requirements.

### Product / Operations Leadership

- Validate workflow fit for facility operations.  
- Confirm data fields, forms, and reporting expectations.

### Clinical Leadership

- Validate clinical forms, therapy documentation, medication workflows, and incident handling.

### Technical Team

- Implement, test, secure, deploy, and maintain the platform.  
- Manage integrations, backups, and system configuration.

### End Users

- Participate in user acceptance testing.  
- Use the system according to approved workflow and access policies.

## **13\. Implementation Approach**

Implementation and enhancement work should follow these principles:

1. Preserve role-based security boundaries.  
2. Prioritize resident safety, documentation accuracy, and operational traceability.  
3. Treat AI as an assistive layer, not an autonomous decision-maker.  
4. Roll out new modules in controlled phases with validation from the responsible business owners.  
5. Maintain clear separation between public-facing content and protected internal data.

## **14\. Risks and Mitigation**

Each risk is categorized by domain (Security, Technical, Operational, Legal/Compliance, Financial, Schedule/Human Resources) and described with the specific impact that would occur if the risk materializes.

| Category | Risk | Impact if Risk Occurs | Mitigation |
| :---- | :---- | :---- | :---- |
| Security | Unauthorized access to sensitive records | Loss or exposure of confidential resident, clinical, and staff data, leading to privacy breaches, reputational damage, and potential regulatory penalties | Enforce permissions, authentication, audit review, and least-privilege role design |
| Security | Compromise of AI provider API keys or sensitive system settings | Unauthorized use of paid AI services, financial loss, and potential exposure of organizational data submitted through compromised endpoints | Encrypt API keys at rest, restrict access to AI settings, rotate credentials regularly, and monitor usage logs |
| Technical | Poor-quality or unsafe AI outputs | Inaccurate clinical summaries, misleading mentorship guidance, or flawed reports that, if relied upon without review, could affect resident care decisions and operational accuracy | Keep AI configurable, use scoped prompts, require human review for clinical/management decisions |
| Technical | Data inconsistency across modules | Mismatched resident, clinical, or staffing records leading to incorrect care decisions, broken reports, and loss of data integrity across the platform | Standardize forms, validation rules, and record ownership |
| Technical | System downtime or performance degradation | Inability of staff to record care, administer medications, or access resident records during operational hours, causing delays in service delivery | Maintain reliable hosting, monitor performance, run regular backups, and ensure essential workflows function independently of optional AI services |
| Operational | Incomplete user adoption | Continued reliance on paper or spreadsheets, fragmented record-keeping, and reduced return on investment from the platform | Provide training, phased rollout, and workflow-aligned UX refinement |
| Operational | Overextension into non-core features | Delivery delays on core modules, dilution of focus, and increased maintenance burden without clear operational benefit | Maintain phase boundaries and controlled change approval |
| Legal / Compliance | Reporting gaps for compliance needs | Inability to demonstrate regulatory compliance during audits or inspections, leading to fines, sanctions, or restrictions on facility operations | Validate reports with operational and clinical stakeholders before release |
| Legal / Compliance | Inadequate audit trail for sensitive actions | Inability to investigate incidents, prove accountability, or respond to legal or regulatory inquiries about access to resident data | Maintain audit logs for critical administrative activity and review them periodically |
| Financial | Uncontrolled AI usage costs | Unexpected escalation of AI provider charges that exceed planned operating budget for the platform | Configure model selection, cap usage where supported, and monitor AI spending against budget |
| Financial | Scope creep beyond approved phases | Unbudgeted development work, delayed delivery of approved scope, and erosion of project funding | Enforce change-control process and require sponsor approval for scope additions |
| Schedule / Human Resources | Key personnel turnover or knowledge gaps | Delayed delivery, reduced support quality, and loss of institutional understanding of the platform | Maintain technical and operational documentation, cross-train team members, and avoid single points of knowledge |
| Schedule / Human Resources | Insufficient stakeholder availability for validation | Delayed user acceptance testing, late discovery of workflow gaps, and pushed release timelines | Plan validation sessions early, secure stakeholder commitments, and run phased reviews aligned to module readiness |

## **15\. Acceptance Criteria**

CareNest shall be considered compliant with this TOR when:

1. Authorized users can access only the modules and actions permitted to their role.  
2. Resident, clinical, staff, therapy, and mentorship records can be created and reviewed through the application.  
3. Public website content can be managed through the administrative interface.  
4. Core reports and implemented export flows function correctly.  
5. Audit log visibility is available for authorized administrative users.  
6. AI-assisted features are configurable, bounded, and do not prevent manual completion of critical workflows.  
7. The system can be built, linted, and tested using the repository’s documented commands.

## **16\. Success Indicators**

- Reduced reliance on paper or fragmented spreadsheets.  
- Faster retrieval of resident and clinical records.  
- Improved completeness of care and clinical documentation.  
- Better visibility into staff, therapy, and mentorship activities.  
- More consistent reporting for management and compliance oversight.

## **17\. Conclusion**

CareNest is positioned as a unified care operations platform with strong emphasis on resident records, structured documentation, staff coordination, therapy workflows, reporting, and supervised AI support. This TOR establishes the baseline reference for governing current delivery and future enhancements of the system.

### 17.1 Managing Scope Creep and Future Feature Requests

The functional domains documented in Section 5.1 represent the agreed scope under this TOR. Items listed in Section 5.2 are explicitly excluded from the current phase. As the system matures, new feature requests will inevitably arise from clinical, operational, administrative, or compliance stakeholders. To preserve delivery quality, budget, and timelines, all such requests shall be governed by a controlled change process rather than added informally.

The following principles shall apply when additional features are proposed:

1. **Formal Change Request** — Any new feature or significant modification outside the documented scope must be submitted as a written change request describing the business need, expected impact, and target users.
2. **Impact Assessment** — The technical team shall evaluate the request against architecture, security, data model, AI usage, and integration impact, and shall provide an estimate of effort, cost, schedule impact, and risk implications.
3. **Sponsor and Stakeholder Approval** — The System Owner / Sponsor, in consultation with relevant clinical, operational, or compliance leads, shall approve, defer, reject, or re-prioritize the request. No out-of-scope feature shall be implemented without this approval.
4. **Phased Inclusion** — Approved enhancements shall be grouped into a future phase or release rather than injected mid-phase, so that current deliverables remain on track and the platform's role-based security, data integrity, and audit boundaries are preserved.
5. **TOR Update** — Material additions to scope shall be reflected through a versioned update of this TOR, ensuring that the document remains the single source of truth for what CareNest is committed to deliver.
6. **Cost and Resource Re-baselining** — Where new features expand effort, infrastructure usage, AI consumption, or support obligations, the budget, timeline, and resource plan shall be re-baselined before work commences.
7. **Avoid Silent Expansion** — Stakeholders, technical contributors, and AI-assisted workflows shall not introduce features, modules, or integrations that have not gone through the change process, even if they appear convenient or low-effort, since unmanaged expansion erodes maintainability and increases risk.

This approach ensures that CareNest evolves intentionally, that each enhancement is justified by operational or clinical value, and that the platform continues to serve its core mission of safe, accountable, and well-documented behavioral health facility operations.

