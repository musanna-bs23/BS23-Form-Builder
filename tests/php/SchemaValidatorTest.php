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
}
