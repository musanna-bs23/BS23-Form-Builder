# Entries Command Center Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a premium admin entries dashboard with filtering, analytics, detail drawer, deletion, and CSV export.

**Architecture:** PHP repositories and REST controllers provide secure entry data, summaries, deletion, and export from the existing entries table. A separate React admin app renders the command center and is enqueued only on the Entries submenu page.

**Tech Stack:** WordPress PHP, `$wpdb`, REST API, React with `@wordpress/scripts`, `@wordpress/api-fetch`, CSS/Sass, PHPUnit tests, Jest tests.

---

## File Structure

- `includes/Admin/EntriesPage.php`: Adds Entries submenu, renders React root, enqueues assets.
- `includes/Entries/EntryQueryRepository.php`: Query, summary, trend, form breakdown, detail, delete.
- `includes/Rest/EntriesRestController.php`: REST endpoints for entries dashboard.
- `includes/Export/CsvExporter.php`: CSV generation for filtered entries.
- `assets/entries/src/index.js`: React mount.
- `assets/entries/src/app.js`: Dashboard state and layout.
- `assets/entries/src/api.js`: REST API helpers.
- `assets/entries/src/components/SummaryCards.js`: Metric cards.
- `assets/entries/src/components/TrendChart.js`: CSS-based trend bars.
- `assets/entries/src/components/FormBreakdown.js`: Entries by form bars.
- `assets/entries/src/components/EntriesToolbar.js`: Filters, search, export, bulk delete.
- `assets/entries/src/components/EntriesTable.js`: Entries grid/table.
- `assets/entries/src/components/EntryDrawer.js`: Detail drawer.
- `assets/entries/src/styles.scss`: Premium command center styles.
- `tests/php/EntryQueryRepositoryTest.php`: Repository tests.
- `tests/php/CsvExporterTest.php`: Export tests.
- `tests/php/EntriesRestControllerTest.php`: REST security tests.
- `assets/entries/src/__tests__/entries-app.test.js`: UI behavior tests.

---

### Task 1: Entry Query Repository

- [ ] Add failing repository tests for pagination, form filter, date filter, search, summary, trend, form breakdown, single delete, and bulk delete.
- [ ] Implement `EntryQueryRepository` with prepared SQL and decoded entry data.
- [ ] Run targeted PHP tests.
- [ ] Commit `feat: add entries query repository`.

### Task 2: CSV Exporter

- [ ] Add failing CSV exporter tests for headers and filtered rows.
- [ ] Implement `CsvExporter` using `fputcsv` to `php://temp`.
- [ ] Run targeted PHP tests.
- [ ] Commit `feat: add entries csv export`.

### Task 3: Entries REST API

- [ ] Add failing REST tests for unauthorized rejection and list/detail/delete/export endpoints.
- [ ] Implement `EntriesRestController`.
- [ ] Register controller in `Plugin` and require file in bootstrap.
- [ ] Run targeted PHP tests.
- [ ] Commit `feat: add entries REST API`.

### Task 4: Entries Admin Page Shell

- [ ] Add failing PHP test that Entries submenu is registered.
- [ ] Implement `EntriesPage` with root div and asset enqueue.
- [ ] Add entries app mount, API helper, minimal app shell.
- [ ] Build entries assets.
- [ ] Commit `feat: add entries admin page`.

### Task 5: Command Center UI

- [ ] Add failing JS tests for summary cards, table empty state, filter changes, and drawer data rendering.
- [ ] Implement summary cards, charts, toolbar, table, drawer, loading/error states.
- [ ] Style premium dashboard UI with responsive layout.
- [ ] Run JS tests and build.
- [ ] Commit `feat: add entries command center UI`.

### Task 6: Final Verification

- [ ] Run PHP syntax checks.
- [ ] Run `composer test` when WordPress test suite is available.
- [ ] Run `npm run test:js -- --runInBand`.
- [ ] Run `npm run build` plus entries build.
- [ ] Run `git status --short`.
- [ ] Push `feature/entries-command-center`.
