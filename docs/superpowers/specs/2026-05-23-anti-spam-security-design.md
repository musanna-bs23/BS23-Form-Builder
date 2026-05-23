# Anti-Spam Security Design

## Goal

Add a publish-safe anti-spam layer that reduces automated submissions without external services, new database tables, or extra setup for site owners.

## Approach

The frontend renderer will add hidden anti-spam metadata to every form: a honeypot text input and a signed render timestamp. The submission handler will validate those values before schema validation, upload storage, entry creation, and email notifications. Form settings will control whether anti-spam is enabled, the minimum submit time, rate limit count, and rate limit window.

## Protections

- Honeypot: bots that fill the hidden field are rejected.
- Minimum submit time: submissions sent too quickly after render are rejected.
- Rate limiting: submissions from the same IP and form are limited through WordPress transients.
- Generic error response: anti-spam failures return a neutral form-level error and do not save entries or send emails.

## Settings

The default settings keep protection enabled:

- `security.enabled`: `true`
- `security.honeypot`: `true`
- `security.minimum_time`: `3`
- `security.rate_limit_count`: `5`
- `security.rate_limit_window`: `300`

Admins can adjust these in the existing Form Settings panel. Values are sanitized server-side, and unsafe values fall back to defaults.

## Boundaries

This pass does not add reCAPTCHA, Turnstile, Akismet, or IP deny lists. Those require third-party credentials, privacy documentation, and broader UX. This feature focuses on strong built-in defaults that work immediately after activation.

## Testing

PHP tests will cover settings sanitization, renderer hidden fields, honeypot rejection, minimum-time rejection, and rate-limit rejection. Existing JS settings panel tests will cover the admin controls. Full verification will include PHP lint, pure Elementor unit tests, JS tests, build, composer validation, whitespace checks, and the known `composer test` command with the local `WP_TESTS_DIR` caveat.
