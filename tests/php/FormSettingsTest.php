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
            'security' => [
                'enabled' => 'true',
                'honeypot' => 'true',
                'minimum_time' => '4',
                'rate_limit_count' => '7',
                'rate_limit_window' => '600',
                'unsafe' => 'bad',
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
        $this->assertTrue($sanitized['security']['enabled']);
        $this->assertTrue($sanitized['security']['honeypot']);
        $this->assertSame(4, $sanitized['security']['minimum_time']);
        $this->assertSame(7, $sanitized['security']['rate_limit_count']);
        $this->assertSame(600, $sanitized['security']['rate_limit_window']);
        $this->assertArrayNotHasKey('unsafe', $sanitized['security']);
    }

    public function test_security_settings_fall_back_to_safe_ranges(): void
    {
        $settings = new FormSettings();
        $sanitized = $settings->sanitize([
            'security' => [
                'enabled' => 'false',
                'honeypot' => 'false',
                'minimum_time' => '-1',
                'rate_limit_count' => '0',
                'rate_limit_window' => '999999',
            ],
        ]);

        $this->assertFalse($sanitized['security']['enabled']);
        $this->assertFalse($sanitized['security']['honeypot']);
        $this->assertSame(3, $sanitized['security']['minimum_time']);
        $this->assertSame(5, $sanitized['security']['rate_limit_count']);
        $this->assertSame(300, $sanitized['security']['rate_limit_window']);
    }
}
