<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Rest;

use BS23\FormBuilder\Builder\SchemaValidator;
use BS23\FormBuilder\PostTypes\FormPostType;
use InvalidArgumentException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class FormRestController
{
    public const NAMESPACE = 'bs23-form-builder/v1';
    public const META_KEY = '_bs23_form_schema';

    private SchemaValidator $schemaValidator;

    public function __construct(SchemaValidator $schemaValidator)
    {
        $this->schemaValidator = $schemaValidator;
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/forms', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'listForms'],
                'permission_callback' => [$this, 'canManageForms'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'createForm'],
                'permission_callback' => [$this, 'canManageForms'],
            ],
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

    public function listForms(): WP_REST_Response
    {
        $forms = get_posts([
            'post_type' => FormPostType::NAME,
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
            'no_found_rows' => true,
        ]);

        return new WP_REST_Response(array_map(
            fn ($form): array => $this->prepareFormListItem((int) $form->ID),
            $forms
        ), 200);
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function createForm(WP_REST_Request $request)
    {
        $title = $this->sanitizeTitle($request->get_param('title'), 'Untitled Form');

        if (is_wp_error($title)) {
            return $title;
        }

        $schema = $this->sanitizeSchema($request->get_param('schema'));

        if (is_wp_error($schema)) {
            return $schema;
        }

        $formId = wp_insert_post([
            'post_title' => $title,
            'post_type' => FormPostType::NAME,
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($formId)) {
            return new WP_Error(
                'bs23_form_create_failed',
                __('Unable to create form.', 'bs23-form-builder'),
                ['status' => 500]
            );
        }

        update_post_meta($formId, self::META_KEY, $schema);

        return new WP_REST_Response($this->prepareFormResponse((int) $formId), 201);
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function getForm(WP_REST_Request $request)
    {
        $form = $this->getFormPost((int) $request['id']);

        if (is_wp_error($form)) {
            return $form;
        }

        return new WP_REST_Response($this->prepareFormResponse((int) $form->ID), 200);
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function updateForm(WP_REST_Request $request)
    {
        $form = $this->getFormPost((int) $request['id']);

        if (is_wp_error($form)) {
            return $form;
        }

        $title = $this->sanitizeTitle($request->get_param('title'), $form->post_title ?: 'Untitled Form');

        if (is_wp_error($title)) {
            return $title;
        }

        $schema = $this->sanitizeSchema($request->get_param('schema'));

        if (is_wp_error($schema)) {
            return $schema;
        }

        $result = wp_update_post([
            'ID' => (int) $form->ID,
            'post_title' => $title,
        ], true);

        if (is_wp_error($result)) {
            return new WP_Error(
                'bs23_form_update_failed',
                __('Unable to update form.', 'bs23-form-builder'),
                ['status' => 500]
            );
        }

        update_post_meta((int) $form->ID, self::META_KEY, $schema);

        return new WP_REST_Response($this->prepareFormResponse((int) $form->ID), 200);
    }

    /**
     * @param mixed $schema
     *
     * @return array|WP_Error
     */
    private function sanitizeSchema($schema)
    {
        if (! is_array($schema)) {
            return new WP_Error(
                'bs23_form_invalid_schema',
                __('Schema must be an object.', 'bs23-form-builder'),
                ['status' => 400]
            );
        }

        try {
            return $this->schemaValidator->sanitize($schema);
        } catch (InvalidArgumentException $exception) {
            return new WP_Error(
                'bs23_form_invalid_schema',
                $exception->getMessage(),
                ['status' => 400]
            );
        }
    }

    /**
     * @param mixed $title
     *
     * @return string|WP_Error
     */
    private function sanitizeTitle($title, string $fallback)
    {
        if (is_array($title) || is_object($title)) {
            return new WP_Error(
                'bs23_invalid_title',
                __('Form title must be a string.', 'bs23-form-builder'),
                ['status' => 400]
            );
        }

        $sanitizedTitle = sanitize_text_field(is_scalar($title) ? (string) $title : '');

        if ($sanitizedTitle === '') {
            return $fallback;
        }

        return $sanitizedTitle;
    }

    /**
     * @return \WP_Post|WP_Error
     */
    private function getFormPost(int $formId)
    {
        $form = get_post($formId);

        if (! $form || $form->post_type !== FormPostType::NAME) {
            return new WP_Error(
                'bs23_form_not_found',
                __('Form not found.', 'bs23-form-builder'),
                ['status' => 404]
            );
        }

        return $form;
    }

    private function prepareFormResponse(int $formId): array
    {
        return [
            'id' => $formId,
            'title' => get_the_title($formId),
            'schema' => get_post_meta($formId, self::META_KEY, true),
        ];
    }

    private function prepareFormListItem(int $formId): array
    {
        $schema = get_post_meta($formId, self::META_KEY, true);
        $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];

        return [
            'id' => $formId,
            'title' => get_the_title($formId),
            'field_count' => $this->fieldCount($fields),
            'updated_at' => get_post_modified_time('c', false, $formId),
        ];
    }

    private function fieldCount(array $fields): int
    {
        $count = 0;
        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }
            if (($field['type'] ?? '') === 'container') {
                foreach (($field['children'] ?? []) as $column) {
                    $count += is_array($column) ? $this->fieldCount($column) : 0;
                }
                continue;
            }
            $count++;
        }

        return $count;
    }
}
