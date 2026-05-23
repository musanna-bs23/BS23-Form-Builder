<?php
declare(strict_types=1);

namespace {
    function wp_hash(string $data, string $scheme = 'auth'): string
    {
        return hash_hmac('sha256', $data, 'unit-test-key-' . $scheme);
    }

    function get_transient(string $key)
    {
        return $GLOBALS['bs23_test_transients'][$key] ?? false;
    }

    function set_transient(string $key, $value, int $expiration): bool
    {
        $GLOBALS['bs23_test_transients'][$key] = $value;
        $GLOBALS['bs23_test_transient_expirations'][$key] = $expiration;

        return true;
    }
}

namespace BS23\FormBuilder\Tests\Unit {
    use BS23\FormBuilder\Security\AntiSpamGuard;
    use PHPUnit\Framework\TestCase;

    final class AntiSpamGuardTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['bs23_test_transients'] = [];
            $GLOBALS['bs23_test_transient_expirations'] = [];
            $_SERVER = ['REMOTE_ADDR' => '203.0.113.10'];
        }

        public function test_allows_submission_when_security_is_disabled(): void
        {
            $guard = new AntiSpamGuard();

            $result = $guard->check(25, [], ['enabled' => false]);

            self::assertTrue($result['allowed']);
        }

        public function test_blocks_filled_honeypot(): void
        {
            $guard = new AntiSpamGuard();
            $timestamp = time() - 10;

            $result = $guard->check(25, [
                AntiSpamGuard::HONEYPOT_FIELD => 'spam',
                AntiSpamGuard::RENDERED_AT_FIELD => (string) $timestamp,
                AntiSpamGuard::TOKEN_FIELD => AntiSpamGuard::tokenFor(25, $timestamp),
            ], $this->settings());

            self::assertFalse($result['allowed']);
            self::assertSame('Spam protection rejected this submission. Please try again.', $result['error']);
        }

        public function test_blocks_invalid_render_token(): void
        {
            $guard = new AntiSpamGuard();
            $timestamp = time() - 10;

            $result = $guard->check(25, [
                AntiSpamGuard::RENDERED_AT_FIELD => (string) $timestamp,
                AntiSpamGuard::TOKEN_FIELD => 'bad',
            ], $this->settings());

            self::assertFalse($result['allowed']);
        }

        public function test_blocks_submission_before_minimum_time(): void
        {
            $guard = new AntiSpamGuard();
            $timestamp = time();

            $result = $guard->check(25, [
                AntiSpamGuard::RENDERED_AT_FIELD => (string) $timestamp,
                AntiSpamGuard::TOKEN_FIELD => AntiSpamGuard::tokenFor(25, $timestamp),
            ], $this->settings(['minimum_time' => 3]));

            self::assertFalse($result['allowed']);
        }

        public function test_blocks_when_rate_limit_is_exceeded(): void
        {
            $guard = new AntiSpamGuard();
            $timestamp = time() - 10;
            $posted = [
                AntiSpamGuard::RENDERED_AT_FIELD => (string) $timestamp,
                AntiSpamGuard::TOKEN_FIELD => AntiSpamGuard::tokenFor(25, $timestamp),
            ];
            $settings = $this->settings(['rate_limit_count' => 1, 'rate_limit_window' => 120]);

            self::assertTrue($guard->check(25, $posted, $settings)['allowed']);
            $result = $guard->check(25, $posted, $settings);

            self::assertFalse($result['allowed']);
            self::assertSame([120], array_values($GLOBALS['bs23_test_transient_expirations']));
        }

        private function settings(array $overrides = []): array
        {
            return array_merge([
                'enabled' => true,
                'honeypot' => true,
                'minimum_time' => 3,
                'rate_limit_count' => 5,
                'rate_limit_window' => 300,
            ], $overrides);
        }
    }
}
