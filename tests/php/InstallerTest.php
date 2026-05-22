<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Install\Installer;
use WP_UnitTestCase;

final class InstallerTest extends WP_UnitTestCase
{
    public function test_installer_creates_entries_table(): void
    {
        global $wpdb;

        Installer::activate();

        $table = Installer::entriesTableName();
        $columns = $wpdb->get_col("DESC {$table}", 0);

        $this->assertContains('id', $columns);
        $this->assertContains('form_id', $columns);
        $this->assertContains('entry_data', $columns);
        $this->assertContains('user_id', $columns);
        $this->assertContains('user_ip', $columns);
        $this->assertContains('user_agent', $columns);
        $this->assertContains('created_at', $columns);
    }
}
