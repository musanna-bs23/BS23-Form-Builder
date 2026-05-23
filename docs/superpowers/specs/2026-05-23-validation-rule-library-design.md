# Validation Rule Library Design

## Goal

Expand field validation from a few visual controls into a Pro validation rule library that accepts familiar pipe-separated rules, while keeping the UI user-friendly and the server secure.

## Rule Entry

The builder keeps the existing friendly controls and adds **Custom validation rules** under Advanced Validation. Example:

```text
required|string|min:3|max:50|regex:/^[A-Z]+$/
```

Rules are stored as `field.settings.validation.rules`. Existing visual controls still work and are converted internally into the same validation path.

## Supported Rules

The first library supports rules that suit form input:

- Presence: required, nullable, present, filled, accepted, declined, prohibited.
- Conditional presence: required_if, required_unless, required_with, required_without, nullable_if.
- Types/formats: string, integer, numeric, float, double, boolean, email, url, active_url, array, date, date_format, json, file, image, uuid, ip, ipv4, ipv6, phone, credit_card, postal_code, latitude, longitude, timezone, url_safe, hex_color, slug, username.
- Text shape: alpha, alpha_num, alpha_dash, alpha_spaces, lowercase, uppercase, starts_with, ends_with.
- Size/range: min, max, size, between, digits, digits_between.
- Comparison: confirmed, same, different, in, not_in.
- Pattern: regex, not_regex.
- Files: mimes, mimetypes, extensions, max_file_size, min_file_size.
- Extension hooks: unique, exists, password_strength, custom_validation.

Database-backed and site-specific rules (`unique`, `exists`, `password_strength`, `custom_validation`) are exposed through WordPress filters. This keeps the plugin publishable and avoids hardcoding assumptions about user tables.

## Security

All validation runs server-side. Regex patterns are executed with warning suppression and invalid patterns fail safely with a validation error. File checks only use sanitized upload metadata. Unsupported or malformed rules are ignored during schema sanitization if they cannot be represented safely.

## UI

The UI is intentionally compact:

- Simple fields for common rules remain.
- A textarea named Custom validation rules accepts advanced pipe syntax.
- A short placeholder shows examples, not long instructional text.

## Testing

Tests cover representative rule families:

- presence and conditional presence,
- type/format validation,
- text and numeric size validation,
- comparison and list validation,
- regex/not_regex,
- file metadata validation,
- filter-backed unique/exists/custom validation hooks,
- schema sanitization and UI payload behavior.
