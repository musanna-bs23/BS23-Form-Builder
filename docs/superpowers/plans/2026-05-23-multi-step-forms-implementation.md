# Multi-Step Forms Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add frontend multi-step form support using the existing `form_step` field type.

**Architecture:** Render `form_step` as step markers. The frontend runtime groups DOM nodes into steps, controls navigation, validates active-step fields before advancing, and keeps submit available only on the final step.

**Tech Stack:** WordPress PHP renderer, vanilla JS frontend runtime, Jest/jsdom, CSS, PHP syntax checks.

---

## Tasks

### Task 1: Runtime Step Navigation

- [ ] Write failing JS tests for step grouping, Next/Previous behavior, and final submit visibility.
- [ ] Update `assets/frontend/src/runtime.js` with step initialization and active-step validation.
- [ ] Run targeted JS tests and commit.

### Task 2: Renderer Step Markers

- [ ] Write failing PHP renderer test for `form_step` marker output.
- [ ] Update `includes/Frontend/Renderer.php` to render `form_step`.
- [ ] Add frontend CSS for progress and navigation.
- [ ] Run syntax checks and commit.

### Task 3: Build And Verification

- [ ] Run full JS tests.
- [ ] Run full build.
- [ ] Run composer validation.
- [ ] Run PHP syntax checks.
- [ ] Run whitespace check.
- [ ] Run `composer test` and record local WordPress test-suite status.
- [ ] Push branch.
