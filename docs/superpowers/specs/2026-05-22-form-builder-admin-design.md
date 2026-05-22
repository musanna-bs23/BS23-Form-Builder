# Form Builder Admin Design

Date: 2026-05-22

## Goal

Build the first milestone of a WordPress form plugin: an admin-only form builder that lets site admins create and save forms by dragging fields from a right-side palette into a left-side canvas. This milestone establishes the plugin foundation and data model for later frontend rendering, submissions, validation, reports, and Elementor styling.

The plugin should be built with WordPress.org publication in mind from the first milestone: minimal database footprint, no external tracking, no remote asset CDN, strict sanitization/escaping, nonce and capability checks, translation readiness, GPL-compatible dependencies, and clean uninstall behavior.

## Scope

This milestone includes:

- Top-level WordPress admin menu for the plugin.
- Forms list page with a New Form action.
- New/Edit Form builder screen.
- Full-screen admin builder interface.
- Left-side form canvas/build area.
- Right-side grouped field palette inspired by Fluent Forms-style workflow, with original visual design.
- Dragging fields from palette to canvas.
- Dragging fields into one, two, three, and four column containers.
- Section Break support as a draggable field.
- Saving form title and builder schema.
- Loading saved schema for edit.
- Server-side schema validation before save.
- TDD-based PHP and JavaScript test coverage for the first milestone.

This milestone excludes:

- Frontend form rendering.
- Public submissions.
- Submission validation.
- Entries/reports.
- Email notifications.
- Elementor widget/style controls.
- Conditional logic.
- Payment fields.
- File upload processing.
- Integrations.
- Multi-step runtime behavior.

Fields that have complex runtime behavior may appear in the palette and save into the schema, but their frontend/runtime behavior is deferred to later milestones.

## Storage

Use a WordPress-native custom post type plus post meta for form definitions.

- Register an internal form custom post type.
- Store form title in the post title.
- Store builder schema as versioned JSON-compatible post meta.
- Do not create custom database tables in this milestone.

This keeps the first release lightweight and WordPress.org-friendly. Custom tables are deferred until submission entries are implemented, where a single entries table may be justified for search, filtering, reporting, and retention controls.

## Architecture

The plugin will be split into small units with clear responsibilities:

- Main plugin bootstrap: constants, loading, activation, uninstall hooks.
- `Admin/Menu`: top-level admin menu, Forms list page, and New/Edit builder page.
- `PostTypes/FormPostType`: registers the internal form post type.
- `Rest/FormRoutes`: REST endpoints for loading and saving form builder data.
- `Builder/Schema`: allow-listed schema validation and sanitization.
- `assets/admin`: React admin builder app, loaded only on plugin admin pages.
- `assets/admin/components`: field palette, canvas, draggable field card, container row, and save controls.
- `tests`: PHP tests for WordPress/server behavior and JavaScript tests for builder state/schema behavior.

The admin app will use the WordPress JavaScript stack where practical:

- `@wordpress/scripts` for build/test tooling.
- `@wordpress/components` for WordPress-native UI primitives.
- `@wordpress/icons` or an approved GPL-compatible icon source.
- `@wordpress/api-fetch` for REST requests.

Drag/drop in this milestone will use native browser drag/drop or pointer events to avoid adding a licensing and bundle-size dependency before the builder requirements prove one is necessary.

## Builder UI

The builder layout:

- Header with form title input, save button, and status feedback.
- Main workspace with canvas on the left and field palette on the right.
- Canvas shows an empty state until fields are added.
- Field cards in the canvas show label, type, and basic action controls.
- Containers show fixed one, two, three, or four column slots.
- Fields can be dropped into root canvas or into a specific container column.
- Palette groups are collapsible.

The interface should feel polished, modern, and production-grade, while remaining an original design rather than a clone. It should prioritize clarity, compact controls, consistent spacing, visible drop targets, and stable dimensions to avoid layout shifts.

## Field Palette

General fields:

- Name Fields
- Email
- Simple Text
- Mask Input
- Text Area
- Address Fields
- Country List
- Numeric Field
- Dropdown
- Radio Field
- Checkbox
- Multiple Choice
- Website URL
- Time & Date
- Image Upload
- File Upload
- Custom HTML
- Phone/Mobile

