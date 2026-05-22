# Form Builder Admin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the first BS23 Form Builder milestone: a WordPress admin form builder with draggable fields, column containers, and CPT/post-meta persistence.

**Architecture:** The plugin uses a WordPress-native custom post type for form records and post meta for versioned builder schema JSON. PHP owns bootstrap, admin pages, REST routes, capabilities, and schema validation; the React admin app owns drag/drop builder state and REST save/load.

**Tech Stack:** WordPress PHP plugin, custom post type, REST API, `@wordpress/scripts`, React, `@wordpress/components`, `@wordpress/api-fetch`, Jest, WordPress PHPUnit test suite.

---

## File Structure

- `bs23-form-builder.php`: Main plugin bootstrap, constants, autoloader, service registration.
- `uninstall.php`: Removes plugin-owned data only when uninstalling.
- `composer.json`: PHP dev tooling and autoload metadata.
- `package.json`: WordPress scripts, Jest commands, build commands.
- `phpunit.xml.dist`: PHPUnit configuration.
- `tests/bootstrap.php`: WordPress test-suite bootstrap.
- `tests/php/FormPostTypeTest.php`: CPT registration tests.
- `tests/php/SchemaValidatorTest.php`: builder schema validation tests.
- `tests/php/FormRestControllerTest.php`: REST permission and persistence tests.
- `tests/php/AdminMenuTest.php`: admin menu registration tests.
- `includes/Plugin.php`: Registers plugin services.
- `includes/PostTypes/FormPostType.php`: Registers internal form CPT.
- `includes/Builder/SchemaValidator.php`: Validates and sanitizes builder schema.
- `includes/Rest/FormRestController.php`: REST load/create/save endpoints.
- `includes/Admin/Menu.php`: Admin menu and builder page shell.
- `assets/admin/src/index.js`: React app entry.
- `assets/admin/src/app.js`: Builder app composition.
- `assets/admin/src/fields.js`: Field palette definitions and defaults.
- `assets/admin/src/schema.js`: Pure schema helpers.
- `assets/admin/src/components/Palette.js`: Grouped right-side field palette.
- `assets/admin/src/components/Canvas.js`: Left-side builder canvas.
- `assets/admin/src/components/FieldCard.js`: Canvas field card.
- `assets/admin/src/components/ContainerField.js`: Column container UI.
- `assets/admin/src/components/SaveBar.js`: Title/save/status controls.
- `assets/admin/src/__tests__/schema.test.js`: JS schema helper tests.
- `assets/admin/src/__tests__/builder-state.test.js`: drag/drop state tests.
- `assets/admin/src/__tests__/app.test.js`: builder component behavior tests.
- `assets/admin/src/styles.scss`: Admin builder styling.

---

### Task 1: Tooling Baseline

**Files:**
- Create: `composer.json`
- Create: `package.json`
- Create: `phpunit.xml.dist`
- Create: `tests/bootstrap.php`
- Create: `assets/admin/src/__tests__/schema.test.js`

- [ ] **Step 1: Add PHP tooling configuration**

Create `composer.json`:

```json
{
  "name": "bs23/form-builder",
  "description": "BS23 Form Builder WordPress plugin.",
  "type": "wordpress-plugin",
  "license": "GPL-2.0-or-later",
  "autoload": {
    "psr-4": {
      "BS23\\FormBuilder\\": "includes/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "BS23\\FormBuilder\\Tests\\": "tests/php/"
    }
  },
  "require": {},
  "require-dev": {
    "phpunit/phpunit": "^9.6"
  },
  "scripts": {
    "test": "phpunit"
  }
}
```

- [ ] **Step 2: Add JavaScript tooling configuration**

Create `package.json`:

```json
{
  "name": "bs23-form-builder",
  "version": "0.1.0",
  "private": true,
  "license": "GPL-2.0-or-later",
  "scripts": {
    "build": "wp-scripts build assets/admin/src/index.js --output-path=assets/admin/build",
    "start": "wp-scripts start assets/admin/src/index.js --output-path=assets/admin/build",
    "test:js": "wp-scripts test-unit-js --env=jsdom",
    "test": "npm run test:js && composer test"
  },
  "devDependencies": {
    "@testing-library/react": "^15.0.0",
    "@wordpress/scripts": "^30.0.0"
  },
  "dependencies": {
    "@wordpress/api-fetch": "^7.0.0",
    "@wordpress/components": "^29.0.0",
    "@wordpress/element": "^6.0.0",
    "@wordpress/icons": "^10.0.0"
  }
}
```

- [ ] **Step 3: Add PHPUnit config**

Create `phpunit.xml.dist`:

```xml
<?xml version="1.0"?>
<phpunit bootstrap="tests/bootstrap.php" colors="true" verbose="true">
  <testsuites>
    <testsuite name="BS23 Form Builder">
      <directory suffix="Test.php">tests/php</directory>
    </testsuite>
  </testsuites>
</phpunit>
```

- [ ] **Step 4: Add WordPress test bootstrap**

