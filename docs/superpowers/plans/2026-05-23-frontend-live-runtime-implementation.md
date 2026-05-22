# Frontend Live Runtime Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add live frontend conditional logic and client-side validation to rendered forms.

**Architecture:** Renderer embeds schema metadata and stable field wrappers. A vanilla JS frontend bundle reads the schema, evaluates visibility and validation, disables hidden fields, and renders inline errors. PHP validation stays authoritative.

**Tech Stack:** WordPress PHP, vanilla JS bundled by `@wordpress/scripts`, Jest/jsdom, PHP syntax checks.

---

## Tasks

### Task 1: Runtime Core

- [ ] Write failing JS tests for condition evaluation and validation messages.
- [ ] Create `assets/frontend/src/runtime.js` with exported helpers and DOM initializer.
- [ ] Run targeted JS tests and commit.

### Task 2: Renderer Metadata

- [ ] Write failing PHP tests for form schema script and field wrapper data attributes.
- [ ] Update `Renderer` to embed schema JSON and field data attributes.
- [ ] Update frontend CSS for hidden state and inline errors.
- [ ] Run PHP syntax checks and commit.

### Task 3: Asset Enqueue And Build

- [ ] Update `package.json` build scripts for frontend bundle.
- [ ] Update `Shortcode` to enqueue frontend JS build asset.
- [ ] Run JS tests/build/PHP checks.
- [ ] Commit generated frontend assets.

### Task 4: Final Verification

- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `composer validate --strict`.
- [ ] Run PHP syntax checks.
- [ ] Run `git diff --check`.
- [ ] Run `composer test` and record local WP test suite status.
- [ ] Push branch.
