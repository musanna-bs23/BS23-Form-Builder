# Conditional Logic Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add secure field-level show/hide conditional logic to the builder, frontend renderer, and submission validator.

**Architecture:** Store conditional logic in `field.settings.conditionalLogic`, edit it through the existing field settings panel, and evaluate it in shared PHP logic before rendering or validation. Keep the first version server-correct and schema-compatible; live client-side toggling can layer on later.

**Tech Stack:** WordPress PHP, React via `@wordpress/scripts`, existing schema helpers, PHPUnit test files, Jest unit tests.

---

## File Structure

- Create `assets/admin/src/conditional-logic.js`: admin helpers for eligible field options and default logic payloads.
- Modify `assets/admin/src/components/FieldSettingsPanel.js`: add conditional logic controls.
- Modify `assets/admin/src/app.js`: pass schema fields into the settings panel.
- Modify `includes/Builder/SchemaValidator.php`: sanitize nested conditional logic safely.
- Create `includes/ConditionalLogic/Evaluator.php`: server-side condition evaluator.
- Modify `includes/Frontend/Renderer.php`: skip hidden fields.
- Modify `includes/Validation/SubmissionValidator.php`: skip hidden fields before validation/data storage.
- Add/extend tests in `assets/admin/src/__tests__` and `tests/php`.

## Tasks

### Task 1: Admin Conditional Logic Helpers

- [ ] Write failing Jest tests for eligible condition source fields and default conditional logic payloads.
- [ ] Add `assets/admin/src/conditional-logic.js` with `defaultConditionalLogic`, `conditionalOperators`, `conditionNeedsValue`, and `conditionSourceFields`.
- [ ] Run `npm run test:js -- --runInBand` and confirm the new tests pass.
- [ ] Commit helper changes.

### Task 2: Field Settings UI

- [ ] Write failing Jest tests for enabling conditional logic, changing action/match/operator/value, and adding/removing rule rows.
- [ ] Update `FieldSettingsPanel` to render the conditional logic controls.
- [ ] Pass root schema fields from `app.js` into the panel.
- [ ] Add compact styles in `assets/admin/src/styles.scss`.
- [ ] Run `npm run test:js -- --runInBand` and confirm the panel tests pass.
- [ ] Commit UI changes.

### Task 3: PHP Evaluator And Sanitizer

- [ ] Write failing PHP tests for evaluator outcomes and schema sanitization of nested conditional logic.
- [ ] Create `includes/ConditionalLogic/Evaluator.php`.
- [ ] Update `includes/Builder/SchemaValidator.php` to preserve valid conditional logic and strip invalid nested values.
- [ ] Run PHP syntax checks.
- [ ] Commit evaluator and sanitizer changes.

### Task 4: Renderer And Validator Integration

- [ ] Write failing PHP tests for hidden field rendering and hidden required validation skip.
- [ ] Update `Renderer` to skip hidden fields.
- [ ] Update `SubmissionValidator` to skip hidden fields and omit hidden values from entry data.
- [ ] Run PHP syntax checks and available JS tests.
- [ ] Commit integration changes.

### Task 5: Build And Final Verification

- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `composer validate --strict`.
- [ ] Run `find includes tests/php -name '*.php' -print0 | xargs -0 -n1 php -l && php -l bs23-form-builder.php`.
- [ ] Run `git diff --check`.
- [ ] Run `composer test` and record the local WordPress test-suite blocker if `WP_TESTS_DIR` is still unset.
- [ ] Commit generated build assets if changed.
- [ ] Push `feature/conditional-logic`.
