# Entries Command Center Design

Date: 2026-05-22

## Goal

Build a premium admin entries experience for BS23 Form Builder. This milestone turns stored submissions into a polished command center with searchable entries, strong filtering, single-entry inspection, CSV export, and useful visual analytics.

The UI should feel like a modern SaaS operations dashboard: dense, clean, fast to scan, and visually refined without being decorative or cluttered.

## Scope

This milestone includes:

- `Entries` submenu under `BS23 Forms`.
- Admin-only React screen for entries and analytics.
- Summary cards:
  - Total entries.
  - Entries today.
  - Entries this week.
  - Last submission time.
- Charts:
  - Submission trend over recent days.
  - Entries by form.
- Entries table:
  - Search.
  - Form filter.
  - Date range filter.
  - Pagination.
  - Sort by newest/oldest.
  - Row selection.
  - Bulk delete.
  - CSV export for the current filters.
- Entry detail drawer:
  - Form title.
  - Submitted date.
  - User metadata.
  - Field/value list from stored `entry_data`.
  - Delete action.
- Loading, empty, and error states.
- Responsive admin layout.
- REST API endpoints for listing, summary, detail, delete, bulk delete, and CSV export.
- Tests for repository querying, filtering, deletion, summary stats, and CSV formatting.

This milestone excludes:

- Editing submitted entries.
- Restoring deleted entries.
- Saved report presets.
- Email notification analytics.
- Field-level charts beyond a clean field/value detail view.
- New database tables.

## Storage

Continue using the existing entries table:

`{$wpdb->prefix}bs23_form_entries`

No additional table is added. All reporting in this milestone derives from:

- `form_id`
- `entry_data`
- `user_id`
- `user_ip`
- `user_agent`
- `created_at`

## Architecture

New or expanded PHP units:

- `Admin/EntriesPage`: registers the Entries submenu, renders React root, enqueues entry admin assets.
- `Entries/EntryQueryRepository`: reads entries, counts, summaries, form breakdowns, trend data, single entry details, and deletes rows.
- `Rest/EntriesRestController`: REST API for entries command center.
- `Export/CsvExporter`: converts filtered entries into CSV output.

New admin app files:

- `assets/entries/src/index.js`: mounts the entries app.
- `assets/entries/src/app.js`: command center state and layout.
- `assets/entries/src/api.js`: REST calls.
- `assets/entries/src/components/SummaryCards.js`
- `assets/entries/src/components/TrendChart.js`
- `assets/entries/src/components/FormBreakdown.js`
- `assets/entries/src/components/EntriesToolbar.js`
- `assets/entries/src/components/EntriesTable.js`
- `assets/entries/src/components/EntryDrawer.js`
- `assets/entries/src/styles.scss`

## REST API

Namespace: `bs23-form-builder/v1`

Endpoints:

- `GET /entries`
  - Query params: `form_id`, `search`, `date_from`, `date_to`, `order`, `page`, `per_page`
  - Returns entries plus pagination metadata.
- `GET /entries/summary`
  - Returns totals, today, week, last submission, trend data, and form breakdown.
- `GET /entries/(?P<id>\d+)`
  - Returns one entry with decoded field data.
- `DELETE /entries/(?P<id>\d+)`
  - Deletes one entry.
- `POST /entries/bulk-delete`
  - Deletes selected IDs.
- `GET /entries/export`
  - Returns CSV for current filters.

All endpoints require `manage_options`.

## UI Direction

The command center first viewport:

- Compact header with title, refresh, and export controls.
- Summary cards directly under the header.
- Two charts in a balanced analytics row.
- Toolbar with search, form filter, date range, sort, and bulk actions.
- Entries table as the primary work surface.
- Detail drawer slides from the right.

Visual rules:

- Use restrained color: neutral base, blue accent, green/red only for status feedback.
- Avoid marketing-style cards inside cards.
- Use cards only for summary widgets, chart panels, table surface, and drawer.
- Keep spacing tight and consistent.
- Ensure text never overflows buttons or cells; truncate long values with title/tooltip.
- Mobile/narrow admin widths stack charts and table controls.

## CSV Export

CSV export includes:

- Entry ID.
- Form ID.
- Form title.
- Created at.
- User ID.
- User IP.
- User agent.
- One column per submitted field key.

The export uses the same filters as the table. Values are sanitized for CSV output and generated server-side.

## Security

- All REST endpoints require `manage_options`.
- REST nonce required by `apiFetch`.
- Query params sanitized.
- SQL uses `$wpdb->prepare`.
- Entry IDs are absint.
- Delete actions require REST nonce/capability.
- CSV output is escaped using `fputcsv`.
- Admin output is escaped.

## Testing

Development follows TDD.

Required PHP tests:

- Query repository returns paginated entries.
- Query repository filters by form ID.
- Query repository filters by date range.
- Query repository searches JSON entry data.
- Summary returns total, today, week, last submission.
- Trend groups recent entries by date.
- Form breakdown groups entries by form.
- Single delete removes one row.
- Bulk delete removes selected rows only.
- CSV exporter includes headers and filtered data.
- REST endpoints reject unauthorized users.

Required JavaScript tests:

- Summary cards render metrics.
- Entries table renders rows and empty state.
- Toolbar emits filter changes.
- Drawer renders decoded entry data.
- App calls API and displays loading/error states.

Manual verification:

- Submit multiple forms.
- Open Entries command center.
- Filter/search entries.
- Open detail drawer.
- Delete one entry.
- Bulk delete selected entries.
- Export CSV and confirm filtered data.
