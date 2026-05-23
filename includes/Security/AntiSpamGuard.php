<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Security;

final class AntiSpamGuard
{
    public const HONEYPOT_FIELD = 'bs23_hp';
    public const RENDERED_AT_FIELD = 'bs23_rendered_at';
    public const TOKEN_FIELD = 'bs23_render_token';

    public static function tokenFor(int $formId, int $timestamp): string
    {
        return wp_hash($formId . '|' . $timestamp, 'nonce');
    }
}
