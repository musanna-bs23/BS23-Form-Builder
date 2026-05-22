<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Notifications\TemplateRenderer;
use WP_UnitTestCase;

final class TemplateRendererTest extends WP_UnitTestCase
{
    public function test_template_renderer_expands_tags(): void
    {
        $formId = self::factory()->post->create(['post_title' => 'Contact']);

        $message = (new TemplateRenderer())->render(
            '{form_title} #{entry_id} {field:email} {all_fields}',
            (int) $formId,
            44,
            ['email' => 'person@example.com']
        );

        $this->assertStringContainsString('Contact', $message);
        $this->assertStringContainsString('44', $message);
        $this->assertStringContainsString('person@example.com', $message);
        $this->assertStringContainsString('email:', $message);
    }
}
