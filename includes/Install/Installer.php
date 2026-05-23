<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Install;

final class Installer
{
    public static function activate(): void
    {
        self::createEntriesTable();
    }

    public static function entriesTableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'bs23_form_entries';
    }

    private static function createEntriesTable(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::entriesTableName();
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            entry_data longtext NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            user_ip varchar(100) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY created_at (created_at)
        ) {$charsetCollate};";

        dbDelta($sql);
    }
}
