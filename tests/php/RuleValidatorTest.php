<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Validation\RuleValidator;
use WP_UnitTestCase;

final class RuleValidatorTest extends WP_UnitTestCase
{
    public function test_validates_presence_text_and_range_rules(): void
    {
        $validator = new RuleValidator();

        $this->assertSame('', $validator->validate('Username', 'username', 'john_doe', ['username' => 'john_doe'], 'required|string|min:3|max:20|alpha_dash'));
        $this->assertStringContainsString('at least 3', $validator->validate('Username', 'username', 'ab', ['username' => 'ab'], 'required|min:3'));
    }

    public function test_validates_format_and_comparison_rules(): void
    {
        $validator = new RuleValidator();
        $input = ['email' => 'person@example.com', 'email_confirmation' => 'other@example.com', 'role' => 'admin'];

        $this->assertSame('', $validator->validate('Role', 'role', 'admin', $input, 'in:admin,editor'));
        $this->assertStringContainsString('match confirmation', $validator->validate('Email', 'email', 'person@example.com', $input, 'confirmed'));
        $this->assertStringContainsString('valid UUID', $validator->validate('Token', 'token', 'bad', ['token' => 'bad'], 'uuid'));
    }

    public function test_validates_dates_ip_json_and_color_rules(): void
    {
        $validator = new RuleValidator();

        $this->assertSame('', $validator->validate('Launch', 'launch', '2026-05-23', ['launch' => '2026-05-23'], 'date|after:2026-01-01'));
        $this->assertSame('', $validator->validate('IP', 'ip', '127.0.0.1', ['ip' => '127.0.0.1'], 'ip|ipv4'));
        $this->assertSame('', $validator->validate('Config', 'config', '{"enabled":true}', ['config' => '{"enabled":true}'], 'json'));
        $this->assertSame('', $validator->validate('Color', 'color', '#aabbcc', ['color' => '#aabbcc'], 'hex_color'));
    }

    public function test_validates_file_rules(): void
    {
        $validator = new RuleValidator();
        $file = ['name' => 'avatar.jpg', 'type' => 'image/jpeg', 'size' => 512 * 1024];

        $this->assertSame('', $validator->validate('Avatar', 'avatar', $file, ['avatar' => $file], 'file|image|extensions:jpg,png|max_file_size:1|min_file_size:0.1'));
        $this->assertStringContainsString('allowed file type', $validator->validate('Avatar', 'avatar', $file, ['avatar' => $file], 'extensions:pdf'));
    }

    public function test_filter_backed_rules_can_fail(): void
    {
        add_filter('bs23_form_builder_validation_unique', static fn (): bool => false);

        $message = (new RuleValidator())->validate('Email', 'email', 'person@example.com', ['email' => 'person@example.com'], 'unique:users,email');

        remove_all_filters('bs23_form_builder_validation_unique');
        $this->assertStringContainsString('already exists', $message);
    }
}
