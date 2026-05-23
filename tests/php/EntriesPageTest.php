<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use WP_UnitTestCase;

final class EntriesPageTest extends WP_UnitTestCase
{
    public function test_entries_submenu_is_registered(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        do_action('admin_menu');

        global $submenu;
        $items = $submenu['bs23-form-builder'] ?? [];
        $labels = array_map(static fn ($item) => $item[0] ?? '', $items);

        $this->assertContains('Entries', $labels);
    }
}
