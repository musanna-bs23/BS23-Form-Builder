<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Frontend\Renderer;
use BS23\FormBuilder\Submission\EntryRepository;
use BS23\FormBuilder\Submission\SubmissionHandler;
use BS23\FormBuilder\Validation\SubmissionValidator;
use WP_UnitTestCase;

final class RendererTest extends WP_UnitTestCase
{
    public function test_renderer_outputs_text_email_and_default_submit(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'text', 'label' => 'Full Name', 'name' => 'full_name', 'required' => true],
                ['id' => 'field_2', 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => true],
            ],
        ]);

        $this->assertStringContainsString('name="full_name"', $html);
        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }

    public function test_renderer_outputs_container_and_section_break(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'container_1',
                    'type' => 'container',
                    'columns' => 2,
                    'children' => [
                        [['id' => 'field_1', 'type' => 'text', 'label' => 'First', 'name' => 'first']],
                        [['id' => 'field_2', 'type' => 'section_break', 'label' => 'Next']],
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('bs23-form__row--2', $html);
        $this->assertStringContainsString('Next', $html);
    }

    public function test_renderer_skips_hidden_conditional_field(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'text', 'label' => 'Department', 'name' => 'department', 'settings' => ['default' => 'Support']],
                [
                    'id' => 'field_2',
                    'type' => 'email',
                    'label' => 'Sales Email',
                    'name' => 'sales_email',
                    'required' => true,
                    'settings' => [
                        'conditionalLogic' => [
                            'enabled' => true,
                            'action' => 'show',
                            'match' => 'all',
                            'rules' => [
                                ['field' => 'department', 'operator' => 'equals', 'value' => 'Sales'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('data-bs23-field-id="field_2"', $html);
        $this->assertStringContainsString('class="bs23-form__schema"', $html);
        $this->assertStringContainsString('sales_email', $html);
    }

    private function renderer(): Renderer
    {
        return new Renderer(new SubmissionHandler(new SubmissionValidator(), new EntryRepository()));
    }
}
