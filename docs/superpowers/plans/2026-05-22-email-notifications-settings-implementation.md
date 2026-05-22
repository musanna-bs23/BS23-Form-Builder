# Email Notifications Settings Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add per-form notification and confirmation settings, email delivery after valid submissions, and a builder settings UI.

**Architecture:** PHP owns settings defaults/sanitization, template rendering, mail sending, and REST endpoints. The existing submission handler loads settings after entry insertion and sends notifications. The existing builder React app gains a settings panel that loads/saves settings and triggers a test email.

**Tech Stack:** WordPress PHP post meta, REST API, `wp_mail`, React, `@wordpress/api-fetch`, Jest, PHPUnit.

---

## File Structure

- `includes/Settings/FormSettings.php`: Defaults, sanitization, load/save settings.
- `includes/Notifications/TemplateRenderer.php`: Expands notification template tags.
- `includes/Notifications/Mailer.php`: Sends notification emails.
- `includes/Rest/FormSettingsRestController.php`: REST load/save/test email.
- `includes/Submission/SubmissionHandler.php`: Sends notifications and uses confirmation settings.
- `assets/admin/src/settings-api.js`: Settings REST helpers.
- `assets/admin/src/components/SettingsPanel.js`: Notification/confirmation UI.
- `assets/admin/src/app.js`: Loads/saves settings and renders panel.
- `assets/admin/src/styles.scss`: Settings panel styling.
- `tests/php/FormSettingsTest.php`
- `tests/php/TemplateRendererTest.php`
- `tests/php/MailerTest.php`
- `tests/php/FormSettingsRestControllerTest.php`
- `assets/admin/src/__tests__/settings-panel.test.js`

---

### Task 1: Settings Storage

- [ ] Add failing tests for defaults, save/load, sanitization, invalid email fallback, redirect URL sanitization.
- [ ] Implement `FormSettings`.
- [ ] Require class in bootstrap.
- [ ] Run targeted tests.
- [ ] Commit `feat: add form settings storage`.

### Task 2: Template Renderer And Mailer

- [ ] Add failing tests for template tags and `wp_mail` dispatch.
- [ ] Implement `TemplateRenderer` and `Mailer`.
- [ ] Run targeted tests.
- [ ] Commit `feat: add notification mailer`.

### Task 3: Settings REST API

- [ ] Add failing tests for unauthorized rejection, GET defaults, PUT sanitized save, POST test email.
- [ ] Implement `FormSettingsRestController`.
- [ ] Register controller in `Plugin` and bootstrap.
- [ ] Run targeted tests.
- [ ] Commit `feat: add form settings REST API`.

### Task 4: Submission Integration

- [ ] Add failing tests proving valid submission sends notification and custom confirmation message is used.
- [ ] Update `SubmissionHandler` to load settings, send notification after entry insert, and apply confirmation/redirect behavior.
- [ ] Run targeted tests.
- [ ] Commit `feat: send submission notifications`.

### Task 5: Builder Settings UI

- [ ] Add failing JS tests for settings panel controls, save payload, and test email action.
- [ ] Add `settings-api.js`.
- [ ] Add `SettingsPanel`.
- [ ] Update `app.js` to load/save settings for current form ID and show panel.
- [ ] Style settings panel.
- [ ] Run JS tests and build.
- [ ] Commit `feat: add builder settings panel`.

### Task 6: Final Verification

- [ ] Run PHP syntax checks.
- [ ] Run `composer test` when WordPress test suite is available.
- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `git status --short`.
- [ ] Push `feature/email-notifications-settings`.
