# Anti-Spam Security Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add built-in honeypot, minimum submit-time, and transient-backed rate limiting to BS23 form submissions.

**Architecture:** Extend form settings with a `security` group, add a focused `AntiSpamGuard` service for submission checks, render signed anti-spam fields in `Renderer`, and call the guard from `SubmissionHandler` before validation/storage/email. Admin settings update the same saved settings API already used by notifications and styles.

**Tech Stack:** WordPress PHP 7.4 plugin APIs, WordPress transients, React settings panel, Jest, PHPUnit.

---

### Task 1: Settings Model

**Files:**
- Modify: `includes/Settings/FormSettings.php`
- Modify: `assets/admin/src/settings-api.js`
- Modify: `assets/admin/src/components/SettingsPanel.js`
- Modify: `assets/admin/src/__tests__/settings-panel.test.js`
- Modify: `tests/php/FormSettingsTest.php`

- [ ] Write failing tests for `security` defaults and sanitization.
- [ ] Run targeted settings tests and confirm they fail because `security` does not exist.
- [ ] Add server and admin defaults plus settings UI controls.
- [ ] Re-run targeted tests and commit.

### Task 2: Renderer Hidden Fields

**Files:**
- Modify: `includes/Frontend/Renderer.php`
- Modify: `tests/php/RendererTest.php`

- [ ] Write a failing renderer test asserting hidden honeypot, timestamp, and signature fields are present.
- [ ] Run the renderer test and confirm it fails.
- [ ] Render `bs23_hp`, `bs23_rendered_at`, and `bs23_render_token` fields.
- [ ] Re-run the renderer test and commit.

### Task 3: Submission Guard

**Files:**
- Create: `includes/Security/AntiSpamGuard.php`
- Modify: `includes/Submission/SubmissionHandler.php`
- Modify: `includes/Plugin.php`
- Modify: `bs23-form-builder.php`
- Create: `tests/unit/AntiSpamGuardTest.php`
- Modify: `tests/php/SubmissionHandlerTest.php`

- [ ] Write failing pure unit tests for honeypot, minimum-time, token, and rate-limit decisions.
- [ ] Implement `AntiSpamGuard`.
- [ ] Write failing submission handler test proving blocked submissions do not create entries.
- [ ] Wire the guard into `SubmissionHandler`.
- [ ] Re-run targeted tests and commit.

### Task 4: Full Verification And Push

**Files:**
- No production edits expected.

- [ ] Run PHP lint across plugin and tests.
- [ ] Run `./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/unit`.
- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `composer validate --strict`.
- [ ] Run `git diff --check`.
- [ ] Run `composer test` and record `WP_TESTS_DIR` status.
- [ ] Push `feature/anti-spam-security`.
