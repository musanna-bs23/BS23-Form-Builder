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
