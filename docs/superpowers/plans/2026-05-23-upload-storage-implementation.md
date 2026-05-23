# Upload Storage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add secure file/image upload handling and entry display/export support.

**Architecture:** Renderer emits file controls and multipart forms. Submission handling passes file data to validation, then an `UploadStorage` service moves files into WordPress uploads and returns metadata stored in entries.

**Tech Stack:** WordPress PHP upload APIs, existing React entries UI, Jest, PHP syntax checks.

---

## Tasks

### Task 1: Render Upload Fields

- [ ] Write failing PHP renderer tests for multipart forms and file/image controls.
- [ ] Update `Renderer` to detect upload fields and output file inputs.
- [ ] Run syntax checks and commit.

### Task 2: Validate And Store Uploads

- [ ] Write failing PHP tests for upload metadata validation and storage service.
- [ ] Create `includes/Submission/UploadStorage.php`.
- [ ] Update `SubmissionHandler` to merge files and store upload metadata after validation.
- [ ] Update `SubmissionValidator` file rules as needed.
- [ ] Run syntax checks and commit.

### Task 3: Entries UI And CSV

- [ ] Write failing JS test for file rendering in entry drawer.
- [ ] Update `EntryDrawer` to render file links/thumbnails.
- [ ] Write failing PHP CSV test for file metadata export.
- [ ] Update `CsvExporter`.
- [ ] Run tests/checks and commit.

### Task 4: Final Verification

- [ ] Run full JS tests.
- [ ] Run full build.
- [ ] Run composer validation.
- [ ] Run PHP syntax checks.
- [ ] Run whitespace check.
- [ ] Run `composer test` and record local WP test-suite status.
- [ ] Push branch.
