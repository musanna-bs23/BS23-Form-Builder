# Admin UI Overhaul Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a premium admin form-builder workspace with forms list, double-click field insertion, tabbed inspector, and polished gradient UI.

**Architecture:** Add a list endpoint to the existing form REST controller. Refactor the React admin shell into a product workspace with a form library sidebar, center canvas, and tabbed inspector while reusing existing builder/schema/settings components. Keep behavior testable through focused JS and PHP tests.

**Tech Stack:** WordPress REST API, React via `@wordpress/element`, `@wordpress/api-fetch`, Jest Testing Library, Sass/CSS.

---

### Task 1: Forms List API

**Files:**
- Modify: `includes/Rest/FormRestController.php`
- Modify: `tests/php/FormRestControllerTest.php`
- Modify: `assets/admin/src/app.js`

- [ ] Write failing PHP test for `GET /forms` returning saved form IDs, titles, and schema summaries.
- [ ] Implement `GET /forms` in `FormRestController`.
- [ ] Run PHP lint and note WordPress test-suite caveat if local `WP_TESTS_DIR` is unavailable.
- [ ] Commit.

### Task 2: Admin Data Loading

**Files:**
- Create: `assets/admin/src/forms-api.js`
- Modify: `assets/admin/src/app.js`
- Modify: `assets/admin/src/__tests__/app.test.js`

- [ ] Write failing JS tests for loading form list, selecting a form, and New Form reset.
- [ ] Implement `listForms()` and `loadForm()` usage in `App`.
- [ ] Run targeted JS tests and commit.

### Task 3: Double-Click Field Add

**Files:**
- Modify: `assets/admin/src/components/Palette.js`
- Modify: `assets/admin/src/app.js`
- Modify: `assets/admin/src/__tests__/app.test.js`

- [ ] Write failing JS test that double-clicking a palette field adds it to the canvas.
- [ ] Pass `onAddField` into `Palette` and wire `onDoubleClick`.
- [ ] Run targeted JS tests and commit.

### Task 4: Tabbed Inspector

**Files:**
- Create: `assets/admin/src/components/InspectorPanel.js`
- Modify: `assets/admin/src/app.js`
- Modify: `assets/admin/src/components/SettingsPanel.js`
- Modify: `assets/admin/src/__tests__/settings-panel.test.js`
- Modify: `assets/admin/src/__tests__/app.test.js`

- [ ] Write failing JS tests for inspector tabs: Fields, Field Settings, Form, Email, Style, Security.
- [ ] Split settings panel sections by mode while keeping save/test actions.
- [ ] Implement tab switching and place Field Settings separately.
- [ ] Run targeted JS tests and commit.

### Task 5: Premium Visual UI

**Files:**
- Modify: `assets/admin/src/styles.scss`
- Modify: relevant admin components if extra class names are needed.

- [ ] Update CSS for gradient shell, forms sidebar, center canvas, palette, inspector, tabs, selected states, animations, and responsive layout.
- [ ] Run JS tests and `npm run build`.
- [ ] Commit generated admin build assets.

### Task 6: Full Verification And Push

**Files:**
- No production edits expected.

- [ ] Run PHP lint.
- [ ] Run pure PHPUnit unit tests.
- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `composer validate --strict`.
- [ ] Run `git diff --check`.
- [ ] Run `composer test` and record `WP_TESTS_DIR` status.
- [ ] Push `feature/admin-ui-overhaul`.
