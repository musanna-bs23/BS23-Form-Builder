# Frontend Render Submit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add frontend shortcode rendering, server-side submission validation, and one-table entry storage for saved BS23 forms.

**Architecture:** Form definitions stay in the existing `bs23_form` CPT and `_bs23_form_schema` meta. Runtime rendering is split from submission handling: renderer returns escaped markup, validator returns structured errors/data, repository writes to the entries table, and the handler coordinates POST requests.

**Tech Stack:** WordPress PHP plugin, shortcode API, activation hook/dbDelta, `$wpdb`, WordPress PHPUnit tests, existing schema/post type services.

---

## File Structure

- `includes/Install/Installer.php`: Creates the entries table on activation.
- `includes/Frontend/Shortcode.php`: Registers `[bs23_form]` and renders form markup for a form ID.
- `includes/Frontend/Renderer.php`: Converts schema arrays into frontend HTML.
- `includes/Submission/SubmissionHandler.php`: Detects POST submissions, verifies nonce, validates, stores, and exposes request messages.
- `includes/Submission/EntryRepository.php`: Inserts entry rows into the custom table.
- `includes/Validation/SubmissionValidator.php`: Validates and sanitizes submitted values against schema fields.
- `bs23-form-builder.php`: Requires new class files and registers activation hook.
- `includes/Plugin.php`: Registers shortcode/submission services.
- `assets/frontend/forms.css`: Basic frontend form/container styles.
- `tests/php/InstallerTest.php`: Entries table creation tests.
- `tests/php/RendererTest.php`: Field/container rendering tests.
- `tests/php/SubmissionValidatorTest.php`: Required/email/url/sanitization tests.
- `tests/php/SubmissionHandlerTest.php`: Nonce, valid insert, invalid submission tests.

---

### Task 1: Entries Table Installer

**Files:**
- Create: `includes/Install/Installer.php`
- Create: `tests/php/InstallerTest.php`
- Modify: `bs23-form-builder.php`

- [ ] Write a failing test that calls `Installer::activate()` and asserts the entries table exists with columns `id`, `form_id`, `entry_data`, `user_id`, `user_ip`, `user_agent`, `created_at`.
- [ ] Run `composer test -- --filter InstallerTest` and confirm failure because `Installer` does not exist.
- [ ] Implement `Installer::activate()` using `dbDelta` and `$wpdb->prefix . 'bs23_form_entries'`.
- [ ] Register activation hook in `bs23-form-builder.php`.
- [ ] Run `composer test -- --filter InstallerTest` and confirm pass.
- [ ] Commit: `feat: add entries table installer`.

### Task 2: Submission Validator

**Files:**
- Create: `includes/Validation/SubmissionValidator.php`
- Create: `tests/php/SubmissionValidatorTest.php`
- Modify: `bs23-form-builder.php`

- [ ] Write failing tests for required empty text, invalid email, invalid URL, valid text/email/url, checkbox array sanitization, and ignored unsupported fields.
- [ ] Run `composer test -- --filter SubmissionValidatorTest` and confirm failure because validator does not exist.
- [ ] Implement `SubmissionValidator::validate(array $schema, array $input): array` returning `['valid' => bool, 'errors' => array, 'data' => array]`.
- [ ] Flatten container children recursively.
- [ ] Validate only supported runtime input fields.
- [ ] Sanitize text-like values with `sanitize_text_field`, textarea with `sanitize_textarea_field`, URL with `esc_url_raw`, checkbox/multiple values as arrays of sanitized strings.
- [ ] Run targeted and full PHP tests.
- [ ] Commit: `feat: validate frontend submissions`.

### Task 3: Frontend Renderer

**Files:**
- Create: `includes/Frontend/Renderer.php`
- Create: `tests/php/RendererTest.php`
- Create: `assets/frontend/forms.css`
- Modify: `bs23-form-builder.php`

- [ ] Write failing tests for rendering text/email fields, required attributes, two-column container class, section break, hidden field, and default submit button.
- [ ] Run `composer test -- --filter RendererTest` and confirm failure because renderer does not exist.
- [ ] Implement `Renderer::render(int $formId, array $schema, array $state = []): string`.
- [ ] Render a `<form method="post" class="bs23-form">` with nonce, `bs23_form_id`, and action marker.
- [ ] Escape labels, names, values, and attributes.
- [ ] Render supported fields and skip unsupported runtime fields.
- [ ] Render containers recursively with `bs23-form-row bs23-form-row--N`.
- [ ] Enqueue/register `assets/frontend/forms.css` from shortcode layer later; renderer only returns markup.
- [ ] Run targeted and full PHP tests.
- [ ] Commit: `feat: render frontend forms`.

### Task 4: Shortcode Registration

**Files:**
- Create: `includes/Frontend/Shortcode.php`
- Create: `tests/php/ShortcodeTest.php`
- Modify: `includes/Plugin.php`
- Modify: `bs23-form-builder.php`

- [ ] Write failing tests that `[bs23_form id="bad"]` returns empty markup and a saved form renders expected fields.
- [ ] Run `composer test -- --filter ShortcodeTest` and confirm failure because shortcode is not registered.
- [ ] Implement `Shortcode::register()` with `add_shortcode('bs23_form', ...)`.
- [ ] Load form schema from `_bs23_form_schema` only when post type is `bs23_form`.
- [ ] Enqueue frontend stylesheet only when shortcode renders a valid form.
- [ ] Register shortcode service in `Plugin`.
- [ ] Run targeted and full PHP tests.
- [ ] Commit: `feat: add frontend form shortcode`.

### Task 5: Entry Repository And Submission Handler

**Files:**
- Create: `includes/Submission/EntryRepository.php`
- Create: `includes/Submission/SubmissionHandler.php`
- Create: `tests/php/SubmissionHandlerTest.php`
- Modify: `includes/Plugin.php`
- Modify: `bs23-form-builder.php`

- [ ] Write failing tests for missing nonce rejection, required validation error, invalid email error, valid submission inserting one row, and stored value sanitization.
- [ ] Run `composer test -- --filter SubmissionHandlerTest` and confirm failure because handler/repository do not exist.
- [ ] Implement `EntryRepository::insert(int $formId, array $data): int`.
- [ ] Implement `SubmissionHandler::register()` on `init`.
- [ ] Detect POST marker `bs23_form_submit`.
- [ ] Verify nonce action includes form ID.
- [ ] Load `bs23_form` schema and validate with `SubmissionValidator`.
- [ ] Store valid entries with user ID, IP, user agent, and current time.
- [ ] Store per-request success/errors/values so shortcode rendering can display feedback.
- [ ] Register services in `Plugin`.
- [ ] Run targeted and full PHP tests.
- [ ] Commit: `feat: store frontend form submissions`.

### Task 6: Final Verification

**Files:**
- Modify only files required by verification fixes.

- [ ] Run `composer test`.
- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `git status --short`.
- [ ] Push branch `feature/frontend-render-submit`.

