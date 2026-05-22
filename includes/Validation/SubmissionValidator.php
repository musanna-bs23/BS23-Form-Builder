<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Validation;

final class SubmissionValidator
{
    private const SUPPORTED_FIELDS = [
        'name', 'email', 'text', 'textarea', 'number', 'dropdown', 'radio', 'checkbox',
        'multiple_choice', 'url', 'phone', 'hidden',
    ];

    public function validate(array $schema, array $input): array
    {
        $errors = [];
        $data = [];

        foreach ($this->fields($schema['fields'] ?? []) as $field) {
            $type = $field['type'] ?? '';

            if (! in_array($type, self::SUPPORTED_FIELDS, true)) {
                continue;
            }

            $name = sanitize_key((string) ($field['name'] ?? $field['id'] ?? ''));
            if ($name === '') {
                continue;
            }

            $rawValue = $input[$name] ?? null;
            $value = $this->sanitizeValue($type, $rawValue);

            if (! empty($field['required']) && $this->isEmpty($value)) {
                $errors[$name] = sprintf(__('%s is required.', 'bs23-form-builder'), (string) ($field['label'] ?? $name));
                continue;
            }

            if (! $this->isEmpty($value) && $type === 'email' && ! is_email((string) $value)) {
                $errors[$name] = sprintf(__('%s must be a valid email address.', 'bs23-form-builder'), (string) ($field['label'] ?? $name));
                continue;
            }

            if (! $this->isEmpty($value) && $type === 'url' && filter_var((string) $value, FILTER_VALIDATE_URL) === false) {
                $errors[$name] = sprintf(__('%s must be a valid URL.', 'bs23-form-builder'), (string) ($field['label'] ?? $name));
                continue;
            }

            $data[$name] = $value;
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'data' => $data,
        ];
    }

    private function fields(array $fields): array
    {
        $flat = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            if (($field['type'] ?? '') === 'container') {
                foreach (($field['children'] ?? []) as $column) {
                    if (is_array($column)) {
                        $flat = array_merge($flat, $this->fields($column));
                    }
                }
                continue;
            }

            $flat[] = $field;
        }

        return $flat;
    }

    private function sanitizeValue(string $type, $value)
    {
        if (in_array($type, ['checkbox', 'multiple_choice'], true)) {
            $values = is_array($value) ? $value : ($value === null ? [] : [$value]);

            return array_values(array_map(
                static fn ($item): string => sanitize_text_field((string) wp_unslash($item)),
                $values
            ));
        }

        if ($value === null) {
            return '';
        }

        $value = is_scalar($value) ? wp_unslash($value) : '';

        if ($type === 'textarea') {
            return sanitize_textarea_field((string) $value);
        }

        if ($type === 'url') {
            return esc_url_raw((string) $value);
        }

        return sanitize_text_field((string) $value);
    }

    private function isEmpty($value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, static fn ($item): bool => (string) $item !== '')) === 0;
        }

        return trim((string) $value) === '';
    }
}