Advanced fields:

- Hidden Field
- Section Break
- Shortcode
- Terms & Conditions
- Action Hook
- Form Step
- Ratings
- Checkable Grid
- GDPR Agreement
- Password
- Custom Submit Button
- Range Slider
- Net Promoter Score
- Dynamic Field
- Chained Select
- Color Picker
- Repeat Field
- Post/CPT Select
- Rich Text Input
- Save & Resume

Container fields:

- One Column Container
- Two Column Container
- Three Column Container
- Four Column Container

Only builder placement and schema persistence are required for all palette items in this milestone. Runtime behavior is intentionally deferred.

## Schema

Forms are saved as a versioned builder schema in post meta.

Example:

```json
{
  "version": 1,
  "fields": [
    {
      "id": "field_abc123",
      "type": "text",
      "label": "Text Field",
      "name": "text_field",
      "required": false,
      "settings": {}
    },
    {
      "id": "container_xyz123",
      "type": "container",
      "columns": 2,
      "children": [
        [
          {
            "id": "field_1",
            "type": "email",
            "label": "Email",
            "name": "email",
            "required": false,
            "settings": {}
          }
        ],
        []
      ]
    }
  ]
}
```

Rules:

- `version` must be an integer and starts at `1`.
- `fields` must be an array.
- Each field must have a stable `id`, allow-listed `type`, `label`, `name`, `required`, and `settings`.
- Containers use `type: "container"`, a `columns` value of `1`, `2`, `3`, or `4`, and `children` arrays matching the column count.
- Unknown field types are rejected.
- Unknown top-level properties are ignored or stripped.
- Settings are sanitized by allow list, with broad runtime behavior deferred.

## REST API

The first milestone needs REST endpoints for the admin builder:

- Load form data for edit.
- Create a new form.
- Save title and builder schema for an existing form.

Security rules:

- Require the `manage_options` capability for this milestone.
- Require REST nonce.
- Reject unauthorized requests.
- Validate post ownership/type before updating.
- Sanitize title and schema before persistence.
- Return structured validation errors for invalid schema.

## Error Handling

Admin UI behavior:

- If save succeeds, show saved status and keep the current builder state.
- If save fails, show a clear error and keep unsaved builder state.
- If schema validation fails, show a useful message.
- If permission or nonce validation fails, show a permission error.
- If builder JavaScript fails to load, show a fallback admin message.

Server behavior:

- Return appropriate HTTP status codes for permission, validation, and persistence failures.
- Avoid exposing sensitive details in REST errors.
- Use WordPress APIs for database writes.

## Publish Readiness

The first milestone must follow these constraints:

- No custom tables.
- No external asset CDN.
- No telemetry or remote calls.
- GPL-compatible dependencies only.
- Translation-ready strings with a plugin text domain.
- Capability checks for all admin actions.
- Nonce checks for REST writes.
- Sanitization on input and escaping on output.
- Namespaced/prefixed functions, classes, handles, and meta keys.
- Uninstall behavior scoped to plugin-owned data.

## Testing

Development will follow TDD: write a failing test, verify the failure, implement the minimal code, verify passing tests, then refactor.

Required PHP tests:

- Form custom post type registers correctly.
- Unauthorized REST save is rejected.
- Valid builder schema saves to post meta.
- Invalid field type is rejected.
- Container schema rejects invalid column counts or child structure.

Required JavaScript tests:

- Dragging a field into the root canvas updates schema state.
- Dragging a field into a two, three, or four column container places it in the selected column.
- Builder serializes the same schema shape expected by the REST endpoint.
- Unknown field type is not accepted into persisted schema state.

Manual verification:

- Admin menu appears.
- New Form opens the builder.
- Fields can be dragged from palette to canvas.
- Containers accept drops into columns.
- Save and reload preserves the schema.

## Future Milestones

Later milestones should be handled one by one:

- Frontend shortcode rendering.
- Submission handling and validation.
- Entries storage and reports.
- Email notifications.
- Elementor widget and style controls.
- Conditional logic.
- File upload processing.
- Integrations and advanced fields.
