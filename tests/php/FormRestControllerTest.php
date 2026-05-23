<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\PostTypes\FormPostType;
use WP_REST_Request;
use WP_UnitTestCase;

final class FormRestControllerTest extends WP_UnitTestCase
{
    public function tear_down(): void
    {
        wp_set_current_user(0);

        parent::tear_down();
    }

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

    public function test_create_rejects_array_title(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $request = new WP_REST_Request('POST', '/bs23-form-builder/v1/forms');
        $request->set_body_params([
            'title' => ['Contact Form'],
            'schema' => ['version' => 1, 'fields' => []],
        ]);

        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(400, $response->get_status());
        $this->assertSame('bs23_invalid_title', $data['code']);
    }

    public function test_invalid_schema_returns_bad_request(): void
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
                    ['id' => 'field_1', 'type' => 'unsafe', 'label' => 'Unsafe', 'name' => 'unsafe'],
                ],
            ],
        ]);

        $response = rest_do_request($request);

        $this->assertSame(400, $response->get_status());
    }

    public function test_get_returns_saved_form_schema(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $formId = $this->createFormPost('Contact Form', [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email'],
            ],
        ]);

        $request = new WP_REST_Request('GET', sprintf('/bs23-form-builder/v1/forms/%d', $formId));
        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertSame($formId, $data['id']);
        $this->assertSame('Contact Form', $data['title']);
        $this->assertSame('email', $data['schema']['fields'][0]['type']);
    }

    public function test_list_returns_saved_forms_for_builder_sidebar(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $contactId = $this->createFormPost('Contact Form', [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email'],
                ['id' => 'field_2', 'type' => 'text', 'label' => 'Name', 'name' => 'name'],
            ],
        ]);
        $quoteId = $this->createFormPost('Quote Form', [
            'version' => 1,
            'fields' => [],
        ]);

        $request = new WP_REST_Request('GET', '/bs23-form-builder/v1/forms');
        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertSame([$quoteId, $contactId], array_column($data, 'id'));
        $this->assertSame('Quote Form', $data[0]['title']);
        $this->assertSame(0, $data[0]['field_count']);
        $this->assertSame(2, $data[1]['field_count']);
        $this->assertSame('[bs23_form id="' . $quoteId . '"]', $data[0]['shortcode']);
        $this->assertSame('publish', $data[0]['status']);
        $this->assertArrayHasKey('entries_count', $data[0]);
        $this->assertArrayHasKey('entries_this_month', $data[0]);
        $this->assertArrayHasKey('entries_today', $data[0]);
        $this->assertArrayHasKey('created_at', $data[0]);
    }

    public function test_delete_removes_form(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $formId = $this->createFormPost('Contact Form', [
            'version' => 1,
            'fields' => [],
        ]);

        $request = new WP_REST_Request('DELETE', sprintf('/bs23-form-builder/v1/forms/%d', $formId));
        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertTrue($data['deleted']);
        $this->assertNull(get_post($formId));
    }

    public function test_put_updates_title_and_schema(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $formId = $this->createFormPost('Contact Form', [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email'],
            ],
        ]);

        $request = new WP_REST_Request('PUT', sprintf('/bs23-form-builder/v1/forms/%d', $formId));
        $request->set_body_params([
            'title' => 'Updated Contact Form',
            'schema' => [
                'version' => 1,
                'fields' => [
                    ['id' => 'field_2', 'type' => 'text', 'label' => 'Name', 'name' => 'name'],
                ],
            ],
        ]);

        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertSame($formId, $data['id']);
        $this->assertSame('Updated Contact Form', get_the_title($formId));
        $this->assertSame('text', get_post_meta($formId, '_bs23_form_schema', true)['fields'][0]['type']);
    }

    public function test_put_rejects_array_title_and_preserves_existing_title(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $formId = $this->createFormPost('Contact Form', [
            'version' => 1,
            'fields' => [],
        ]);

        $request = new WP_REST_Request('PUT', sprintf('/bs23-form-builder/v1/forms/%d', $formId));
        $request->set_body_params([
            'title' => ['Updated Contact Form'],
            'schema' => ['version' => 1, 'fields' => []],
        ]);

        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame(400, $response->get_status());
        $this->assertSame('bs23_invalid_title', $data['code']);
        $this->assertSame('Contact Form', get_the_title($formId));
    }

    public function test_get_for_non_form_id_returns_not_found(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $postId = self::factory()->post->create(['post_type' => 'post']);

        $request = new WP_REST_Request('GET', sprintf('/bs23-form-builder/v1/forms/%d', $postId));
        $response = rest_do_request($request);

        $this->assertSame(404, $response->get_status());
    }

    public function test_put_for_non_form_id_returns_not_found(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('init');
        do_action('rest_api_init');

        $postId = self::factory()->post->create(['post_type' => 'post']);

        $request = new WP_REST_Request('PUT', sprintf('/bs23-form-builder/v1/forms/%d', $postId));
        $request->set_body_params([
            'title' => 'Updated Contact Form',
            'schema' => ['version' => 1, 'fields' => []],
        ]);

        $response = rest_do_request($request);

        $this->assertSame(404, $response->get_status());
    }

    public function test_subscriber_cannot_create_forms(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        do_action('init');
        do_action('rest_api_init');

        $request = new WP_REST_Request('POST', '/bs23-form-builder/v1/forms');
        $request->set_body_params([
            'title' => 'Contact Form',
            'schema' => ['version' => 1, 'fields' => []],
        ]);

        $response = rest_do_request($request);

        $this->assertSame(403, $response->get_status());
    }

    private function createFormPost(string $title, array $schema): int
    {
        $formId = self::factory()->post->create([
            'post_title' => $title,
            'post_type' => FormPostType::NAME,
            'post_status' => 'publish',
        ]);

        update_post_meta($formId, '_bs23_form_schema', $schema);

        return $formId;
    }
}
