# Conditional Logic Design

## Product Goal

Add Pro-style conditional logic so form builders can show or hide fields based on earlier answers. The first version focuses on a polished, secure, and understandable field-level experience instead of a complex rules engine.

## Scope

This feature adds show/hide behavior for normal fields, section breaks, custom HTML blocks, submit buttons, and fields inside containers. Hidden fields are not rendered on the frontend and are skipped during server-side validation, so a required hidden field cannot block a submission.

The first version supports one condition group per field:

- Action: show or hide this field.
- Match mode: all conditions or any condition.
- Conditions: source field, operator, and comparison value.
- Operators: equals, not equals, contains, is empty, is not empty.

Nested conditional dependencies are evaluated with submitted values only. A field can depend on another field value, but this version does not build a visual dependency graph or detect loops in the UI.

## Admin Experience

The existing field settings panel gains a "Conditional Logic" section. It appears after the common field controls and uses compact controls:

- Enable conditional logic toggle.
- Action dropdown: show or hide.
- Match dropdown: all or any.
- Repeating condition rows with field dropdown, operator dropdown, value input where needed, and remove button.
- Add condition button.

The source field dropdown lists eligible non-layout fields from the current schema, excluding the selected field. Labels are shown for readability; saved rules use stable field names so rendering and validation work after reload.

## Schema

Rules are stored inside each field's settings:

```json
{
  "conditionalLogic": {
    "enabled": true,
    "action": "show",
    "match": "all",
    "rules": [
      {
        "field": "department",
        "operator": "equals",
        "value": "Sales"
      }
    ]
  }
}
```

Schema sanitization allows this nested settings object and removes unsupported operators, actions, empty field names, and non-scalar values.

## Frontend Behavior

The renderer evaluates conditional logic before rendering each field. If the field should be hidden, it returns no markup. Container columns render their children normally, and hidden child fields simply disappear from the column.

For a "show" action, the field renders only when conditions match. For a "hide" action, the field is hidden when conditions match. Empty or invalid rule sets fall back to visible to avoid accidentally losing fields.

## Submission Security

Server-side validation evaluates the same rules using posted input before required checks and type validation. Hidden fields are skipped completely and are not included in sanitized entry data.

This is important because frontend-only conditional logic is not secure: users can tamper with hidden fields or submit missing required fields. The server is the source of truth.

## Testing

JavaScript tests cover:

- Building eligible condition source field options.
- Updating conditional logic in the field settings panel.
- Rendering the conditional logic UI only when a field is selected.

PHP tests cover:

- Renderer hides and shows fields based on conditions.
- Required hidden fields do not render.
- Validator skips required hidden fields and excludes hidden data.
- Schema sanitizer preserves valid conditional logic and strips invalid rule data.

## Out Of Scope

This milestone does not include live browser-side conditional toggling after a user changes an answer. The first render and server validation are correct. A later milestone can add frontend JavaScript for instant hide/show behavior without changing the saved schema.
