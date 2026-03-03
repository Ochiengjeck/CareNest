# Therapy Module Documentation

## Overview

The Therapy Module provides comprehensive therapy management capabilities for CareNest, enabling therapists to manage their assigned residents, conduct and document therapy sessions, and generate AI-powered clinical reports. The module supports a complete clinical workflow from session scheduling through documentation and reporting.

## Table of Contents

1. [Role & Permissions](#role--permissions)
2. [Database Schema](#database-schema)
3. [Models](#models)
4. [Routes](#routes)
5. [Pages & Features](#pages--features)
6. [AI Integration](#ai-integration)
7. [Test Users](#test-users)
8. [Usage Guide](#usage-guide)

---

## Role & Permissions

### New Role: `therapist`

A dedicated role for therapy professionals with the following permissions:

| Permission | Description |
|------------|-------------|
| `view-residents` | View resident profiles and basic information |
| `view-care-plans` | Reference care plans during therapy sessions |
| `view-therapy` | View therapy sessions and reports |
| `conduct-therapy` | Create and document therapy sessions |
| `view-reports` | Access therapy reports and AI report generation |

### New Permissions

| Permission | Description | Assigned To |
|------------|-------------|-------------|
| `manage-therapy` | Full therapy management (CRUD sessions, manage assignments) | system_admin, care_home_manager |
| `view-therapy` | View therapy sessions and data | system_admin, care_home_manager, nurse, therapist |
| `conduct-therapy` | Conduct and document therapy sessions | system_admin, therapist |

### Updated Existing Roles

| Role | Added Permissions |
|------|-------------------|
| `system_admin` | `manage-therapy`, `view-therapy`, `conduct-therapy` |
| `care_home_manager` | `manage-therapy`, `view-therapy` |
| `nurse` | `view-therapy` (read-only access) |

---

## Database Schema

### Table: `therapist_assignments`

Links therapists to residents for ongoing therapy relationships.

```sql
CREATE TABLE therapist_assignments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    therapist_id BIGINT UNSIGNED NOT NULL,      -- FK to users (therapist)
    resident_id BIGINT UNSIGNED NOT NULL,       -- FK to residents
    assigned_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    notes TEXT NULL,
    assigned_by BIGINT UNSIGNED NULL,           -- FK to users (who made assignment)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,                  -- Soft deletes

    UNIQUE KEY (therapist_id, resident_id),
    INDEX (status),
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### Table: `therapy_sessions`

Stores detailed therapy session records following clinical documentation standards.

```sql
CREATE TABLE therapy_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    therapist_id BIGINT UNSIGNED NOT NULL,      -- FK to users (therapist)
    resident_id BIGINT UNSIGNED NOT NULL,       -- FK to residents
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    service_type ENUM(
        'individual',           -- Individual Note (IND)
        'group',                -- Group (GR)
        'intake_assessment',    -- Intake/Assessment (INT)
        'crisis',               -- Crisis (CR)
        'collateral',           -- Collateral (CO)
        'case_management',      -- Case Management (CM)
        'treatment_planning',   -- Treatment Planning (TP)
        'discharge',            -- Discharge (D)
        'other'                 -- Other (O)
    ) NOT NULL,
    challenge_index ENUM(
        'substance_use',        -- 1. Substance Use Disorder
        'mental_health',        -- 2. Mental Health
        'physical_health',      -- 3. Physical Health
        'employment_education', -- 4. Employment/Education
        'financial_housing',    -- 5. Financial/Housing
        'legal',                -- 6. Legal
        'psychosocial_family',  -- 7. Psycho-Social/Family
        'spirituality'          -- 8. Spirituality
    ) NULL,
    session_topic VARCHAR(255) NOT NULL,
    interventions TEXT NULL,                    -- Provider Support & Interventions
    progress_notes TEXT NULL,                   -- Client's Specific Progress
    client_plan TEXT NULL,                      -- Client's Plan
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    supervisor_id BIGINT UNSIGNED NULL,         -- BHP supervisor
    supervisor_signed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,                  -- Soft deletes

    INDEX (session_date),
    INDEX (service_type),
    INDEX (status),
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## Models

### TherapistAssignment

**Location:** `app/Models/TherapistAssignment.php`

**Relationships:**
- `therapist()` - BelongsTo User
- `resident()` - BelongsTo Resident
- `assignedBy()` - BelongsTo User

**Scopes:**
- `active()` - Only active assignments
- `forTherapist($userId)` - Filter by therapist
- `forResident($residentId)` - Filter by resident

**Accessors:**
- `status_label` - Human-readable status (Active, Inactive, Completed)
- `status_color` - Color for UI badges (green, zinc, blue)

### TherapySession

**Location:** `app/Models/TherapySession.php`

**Relationships:**
- `therapist()` - BelongsTo User
- `resident()` - BelongsTo Resident
- `supervisor()` - BelongsTo User
- `creator()` - BelongsTo User
- `updater()` - BelongsTo User

**Scopes:**
- `completed()` - Only completed sessions
- `scheduled()` - Only scheduled sessions
- `forTherapist($id)` - Filter by therapist
- `forResident($id)` - Filter by resident
- `inDateRange($from, $to)` - Filter by date range
- `byServiceType($type)` - Filter by service type
- `today()` - Today's sessions
- `upcoming()` - Future scheduled sessions

**Accessors:**
- `service_type_label` - Human-readable service type
- `service_type_color` - Color for UI badges
- `challenge_label` - Full challenge/barrier label with index number
- `status_label` - Human-readable status
- `status_color` - Color for UI badges
- `duration_minutes` - Calculated session duration
- `formatted_time_range` - "10:00 AM - 11:00 AM" format

### User Model Updates

**Location:** `app/Models/User.php`

Added relationships:
```php
public function therapistAssignments(): HasMany
public function therapySessions(): HasMany
public function assignedResidents(): HasMany  // Active assignments only
```

Added therapist to dashboard widgets:
```php
if (in_array('therapist', $roles)) {
    $widgets = array_merge($widgets, ['therapy-sessions-today', 'my-therapy-residents', 'upcoming-sessions']);
}
```

---

## Routes

**Location:** `routes/therapy.php`

### Therapist Routes (requires `conduct-therapy`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/therapy/dashboard` | `therapy.dashboard` | Therapist's home dashboard |
| GET | `/therapy/my-residents` | `therapy.my-residents` | List of assigned residents |
| GET | `/therapy/sessions/create` | `therapy.sessions.create` | Schedule new session |
| GET | `/therapy/sessions/{session}/document` | `therapy.sessions.document` | Document completed session |

### View Routes (requires `view-therapy`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/therapy/sessions` | `therapy.sessions.index` | List all sessions |
| GET | `/therapy/sessions/{session}` | `therapy.sessions.show` | View session details |

### Admin Routes (requires `manage-therapy`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/therapy/therapists` | `therapy.therapists.index` | List all therapists |
| GET | `/therapy/therapists/{user}` | `therapy.therapists.show` | Therapist details |
| GET | `/therapy/assignments` | `therapy.assignments.index` | List all assignments |
| GET | `/therapy/assignments/create` | `therapy.assignments.create` | Create assignment |
| GET | `/therapy/assignments/{assignment}/edit` | `therapy.assignments.edit` | Edit assignment |
| GET | `/therapy/sessions/{session}/edit` | `therapy.sessions.edit` | Edit session |

### Report Routes (requires `view-therapy` + `view-reports`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/therapy/reports/generate` | `therapy.reports.generate` | AI report generator |

---

## Pages & Features

### Therapist Interface

#### Dashboard (`therapy/⚡dashboard.blade.php`)

The therapist's home page showing:
- **Quick Stats Cards:**
  - Today's sessions count
  - Assigned residents count
  - Completed sessions this week
  - Pending documentation count
- **Today's Sessions:** List with status, resident name, topic, and quick actions
- **Upcoming Sessions:** Next 5 scheduled sessions
- **Pending Documentation:** Sessions needing documentation with direct links
- **Quick Actions:** New Session, My Residents, Generate Report buttons

#### My Residents (`therapy/⚡my-residents.blade.php`)

Grid view of assigned residents with:
- Resident avatar, name, room number, age
- Session count and last session date
- Assignment date and status
- Quick action: Create new session
- Filters: search, status (active/inactive/completed)
- Pagination

### Session Management

#### Sessions Index (`therapy/sessions/⚡index.blade.php`)

Comprehensive session list with:
- **Filters:** Search, therapist (admin only), service type, status, date range
- **Table columns:** Date/time, therapist (admin), resident, topic, type badge, status badge
- **Actions:** View, Document (if needed), Edit (admin)
- Pagination (20 per page)

#### Create Session (`therapy/sessions/⚡create.blade.php`)

Form to schedule a new session:
- **Session Details:** Therapist (admin selectable), resident, date, service type, time range, challenge index, status
- **Session Topic:** Required field for session focus
- **Notes:** Optional additional notes
- Therapists see only their assigned residents; admins see all active residents

#### View Session (`therapy/sessions/⚡show.blade.php`)

Detailed session view with:
- **Status badges** and quick actions (Mark Completed, Cancel, No Show)
- **Session Information:** Date, time, duration, service type, challenge index, topic
- **Participants:** Therapist (BHT), Client, Supervisor (BHP) with avatars
- **Clinical Documentation:** Interventions, progress notes, client plan (if documented)
- **Record Information:** Session ID, created by, timestamps
- Link to generate report

#### Document Session (`therapy/sessions/⚡document.blade.php`)

Clinical documentation form with AI assistance:
- **Session Summary:** Quick reference card with session details
- **Provider Support & Interventions:** Textarea with AI Assist button
- **Client's Specific Progress:** Textarea with AI Assist button
- **Client's Plan:** Textarea with AI Assist button
- **Additional Notes:** Optional field
- AI generates professional clinical content based on session context

#### Edit Session (`therapy/sessions/⚡edit.blade.php`)

Admin-only full edit form:
- All session fields editable
- Clinical documentation fields
- Delete session option with confirmation

### Admin Interface

#### Therapists Index (`therapy/therapists/⚡index.blade.php`)

Grid of all therapists showing:
- Avatar, name, email
- Stats: Active residents, sessions this month, total sessions
- Actions: View Details, Assign Resident

#### Therapist Detail (`therapy/therapists/⚡show.blade.php`)

Comprehensive therapist profile:
- **Stats Cards:** Active residents, sessions this month, completed, total
- **Assigned Residents:** List with status badges
- **Recent Sessions:** Latest 10 sessions
- **Account Information:** Roles, creation date, verification status

#### Assignments Index (`therapy/assignments/⚡index.blade.php`)

Table of all therapist-resident assignments:
- **Filters:** Search resident, therapist, status
- **Columns:** Therapist, resident, assigned date, status, assigned by
- **Actions:** Edit, New Session, View Resident

#### Create/Edit Assignment (`therapy/assignments/⚡create.blade.php`, `⚡edit.blade.php`)

Form to manage assignments:
- Therapist and resident selection
- Assigned date
- Status (active/inactive/completed)
- Notes
- Delete option (edit only)

### AI Report Generator

#### Generate Reports (`therapy/reports/⚡generate.blade.php`)

AI-powered report generation with four report types:

1. **Individual Session Note**
   - Select a completed session
   - Generates formal clinical therapy note
   - Includes all session details and documentation

2. **Progress Summary (Multi-Session)**
   - Select resident and date range
   - Analyzes multiple sessions
   - Identifies patterns, achievements, recommendations

3. **Therapist Caseload Report**
   - Select therapist and date range
   - Productivity and workload analysis
   - Session distribution by type

4. **Resident Therapy History**
   - Select resident
   - Complete therapy engagement overview
   - Long-term patterns and compliance

**Features:**
- Custom instructions field for specific requirements
- Markdown-formatted output
- Fallback message when AI not configured

---

## AI Integration

### Use Case: `therapy_reporting`

**Location:** Added to `app/Services/AI/AiManager.php`

```php
'therapy_reporting' => [
    'label' => 'Therapy Session Reporting',
    'description' => 'Generate professional therapy session notes and progress reports',
    'icon' => 'clipboard-document-check',
    'recommended_provider' => 'groq',
],
```

### Configuration

Enable in **System Settings > AI Configuration**:
1. Enable AI globally
2. Configure Groq or Gemini API key
3. Enable "Therapy Session Reporting" use case
4. Optionally customize system prompt

### Helper Methods Added

```php
// Check if a use case is enabled
$aiManager->isUseCaseEnabled('therapy_reporting');

// Get the provider for a use case
$aiManager->getUseCaseProvider('therapy_reporting');

// Check if a provider is configured
$aiManager->isConfigured('groq');
```

### AI-Assisted Documentation

The document session page includes AI Assist buttons that generate:
- **Interventions:** Based on session topic and service type
- **Progress Notes:** Based on interventions and client context
- **Client Plan:** Based on session progress and recommendations

Prompts include:
- Session metadata (date, time, type, topic)
- Client information (name, conditions)
- Challenge/barrier focus area
- Previously filled fields for context

---

## Test Users

### Therapist Accounts

| Email | Password | Name | Role |
|-------|----------|------|------|
| `therapist@carenest.test` | `password` | Test Therapist | therapist |
| `therapist2@carenest.test` | `password` | Sarah Johnson | therapist |

### Seeded Test Data

**TherapistAssignmentSeeder:**
- Distributes active residents evenly among therapists
- Creates active assignments with realistic dates

**TherapySessionSeeder:**
- Creates 2-5 completed sessions per assignment
- Uses 4 different session templates:
  - DBT-Informed - Sitting With Discomfort Safely
  - Cognitive Behavioral Therapy - Identifying Thought Patterns
  - Mindfulness and Stress Reduction
  - Coping Skills Development
- Creates 1 upcoming scheduled session per assignment
- Includes full clinical documentation for completed sessions

---

## Usage Guide

### For Therapists

1. **Log in** with `therapist@carenest.test` / `password`
2. **View Dashboard** to see today's sessions and stats
3. **Check My Residents** for assigned caseload
4. **Create Session** by clicking "New Session" from dashboard or resident card
5. **Document Sessions** after completion using the Document button
6. **Use AI Assist** to help generate clinical documentation
7. **Generate Reports** for individual sessions or progress summaries

### For Administrators

1. **Log in** with `admin@carenest.test` or `manager@carenest.test`
2. **View Therapists** to see all therapists and their caseloads
3. **Manage Assignments** to assign/reassign residents to therapists
4. **View All Sessions** with full filtering capabilities
5. **Edit Sessions** as needed
6. **Generate Reports** for oversight and compliance

### Sidebar Navigation

The Therapy section appears for users with relevant permissions:

```
Therapy
├── My Dashboard      (conduct-therapy)
├── My Residents      (conduct-therapy)
├── Sessions          (view-therapy OR conduct-therapy)
├── Therapists        (manage-therapy)
├── Assignments       (manage-therapy)
└── Generate Reports  (view-reports)
```

---

## Files Summary

### New Files Created (22)

| File | Purpose |
|------|---------|
| `database/migrations/2026_01_31_100000_create_therapist_assignments_table.php` | Assignments table |
| `database/migrations/2026_01_31_100001_create_therapy_sessions_table.php` | Sessions table |
| `app/Models/TherapistAssignment.php` | Assignment model |
| `app/Models/TherapySession.php` | Session model |
| `app/Concerns/TherapistAssignmentValidationRules.php` | Assignment validation |
| `app/Concerns/TherapySessionValidationRules.php` | Session validation |
| `routes/therapy.php` | All therapy routes |
| `database/seeders/TherapistAssignmentSeeder.php` | Test assignments |
| `database/seeders/TherapySessionSeeder.php` | Test sessions |
| `resources/views/pages/therapy/⚡dashboard.blade.php` | Therapist dashboard |
| `resources/views/pages/therapy/⚡my-residents.blade.php` | Assigned residents |
| `resources/views/pages/therapy/sessions/⚡index.blade.php` | Sessions list |
| `resources/views/pages/therapy/sessions/⚡create.blade.php` | Create session |
| `resources/views/pages/therapy/sessions/⚡show.blade.php` | View session |
| `resources/views/pages/therapy/sessions/⚡document.blade.php` | Document session |
| `resources/views/pages/therapy/sessions/⚡edit.blade.php` | Edit session |
| `resources/views/pages/therapy/therapists/⚡index.blade.php` | Therapists list |
| `resources/views/pages/therapy/therapists/⚡show.blade.php` | Therapist detail |
| `resources/views/pages/therapy/assignments/⚡index.blade.php` | Assignments list |
| `resources/views/pages/therapy/assignments/⚡create.blade.php` | Create assignment |
| `resources/views/pages/therapy/assignments/⚡edit.blade.php` | Edit assignment |
| `resources/views/pages/therapy/reports/⚡generate.blade.php` | AI report generator |

### Modified Files (7)

| File | Changes |
|------|---------|
| `database/seeders/RolePermissionSeeder.php` | Added 3 permissions, therapist role, updated existing roles |
| `database/seeders/TestUserSeeder.php` | Added 2 therapist test users |
| `database/seeders/DatabaseSeeder.php` | Added therapy seeders to call list |
| `app/Models/User.php` | Added therapy relationships and dashboard widgets |
| `app/Services/AI/AiManager.php` | Added therapy_reporting use case and helper methods |
| `routes/web.php` | Added `require __DIR__.'/therapy.php'` |
| `resources/views/layouts/app/sidebar.blade.php` | Added Therapy navigation section |

---

## Service Types Reference

Based on the clinical report format:

| Code | Enum Value | Label |
|------|------------|-------|
| IND | `individual` | Individual Note |
| GR | `group` | Group |
| INT | `intake_assessment` | Intake/Assessment |
| CR | `crisis` | Crisis |
| CO | `collateral` | Collateral |
| CM | `case_management` | Case Management |
| TP | `treatment_planning` | Treatment Planning |
| D | `discharge` | Discharge |
| O | `other` | Other |

## Challenge Index Reference

Based on the clinical report format:

| Index | Enum Value | Label |
|-------|------------|-------|
| 1 | `substance_use` | Substance Use Disorder |
| 2 | `mental_health` | Mental Health |
| 3 | `physical_health` | Physical Health |
| 4 | `employment_education` | Employment/Education |
| 5 | `financial_housing` | Financial/Housing |
| 6 | `legal` | Legal |
| 7 | `psychosocial_family` | Psycho-Social/Family |
| 8 | `spirituality` | Spirituality |
