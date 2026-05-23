# Elementor Widget Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an optional Elementor widget that renders BS23 forms and exposes Elementor style controls backed by the existing frontend CSS variables.

**Architecture:** Register Elementor integration from `Plugin::register()` through a small integration class that only hooks when Elementor has loaded. Keep rendering centralized by delegating widget output to the existing shortcode. Keep Elementor-specific code in `includes/Elementor/`.

**Tech Stack:** WordPress plugin PHP 7.4, Elementor widget APIs, PHPUnit with test stubs, existing shortcode/frontend CSS variable system.

---

### Task 1: Elementor Integration Registration

**Files:**
- Create: `tests/unit/ElementorIntegrationTest.php`
- Create: `includes/Elementor/Integration.php`
- Modify: `includes/Plugin.php`
- Modify: `bs23-form-builder.php`

- [ ] Write a failing pure PHPUnit test that stubs `add_action()` and `did_action()` and asserts `Integration::register()` adds an Elementor widget hook only when Elementor has loaded.
- [ ] Run: `./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/unit/ElementorIntegrationTest.php`
- [ ] Implement `BS23\FormBuilder\Elementor\Integration` with `register()` and `registerWidgets()` methods.
- [ ] Wire the integration from `Plugin::register()` and require the file from `bs23-form-builder.php`.
- [ ] Re-run the targeted unit test and PHP lint.
- [ ] Commit with `feat: register elementor integration`.

### Task 2: Elementor Form Widget

**Files:**
- Create: `tests/unit/ElementorFormWidgetTest.php`
- Create: `includes/Elementor/FormWidget.php`
- Modify: `includes/Elementor/Integration.php`
- Modify: `bs23-form-builder.php`

- [ ] Write failing pure PHPUnit tests with minimal Elementor class stubs for widget name/title/icon/category, form options from published forms, placeholder rendering with no form selected, and shortcode rendering with a selected form.
- [ ] Run: `./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/unit/ElementorFormWidgetTest.php`
- [ ] Implement `FormWidget` extending `\Elementor\Widget_Base`.
- [ ] Add content controls for form selection.
- [ ] Add `render()` that calls `do_shortcode('[bs23_form id="X"]')` for selected forms and escaped placeholder text otherwise.
- [ ] Update `Integration::registerWidgets()` to register `FormWidget` with Elementor's widgets manager.
- [ ] Re-run targeted tests and PHP lint.
- [ ] Commit with `feat: add elementor form widget`.

### Task 3: Elementor Style Controls

**Files:**
- Modify: `tests/unit/ElementorFormWidgetTest.php`
- Modify: `includes/Elementor/FormWidget.php`

- [ ] Add a failing test that records registered style controls and asserts selectors write expected CSS variables on `{{WRAPPER}} .bs23-form`.
- [ ] Run: `./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/unit/ElementorFormWidgetTest.php`
- [ ] Implement style sections and controls for layout, label, input, button, messages, and steps.
- [ ] Re-run targeted tests and PHP lint.
- [ ] Commit with `feat: add elementor style controls`.

### Task 4: Full Verification And Push

**Files:**
- No production edits expected.

- [ ] Run: `find includes tests -name '*.php' -print0 | xargs -0 -n1 php -l && php -l bs23-form-builder.php`
- [ ] Run: `./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/unit`
- [ ] Run: `npm run test:js -- --runInBand`
- [ ] Run: `npm run build`
- [ ] Run: `composer validate --strict`
- [ ] Run: `git diff --check`
- [ ] Run: `composer test` and record if it is blocked by missing `WP_TESTS_DIR`.
- [ ] Push `feature/elementor-widget` to origin and report the PR URL.
