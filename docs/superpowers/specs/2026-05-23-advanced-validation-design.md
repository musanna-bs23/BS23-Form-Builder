# Advanced Validation Design

## Product Goal

Add Pro-grade validation controls so each field can enforce length, numeric range, regex pattern, upload file size, and allowed file extensions. The feature must be secure server-side, easy to configure in the builder, and stored in the existing field schema without new database tables.

## Validation Rules

Rules are stored in `field.settings.validation`:

```json
{
  "validation": {
    "minLength": "3",
    "maxLength": "120",
    "minValue": "1",
    "maxValue": "99",
    "pattern": "^[A-Z0-9]+$",
    "patternMessage": "Use uppercase letters and numbers only.",
    "maxFileSizeMb": "5",
    "allowedExtensions": "jpg,png,pdf"
  }
}
```

Text-like fields support min/max characters and regex. Number/range fields support min/max value. File/image upload fields support max size and allowed extensions. Unsupported rules are harmless if present because the server applies only rules relevant to the current field type.

## Builder UI

The existing field settings panel gets an **Advanced Validation** section below common controls and before conditional logic. It shows only relevant controls for the selected field type:

- Text, textarea, name, email, url, phone, password, rich text: min characters, max characters, regex pattern, custom regex message.
- Number and range: minimum value, maximum value.
- File/image upload: max file size in MB, allowed extensions.

The controls are plain, compact, and scannable. They save through the current form save flow and require no extra REST endpoints.

## Server Security

Validation is enforced in `SubmissionValidator` after sanitization and before data is accepted. Regex is wrapped safely with delimiter escaping, invalid regex patterns return a validation error instead of causing warnings or fatal errors. File upload validation checks `$_FILES`-style payloads when available and also handles array payloads used by tests.

The schema sanitizer preserves nested validation settings with conservative sanitization:

- numeric rules keep numeric strings only,
- regex pattern and custom message are sanitized as text,
- allowed extensions are normalized to lowercase comma-separated extension tokens,
- max file size accepts positive numeric MB values.

## Testing

JavaScript tests cover field-specific visibility of advanced validation controls and update payloads.

PHP tests cover:

- min/max character validation,
- min/max numeric validation,
- regex validation and custom messages,
- file extension and file size validation,
- schema sanitization of nested validation settings.

## Out Of Scope

This milestone does not add client-side live validation. Server-side validation is the required security layer. Client-side validation can be added later using the same schema.
