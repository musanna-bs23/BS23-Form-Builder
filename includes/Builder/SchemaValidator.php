<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Builder;

use InvalidArgumentException;

final class SchemaValidator
{
    private const FIELD_TYPES = [
        'name', 'email', 'text', 'mask', 'textarea', 'address', 'country', 'number',
        'dropdown', 'radio', 'checkbox', 'multiple_choice', 'url', 'datetime',
        'image_upload', 'file_upload', 'html', 'phone', 'hidden', 'section_break',
        'shortcode', 'terms', 'action_hook', 'form_step', 'ratings', 'checkable_grid',
        'gdpr', 'password', 'submit', 'range', 'nps', 'dynamic', 'chained_select',
        'color', 'repeat', 'post_select', 'rich_text', 'save_resume', 'container',
    ];

    public function sanitize(array $schema): array
    {
        $version = isset($schema['version']) ? $this->parseInteger($schema['version']) : 1;

        if ($version !== 1) {
            throw new InvalidArgumentException('Unsupported schema version.');
        }

        $fields = $schema['fields'] ?? [];

        if (! is_array($fields)) {
            throw new InvalidArgumentException('Schema fields must be an array.');
        }

        return [
            'version' => 1,
            'fields' => $this->sanitizeFields($fields),
        ];
    }

    private function sanitizeFields(array $fields): array
    {
        $sanitized = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                throw new InvalidArgumentException('Field must be an object.');
            }

            $sanitized[] = $this->sanitizeField($field);
        }

        return $sanitized;
    }

    private function sanitizeField(array $field): array
    {
        $type = $this->sanitizeType($field['type'] ?? '');

        if ($type === 'container') {
            return $this->sanitizeContainer($field);
        }

        return [
            'id' => $this->sanitizeId($field['id'] ?? null, 'field_'),
            'type' => $type,
            'label' => sanitize_text_field((string) ($field['label'] ?? 'Field')),
            'name' => sanitize_key(str_replace(' ', '_', (string) ($field['name'] ?? $type))),
            'required' => $this->sanitizeRequired($field['required'] ?? false),
            'settings' => is_array($field['settings'] ?? null) ? $this->sanitizeSettings($field['settings']) : [],
        ];
    }

    private function sanitizeContainer(array $field): array
    {
        $columns = $this->parseInteger($field['columns'] ?? null);

        if (! in_array($columns, [1, 2, 3, 4], true)) {
            throw new InvalidArgumentException('Invalid container column count.');
        }

        $children = $field['children'] ?? [];

        if (! is_array($children) || count($children) !== $columns) {
            throw new InvalidArgumentException('Container children must match column count.');
        }

        $sanitizedChildren = [];
        foreach ($children as $column) {
            if (! is_array($column)) {
                throw new InvalidArgumentException('Container column must be an array.');
            }
            $sanitizedChildren[] = $this->sanitizeFields($column);
        }

        return [
            'id' => $this->sanitizeId($field['id'] ?? null, 'container_'),
            'type' => 'container',
            'columns' => $columns,
            'children' => $sanitizedChildren,
        ];
    }

    private function sanitizeSettings(array $settings): array
    {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            $safeKey = sanitize_key(str_replace(' ', '_', (string) $key));
            if (is_scalar($value)) {
                $sanitized[$safeKey] = sanitize_text_field((string) $value);
            }
        }

        return $sanitized;
    }

    private function parseInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^(0|[1-9][0-9]*)$/', $value) === 1) {
            return (int) $value;
        }

        return null;
    }

    private function sanitizeType($type): string
    {
        $rawType = is_scalar($type) ? (string) $type : '';
        $sanitizedType = sanitize_key($rawType);

        if ($rawType !== $sanitizedType || ! in_array($rawType, self::FIELD_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid field type: %s', $rawType));
        }

        return $rawType;
    }

    private function sanitizeId($id, string $prefix): string
    {
        $sanitizedId = is_scalar($id) ? sanitize_key((string) $id) : '';

        if ($sanitizedId === '') {
            $sanitizedId = sanitize_key(wp_unique_id($prefix));
        }

        return $sanitizedId;
    }

    private function sanitizeRequired($required): bool
    {
        $sanitized = filter_var($required, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $sanitized ?? false;
    }
}
