# Frontend Style System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add form style settings and a polished frontend theme powered by CSS variables.

**Architecture:** Extend existing form settings with a `style` group. Renderer outputs sanitized CSS custom properties inline on the form. Frontend CSS consumes those variables. Admin SettingsPanel edits style tokens.

**Tech Stack:** WordPress PHP settings/renderer, React admin settings panel, Jest, PHP syntax checks.

---

## Tasks

### Task 1: Settings Model

- [ ] Write failing PHP tests for style defaults and sanitization.
- [ ] Update `FormSettings` defaults/sanitize.
- [ ] Update `assets/admin/src/settings-api.js` defaults.
- [ ] Run PHP syntax checks and commit.

### Task 2: Admin Style Controls

- [ ] Write failing Jest tests for style settings controls.
- [ ] Update `SettingsPanel` with a Style section.
- [ ] Add admin CSS if needed.
- [ ] Run JS tests and commit.

### Task 3: Frontend Renderer And Theme

- [ ] Write failing PHP renderer test for CSS variable output.
- [ ] Update `Renderer` to accept settings and output inline variables.
- [ ] Update `Shortcode` to pass saved settings to renderer.
- [ ] Upgrade `assets/frontend/forms.css`.
- [ ] Run checks and commit.

### Task 4: Final Verification

- [ ] Run full JS tests.
- [ ] Run full build.
- [ ] Run composer validation.
- [ ] Run PHP syntax checks.
- [ ] Run whitespace check.
- [ ] Run `composer test` and record local WordPress test-suite status.
- [ ] Push branch.