Create `tests/bootstrap.php`:

```php
<?php
declare(strict_types=1);

$_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';

if (! file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WordPress test suite not found. Set WP_TESTS_DIR.\n");
    exit(1);
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', static function (): void {
    require dirname(__DIR__) . '/bs23-form-builder.php';
});

require $_tests_dir . '/includes/bootstrap.php';
```

- [ ] **Step 5: Install dependencies**

Run:

```bash
composer install
npm install
```

Expected: dependencies install without license or audit-blocking errors.

- [ ] **Step 6: Commit tooling**

```bash
git add composer.json package.json phpunit.xml.dist tests/bootstrap.php package-lock.json composer.lock
git commit -m "chore: add plugin test tooling"
```

---

### Task 2: Plugin Bootstrap And CPT

**Files:**
- Create: `tests/php/FormPostTypeTest.php`
- Create: `bs23-form-builder.php`
- Create: `includes/Plugin.php`
- Create: `includes/PostTypes/FormPostType.php`

- [ ] **Step 1: Write failing CPT test**

Create `tests/php/FormPostTypeTest.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use WP_UnitTestCase;

final class FormPostTypeTest extends WP_UnitTestCase
{
    public function test_form_post_type_is_registered(): void
    {
        do_action('init');

        $post_type = get_post_type_object('bs23_form');

        $this->assertNotNull($post_type);
        $this->assertFalse($post_type->public);
        $this->assertTrue($post_type->show_ui);
        $this->assertSame('BS23 Forms', $post_type->labels->name);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run:

```bash
composer test -- --filter FormPostTypeTest
```

Expected: FAIL because `bs23-form-builder.php` or `bs23_form` registration does not exist yet.

- [ ] **Step 3: Implement minimal bootstrap**

Create `bs23-form-builder.php`:

```php
<?php
/**
 * Plugin Name: BS23 Form Builder
 * Description: Drag-and-drop WordPress form builder.
 * Version: 0.1.0
 * Author: BS23
 * License: GPL-2.0-or-later
 * Text Domain: bs23-form-builder
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('BS23_FORM_BUILDER_FILE', __FILE__);
define('BS23_FORM_BUILDER_DIR', plugin_dir_path(__FILE__));
define('BS23_FORM_BUILDER_URL', plugin_dir_url(__FILE__));
define('BS23_FORM_BUILDER_VERSION', '0.1.0');

require_once BS23_FORM_BUILDER_DIR . 'includes/Plugin.php';
require_once BS23_FORM_BUILDER_DIR . 'includes/PostTypes/FormPostType.php';

add_action('plugins_loaded', static function (): void {
    (new BS23\FormBuilder\Plugin())->register();
});
```

Create `includes/Plugin.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder;

use BS23\FormBuilder\PostTypes\FormPostType;

final class Plugin
{
    public function register(): void
    {
        (new FormPostType())->register();
    }
}
```

Create `includes/PostTypes/FormPostType.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\PostTypes;

final class FormPostType
{
    public const NAME = 'bs23_form';

    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType(): void
    {
        register_post_type(self::NAME, [
            'labels' => [
                'name' => __('BS23 Forms', 'bs23-form-builder'),
                'singular_name' => __('BS23 Form', 'bs23-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
        ]);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run:

```bash
composer test -- --filter FormPostTypeTest
```

Expected: PASS.

- [ ] **Step 5: Commit bootstrap and CPT**

```bash
git add bs23-form-builder.php includes/Plugin.php includes/PostTypes/FormPostType.php tests/php/FormPostTypeTest.php
git commit -m "feat: register form post type"
```

---

### Task 3: Schema Validator

**Files:**
- Create: `tests/php/SchemaValidatorTest.php`
- Create: `includes/Builder/SchemaValidator.php`

- [ ] **Step 1: Write failing schema tests**

Create `tests/php/SchemaValidatorTest.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Builder\SchemaValidator;
use WP_UnitTestCase;

final class SchemaValidatorTest extends WP_UnitTestCase
{
    public function test_valid_schema_is_sanitized(): void
    {
        $validator = new SchemaValidator();
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'email',
                    'label' => 'Email <b>Address</b>',
                    'name' => 'email address',
                    'required' => true,
                    'settings' => [],
                ],
            ],
        ];

        $result = $validator->sanitize($schema);

        $this->assertSame(1, $result['version']);
        $this->assertSame('Email Address', $result['fields'][0]['label']);
        $this->assertSame('email_address', $result['fields'][0]['name']);
    }

    public function test_invalid_field_type_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type: unsafe');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'unsafe', 'label' => 'Unsafe', 'name' => 'unsafe'],
            ],
        ]);
    }

    public function test_invalid_container_column_count_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid container column count.');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'container_1', 'type' => 'container', 'columns' => 5, 'children' => []],
            ],
        ]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run:

```bash
composer test -- --filter SchemaValidatorTest
```

Expected: FAIL because `SchemaValidator` does not exist.

- [ ] **Step 3: Implement schema validator**

Create `includes/Builder/SchemaValidator.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Builder;

use InvalidArgumentException;

final class SchemaValidator
{
    private const FIELD_TYPES = [
        'name', 'email', 'text', 'mask', 'textarea', 'address', 'country', 'number',
        'dropdown', 'radio', 'checkbox', 'multiple_choice', 'url', 'datetime',
        'image_upload', 'file_upload', 'html', 'phone', 'hidden', 'section_break',
        'shortcode', 'terms', 'action_hook', 'form_step', 'ratings', 'checkable_grid',
        'gdpr', 'password', 'submit', 'range', 'nps', 'dynamic', 'chained_select',
        'color', 'repeat', 'post_select', 'rich_text', 'save_resume', 'container',
    ];

    public function sanitize(array $schema): array
    {
        $version = isset($schema['version']) ? absint($schema['version']) : 1;

        if ($version !== 1) {
            throw new InvalidArgumentException('Unsupported schema version.');
        }

        $fields = $schema['fields'] ?? [];

        if (! is_array($fields)) {
            throw new InvalidArgumentException('Schema fields must be an array.');
        }

        return [
            'version' => 1,
            'fields' => array_map([$this, 'sanitizeField'], $fields),
        ];
    }

    private function sanitizeField(array $field): array
    {
        $type = sanitize_key((string) ($field['type'] ?? ''));

        if (! in_array($type, self::FIELD_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid field type: %s', $type));
        }

        if ($type === 'container') {
            return $this->sanitizeContainer($field);
        }

        return [
            'id' => sanitize_key((string) ($field['id'] ?? wp_unique_id('field_'))),
            'type' => $type,
            'label' => sanitize_text_field((string) ($field['label'] ?? 'Field')),
            'name' => sanitize_key(str_replace(' ', '_', (string) ($field['name'] ?? $type))),
            'required' => (bool) ($field['required'] ?? false),
            'settings' => is_array($field['settings'] ?? null) ? $this->sanitizeSettings($field['settings']) : [],
        ];
    }

    private function sanitizeContainer(array $field): array
    {
        $columns = absint($field['columns'] ?? 0);

        if (! in_array($columns, [1, 2, 3, 4], true)) {
            throw new InvalidArgumentException('Invalid container column count.');
        }

        $children = $field['children'] ?? [];

        if (! is_array($children) || count($children) !== $columns) {
            throw new InvalidArgumentException('Container children must match column count.');
        }

        $sanitizedChildren = [];
        foreach ($children as $column) {
            if (! is_array($column)) {
                throw new InvalidArgumentException('Container column must be an array.');
            }
            $sanitizedChildren[] = array_map([$this, 'sanitizeField'], $column);
        }

        return [
            'id' => sanitize_key((string) ($field['id'] ?? wp_unique_id('container_'))),
            'type' => 'container',
            'columns' => $columns,
            'children' => $sanitizedChildren,
        ];
    }

    private function sanitizeSettings(array $settings): array
    {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            $safeKey = sanitize_key((string) $key);
            if (is_scalar($value)) {
                $sanitized[$safeKey] = sanitize_text_field((string) $value);
            }
        }

        return $sanitized;
    }
}
```

- [ ] **Step 4: Load validator file in bootstrap**

Modify `bs23-form-builder.php`:

```php
require_once BS23_FORM_BUILDER_DIR . 'includes/Plugin.php';
require_once BS23_FORM_BUILDER_DIR . 'includes/PostTypes/FormPostType.php';
require_once BS23_FORM_BUILDER_DIR . 'includes/Builder/SchemaValidator.php';
```

- [ ] **Step 5: Run tests to verify they pass**

Run:

```bash
composer test -- --filter SchemaValidatorTest
```

Expected: PASS.

- [ ] **Step 6: Commit schema validator**

```bash
git add bs23-form-builder.php includes/Builder/SchemaValidator.php tests/php/SchemaValidatorTest.php
git commit -m "feat: validate builder schema"
```

---

### Task 4: REST Form Save And Load

**Files:**
- Create: `tests/php/FormRestControllerTest.php`
- Create: `includes/Rest/FormRestController.php`
- Modify: `includes/Plugin.php`
- Modify: `bs23-form-builder.php`

- [ ] **Step 1: Write failing REST tests**

Create `tests/php/FormRestControllerTest.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\PostTypes\FormPostType;
use WP_REST_Request;
use WP_UnitTestCase;

final class FormRestControllerTest extends WP_UnitTestCase
{
    public function test_unauthorized_save_is_rejected(): void
    {
        do_action('init');
        do_action('rest_api_init');

        $request = new WP_REST_Request('POST', '/bs23-form-builder/v1/forms');
        $request->set_body_params([
            'title' => 'Contact Form',
            'schema' => ['version' => 1, 'fields' => []],
        ]);

        $response = rest_do_request($request);

        $this->assertSame(401, $response->get_status());
    }

    public function test_valid_schema_creates_form(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $request = new WP_REST_Request('POST', '/bs23-form-builder/v1/forms');
        $request->set_body_params([
            'title' => 'Contact Form',
            'schema' => [
                'version' => 1,
                'fields' => [
                    ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email'],
                ],
            ],
        ]);

        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(201, $response->get_status());
        $this->assertSame('Contact Form', get_the_title($data['id']));
        $this->assertSame(FormPostType::NAME, get_post_type($data['id']));
        $this->assertSame('email', get_post_meta($data['id'], '_bs23_form_schema', true)['fields'][0]['type']);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run:

```bash
composer test -- --filter FormRestControllerTest
```

Expected: FAIL because REST routes do not exist.

- [ ] **Step 3: Implement REST controller**

Create `includes/Rest/FormRestController.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Rest;

use BS23\FormBuilder\Builder\SchemaValidator;
use BS23\FormBuilder\PostTypes\FormPostType;
use InvalidArgumentException;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

final class FormRestController
{
    public const NAMESPACE = 'bs23-form-builder/v1';
    public const META_KEY = '_bs23_form_schema';

    private SchemaValidator $validator;

    public function __construct(SchemaValidator $validator)
    {
        $this->validator = $validator;
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/forms', [
            'methods' => 'POST',
            'callback' => [$this, 'createForm'],
            'permission_callback' => [$this, 'canManageForms'],
        ]);

        register_rest_route(self::NAMESPACE, '/forms/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getForm'],
                'permission_callback' => [$this, 'canManageForms'],
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'updateForm'],
                'permission_callback' => [$this, 'canManageForms'],
            ],
        ]);
    }

    public function canManageForms(): bool
    {
        return current_user_can('manage_options');
    }

    public function createForm(WP_REST_Request $request)
    {
        $title = sanitize_text_field((string) $request->get_param('title'));
        $schema = $request->get_param('schema');

        try {
            $sanitizedSchema = $this->validator->sanitize(is_array($schema) ? $schema : []);
        } catch (InvalidArgumentException $exception) {
            return new WP_Error('bs23_invalid_schema', $exception->getMessage(), ['status' => 400]);
        }

        $postId = wp_insert_post([
            'post_type' => FormPostType::NAME,
            'post_status' => 'publish',
            'post_title' => $title ?: __('Untitled Form', 'bs23-form-builder'),
        ], true);

        if (is_wp_error($postId)) {
            return new WP_Error('bs23_save_failed', __('Could not save form.', 'bs23-form-builder'), ['status' => 500]);
        }

        update_post_meta((int) $postId, self::META_KEY, $sanitizedSchema);

        return new WP_REST_Response(['id' => (int) $postId, 'schema' => $sanitizedSchema], 201);
    }

    public function getForm(WP_REST_Request $request)
    {
        $postId = absint($request['id']);

        if (get_post_type($postId) !== FormPostType::NAME) {
            return new WP_Error('bs23_not_found', __('Form not found.', 'bs23-form-builder'), ['status' => 404]);
        }

        return new WP_REST_Response([
            'id' => $postId,
            'title' => get_the_title($postId),
            'schema' => get_post_meta($postId, self::META_KEY, true) ?: ['version' => 1, 'fields' => []],
        ], 200);
    }

    public function updateForm(WP_REST_Request $request)
    {
        $postId = absint($request['id']);

        if (get_post_type($postId) !== FormPostType::NAME) {
            return new WP_Error('bs23_not_found', __('Form not found.', 'bs23-form-builder'), ['status' => 404]);
        }

        $schema = $request->get_param('schema');

        try {
            $sanitizedSchema = $this->validator->sanitize(is_array($schema) ? $schema : []);
        } catch (InvalidArgumentException $exception) {
            return new WP_Error('bs23_invalid_schema', $exception->getMessage(), ['status' => 400]);
        }

        wp_update_post([
            'ID' => $postId,
            'post_title' => sanitize_text_field((string) $request->get_param('title')),
        ]);
        update_post_meta($postId, self::META_KEY, $sanitizedSchema);

        return new WP_REST_Response(['id' => $postId, 'schema' => $sanitizedSchema], 200);
    }
}
```

- [ ] **Step 4: Register REST service**

Modify `bs23-form-builder.php` to load the controller:

```php
require_once BS23_FORM_BUILDER_DIR . 'includes/Rest/FormRestController.php';
```

Modify `includes/Plugin.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder;

use BS23\FormBuilder\Builder\SchemaValidator;
use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\FormRestController;

final class Plugin
{
    public function register(): void
    {
        (new FormPostType())->register();
        (new FormRestController(new SchemaValidator()))->register();
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run:

```bash
composer test -- --filter FormRestControllerTest
```

Expected: PASS.

- [ ] **Step 6: Commit REST persistence**

```bash
git add bs23-form-builder.php includes/Plugin.php includes/Rest/FormRestController.php tests/php/FormRestControllerTest.php
git commit -m "feat: add form REST persistence"
```

---

### Task 5: JavaScript Schema State

**Files:**
- Create: `assets/admin/src/fields.js`
- Create: `assets/admin/src/schema.js`
- Create: `assets/admin/src/__tests__/schema.test.js`
- Create: `assets/admin/src/__tests__/builder-state.test.js`

- [ ] **Step 1: Write failing JS schema tests**

Create `assets/admin/src/__tests__/schema.test.js`:

```js
import { createField, createContainer, isAllowedFieldType } from '../schema';

test('creates an email field with stable schema properties', () => {
  const field = createField('email');

  expect(field.type).toBe('email');
  expect(field.label).toBe('Email');
  expect(field.name).toBe('email');
  expect(field.required).toBe(false);
  expect(field.settings).toEqual({});
});

test('creates a four column container', () => {
  const container = createContainer(4);

  expect(container.type).toBe('container');
  expect(container.columns).toBe(4);
  expect(container.children).toEqual([[], [], [], []]);
});

test('rejects unknown field type', () => {
  expect(isAllowedFieldType('unsafe')).toBe(false);
});
```

- [ ] **Step 2: Write failing builder state tests**

Create `assets/admin/src/__tests__/builder-state.test.js`:

```js
import { addFieldToRoot, addFieldToContainer, createContainer } from '../schema';

test('adds field to root canvas schema', () => {
  const schema = { version: 1, fields: [] };
  const next = addFieldToRoot(schema, 'email');

  expect(next.fields).toHaveLength(1);
  expect(next.fields[0].type).toBe('email');
});

test('adds field to selected container column', () => {
  const container = createContainer(3);
  const schema = { version: 1, fields: [container] };
  const next = addFieldToContainer(schema, container.id, 1, 'text');

  expect(next.fields[0].children[1]).toHaveLength(1);
  expect(next.fields[0].children[1][0].type).toBe('text');
});
```

- [ ] **Step 3: Run JS tests to verify they fail**

Run:

```bash
npm run test:js -- --runTestsByPath assets/admin/src/__tests__/schema.test.js assets/admin/src/__tests__/builder-state.test.js
```

Expected: FAIL because `schema.js` does not exist.

- [ ] **Step 4: Implement field definitions and schema helpers**

Create `assets/admin/src/fields.js`:

```js
export const FIELD_GROUPS = [
  {
    id: 'general',
    label: 'General Fields',
    fields: [
      ['name', 'Name Fields'], ['email', 'Email'], ['text', 'Simple Text'], ['mask', 'Mask Input'],
      ['textarea', 'Text Area'], ['address', 'Address Fields'], ['country', 'Country List'],
      ['number', 'Numeric Field'], ['dropdown', 'Dropdown'], ['radio', 'Radio Field'],
      ['checkbox', 'Checkbox'], ['multiple_choice', 'Multiple Choice'], ['url', 'Website URL'],
      ['datetime', 'Time & Date'], ['image_upload', 'Image Upload'], ['file_upload', 'File Upload'],
      ['html', 'Custom HTML'], ['phone', 'Phone/Mobile'],
    ],
  },
  {
    id: 'advanced',
    label: 'Advanced Fields',
    fields: [
      ['hidden', 'Hidden Field'], ['section_break', 'Section Break'], ['shortcode', 'Shortcode'],
      ['terms', 'Terms & Conditions'], ['action_hook', 'Action Hook'], ['form_step', 'Form Step'],
      ['ratings', 'Ratings'], ['checkable_grid', 'Checkable Grid'], ['gdpr', 'GDPR Agreement'],
      ['password', 'Password'], ['submit', 'Custom Submit Button'], ['range', 'Range Slider'],
      ['nps', 'Net Promoter Score'], ['dynamic', 'Dynamic Field'], ['chained_select', 'Chained Select'],
      ['color', 'Color Picker'], ['repeat', 'Repeat Field'], ['post_select', 'Post/CPT Select'],
      ['rich_text', 'Rich Text Input'], ['save_resume', 'Save & Resume'],
    ],
  },
  {
    id: 'container',
    label: 'Container',
    fields: [
      ['container_1', 'One Column Container'], ['container_2', 'Two Column Container'],
      ['container_3', 'Three Column Container'], ['container_4', 'Four Column Container'],
    ],
  },
];

export const FIELD_LABELS = FIELD_GROUPS.reduce((labels, group) => {
  group.fields.forEach(([type, label]) => {
    labels[type] = label;
  });
  return labels;
}, {});
```

Create `assets/admin/src/schema.js`:

```js
import { FIELD_LABELS } from './fields';

const allowedTypes = new Set(Object.keys(FIELD_LABELS).filter((type) => !type.startsWith('container_')));

export function isAllowedFieldType(type) {
  return allowedTypes.has(type);
}

export function createField(type) {
  if (!isAllowedFieldType(type)) {
    throw new Error(`Invalid field type: ${type}`);
  }

  return {
    id: `${type}_${Date.now()}_${Math.random().toString(16).slice(2)}`,
    type,
    label: FIELD_LABELS[type],
    name: type,
    required: false,
    settings: {},
  };
}

export function createContainer(columns) {
  if (![1, 2, 3, 4].includes(columns)) {
    throw new Error('Invalid container column count.');
  }

  return {
    id: `container_${Date.now()}_${Math.random().toString(16).slice(2)}`,
    type: 'container',
    columns,
    children: Array.from({ length: columns }, () => []),
  };
}

export function createPaletteItem(type) {
  if (type.startsWith('container_')) {
    return createContainer(Number(type.replace('container_', '')));
  }
  return createField(type);
}

export function addFieldToRoot(schema, type) {
  return {
    ...schema,
    fields: [...schema.fields, createPaletteItem(type)],
  };
}

export function addFieldToContainer(schema, containerId, columnIndex, type) {
  return {
    ...schema,
    fields: schema.fields.map((field) => {
      if (field.id !== containerId || field.type !== 'container') {
        return field;
      }

      const children = field.children.map((column, index) => {
        if (index !== columnIndex) {
          return column;
        }
        return [...column, createPaletteItem(type)];
      });

      return { ...field, children };
    }),
  };
}
```

- [ ] **Step 5: Run JS tests to verify they pass**

Run:

```bash
npm run test:js -- --runTestsByPath assets/admin/src/__tests__/schema.test.js assets/admin/src/__tests__/builder-state.test.js
```

Expected: PASS.

- [ ] **Step 6: Commit JS schema state**

```bash
git add assets/admin/src/fields.js assets/admin/src/schema.js assets/admin/src/__tests__/schema.test.js assets/admin/src/__tests__/builder-state.test.js
git commit -m "feat: add builder schema state"
```

---

### Task 6: Admin Menu And Builder Shell

**Files:**
- Create: `includes/Admin/Menu.php`
- Modify: `includes/Plugin.php`
- Modify: `bs23-form-builder.php`
- Create: `assets/admin/src/index.js`
- Create: `assets/admin/src/app.js`
- Create: `assets/admin/src/styles.scss`

- [ ] **Step 1: Write failing PHP menu test**

Create `tests/php/AdminMenuTest.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use WP_UnitTestCase;

final class AdminMenuTest extends WP_UnitTestCase
{
    public function test_admin_menu_class_registers_menu_hook(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('admin_menu');

        global $menu;

        $labels = array_map(static fn ($item) => $item[0] ?? '', $menu);

        $this->assertContains('BS23 Forms', $labels);
    }
}
```

- [ ] **Step 2: Run menu test to verify it fails**

Run:

```bash
composer test -- --filter AdminMenuTest
```

Expected: FAIL because admin menu is not registered.

- [ ] **Step 3: Implement admin menu and builder shell**

Create `includes/Admin/Menu.php`:

```php
<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Admin;

final class Menu
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            __('BS23 Forms', 'bs23-form-builder'),
            __('BS23 Forms', 'bs23-form-builder'),
            'manage_options',
            'bs23-form-builder',
            [$this, 'renderBuilderPage'],
            'dashicons-feedback',
            56
        );
    }

    public function renderBuilderPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'bs23-form-builder'));
        }

        echo '<div id="bs23-form-builder-root">';
        echo '<p>' . esc_html__('Loading BS23 Form Builder...', 'bs23-form-builder') . '</p>';
        echo '</div>';
    }

    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'toplevel_page_bs23-form-builder') {
            return;
        }

        $assetFile = BS23_FORM_BUILDER_DIR . 'assets/admin/build/index.asset.php';
        $asset = file_exists($assetFile) ? require $assetFile : ['dependencies' => [], 'version' => BS23_FORM_BUILDER_VERSION];

        wp_enqueue_script(
            'bs23-form-builder-admin',
            BS23_FORM_BUILDER_URL . 'assets/admin/build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
        wp_enqueue_style(
            'bs23-form-builder-admin',
            BS23_FORM_BUILDER_URL . 'assets/admin/build/style-index.css',
            [],
            $asset['version']
        );
        wp_localize_script('bs23-form-builder-admin', 'bs23FormBuilder', [
            'restUrl' => esc_url_raw(rest_url('bs23-form-builder/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
}
```

Modify `bs23-form-builder.php`:

```php
require_once BS23_FORM_BUILDER_DIR . 'includes/Admin/Menu.php';
```

Modify `includes/Plugin.php`:

```php
use BS23\FormBuilder\Admin\Menu;

// inside register():
(new Menu())->register();
```

- [ ] **Step 4: Add minimal React shell**

Create `assets/admin/src/index.js`:

```js
import { createRoot } from '@wordpress/element';
import App from './app';
import './styles.scss';

const root = document.getElementById('bs23-form-builder-root');

if (root) {
  createRoot(root).render(<App />);
}
```

Create `assets/admin/src/app.js`:

```js
export default function App() {
  return (
    <div className="bs23-builder">
      <header className="bs23-builder__header">
        <input className="bs23-builder__title" defaultValue="Untitled Form" aria-label="Form title" />
        <button className="button button-primary" type="button">Save Form</button>
      </header>
      <main className="bs23-builder__workspace">
        <section className="bs23-builder__canvas">Drop fields here</section>
        <aside className="bs23-builder__palette">Fields</aside>
      </main>
    </div>
  );
}
```

Create `assets/admin/src/styles.scss`:

```scss
.bs23-builder {
  margin: 0 0 0 -20px;
  min-height: calc(100vh - 32px);
  background: #f6f7fb;
  color: #111827;
}

.bs23-builder__header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #ffffff;
  border-bottom: 1px solid #d7dde8;
}

.bs23-builder__title {
  flex: 1;
  max-width: 520px;
  font-size: 20px;
  font-weight: 600;
}

.bs23-builder__workspace {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 390px;
  gap: 18px;
  padding: 18px;
}

.bs23-builder__canvas,
.bs23-builder__palette {
  min-height: 640px;
  background: #ffffff;
  border: 1px solid #d7dde8;
  border-radius: 8px;
}
```

- [ ] **Step 5: Run tests and build**

Run:

```bash
composer test -- --filter AdminMenuTest
npm run build
```

Expected: PHP test PASS and build creates `assets/admin/build/index.js`.

- [ ] **Step 6: Commit admin shell**

```bash
git add bs23-form-builder.php includes/Plugin.php includes/Admin/Menu.php tests/php/AdminMenuTest.php assets/admin/src/index.js assets/admin/src/app.js assets/admin/src/styles.scss assets/admin/build
git commit -m "feat: add admin builder shell"
```

---

### Task 7: Palette, Canvas, Containers, And Save Flow

**Files:**
- Create: `assets/admin/src/components/Palette.js`
- Create: `assets/admin/src/components/Canvas.js`
- Create: `assets/admin/src/components/FieldCard.js`
- Create: `assets/admin/src/components/ContainerField.js`
- Create: `assets/admin/src/components/SaveBar.js`
- Modify: `assets/admin/src/app.js`
- Modify: `assets/admin/src/styles.scss`

- [ ] **Step 1: Write failing component behavior test**

Create `assets/admin/src/__tests__/app.test.js`:

```js
import { render, screen, fireEvent } from '@testing-library/react';
import App from '../app';

test('renders palette groups and adds email field to canvas', () => {
  render(<App />);

  expect(screen.getByText('General Fields')).not.toBeNull();

  fireEvent.dragStart(screen.getByText('Email'), {
    dataTransfer: { setData: jest.fn() },
  });
  fireEvent.drop(screen.getByLabelText('Form canvas'), {
    dataTransfer: { getData: () => 'email' },
  });

  expect(screen.getAllByText('Email')).toHaveLength(2);
});
```

- [ ] **Step 2: Run component test to verify it fails**

Run:

```bash
npm run test:js -- --runTestsByPath assets/admin/src/__tests__/app.test.js
```

Expected: FAIL because palette/canvas behavior is not implemented.

- [ ] **Step 3: Implement components**

Create `assets/admin/src/components/Palette.js`:

```js
import { FIELD_GROUPS } from '../fields';

export default function Palette() {
  return (
    <aside className="bs23-palette">
      {FIELD_GROUPS.map((group) => (
        <section className="bs23-palette__group" key={group.id}>
          <h2>{group.label}</h2>
          <div className="bs23-palette__grid">
            {group.fields.map(([type, label]) => (
              <button
                className="bs23-palette__item"
                draggable
                key={type}
                type="button"
                onDragStart={(event) => event.dataTransfer.setData('text/plain', type)}
              >
                <span className="dashicons dashicons-plus-alt2" aria-hidden="true" />
                {label}
              </button>
            ))}
          </div>
        </section>
      ))}
    </aside>
  );
}
```

Create `assets/admin/src/components/FieldCard.js`:

```js
export default function FieldCard({ field }) {
  return (
    <div className="bs23-field-card">
      <strong>{field.label}</strong>
      <span>{field.type}</span>
    </div>
  );
}
```

Create `assets/admin/src/components/ContainerField.js`:

```js
import FieldCard from './FieldCard';

export default function ContainerField({ field, onDropField }) {
  return (
    <div className="bs23-container-field">
      {field.children.map((column, index) => (
        <div
          aria-label={`Column ${index + 1}`}
          className="bs23-container-field__column"
          key={index}
          onDragOver={(event) => event.preventDefault()}
          onDrop={(event) => {
            event.preventDefault();
            onDropField(field.id, index, event.dataTransfer.getData('text/plain'));
          }}
        >
          {column.map((child) => <FieldCard field={child} key={child.id} />)}
        </div>
      ))}
    </div>
  );
}
```

Create `assets/admin/src/components/Canvas.js`:

```js
import FieldCard from './FieldCard';
import ContainerField from './ContainerField';

export default function Canvas({ schema, onDropRoot, onDropContainer }) {
  return (
    <section
      aria-label="Form canvas"
      className="bs23-canvas"
      onDragOver={(event) => event.preventDefault()}
      onDrop={(event) => {
        event.preventDefault();
        onDropRoot(event.dataTransfer.getData('text/plain'));
      }}
    >
      {schema.fields.length === 0 && <p className="bs23-canvas__empty">Drop fields here</p>}
      {schema.fields.map((field) => (
        field.type === 'container'
          ? <ContainerField field={field} key={field.id} onDropField={onDropContainer} />
          : <FieldCard field={field} key={field.id} />
      ))}
    </section>
  );
}
```

Create `assets/admin/src/components/SaveBar.js`:

```js
export default function SaveBar({ title, onTitleChange, onSave, status }) {
  return (
    <header className="bs23-builder__header">
      <input
        className="bs23-builder__title"
        value={title}
        aria-label="Form title"
        onChange={(event) => onTitleChange(event.target.value)}
      />
      <button className="button button-primary" type="button" onClick={onSave}>Save Form</button>
      <span className="bs23-builder__status">{status}</span>
    </header>
  );
}
```

- [ ] **Step 4: Wire app state and REST save**

Modify `assets/admin/src/app.js`:

```js
import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import Canvas from './components/Canvas';
import Palette from './components/Palette';
import SaveBar from './components/SaveBar';
import { addFieldToContainer, addFieldToRoot } from './schema';

export default function App() {
  const [title, setTitle] = useState('Untitled Form');
  const [schema, setSchema] = useState({ version: 1, fields: [] });
  const [formId, setFormId] = useState(null);
  const [status, setStatus] = useState('');

  const saveForm = async () => {
    setStatus('Saving...');
    try {
      const response = await apiFetch({
        path: formId ? `/bs23-form-builder/v1/forms/${formId}` : '/bs23-form-builder/v1/forms',
        method: formId ? 'PUT' : 'POST',
        data: { title, schema },
      });
      setFormId(response.id);
      setStatus('Saved');
    } catch (error) {
      setStatus(error.message || 'Save failed');
    }
  };

  return (
    <div className="bs23-builder">
      <SaveBar title={title} onTitleChange={setTitle} onSave={saveForm} status={status} />
      <main className="bs23-builder__workspace">
        <Canvas
          schema={schema}
          onDropRoot={(type) => setSchema((current) => addFieldToRoot(current, type))}
          onDropContainer={(containerId, columnIndex, type) => {
            setSchema((current) => addFieldToContainer(current, containerId, columnIndex, type));
          }}
        />
        <Palette />
      </main>
    </div>
  );
}
```

- [ ] **Step 5: Extend styling**

Append to `assets/admin/src/styles.scss`:

```scss
.bs23-palette {
  height: calc(100vh - 92px);
  overflow: auto;
}

.bs23-palette__group {
  margin: 0 0 14px;
  padding: 18px;
  background: #ffffff;
  border: 1px solid #d7dde8;
  border-radius: 8px;
}

.bs23-palette__group h2 {
  margin: 0 0 14px;
  font-size: 16px;
}

.bs23-palette__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.bs23-palette__item {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 52px;
  padding: 10px 12px;
  border: 1px solid #cfd7e6;
  border-radius: 8px;
  background: #ffffff;
  color: #111827;
  font-weight: 600;
  cursor: grab;
}

.bs23-canvas {
  min-height: 640px;
  padding: 18px;
  background: #ffffff;
  border: 1px solid #d7dde8;
  border-radius: 8px;
}

.bs23-canvas__empty {
  display: grid;
  min-height: 220px;
  place-items: center;
  color: #64748b;
  border: 1px dashed #aab6ca;
  border-radius: 8px;
}

.bs23-field-card {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 10px;
  padding: 14px;
  border: 1px solid #d7dde8;
  border-radius: 8px;
  background: #f8fafc;
}

.bs23-container-field {
  display: grid;
  gap: 10px;
  margin-bottom: 10px;
}

.bs23-container-field__column {
  min-height: 90px;
  padding: 10px;
  border: 1px dashed #9aa8bd;
  border-radius: 8px;
  background: #fbfdff;
}
```

- [ ] **Step 6: Run tests and build**

Run:

```bash
npm run test:js
npm run build
```

Expected: JS tests PASS and production build succeeds.

- [ ] **Step 7: Commit builder UI**

```bash
git add assets/admin/src assets/admin/build
git commit -m "feat: add drag and drop builder UI"
```

---

### Task 8: Final Verification And Push

**Files:**
- Modify only files required by verification fixes.

- [ ] **Step 1: Run PHP tests**

Run:

```bash
composer test
```

Expected: all PHP tests PASS.

- [ ] **Step 2: Run JS tests**

Run:

```bash
npm run test:js
```

Expected: all JS tests PASS.

- [ ] **Step 3: Run build**

Run:

```bash
npm run build
```

Expected: build exits successfully and writes admin assets.

- [ ] **Step 4: Check git status**

Run:

```bash
git status --short
```

Expected: no uncommitted changes except intentionally generated dependency files already committed in earlier tasks.

- [ ] **Step 5: Push implementation branch**

Run:

```bash
git push origin main
```

Expected: `main` pushes to `origin/main`.
