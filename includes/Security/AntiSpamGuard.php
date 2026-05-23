<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Security;

final class AntiSpamGuard
{
    public const HONEYPOT_FIELD = 'bs23_hp';
    public const RENDERED_AT_FIELD = 'bs23_rendered_at';
    public const TOKEN_FIELD = 'bs23_render_token';
    private const ERROR_MESSAGE = 'Spam protection rejected this submission. Please try again.';

    public function check(int $formId, array $posted, array $settings): array
    {
        if (empty($settings['enabled'])) {
            return $this->allowed();
        }

        if (! empty($settings['honeypot']) && trim((string) ($posted[self::HONEYPOT_FIELD] ?? '')) !== '') {
            return $this->blocked();
        }

        $timestamp = isset($posted[self::RENDERED_AT_FIELD]) && is_numeric($posted[self::RENDERED_AT_FIELD])
            ? (int) $posted[self::RENDERED_AT_FIELD]
            : 0;
        $token = (string) ($posted[self::TOKEN_FIELD] ?? '');

        if ($timestamp < 1 || $token === '' || ! hash_equals(self::tokenFor($formId, $timestamp), $token)) {
            return $this->blocked();
        }

        $minimumTime = $this->positiveInteger($settings['minimum_time'] ?? 3, 3);
        if ((time() - $timestamp) < $minimumTime) {
            return $this->blocked();
        }

        $limit = $this->positiveInteger($settings['rate_limit_count'] ?? 5, 5);
        $window = $this->positiveInteger($settings['rate_limit_window'] ?? 300, 300);
        $key = $this->rateLimitKey($formId);
        $current = get_transient($key);
        $current = is_numeric($current) ? (int) $current : 0;

        if ($current >= $limit) {
            return $this->blocked();
        }

        set_transient($key, $current + 1, $window);

        return $this->allowed();
    }

    public static function tokenFor(int $formId, int $timestamp): string
    {
        return wp_hash($formId . '|' . $timestamp, 'nonce');
    }

    private function allowed(): array
    {
        return [
            'allowed' => true,
            'error' => '',
        ];
    }

    private function blocked(): array
    {
        return [
            'allowed' => false,
            'error' => self::ERROR_MESSAGE,
        ];
    }

    private function positiveInteger($value, int $fallback): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($integer) && $integer > 0 ? $integer : $fallback;
    }

    private function rateLimitKey(int $formId): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        return 'bs23_form_rate_' . md5($formId . '|' . $ip);
    }
}
