# New Form Builder Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rework the New Form builder into a cleaner form-first editing experience with a canvas `+` block inserter, field action toolbar, settings/integrations tab, and richer field customization surface.

**Architecture:** Keep the existing React admin app and schema APIs. Move add-field discovery from the always-visible side palette into a reusable block inserter component opened from canvas `+` buttons, while keeping drag/drop and double-click add behavior available through the inserter. Keep persistence unchanged through the existing REST save/load endpoints.

**Tech Stack:** WordPress admin React via `@wordpress/element`, `@wordpress/api-fetch`, SCSS built with `wp-scripts`, Jest/React Testing Library.

---

### Task 1: Block Inserter Popup

**Files:**
- Create: `assets/admin/src/components/BlockInserter.js`
- Modify: `assets/admin/src/components/Canvas.js`
- Modify: `assets/admin/src/__tests__/app.test.js`

- [ ] **Step 1: Write failing tests**

Add tests that click the canvas plus button, search the block inserter, switch categories, and add an Email field.

- [ ] **Step 2: Run test to verify it fails**

Run: `npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
Expected: FAIL because no canvas plus button or block inserter exists.

- [ ] **Step 3: Implement minimal component**

Create `BlockInserter` with search, tabs (`Recent`, `General`, `Advanced`, `Container`), and block buttons using `FIELD_GROUPS`.

- [ ] **Step 4: Wire into canvas**

Render a centered `+` button in `Canvas`; clicking opens the popup; clicking a block calls `onDropRoot(type)` and closes it.

- [ ] **Step 5: Verify**

Run: `npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
Expected: PASS.

### Task 2: Canvas Field Actions

**Files:**
- Modify: `assets/admin/src/components/Canvas.js`
- Modify: `assets/admin/src/components/FieldCard.js`
- Modify: `assets/admin/src/components/ContainerField.js`
- Modify: `assets/admin/src/app.js`
- Modify: `assets/admin/src/__tests__/app.test.js`

- [ ] **Step 1: Write failing tests**

Add tests that select a field and use the floating action toolbar to duplicate and delete it.

- [ ] **Step 2: Run test to verify it fails**

Run: `npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
Expected: FAIL because field action buttons are not on the canvas.

- [ ] **Step 3: Pass action handlers through canvas**

Pass `onDelete`, `onDuplicate`, and `onMove` from `App` to `Canvas`, then to field cards.

- [ ] **Step 4: Render toolbar**

Render compact field toolbar buttons for move, edit/select, duplicate, and delete when a field is selected or hovered.

- [ ] **Step 5: Verify**

Run: `npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
Expected: PASS.

### Task 3: Builder Tabs and Settings Integration Label

**Files:**
- Modify: `assets/admin/src/components/InspectorPanel.js`
- Modify: `assets/admin/src/components/SettingsPanel.js`
- Modify: `assets/admin/src/__tests__/app.test.js`

- [ ] **Step 1: Write failing tests**

Add tests for top-level builder tabs: `Edit Fields`, `Settings & Integrations`, and `Entries`, with settings exposing email notification, email body, and spam/security controls.

- [ ] **Step 2: Run test to verify it fails**

Run: `npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
Expected: FAIL because existing tabs are low-level inspector tabs.

- [ ] **Step 3: Implement tab labels and settings grouping**

Update visible tab labels and map `Settings & Integrations` to the existing form/email/security settings panels.

- [ ] **Step 4: Verify**

Run: `npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
Expected: PASS.

### Task 4: Styling and Build

**Files:**
- Modify: `assets/admin/src/styles.scss`
- Build outputs: `assets/admin/build/*`

- [ ] **Step 1: Style builder shell**

Make the builder visually form-first: white canvas, selected field highlight, floating action toolbar, centered block inserter, clean tabs, and responsive layout.

- [ ] **Step 2: Build assets**

Run: `npm run build`
Expected: webpack builds admin, entries, and frontend assets successfully.

- [ ] **Step 3: Final verification**

Run:
`npm run test:js -- --runInBand assets/admin/src/__tests__/app.test.js`
`php -l includes/Admin/Menu.php && php -l includes/Rest/FormRestController.php && php -l includes/Plugin.php`
`composer validate --no-check-publish`

Expected: all commands exit 0.

- [ ] **Step 4: Commit**

Commit message: `feat: redesign new form builder workflow`
