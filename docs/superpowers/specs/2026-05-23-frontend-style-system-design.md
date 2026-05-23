# Frontend Style System Design

## Goal

Add beautiful default frontend styling and form-level style controls so users can customize BS23 forms without writing CSS.

## Settings Model

Form settings gain a `style` group. Values are stored in existing form settings post meta:

- `max_width`
- `field_gap`
- `label_color`
- `label_size`
- `input_background`
- `input_border`
- `input_radius`
- `button_background`
- `button_text`
- `button_radius`
- `error_color`
- `success_color`
- `step_active`

The renderer maps these to CSS custom properties on the `<form>` element. Invalid values fall back to safe defaults.

## Builder UI

The existing Form Settings panel gains a "Style" section with compact inputs. This is intentionally token-based rather than a full design builder; Elementor integration can later expose the same tokens as widget controls.

## Frontend Theme

The frontend CSS uses the variables for spacing, inputs, labels, buttons, errors, success messages, uploads, and step progress. Defaults should look clean and professional out of the box.

## Security

All colors and size values are sanitized. CSS values are limited to safe color hexes and simple CSS lengths.

## Testing

PHP tests cover style default/sanitization and renderer CSS variable output. JS tests cover style settings UI payloads.
