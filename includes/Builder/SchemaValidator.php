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
        $version = isset($schema['version']) ? absint($schema['version']) : 1;

        if ($version !== 1) {
            throw new InvalidArgumentException('Unsupported schema version.');
        }

        $fields = $schema['fields'] ?? [];

        if (! is_array($fields)) {
            throw new InvalidArgumentException('Schema fields must be an array.');
        }

        return [
            'version' => 1,
            'fields' => array_map([$this, 'sanitizeField'], $fields),
        ];
    }

    private function sanitizeField(array $field): array
    {
        $type = sanitize_key((string) ($field['type'] ?? ''));

        if (! in_array($type, self::FIELD_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid field type: %s', $type));
        }

        if ($type === 'container') {
            return $this->sanitizeContainer($field);
        }

        return [
            'id' => sanitize_key((string) ($field['id'] ?? wp_unique_id('field_'))),
            'type' => $type,
            'label' => sanitize_text_field((string) ($field['label'] ?? 'Field')),
            'name' => sanitize_key(str_replace(' ', '_', (string) ($field['name'] ?? $type))),
            'required' => (bool) ($field['required'] ?? false),
            'settings' => is_array($field['settings'] ?? null) ? $this->sanitizeSettings($field['settings']) : [],
        ];
    }

    private function sanitizeContainer(array $field): array
    {
        $columns = absint($field['columns'] ?? 0);

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
            $sanitizedChildren[] = array_map([$this, 'sanitizeField'], $column);
        }

        return [
            'id' => sanitize_key((string) ($field['id'] ?? wp_unique_id('container_'))),
            'type' => 'container',
            'columns' => $columns,
            'children' => $sanitizedChildren,
        ];
    }

    private function sanitizeSettings(array $settings): array
    {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            $safeKey = sanitize_key((string) $key);
            if (is_scalar($value)) {
                $sanitized[$safeKey] = sanitize_text_field((string) $value);
            }
        }

        return $sanitized;
    }
}
