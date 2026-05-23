<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Settings;

final class FormSettings
{
    public const META_KEY = '_bs23_form_settings';

    public function defaults(): array
    {
        return [
            'notification' => [
                'enabled' => true,
                'to' => '{admin_email}',
                'subject' => 'New submission from {form_title}',
                'message' => "{all_fields}",
                'reply_to' => '',
            ],
            'confirmation' => [
                'message' => __('Thanks, your submission has been received.', 'bs23-form-builder'),
                'redirect_url' => '',
            ],
            'style' => [
                'max_width' => '760px',
                'field_gap' => '16px',
                'label_color' => '#0f172a',
                'label_size' => '14px',
                'input_background' => '#ffffff',
                'input_border' => '#cbd5e1',
                'input_radius' => '8px',
                'button_background' => '#2563eb',
                'button_text' => '#ffffff',
                'button_radius' => '8px',
                'error_color' => '#b42318',
                'success_color' => '#027a48',
                'step_active' => '#2563eb',
            ],
        ];
    }

    public function get(int $formId): array
    {
        $saved = get_post_meta($formId, self::META_KEY, true);
        if (! is_array($saved)) {
            return $this->defaults();
        }

        return array_replace_recursive($this->defaults(), $this->sanitize($saved));
    }

    public function save(int $formId, array $settings): array
    {
        $sanitized = $this->sanitize($settings);
        update_post_meta($formId, self::META_KEY, $sanitized);

        return $this->get($formId);
    }

    public function sanitize(array $settings): array
    {
        $defaults = $this->defaults();
        $notification = is_array($settings['notification'] ?? null) ? $settings['notification'] : [];
        $confirmation = is_array($settings['confirmation'] ?? null) ? $settings['confirmation'] : [];
        $style = is_array($settings['style'] ?? null) ? $settings['style'] : [];
        $to = sanitize_text_field((string) ($notification['to'] ?? $defaults['notification']['to']));

        if ($to !== '{admin_email}' && ! is_email($to)) {
            $to = '{admin_email}';
        }

        $redirect = esc_url_raw((string) ($confirmation['redirect_url'] ?? ''));

        return [
            'notification' => [
                'enabled' => $this->boolean($notification['enabled'] ?? true),
                'to' => $to,
                'subject' => sanitize_text_field((string) ($notification['subject'] ?? $defaults['notification']['subject'])),
                'message' => sanitize_textarea_field((string) ($notification['message'] ?? $defaults['notification']['message'])),
                'reply_to' => sanitize_key((string) ($notification['reply_to'] ?? '')),
            ],
            'confirmation' => [
                'message' => sanitize_textarea_field((string) ($confirmation['message'] ?? $defaults['confirmation']['message'])),
                'redirect_url' => $redirect,
            ],
            'style' => $this->sanitizeStyle($style, $defaults['style']),
        ];
    }

    public function resolveRecipient(string $recipient): string
    {
        if ($recipient === '{admin_email}') {
            return (string) get_option('admin_email');
        }

        return $recipient;
    }

    private function boolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function sanitizeStyle(array $style, array $defaults): array
    {
        $sanitized = [];
        foreach ($defaults as $key => $default) {
            $value = (string) ($style[$key] ?? $default);
            $sanitized[$key] = in_array($key, ['max_width', 'field_gap', 'label_size', 'input_radius', 'button_radius'], true)
                ? $this->cssLength($value, $default)
                : $this->hexColor($value, $default);
        }

        return $sanitized;
    }

    private function cssLength(string $value, string $fallback): string
    {
        return preg_match('/^(0|[1-9][0-9]*)(px|rem|em|%)$/', $value) === 1 ? $value : $fallback;
    }

    private function hexColor(string $value, string $fallback): string
    {
        return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value) === 1 ? strtolower($value) : $fallback;
    }
}
