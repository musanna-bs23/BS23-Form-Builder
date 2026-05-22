<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Validation;

use DateTime;

final class RuleValidator
{
    public function validate(string $label, string $name, $value, array $input, string $rules): string
    {
        foreach ($this->parseRules($rules) as $rule) {
            $error = $this->validateRule($label, $name, $value, $input, $rule['name'], $rule['params']);
            if ($error !== '') {
                return $error;
            }
        }

        return '';
    }

    private function parseRules(string $rules): array
    {
        $parsed = [];
        foreach (array_filter(array_map('trim', explode('|', $rules))) as $rule) {
            [$name, $params] = array_pad(explode(':', $rule, 2), 2, '');
            $parsed[] = [
                'name' => sanitize_key($name),
                'params' => $params === '' ? [] : array_map('trim', explode(',', $params)),
            ];
        }

        return $parsed;
    }

    private function validateRule(string $label, string $name, $value, array $input, string $rule, array $params): string
    {
        $string = is_array($value) ? '' : trim((string) $value);
        $empty = $this->isEmpty($value);

        if ($rule === 'nullable' && $empty) {
            return '';
        }
        if ($rule === 'required' && $empty) {
            return sprintf(__('%s is required.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'present' && ! array_key_exists($name, $input)) {
            return sprintf(__('%s must be present.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'filled' && array_key_exists($name, $input) && $empty) {
            return sprintf(__('%s must be filled.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'prohibited' && ! $empty) {
            return sprintf(__('%s is prohibited.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'required_if' && ($input[$params[0] ?? ''] ?? null) === ($params[1] ?? null) && $empty) {
            return sprintf(__('%s is required.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'required_unless' && ($input[$params[0] ?? ''] ?? null) !== ($params[1] ?? null) && $empty) {
            return sprintf(__('%s is required.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'required_with' && ! $this->isEmpty($input[$params[0] ?? ''] ?? '') && $empty) {
            return sprintf(__('%s is required.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'required_without' && $this->isEmpty($input[$params[0] ?? ''] ?? '') && $empty) {
            return sprintf(__('%s is required.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'nullable_if' && ($input[$params[0] ?? ''] ?? null) === ($params[1] ?? null) && $empty) {
            return '';
        }
        if ($empty && ! in_array($rule, ['required', 'present', 'filled'], true)) {
            return '';
        }

        if (in_array($rule, ['string'], true) && is_array($value)) {
            return sprintf(__('%s must be a string.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'integer' && filter_var($string, FILTER_VALIDATE_INT) === false) {
            return sprintf(__('%s must be an integer.', 'bs23-form-builder'), $label);
        }
        if (in_array($rule, ['numeric', 'float', 'double'], true) && ! is_numeric($string)) {
            return sprintf(__('%s must be numeric.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'boolean' && ! in_array(strtolower($string), ['1', '0', 'true', 'false', 'yes', 'no', 'on', 'off'], true)) {
            return sprintf(__('%s must be true or false.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'email' && ! is_email($string)) {
            return sprintf(__('%s must be a valid email address.', 'bs23-form-builder'), $label);
        }
        if (in_array($rule, ['url', 'active_url'], true) && filter_var($string, FILTER_VALIDATE_URL) === false) {
            return sprintf(__('%s must be a valid URL.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'active_url' && parse_url($string, PHP_URL_HOST) && ! checkdnsrr((string) parse_url($string, PHP_URL_HOST))) {
            return sprintf(__('%s must be an active URL.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'array' && ! is_array($value)) {
            return sprintf(__('%s must be an array.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'date' && strtotime($string) === false) {
            return sprintf(__('%s must be a valid date.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'date_format' && ! $this->matchesDateFormat($string, $params[0] ?? 'Y-m-d')) {
            return sprintf(__('%s date format is invalid.', 'bs23-form-builder'), $label);
        }
        if (in_array($rule, ['before', 'after', 'before_or_equal', 'after_or_equal'], true) && ! $this->compareDate($string, $params[0] ?? '', $rule)) {
            return sprintf(__('%s date is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'json' && json_decode($string) === null && json_last_error() !== JSON_ERROR_NONE) {
            return sprintf(__('%s must be valid JSON.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'uuid' && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $string) !== 1) {
            return sprintf(__('%s must be a valid UUID.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'ip' && filter_var($string, FILTER_VALIDATE_IP) === false) {
            return sprintf(__('%s must be a valid IP address.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'ipv4' && filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return sprintf(__('%s must be a valid IPv4 address.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'ipv6' && filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return sprintf(__('%s must be a valid IPv6 address.', 'bs23-form-builder'), $label);
        }

        $patternError = $this->patternRuleError($label, $string, $rule, $params);
        if ($patternError !== '') {
            return $patternError;
        }

        $sizeError = $this->sizeRuleError($label, $value, $string, $rule, $params);
        if ($sizeError !== '') {
            return $sizeError;
        }

        $fileError = $this->fileRuleError($label, $value, $rule, $params);
        if ($fileError !== '') {
            return $fileError;
        }

        return $this->filterRuleError($label, $name, $value, $input, $rule, $params);
    }

    private function patternRuleError(string $label, string $value, string $rule, array $params): string
    {
        $patterns = [
            'alpha' => '/^[\pL]+$/u',
            'alpha_num' => '/^[\pL\pN]+$/u',
            'alpha_dash' => '/^[\pL\pN_-]+$/u',
            'alpha_spaces' => '/^[\pL\s]+$/u',
            'lowercase' => '/^\p{Ll}+$/u',
            'uppercase' => '/^\p{Lu}+$/u',
            'digits' => '/^\d{' . (int) ($params[0] ?? 0) . '}$/',
            'phone' => '/^\+?[0-9\s().-]{7,20}$/',
            'credit_card' => '/^\d{12,19}$/',
            'postal_code' => '/^[A-Za-z0-9\s-]{3,12}$/',
            'latitude' => '/^-?([0-8]?\d(\.\d+)?|90(\.0+)?)$/',
            'longitude' => '/^-?((1[0-7]\d|\d?\d)(\.\d+)?|180(\.0+)?)$/',
            'url_safe' => '/^[A-Za-z0-9._~:\/?#\[\]@!$&\'()*+,;=%-]+$/',
            'hex_color' => '/^#?([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/',
            'slug' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'username' => '/^[A-Za-z0-9_.-]{3,30}$/',
        ];

        if (isset($patterns[$rule]) && preg_match($patterns[$rule], $value) !== 1) {
            return sprintf(__('%s format is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'digits_between' && preg_match('/^\d{' . (int) ($params[0] ?? 0) . ',' . (int) ($params[1] ?? 0) . '}$/', $value) !== 1) {
            return sprintf(__('%s digits count is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'starts_with' && ! $this->startsWithAny($value, $params)) {
            return sprintf(__('%s start is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'ends_with' && ! $this->endsWithAny($value, $params)) {
            return sprintf(__('%s ending is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'regex' && ! $this->matchesRegex($value, $params[0] ?? '')) {
            return sprintf(__('%s format is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'not_regex' && $this->matchesRegex($value, $params[0] ?? '')) {
            return sprintf(__('%s format is invalid.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'timezone' && ! in_array($value, timezone_identifiers_list(), true)) {
            return sprintf(__('%s must be a valid timezone.', 'bs23-form-builder'), $label);
        }

        return '';
    }

    private function sizeRuleError(string $label, $value, string $string, string $rule, array $params): string
    {
        $size = is_numeric($string) ? (float) $string : (is_array($value) ? count($value) : strlen($string));
        $target = isset($params[0]) && is_numeric($params[0]) ? (float) $params[0] : null;
        if ($target === null) {
            return '';
        }
        if ($rule === 'min' && $size < $target) {
            return sprintf(__('%s must be at least %s.', 'bs23-form-builder'), $label, (string) $params[0]);
        }
        if ($rule === 'max' && $size > $target) {
            return sprintf(__('%s must be no more than %s.', 'bs23-form-builder'), $label, (string) $params[0]);
        }
        if ($rule === 'size' && $size !== $target) {
            return sprintf(__('%s size must be %s.', 'bs23-form-builder'), $label, (string) $params[0]);
        }
        if ($rule === 'between' && isset($params[1]) && ($size < (float) $params[0] || $size > (float) $params[1])) {
            return sprintf(__('%s must be between %s and %s.', 'bs23-form-builder'), $label, (string) $params[0], (string) $params[1]);
        }

        return '';
    }

    private function fileRuleError(string $label, $value, string $rule, array $params): string
    {
        if (! in_array($rule, ['file', 'image', 'mimes', 'mimetypes', 'extensions', 'max_file_size', 'min_file_size'], true)) {
            return '';
        }

        $file = is_array($value) ? $value : [];
        $name = sanitize_file_name((string) ($file['name'] ?? ''));
        $type = sanitize_text_field((string) ($file['type'] ?? ''));
        $size = isset($file['size']) && is_numeric($file['size']) ? (int) $file['size'] : 0;
        $extension = sanitize_key((string) pathinfo($name, PATHINFO_EXTENSION));

        if ($rule === 'file' && $name === '') {
            return sprintf(__('%s must be a file.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'image' && ! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true)) {
            return sprintf(__('%s must be an image.', 'bs23-form-builder'), $label);
        }
        if (in_array($rule, ['mimes', 'extensions'], true) && ! in_array($extension, array_map('sanitize_key', $params), true)) {
            return sprintf(__('%s must use an allowed file type.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'mimetypes' && ! in_array($type, array_map('sanitize_text_field', $params), true)) {
            return sprintf(__('%s must use an allowed mime type.', 'bs23-form-builder'), $label);
        }
        if ($rule === 'max_file_size' && isset($params[0]) && $size > (float) $params[0] * 1024 * 1024) {
            return sprintf(__('%s must be no larger than %s MB.', 'bs23-form-builder'), $label, (string) $params[0]);
        }
        if ($rule === 'min_file_size' && isset($params[0]) && $size < (float) $params[0] * 1024 * 1024) {
            return sprintf(__('%s must be at least %s MB.', 'bs23-form-builder'), $label, (string) $params[0]);
        }

        return '';
    }

    private function filterRuleError(string $label, string $name, $value, array $input, string $rule, array $params): string
    {
        if (in_array($rule, ['unique', 'exists', 'password_strength', 'custom_validation'], true)) {
            $passed = (bool) apply_filters("bs23_form_builder_validation_{$rule}", true, $value, $params, $name, $input);
            if (! $passed) {
                return $rule === 'exists'
                    ? sprintf(__('%s was not found.', 'bs23-form-builder'), $label)
                    : sprintf(__('%s already exists or is invalid.', 'bs23-form-builder'), $label);
            }
        }

        return '';
    }

    private function isEmpty($value): bool
    {
        return is_array($value) ? count(array_filter($value)) === 0 : trim((string) $value) === '';
    }

    private function matchesRegex(string $value, string $pattern): bool
    {
        return $pattern !== '' && @preg_match($pattern, $value) === 1;
    }

    private function startsWithAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($value, $needle) === 0) {
                return true;
            }
        }
        return false;
    }

    private function endsWithAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && substr($value, -strlen($needle)) === $needle) {
                return true;
            }
        }
        return false;
    }

    private function matchesDateFormat(string $value, string $format): bool
    {
        $date = DateTime::createFromFormat($format, $value);
        return $date instanceof DateTime && $date->format($format) === $value;
    }

    private function compareDate(string $value, string $target, string $rule): bool
    {
        $left = strtotime($value);
        $right = strtotime($target);
        if ($left === false || $right === false) {
            return false;
        }
        if ($rule === 'before') {
            return $left < $right;
        }
        if ($rule === 'after') {
            return $left > $right;
        }
        if ($rule === 'before_or_equal') {
            return $left <= $right;
        }
        return $left >= $right;
    }
}
