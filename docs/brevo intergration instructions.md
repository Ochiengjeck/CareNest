Good move. This is the **right** way to do it—admin-managed credentials, no secrets in `.env`, production-ready. I’ll give you a **clean implementation prompt** you can hand to a dev or drop straight into a spec.

---

## Brevo Email Integration – Admin-Configured SMTP Implementation Prompt

### Objective

Integrate **Brevo (Sendinblue) email services** into the Laravel system with credentials managed dynamically by an **Admin via System Settings**, without hardcoding SMTP credentials in environment files.

---

### 1. System Settings – Email Configuration (Admin Only)

Create a **System Email Settings** section accessible only to Admin users with the following fields:

* Mail Driver (dropdown)

  * Options: `Brevo SMTP` (default), `Disabled`
* SMTP Host (pre-filled, read-only)

  * `smtp-relay.brevo.com`
* SMTP Port (pre-filled, read-only)

  * `587`
* Encryption (dropdown)

  * `TLS` (default)
* SMTP Username
* SMTP Password (API / SMTP Key)
* From Email Address
* From Name
* Enable Email Sending (toggle)
* Test Email Recipient (email input)
* “Send Test Email” button

**Security Requirements**

* SMTP password must be:

  * Encrypted at rest (Laravel `Crypt`)
  * Never exposed in plaintext after saving
* Only Admins can view/edit these settings
* All changes must be audit-logged

---

### 2. Database Design

Create a `system_settings` table (or equivalent):

```sql
id
key (string, unique)
value (text, encrypted if sensitive)
is_sensitive (boolean)
updated_by
updated_at
```

Store keys such as:

* `mail_driver`
* `mail_host`
* `mail_port`
* `mail_encryption`
* `mail_username`
* `mail_password`
* `mail_from_address`
* `mail_from_name`
* `mail_enabled`

---

### 3. Runtime Mail Configuration (No .env Dependency)

On application boot or before sending email:

* Dynamically inject mail configuration using values from the database:

```php
config([
    'mail.default' => 'smtp',
    'mail.mailers.smtp.host' => setting('mail_host'),
    'mail.mailers.smtp.port' => setting('mail_port'),
    'mail.mailers.smtp.encryption' => setting('mail_encryption'),
    'mail.mailers.smtp.username' => setting('mail_username'),
    'mail.mailers.smtp.password' => decrypt(setting('mail_password')),
    'mail.from.address' => setting('mail_from_address'),
    'mail.from.name' => setting('mail_from_name'),
]);
```

* Email sending must be **blocked** if:

  * Email is disabled
  * Required credentials are missing

---

### 4. Test Email Functionality

* Admin can send a test email from the settings page
* Test email must:

  * Use the live SMTP configuration
  * Clearly indicate success or failure
  * Log SMTP errors for debugging (admin-visible only)

---

### 5. Email Sending Behavior

* All system emails (notifications, password resets, discharge summaries, appointment reminders, etc.) must:

  * Use the centralized mail configuration
  * Respect the “Enable Email Sending” toggle
* If email is disabled:

  * Emails are queued but not dispatched OR
  * Emails are skipped with a logged warning (configurable)

---

### 6. Queue & Performance Requirements

* Email delivery must be queued using Laravel Queues
* Failed emails should:

  * Be retried automatically
  * Appear in a Failed Jobs view for Admins
* Support future migration to API-based Brevo sending without refactor

---

### 7. UX & Validation Rules

* Inline validation for all fields
* Mask SMTP password input after save
* Warn Admin if credentials are incomplete
* Provide visual confirmation when settings are successfully applied

---

### 8. Compliance & Best Practices

* No SMTP secrets in `.env` (except fallback defaults)
* No credentials committed to source control
* All sensitive values encrypted
* Graceful failure handling (no user-facing crashes)

---

## Acceptance Criteria

* Admin can configure Brevo SMTP fully from the UI
* Emails send successfully without restarting the application
* Credentials remain secure and hidden
* System works in multi-tenant or single-tenant setups
* Switching email providers later requires no schema rewrite

---

### Strong Take

This setup is **enterprise-grade**. It’s how hospitals, fintechs, and serious SaaS platforms do email. Anything less (hardcoded `.env`, shared SMTP, no audit logs) is amateur hour.

If you want next:

* **Code scaffolding (Controller, Model, Blade/React UI)**
* **Multi-tenant email configs**
* **Failover SMTP provider**
* **Brevo API (non-SMTP) integration**

Say the word.
