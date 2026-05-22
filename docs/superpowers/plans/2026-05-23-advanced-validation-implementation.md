# Advanced Validation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add field-level advanced validation rules for length, numeric range, regex, file size, and allowed extensions.

**Architecture:** Store validation rules under `field.settings.validation`, edit them in `FieldSettingsPanel`, sanitize nested validation settings in `SchemaValidator`, and enforce them in `SubmissionValidator`. Keep storage in existing post meta and avoid new tables.

**Tech Stack:** WordPress PHP, React via `@wordpress/scripts`, Jest, PHPUnit-compatible PHP tests.

---

## Tasks

### Task 1: Admin Validation UI

- [ ] Write failing Jest tests for field-specific validation controls and update payloads.
- [ ] Add helper functions to determine supported validation controls.
- [ ] Update `FieldSettingsPanel` with an Advanced Validation section.
- [ ] Add compact styles.
- [ ] Run JS tests and commit.

### Task 2: Schema Sanitization

- [ ] Write failing PHP tests for nested validation setting sanitization.
- [ ] Update `SchemaValidator` to preserve safe validation settings.
- [ ] Run PHP syntax checks and commit.

### Task 3: Server Validation

- [ ] Write failing PHP tests for length, numeric, regex, and file validation.
- [ ] Update `SubmissionValidator` to enforce advanced validation rules.
- [ ] Run PHP syntax checks and commit.

### Task 4: Build And Verify

- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build`.
- [ ] Run `composer validate --strict`.
- [ ] Run PHP syntax checks.
- [ ] Run `git diff --check`.
- [ ] Run `composer test` and record the local WordPress test-suite blocker if still present.
- [ ] Push `feature/advanced-validation`.
