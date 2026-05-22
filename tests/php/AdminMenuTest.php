<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use WP_UnitTestCase;

final class AdminMenuTest extends WP_UnitTestCase
{
    public function test_admin_menu_class_registers_menu_hook(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        global $menu;
        $menu = [];

        do_action('admin_menu');

        $labels = array_map(static fn ($item) => $item[0] ?? '', $menu);

        $this->assertContains('BS23 Forms', $labels);
    }
}
