<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Validation\SubmissionValidator;
use WP_UnitTestCase;

final class SubmissionValidatorTest extends WP_UnitTestCase
{
    public function test_required_field_returns_error(): void
    {
        $result = (new SubmissionValidator())->validate($this->schema(), ['email' => '']);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_invalid_email_returns_error(): void
    {
        $result = (new SubmissionValidator())->validate($this->schema(), ['email' => 'bad']);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_valid_values_are_sanitized(): void
    {
        $result = (new SubmissionValidator())->validate($this->schema(), [
            'email' => 'person@example.com',
            'website' => 'https://example.com/?a=1',
            'message' => '<b>Hello</b>',
            'topics' => ['News<script>', 'Offers'],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertSame('person@example.com', $result['data']['email']);
        $this->assertSame('Hello', $result['data']['message']);
        $this->assertSame(['News', 'Offers'], $result['data']['topics']);
    }

    public function test_container_children_are_validated(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'container_1',
                    'type' => 'container',
                    'columns' => 2,
                    'children' => [
                        [['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => true]],
                        [],
                    ],
                ],
            ],
        ];

        $result = (new SubmissionValidator())->validate($schema, ['email' => '']);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_hidden_conditional_required_field_is_skipped(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'text', 'label' => 'Department', 'name' => 'department', 'required' => false],
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
        ];

        $result = (new SubmissionValidator())->validate($schema, ['department' => 'Support', 'sales_email' => 'bad']);

        $this->assertTrue($result['valid']);
        $this->assertArrayNotHasKey('sales_email', $result['data']);
        $this->assertArrayNotHasKey('sales_email', $result['errors']);
    }

    public function test_text_length_and_regex_validation_rules(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'text',
                    'label' => 'Code',
                    'name' => 'code',
                    'settings' => [
                        'validation' => [
                            'minLength' => '3',
                            'maxLength' => '5',
                            'pattern' => '^[A-Z]+$',
                            'patternMessage' => 'Uppercase only.',
                        ],
                    ],
                ],
            ],
        ];

        $short = (new SubmissionValidator())->validate($schema, ['code' => 'AB']);
        $pattern = (new SubmissionValidator())->validate($schema, ['code' => 'abc']);

        $this->assertFalse($short['valid']);
        $this->assertStringContainsString('at least 3 characters', $short['errors']['code']);
        $this->assertFalse($pattern['valid']);
        $this->assertSame('Uppercase only.', $pattern['errors']['code']);
    }

    public function test_numeric_min_and_max_validation_rules(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'number',
                    'label' => 'Age',
                    'name' => 'age',
                    'settings' => ['validation' => ['minValue' => '18', 'maxValue' => '65']],
                ],
            ],
        ];

        $result = (new SubmissionValidator())->validate($schema, ['age' => '17']);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('at least 18', $result['errors']['age']);
    }

    public function test_file_extension_and_size_validation_rules(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'file_upload',
                    'label' => 'Resume',
                    'name' => 'resume',
                    'settings' => ['validation' => ['allowedExtensions' => 'pdf,docx', 'maxFileSizeMb' => '1']],
                ],
            ],
        ];

        $result = (new SubmissionValidator())->validate($schema, [
            'resume' => ['name' => 'resume.exe', 'size' => 2 * 1024 * 1024],
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('allowed file type', $result['errors']['resume']);
    }

    public function test_valid_upload_metadata_is_accepted(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'file_upload',
                    'label' => 'Resume',
                    'name' => 'resume',
                    'required' => true,
                    'settings' => ['validation' => ['allowedExtensions' => 'pdf', 'maxFileSizeMb' => '2']],
                ],
            ],
        ];

        $result = (new SubmissionValidator())->validate($schema, [
            'resume' => ['name' => 'resume.pdf', 'type' => 'application/pdf', 'size' => 1024, 'error' => UPLOAD_ERR_OK],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertSame('resume.pdf', $result['data']['resume']['name']);
    }

    public function test_custom_validation_rule_string_is_enforced(): void
    {
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'text',
                    'label' => 'Username',
                    'name' => 'username',
                    'settings' => ['validation' => ['rules' => 'required|string|min:3|max:20|alpha_dash']],
                ],
            ],
        ];

        $result = (new SubmissionValidator())->validate($schema, ['username' => 'ab']);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('at least 3', $result['errors']['username']);
    }

    private function schema(): array
    {
        return [
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => true],
                ['id' => 'field_2', 'type' => 'url', 'label' => 'Website', 'name' => 'website', 'required' => false],
                ['id' => 'field_3', 'type' => 'textarea', 'label' => 'Message', 'name' => 'message', 'required' => false],
                ['id' => 'field_4', 'type' => 'checkbox', 'label' => 'Topics', 'name' => 'topics', 'required' => false],
                ['id' => 'field_5', 'type' => 'ratings', 'label' => 'Unsupported', 'name' => 'rating', 'required' => true],
            ],
        ];
    }
}
