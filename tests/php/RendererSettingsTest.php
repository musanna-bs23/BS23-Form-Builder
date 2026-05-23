<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Frontend\Renderer;
use BS23\FormBuilder\Submission\EntryRepository;
use BS23\FormBuilder\Submission\SubmissionHandler;
use BS23\FormBuilder\Validation\SubmissionValidator;
use WP_UnitTestCase;

final class RendererSettingsTest extends WP_UnitTestCase
{
    public function test_renderer_outputs_common_field_settings(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'text',
                    'label' => 'Name',
                    'name' => 'name',
                    'settings' => [
                        'placeholder' => 'Your name',
                        'default' => 'Hasan',
                        'help' => 'Use your full name.',
                        'className' => 'lead-field',
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('placeholder="Your name"', $html);
        $this->assertStringContainsString('value="Hasan"', $html);
        $this->assertStringContainsString('Use your full name.', $html);
        $this->assertStringContainsString('lead-field', $html);
    }

    public function test_renderer_outputs_choices_html_and_section_description(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'dropdown', 'label' => 'Team', 'name' => 'team', 'settings' => ['choices' => ['Sales', 'Support']]],
                ['id' => 'field_2', 'type' => 'html', 'label' => 'HTML', 'name' => 'html', 'settings' => ['content' => '<strong>Intro</strong><script>bad</script>']],
                ['id' => 'field_3', 'type' => 'section_break', 'label' => 'Next', 'name' => 'section', 'settings' => ['description' => 'More details']],
            ],
        ]);

        $this->assertStringContainsString('Sales', $html);
        $this->assertStringContainsString('<strong>Intro</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('More details', $html);
    }

    public function test_renderer_outputs_frontend_style_variables(): void
    {
        $html = $this->renderer()->render(
            123,
            [
                'version' => 1,
                'fields' => [
                    ['id' => 'field_1', 'type' => 'text', 'label' => 'Name', 'name' => 'name'],
                ],
            ],
            [
                'style' => [
                    'max_width' => '900px',
                    'field_gap' => '20px',
                    'label_color' => '#111827',
                    'button_background' => '#0f766e',
                ],
            ]
        );

        $this->assertStringContainsString('--bs23-form-max-width:900px', $html);
        $this->assertStringContainsString('--bs23-field-gap:20px', $html);
        $this->assertStringContainsString('--bs23-label-color:#111827', $html);
        $this->assertStringContainsString('--bs23-button-background:#0f766e', $html);
    }

    public function test_renderer_ignores_invalid_frontend_style_values(): void
    {
        $html = $this->renderer()->render(
            123,
            ['version' => 1, 'fields' => [['id' => 'field_1', 'type' => 'text', 'label' => 'Name', 'name' => 'name']]],
            [
                'style' => [
                    'max_width' => 'calc(100vw)',
                    'button_background' => 'red;background:url(bad)',
                    'label_color' => '#123456',
                ],
            ]
        );

        $this->assertStringNotContainsString('calc(100vw)', $html);
        $this->assertStringNotContainsString('background:url', $html);
        $this->assertStringContainsString('--bs23-label-color:#123456', $html);
    }

    private function renderer(): Renderer
    {
        return new Renderer(new SubmissionHandler(new SubmissionValidator(), new EntryRepository()));
    }
}
