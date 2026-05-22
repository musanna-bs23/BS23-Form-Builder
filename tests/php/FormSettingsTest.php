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
        ]);

        $this->assertFalse($sanitized['notification']['enabled']);
        $this->assertSame('{admin_email}', $sanitized['notification']['to']);
        $this->assertSame('Hello', $sanitized['notification']['subject']);
        $this->assertSame('emailfield', $sanitized['notification']['reply_to']);
        $this->assertSame('https://example.com/thanks', $sanitized['confirmation']['redirect_url']);
    }
}
