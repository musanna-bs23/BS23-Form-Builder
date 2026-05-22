<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Validation;

use BS23\FormBuilder\ConditionalLogic\Evaluator;

final class SubmissionValidator
{
    private const SUPPORTED_FIELDS = [
        'name', 'email', 'text', 'textarea', 'number', 'dropdown', 'radio', 'checkbox',
        'multiple_choice', 'url', 'phone', 'hidden', 'file_upload', 'image_upload',
    ];

    private Evaluator $conditionalLogic;

    public function __construct(?Evaluator $conditionalLogic = null)
    {
        $this->conditionalLogic = $conditionalLogic ?: new Evaluator();
    }

    public function validate(array $schema, array $input): array
    {
        $errors = [];
        $data = [];

        foreach ($this->fields($schema['fields'] ?? []) as $field) {
            $type = $field['type'] ?? '';

            if (! $this->conditionalLogic->isVisible($field, $input)) {
                continue;
            }

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

            $advancedError = $this->advancedValidationError($field, $type, $name, $value, $rawValue);
            if ($advancedError !== '') {
                $errors[$name] = $advancedError;
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

        if (in_array($type, ['file_upload', 'image_upload'], true)) {
            return is_array($value) ? [
                'name' => sanitize_file_name((string) ($value['name'] ?? '')),
                'size' => isset($value['size']) && is_numeric($value['size']) ? (int) $value['size'] : 0,
            ] : ['name' => '', 'size' => 0];
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

    private function advancedValidationError(array $field, string $type, string $name, $value, $rawValue): string
    {
        $validation = $field['settings']['validation'] ?? [];
        if (! is_array($validation) || $this->isEmpty($value)) {
            return '';
        }

        $label = (string) ($field['label'] ?? $name);

        if (in_array($type, ['name', 'email', 'text', 'textarea', 'url', 'phone', 'hidden'], true)) {
            $length = strlen((string) $value);
            if (isset($validation['minLength']) && is_numeric($validation['minLength']) && $length < (int) $validation['minLength']) {
                return sprintf(__('%s must be at least %d characters.', 'bs23-form-builder'), $label, (int) $validation['minLength']);
            }
            if (isset($validation['maxLength']) && is_numeric($validation['maxLength']) && $length > (int) $validation['maxLength']) {
                return sprintf(__('%s must be no more than %d characters.', 'bs23-form-builder'), $label, (int) $validation['maxLength']);
            }
            if (! empty($validation['pattern'])) {
                $pattern = '~' . str_replace('~', '\~', (string) $validation['pattern']) . '~u';
                $matches = @preg_match($pattern, (string) $value);
                if ($matches !== 1) {
                    return (string) ($validation['patternMessage'] ?? sprintf(__('%s format is invalid.', 'bs23-form-builder'), $label));
                }
            }
        }

        if ($type === 'number') {
            $number = is_numeric($value) ? (float) $value : null;
            if ($number === null) {
                return sprintf(__('%s must be a number.', 'bs23-form-builder'), $label);
            }
            if (isset($validation['minValue']) && is_numeric($validation['minValue']) && $number < (float) $validation['minValue']) {
                return sprintf(__('%s must be at least %s.', 'bs23-form-builder'), $label, (string) $validation['minValue']);
            }
            if (isset($validation['maxValue']) && is_numeric($validation['maxValue']) && $number > (float) $validation['maxValue']) {
                return sprintf(__('%s must be no more than %s.', 'bs23-form-builder'), $label, (string) $validation['maxValue']);
            }
        }

        if (in_array($type, ['file_upload', 'image_upload'], true)) {
            $file = is_array($rawValue) ? $rawValue : [];
            $fileName = sanitize_file_name((string) ($file['name'] ?? ''));
            $fileSize = isset($file['size']) && is_numeric($file['size']) ? (int) $file['size'] : 0;
            if (! empty($validation['allowedExtensions'])) {
                $allowed = array_filter(array_map('sanitize_key', explode(',', (string) $validation['allowedExtensions'])));
                $extension = sanitize_key((string) pathinfo($fileName, PATHINFO_EXTENSION));
                if ($extension === '' || ! in_array($extension, $allowed, true)) {
                    return sprintf(__('%s must use an allowed file type.', 'bs23-form-builder'), $label);
                }
            }
            if (isset($validation['maxFileSizeMb']) && is_numeric($validation['maxFileSizeMb'])) {
                $maxBytes = (float) $validation['maxFileSizeMb'] * 1024 * 1024;
                if ($fileSize > $maxBytes) {
                    return sprintf(__('%s must be no larger than %s MB.', 'bs23-form-builder'), $label, (string) $validation['maxFileSizeMb']);
                }
            }
        }

        return '';
    }

    private function isEmpty($value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, static fn ($item): bool => (string) $item !== '')) === 0;
        }

        return trim((string) $value) === '';
    }
}
