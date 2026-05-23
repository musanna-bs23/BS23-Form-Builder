<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Settings\FormSettings;
use WP_UnitTestCase;

final class FormSettingsTest extends WP_UnitTestCase
{
    public function test_defaults_and_sanitization(): void
    {
        $settings = new FormSettings();
        $sanitized = $settings->sanitize([
            'notification' => [
                'enabled' => 'false',
                'to' => 'not-email',
                'subject' => '<b>Hello</b>',
                'message' => "<script>bad</script>\nMessage",
                'reply_to' => 'Email Field',
            ],
            'confirmation' => [
                'message' => '<b>Thanks</b>',
                'redirect_url' => 'https://example.com/thanks',
            ],
            'style' => [
                'max_width' => '900px',
                'field_gap' => '20px',
                'label_color' => '#111827',
                'label_size' => '15px',
                'input_background' => '#ffffff',
                'input_border' => '#cbd5e1',
                'input_radius' => '10px',
                'button_background' => '#2563eb',
                'button_text' => '#ffffff',
                'button_radius' => '12px',
                'error_color' => '#dc2626',
                'success_color' => '#16a34a',
                'step_active' => '#2563eb',
                'unsafe' => 'url(javascript:bad)',
            ],
        ]);

        $this->assertFalse($sanitized['notification']['enabled']);
        $this->assertSame('{admin_email}', $sanitized['notification']['to']);
        $this->assertSame('Hello', $sanitized['notification']['subject']);
        $this->assertSame('emailfield', $sanitized['notification']['reply_to']);
        $this->assertSame('https://example.com/thanks', $sanitized['confirmation']['redirect_url']);
        $this->assertSame('900px', $sanitized['style']['max_width']);
        $this->assertSame('#111827', $sanitized['style']['label_color']);
        $this->assertArrayNotHasKey('unsafe', $sanitized['style']);
    }
}
