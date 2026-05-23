# Elementor Widget Design

## Goal

Add an optional Elementor widget so site builders can insert a BS23 form from Elementor and adjust core style tokens from Elementor controls without duplicating the form rendering engine.

## Approach

The plugin will register Elementor support only when Elementor is loaded. A small `Elementor\Integration` class will hook into Elementor's widget registration event, and a `FormWidget` class will expose form selection plus style controls. Rendering will delegate to the existing `[bs23_form]` shortcode so validation, uploads, conditional logic, multi-step behavior, frontend scripts, and security stay in one path.

## Elementor Widget Behavior

The widget name will be `bs23_form_builder`, title `BS23 Form`, and category `general`. Its content control will list published BS23 forms from the existing form post type. If no form is selected, the editor shows a short placeholder message instead of rendering an invalid form. If a form is selected, the widget renders the existing shortcode.

## Style Controls

Elementor style controls will target the same CSS custom properties already supported by the frontend style system:

- `--bs23-form-max-width`
- `--bs23-field-gap`
- `--bs23-label-color`
- `--bs23-label-size`
- `--bs23-input-background`
- `--bs23-input-border`
- `--bs23-input-radius`
- `--bs23-button-background`
- `--bs23-button-text`
- `--bs23-button-radius`
- `--bs23-error-color`
- `--bs23-success-color`
- `--bs23-step-active`

Controls will use Elementor selectors against `{{WRAPPER}} .bs23-form`, so Elementor-generated CSS overrides saved form defaults without changing form data.

## Security And Compatibility

No Elementor classes will be loaded unless Elementor exists, avoiding fatal errors for users who do not use Elementor. Form IDs will be sanitized with `absint`. Placeholder text will be escaped. The widget will not create new database tables and will not duplicate frontend submission logic.

## Testing

Pure PHPUnit tests will stub minimal Elementor and WordPress functions to verify integration registration, widget metadata, form option building, and shortcode rendering. The normal WordPress PHPUnit suite remains available when `WP_TESTS_DIR` is configured. PHP lint, JS tests, build, composer validation, and diff whitespace checks will still be run before pushing.
