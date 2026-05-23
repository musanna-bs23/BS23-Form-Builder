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

    public function test_renderer_outputs_form_step_markers(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                ['id' => 'step_1', 'type' => 'form_step', 'label' => 'Contact', 'name' => 'step'],
                ['id' => 'field_1', 'type' => 'text', 'label' => 'Name', 'name' => 'name'],
            ],
        ]);

        $this->assertStringContainsString('data-bs23-step-marker', $html);
        $this->assertStringContainsString('Contact', $html);
    }

    public function test_renderer_outputs_upload_fields_and_multipart_encoding(): void
    {
        $html = $this->renderer()->render(123, [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'file_upload',
                    'label' => 'Resume',
                    'name' => 'resume',
                    'required' => true,
                    'settings' => ['validation' => ['allowedExtensions' => 'pdf,docx']],
                ],
                [
                    'id' => 'field_2',
                    'type' => 'image_upload',
                    'label' => 'Avatar',
                    'name' => 'avatar',
                    'settings' => ['validation' => ['allowedExtensions' => 'jpg,png']],
                ],
            ],
        ]);

        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="resume"', $html);
        $this->assertStringContainsString('accept=".pdf,.docx"', $html);
        $this->assertStringContainsString('accept=".jpg,.png"', $html);
    }

    private function renderer(): Renderer
    {
        return new Renderer(new SubmissionHandler(new SubmissionValidator(), new EntryRepository()));
    }
}
