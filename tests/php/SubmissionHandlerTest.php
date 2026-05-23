<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Install\Installer;
use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\FormRestController;
use BS23\FormBuilder\Security\AntiSpamGuard;
use WP_UnitTestCase;

final class SubmissionHandlerTest extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        do_action('init');
        Installer::activate();
    }

    public function test_submission_without_nonce_is_rejected(): void
    {
        $formId = $this->createForm();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['bs23_form_submit' => '1', 'bs23_form_id' => (string) $formId, 'email' => 'person@example.com'];

        do_action('init');

        $html = do_shortcode('[bs23_form id="' . $formId . '"]');
        $this->assertStringContainsString('Form security check failed.', $html);
    }

    public function test_valid_submission_is_stored(): void
    {
        global $wpdb;

        $formId = $this->createForm();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $timestamp = time() - 10;
        $_POST = [
            'bs23_form_submit' => '1',
            'bs23_form_id' => (string) $formId,
            '_wpnonce' => wp_create_nonce('bs23_form_submit_' . $formId),
            AntiSpamGuard::RENDERED_AT_FIELD => (string) $timestamp,
            AntiSpamGuard::TOKEN_FIELD => AntiSpamGuard::tokenFor($formId, $timestamp),
            AntiSpamGuard::HONEYPOT_FIELD => '',
            'email' => 'person@example.com',
        ];

        do_action('init');

        $count = (int) $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM ' . Installer::entriesTableName() . ' WHERE form_id = %d',
            $formId
        ));

        $this->assertSame(1, $count);
    }

    private function createForm(): int
    {
        $formId = self::factory()->post->create([
            'post_type' => FormPostType::NAME,
            'post_title' => 'Contact',
            'post_status' => 'publish',
        ]);

        update_post_meta($formId, FormRestController::META_KEY, [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => true],
            ],
        ]);

        return (int) $formId;
    }
}
