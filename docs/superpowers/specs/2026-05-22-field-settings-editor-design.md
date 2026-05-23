# Field Settings Editor Design

Date: 2026-05-22

## Goal

Add real field editing to the form builder. Users should be able to click any field in the canvas, edit its settings in a polished settings panel, duplicate/delete fields, manage choices, and have those settings affect frontend rendering and submission validation.

## Scope

This milestone includes:

- Field selection in the builder canvas.
- Settings panel for the selected field.
- Editing common settings:
  - Label.
  - Name/key.
  - Placeholder.
  - Default value.
  - Required toggle.
  - Help text.
  - CSS class.
- Choice editing for dropdown, radio, checkbox, and multiple choice fields.
- Custom HTML content editing.
- Section break title and description editing.
- Submit button text editing.
- Duplicate field.
- Delete field.
- Reorder fields within the root canvas.
- Edit fields inside container columns.
- Save edited schema through the existing form save flow.
- Frontend renderer support for:
  - Placeholder.
  - Default values.
  - Help text.
  - CSS class.
  - Choices/options.
  - Custom HTML content.
  - Section descriptions.
  - Submit button text.
- Submission validator continues using edited `name` and `required` values.

This milestone excludes:

- Drag reorder across container boundaries.
- Conditional logic.
- Field-specific advanced validation rules.
- Visual style builder.
- Elementor controls.

## Builder UX

The builder workspace keeps the existing canvas and right-side palette/settings area.

When no field is selected:

- Show palette and form settings.

When a field is selected:

- Show a field settings panel above or beside the palette.
- The selected field card is visually highlighted.
- Field settings update live in builder state.
- Delete and duplicate actions are available in the settings panel.

Reordering:

- Root fields get simple up/down controls.
- Container column fields get up/down controls within their column.
- This avoids complex cross-container drag reorder in this milestone.

## Schema

No new top-level schema version is required.

Field settings use the existing `settings` object:

```json
{
  "id": "field_1",
  "type": "text",
  "label": "Full Name",
  "name": "full_name",
  "required": true,
  "settings": {
    "placeholder": "Your full name",
    "default": "",
    "help": "Use your legal name.",
    "className": "lead-field"
  }
}
```

Choice fields store choices as an array:

```json
{
  "settings": {
    "choices": ["Option A", "Option B"]
  }
}
```

Custom HTML:

```json
{
  "settings": {
    "content": "<p>Allowed HTML content</p>"
  }
}
```

Section break:

```json
{
  "settings": {
    "description": "Optional section description"
  }
}
```

## Frontend Behavior

Renderer changes:

- Adds `placeholder` attributes when available.
- Uses `settings.default` when there is no submitted value.
- Renders help text under controls.
- Adds sanitized custom CSS class to field wrapper.
- Renders choices from `settings.choices`.
- Renders custom HTML from `settings.content` with `wp_kses_post`.
- Renders section description when provided.
- Uses submit field label/settings text for button text.

Validator changes:

- Uses edited `name` keys already stored in schema.
- Required validation already follows `required`; keep that behavior.
- Choice fields accept submitted values but sanitize them. Strict choice membership validation is deferred.

## Testing

Development follows TDD.

Required JavaScript tests:

- Selecting a root field opens settings.
- Editing label/name/placeholder updates schema state.
- Required toggle updates selected field.
- Choice editor updates `settings.choices`.
- Duplicate field creates a new field with a new ID.
- Delete field removes selected field.
- Move up/down reorders root fields.
- Container child field can be selected and edited.

Required PHP tests:

- Renderer outputs placeholder/default/help/class.
- Renderer outputs choices from `settings.choices`.
- Renderer outputs custom HTML content safely.
- Renderer outputs section description.
- Validator uses edited field name for submitted data.

Manual verification:

- Add fields in builder.
- Select and edit each common setting.
- Edit choices.
- Duplicate/delete/reorder fields.
- Save and reload form.
- Render shortcode on frontend and confirm settings appear.
