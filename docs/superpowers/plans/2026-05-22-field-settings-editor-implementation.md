# Field Settings Editor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add field selection/editing, duplication/deletion, simple reordering, and frontend renderer support for edited field settings.

**Architecture:** JavaScript schema helpers update nested field data immutably. React builder components expose selected field state and a field settings panel. PHP renderer reads the existing schema settings object and outputs the configured frontend attributes/content.

**Tech Stack:** React, Jest, WordPress admin JS, PHP renderer/validator tests.

---

## File Structure

- `assets/admin/src/schema.js`: Add immutable field update/delete/duplicate/reorder helpers.
- `assets/admin/src/components/FieldSettingsPanel.js`: Field editing controls.
- `assets/admin/src/components/Canvas.js`: Selection/reorder/delete hooks.
- `assets/admin/src/components/ContainerField.js`: Selection/reorder for child fields.
- `assets/admin/src/components/FieldCard.js`: Selected state and action controls.
- `assets/admin/src/app.js`: Selected field state and panel rendering.
- `assets/admin/src/styles.scss`: Selected field/settings panel styles.
- `assets/admin/src/__tests__/field-settings.test.js`: JS behavior tests.
- `includes/Frontend/Renderer.php`: Render settings on frontend.
- `tests/php/RendererSettingsTest.php`: PHP renderer tests.

---

### Task 1: Schema Editing Helpers

- [ ] Add failing JS tests for select/update/delete/duplicate/reorder root fields and editing nested container children.
- [ ] Implement helpers in `schema.js`.
- [ ] Run targeted JS tests.
- [ ] Commit `feat: add field schema editing helpers`.

### Task 2: Field Settings Panel UI

- [ ] Add failing JS tests for rendering field settings and emitting changes.
- [ ] Implement `FieldSettingsPanel`.
- [ ] Wire selected field state in `app.js`.
- [ ] Update `Canvas`, `ContainerField`, and `FieldCard` to support selection and actions.
- [ ] Run JS tests and build.
- [ ] Commit `feat: add field settings panel`.

### Task 3: Frontend Renderer Settings Support

- [ ] Add failing PHP tests for placeholder/default/help/class/choices/custom HTML/section description.
- [ ] Update `Renderer.php`.
- [ ] Run PHP syntax checks and PHP tests when WordPress test suite is available.
- [ ] Commit `feat: render field settings on frontend`.

### Task 4: Final Verification

- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run Composer validation and PHP syntax checks.
- [ ] Run `composer test` when WordPress test suite is available.
- [ ] Push `feature/field-settings-editor`.
