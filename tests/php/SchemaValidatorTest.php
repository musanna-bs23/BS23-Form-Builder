<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Builder\SchemaValidator;
use WP_UnitTestCase;

final class SchemaValidatorTest extends WP_UnitTestCase
{
    public function test_valid_schema_is_sanitized(): void
    {
        $validator = new SchemaValidator();
        $schema = [
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'email',
                    'label' => 'Email <b>Address</b>',
                    'name' => 'email address',
                    'required' => true,
                    'settings' => [],
                ],
            ],
        ];

        $result = $validator->sanitize($schema);

        $this->assertSame(1, $result['version']);
        $this->assertSame('Email Address', $result['fields'][0]['label']);
        $this->assertSame('email_address', $result['fields'][0]['name']);
    }

    public function test_invalid_field_type_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type: unsafe');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'unsafe', 'label' => 'Unsafe', 'name' => 'unsafe'],
            ],
        ]);
    }

    public function test_invalid_container_column_count_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid container column count.');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'container_1', 'type' => 'container', 'columns' => 5, 'children' => []],
            ],
        ]);
    }

    public function test_scalar_root_field_entry_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must be an object.');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                'not-a-field',
            ],
        ]);
    }

    public function test_scalar_nested_container_child_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must be an object.');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                [
                    'id' => 'container_1',
                    'type' => 'container',
                    'columns' => 1,
                    'children' => [
                        ['not-a-field'],
                    ],
                ],
            ],
        ]);
    }

    public function test_negative_version_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported schema version.');

        $validator->sanitize([
            'version' => -1,
            'fields' => [],
        ]);
    }

    public function test_negative_container_column_count_is_rejected(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid container column count.');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'container_1', 'type' => 'container', 'columns' => -2, 'children' => [[], []]],
            ],
        ]);
    }

    public function test_malformed_type_is_rejected_before_sanitization(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type: e@mail');

        $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'e@mail', 'label' => 'Email', 'name' => 'email'],
            ],
        ]);
    }

    public function test_empty_sanitized_field_id_falls_back_to_generated_id(): void
    {
        $validator = new SchemaValidator();

        $result = $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => '!!!', 'type' => 'email', 'label' => 'Email', 'name' => 'email'],
            ],
        ]);

        $this->assertNotSame('', $result['fields'][0]['id']);
        $this->assertStringStartsWith('field_', $result['fields'][0]['id']);
    }

    public function test_required_string_false_sanitizes_to_false(): void
    {
        $validator = new SchemaValidator();

        $result = $validator->sanitize([
            'version' => 1,
            'fields' => [
                ['id' => 'field_1', 'type' => 'email', 'label' => 'Email', 'name' => 'email', 'required' => 'false'],
            ],
        ]);

        $this->assertFalse($result['fields'][0]['required']);
    }

    public function test_scalar_settings_are_sanitized_and_non_scalar_settings_are_skipped(): void
    {
        $validator = new SchemaValidator();

        $result = $validator->sanitize([
            'version' => 1,
            'fields' => [
                [
                    'id' => 'field_1',
                    'type' => 'text',
                    'label' => 'Text',
                    'name' => 'text',
                    'settings' => [
                        'placeholder text' => 'Hello <b>World</b>',
                        'maxLength' => 25,
                        'options' => ['A', 'B'],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                'placeholder_text' => 'Hello World',
                'maxlength' => '25',
            ],
            $result['fields'][0]['settings']
        );
    }

    public function test_valid_two_column_container_sanitizes_nested_children(): void
    {
        $validator = new SchemaValidator();

        $result = $validator->sanitize([
            'version' => '1',
            'fields' => [
                [
                    'id' => 'container_1',
                    'type' => 'container',
                    'columns' => '2',
                    'children' => [
                        [
                            [
                                'id' => 'field_1',
                                'type' => 'email',
                                'label' => 'Email <b>Address</b>',
                                'name' => 'email address',
                                'required' => 'true',
                            ],
                        ],
                        [],
                    ],
                ],
            ],
        ]);

        $this->assertSame(2, $result['fields'][0]['columns']);
        $this->assertSame('Email Address', $result['fields'][0]['children'][0][0]['label']);
        $this->assertSame('email_address', $result['fields'][0]['children'][0][0]['name']);
        $this->assertTrue($result['fields'][0]['children'][0][0]['required']);
        $this->assertSame([], $result['fields'][0]['children'][1]);
    }
}
