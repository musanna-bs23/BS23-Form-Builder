# Validation Rule Library Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a Laravel-style custom validation rule parser and server-side evaluator for form fields.

**Architecture:** Store pipe-separated rules in `field.settings.validation.rules`, sanitize them in `SchemaValidator`, and enforce them in a focused `RuleValidator` used by `SubmissionValidator`. Keep existing visual validation controls and route them through the same validation behavior.

**Tech Stack:** WordPress PHP, React field settings panel, Jest, PHPUnit-compatible PHP tests.

---

## Tasks

### Task 1: Builder UI

- [ ] Write failing Jest tests for the Custom validation rules textarea.
- [ ] Update `FieldSettingsPanel` to save `validation.rules`.
- [ ] Run JS tests and commit.

### Task 2: Rule Sanitization

- [ ] Write failing PHP tests for validation rule string sanitization.
- [ ] Update `SchemaValidator` to preserve safe `rules`.
- [ ] Run syntax checks and commit.

### Task 3: Rule Evaluator

- [ ] Write failing PHP tests for representative rule families.
- [ ] Add `includes/Validation/RuleValidator.php`.
- [ ] Integrate it into `SubmissionValidator`.
- [ ] Run syntax checks and commit.

### Task 4: Final Verification

- [ ] Run JS tests.
- [ ] Build assets.
- [ ] Run composer validation.
- [ ] Run PHP syntax checks.
- [ ] Run whitespace checks.
- [ ] Record `composer test` environment result.
- [ ] Push branch.
