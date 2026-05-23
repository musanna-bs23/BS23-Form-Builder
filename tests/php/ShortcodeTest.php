<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\FormRestController;
use BS23\FormBuilder\Settings\FormSettings;
use WP_UnitTestCase;

final class ShortcodeTest extends WP_UnitTestCase
{
    public function test_invalid_form_shortcode_returns_empty_string(): void
    {
        $this->assertSame('', do_shortcode('[bs23_form id="999999"]'));
    }

    public function test_shortcode_renders_saved_form(): void
    {
        do_action('init');

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
        update_post_meta($formId, FormSettings::META_KEY, [
            'style' => [
                'button_background' => '#0f766e',
            ],
        ]);

        $html = do_shortcode('[bs23_form id="' . $formId . '"]');

        $this->assertStringContainsString('bs23-form', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('--bs23-button-background:#0f766e', $html);
    }
}
