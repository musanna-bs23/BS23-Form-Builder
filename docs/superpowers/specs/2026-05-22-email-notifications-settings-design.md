# Email Notifications And Form Settings Design

Date: 2026-05-22

## Goal

Add per-form settings for submission confirmations and email notifications. This milestone lets site owners receive polished submission emails, customize the submit success experience, and test notification delivery from the admin builder.

## Scope

This milestone includes:

- Form settings persisted in post meta.
- REST endpoints to load/save settings for one form.
- REST endpoint to send a test email.
- Builder-side settings panel.
- Notification settings:
  - Enable/disable admin notification.
  - Send-to email.
  - Email subject.
  - Email message template.
  - Reply-to field key.
- Confirmation settings:
  - Success message.
  - Optional redirect URL.
- Email sending after a valid frontend submission.
- Template tags:
  - `{form_title}`
  - `{entry_id}`
  - `{all_fields}`
  - `{field:email}`
  - `{field:any_field_key}`
- Server-side validation/sanitization for all settings.
- Tests for defaults, sanitization, template rendering, and mail dispatch.

This milestone excludes:

- Multiple notification rules.
- Conditional notification routing.
- HTML email builder.
- SMTP setup.
- Per-field settings UI.

## Storage

Settings are stored in post meta on the existing `bs23_form` post:

`_bs23_form_settings`

Default shape:

```json
{
  "notification": {
    "enabled": true,
    "to": "{admin_email}",
    "subject": "New submission from {form_title}",
    "message": "{all_fields}",
    "reply_to": ""
  },
  "confirmation": {
    "message": "Thanks, your submission has been received.",
    "redirect_url": ""
  }
}
```

No database table is added.

## Architecture

New PHP units:

- `Settings/FormSettings`: defaults, sanitization, get/save.
- `Notifications/TemplateRenderer`: renders notification template tags.
- `Notifications/Mailer`: sends notification emails through `wp_mail`.
- `Rest/FormSettingsRestController`: load/save/test email endpoints.

Existing units touched:

- `SubmissionHandler`: after valid entry insert, send notification and use configured confirmation message/redirect.
- `Plugin`: registers settings REST controller.
- Bootstrap: requires new classes.
- Admin builder React app: adds settings panel and API calls.

## REST API

Namespace: `bs23-form-builder/v1`

- `GET /forms/(?P<id>\d+)/settings`
  - Requires `manage_options`.
  - Returns merged defaults and saved settings.
- `PUT /forms/(?P<id>\d+)/settings`
  - Requires `manage_options`.
  - Sanitizes and saves settings.
- `POST /forms/(?P<id>\d+)/settings/test-email`
  - Requires `manage_options`.
  - Sends a test notification using current provided settings or saved settings.

## Builder UI

The builder gains a right-side or top-level settings panel section:

- Notification toggle.
- Send-to email input.
- Subject input.
- Message textarea.
- Reply-to field input.
- Confirmation message textarea.
- Redirect URL input.
- Save settings button.
- Send test email button.
- Status feedback for saving/testing.

UI should remain compact, premium, and consistent with the existing builder. This milestone can use a simple panel layout; future field-level settings can reuse the same pattern.

## Submission Flow

After a valid submission:

1. Entry is inserted.
2. Form settings are loaded.
3. If notifications are enabled, `Mailer` sends email.
4. Confirmation state uses configured success message.
5. If redirect URL is set, handler redirects after successful submission.

If email sending fails, the entry remains saved. The user still receives the confirmation message; mail failure is not shown publicly.

## Security

- Settings REST endpoints require `manage_options`.
- REST nonce is required through `apiFetch`.
- Email addresses validated with `sanitize_email` and `is_email` unless `{admin_email}` token is used.
- Redirect URL sanitized with `esc_url_raw`.
- Message template sanitized with `sanitize_textarea_field`.
- Rendered email content escapes/sanitizes submitted data.
- No remote services or tracking.

## Testing

Development follows TDD.

Required PHP tests:

- Settings defaults are returned when no meta exists.
- Settings sanitization strips unsafe values.
- Invalid send-to email falls back to admin email.
- Template renderer expands `{form_title}`, `{entry_id}`, `{all_fields}`, and field tags.
- Mailer calls `wp_mail` with rendered subject/message.
- Submission handler sends notification after valid submission.
- Confirmation message comes from settings.
- Settings REST rejects unauthorized users.
- Settings REST saves sanitized settings.

Required JavaScript tests:

- Settings panel renders notification and confirmation controls.
- Settings panel emits save payload.
- Test email button calls API and displays status.

Manual verification:

- Open builder.
- Configure notification recipient and confirmation message.
- Send test email.
- Submit form on frontend.
- Confirm entry is stored and email is sent.
- Confirm frontend success message uses configured text.
