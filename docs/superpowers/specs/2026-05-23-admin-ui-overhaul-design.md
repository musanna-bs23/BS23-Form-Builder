# Admin UI Overhaul Design

## Goal

Rework the current plain builder into a premium form-building workspace that is easier to use and visually strong enough for a commercial plugin.

## Product Direction

The UI should feel more polished than the current simple WordPress-style panels. It will use a modern SaaS layout with gradient accents, layered panels, animated hover states, clearer navigation, and dedicated work areas. The design must remain practical: form building, field configuration, form settings, email, style, and security each need their own clear place.

## Layout

The builder will become a three-zone workspace:

- Forms sidebar: search, New Form button, and a list of existing forms with status/meta.
- Center canvas: the active form title, save status, empty state, field cards, containers, and sections.
- Right inspector: tabs for Fields, Field Settings, Form, Email, Style, and Security.

This removes the current problem where fields, field settings, and form settings are stacked together in one long right column.

## Forms List

The admin app will load existing forms from a new `GET /forms` REST endpoint. Clicking a form loads its schema and settings into the builder. New Form resets the builder to an unsaved draft. This makes the plugin usable after multiple forms exist.

## Field Add UX

Drag and drop stays available. Field palette buttons also support double-click to add directly to the form canvas. This solves the issue where lower palette items are hard to drag into the canvas. Palette groups can be searched visually through a compact field search input.

## Visual Style

The UI will use:

- deep-to-bright gradient header/accent bands
- polished white panels with soft shadows
- animated field cards and palette items
- clear selected-field glow
- compact tabs and segmented controls
- better canvas empty state
- responsive layout that stacks sensibly on smaller screens

Gradients must be accents, not unreadable decoration. Text and controls must remain easy to scan.

## Testing

JS tests will cover form list loading/selection, New Form reset, double-click field insertion, and inspector tab behavior. PHP tests will cover the new list endpoint response. Verification will include JS tests, build, PHP lint, pure PHPUnit tests, composer validation, and whitespace checks.
