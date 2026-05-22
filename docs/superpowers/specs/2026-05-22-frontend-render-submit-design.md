# Frontend Render And Submit Design

Date: 2026-05-22

## Goal

Add the first public-facing runtime for BS23 Form Builder: a shortcode that renders saved forms on the frontend, validates submissions, and stores entries. This milestone turns the admin builder into a usable form collection flow while keeping the database footprint small.

## Scope

This milestone includes:

- Shortcode: `[bs23_form id="123"]`.
- Frontend rendering from the saved builder schema.
- Container rendering for one, two, three, and four column rows.
- Runtime support for basic fields:
  - Name Fields
  - Email
  - Simple Text
  - Text Area
  - Numeric Field
  - Dropdown
  - Radio Field
  - Checkbox
  - Multiple Choice
  - Website URL
  - Phone/Mobile
  - Hidden Field
  - Section Break
  - Custom HTML
  - Custom Submit Button
- Default submit button if no custom submit field exists.
- Frontend nonce protection.
- Server-side required validation.
- Server-side email and URL validation.
- One custom entries table for submissions.
- Entry persistence with submitted field values as JSON-compatible data.
- Frontend success and error messages.

This milestone excludes:

- Admin entry list.
- Reports.
- Email notifications.
- File/image upload processing.
- Multi-step runtime behavior.
- Conditional logic.
- Payment fields.
- Elementor widget/style controls.
- Spam protection.

Unsupported runtime fields from the builder schema are skipped safely, except layout containers, section breaks, custom HTML, hidden fields, and submit fields listed above.

## Storage

Form definitions continue to use the existing `bs23_form` custom post type and `_bs23_form_schema` post meta.

Submissions use one table:

`{$wpdb->prefix}bs23_form_entries`

Columns:

- `id` bigint unsigned auto increment primary key.
- `form_id` bigint unsigned not null.
- `entry_data` longtext not null. Stores JSON-encoded sanitized field values.
- `user_id` bigint unsigned null/default 0.
- `user_ip` varchar(100) nullable.
- `user_agent` text nullable.
- `created_at` datetime not null.

No separate entry-meta table is added in this milestone. Reports in a later milestone will initially query by form and created date. Field-level search is outside the current storage design.

## Architecture

New PHP units:

- `Install/Installer`: creates or updates the entries table with `dbDelta`.
- `Frontend/Shortcode`: registers `[bs23_form]`, loads schema, and returns the rendered form markup.
- `Frontend/Renderer`: converts sanitized schema arrays into frontend HTML.
- `Submission/SubmissionHandler`: handles POST submissions, verifies nonce, validates input, stores entries, and provides messages.
- `Submission/EntryRepository`: inserts sanitized entries into the entries table.
- `Validation/SubmissionValidator`: validates required fields and email/URL formats.

Existing units touched:

- Main plugin bootstrap: require new class files and activation hook.
- `Plugin`: register shortcode and submission handling services.

## Rendering

The shortcode renders a `<form>` with:

- Hidden `bs23_form_id`.
- Hidden action marker, e.g. `bs23_form_submit`.
- WordPress nonce field.
- Field wrappers with stable classes.
- Labels escaped with `esc_html`.
- Input names based on schema `name`.
- Containers as CSS grid rows using a column-count class.
- Section breaks as non-input content.
- Custom HTML using a conservative allowed HTML policy.

Supported field rendering:

- Text-like: text, name, phone, hidden.
- Email: `<input type="email">`.
- Number: `<input type="number">`.
- URL: `<input type="url">`.
- Textarea: `<textarea>`.
- Dropdown: `<select>` using configured options when present; otherwise a placeholder option.
- Radio, checkbox, multiple choice: render choices when present; otherwise render a disabled placeholder choice.
- Section break: render title/label as visual divider.
- Custom HTML: render allowed HTML from settings.
- Submit: render submit button label from field label/settings.

## Submission Flow

When a frontend form is submitted:

1. `SubmissionHandler` detects `POST` requests with the form action marker.
2. It verifies the nonce for the submitted form ID.
3. It loads the form schema from `bs23_form`.
4. It validates supported input fields:
   - Required fields must be non-empty.
   - Email fields must pass `is_email`.
   - URL fields must pass `esc_url_raw`/URL validation.
5. If validation fails, errors are stored for the current request and the form re-renders with error messages and submitted values.
6. If validation passes, values are sanitized and inserted into the entries table.
7. The form re-renders with a success message and no submitted values.

## Security

- Shortcode attributes are sanitized.
- Form ID must reference an existing `bs23_form`.
- Nonce is required for submissions.
- All submitted values are sanitized server-side.
- All frontend output is escaped.
- Custom HTML is restricted through `wp_kses_post`.
- Entries table writes use `$wpdb->insert`.
- The table name is built from `$wpdb->prefix`, not user input.

## Testing

Development follows TDD.

Required PHP tests:

- Installer creates entries table name/schema.
- Shortcode returns empty string or message for missing/invalid form ID.
- Shortcode renders a saved form with text/email fields.
- Renderer renders containers with expected column classes.
- Submission without nonce is rejected.
- Submission with required empty field returns validation error.
- Invalid email returns validation error.
- Valid submission inserts one row into entries table.
- Stored entry data is sanitized.

Manual verification:

- Create form in admin builder.
- Add shortcode to a page/post.
- View frontend form.
- Submit invalid values and see errors.
- Submit valid values and see success message.
- Confirm an entry row exists in the custom table.
