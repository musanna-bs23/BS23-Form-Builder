<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Submission;

use BS23\FormBuilder\Install\Installer;

final class EntryRepository
{
    public function insert(int $formId, array $data): int
    {
        global $wpdb;

        $inserted = $wpdb->insert(
            Installer::entriesTableName(),
            [
                'form_id' => $formId,
                'entry_data' => wp_json_encode($data),
                'user_id' => get_current_user_id(),
                'user_ip' => $this->currentIp(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_USER_AGENT'])) : '',
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%d', '%s', '%s', '%s']
        );

        return $inserted === false ? 0 : (int) $wpdb->insert_id;
    }

    private function currentIp(): string
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) wp_unslash($_SERVER['REMOTE_ADDR']) : '';

        return sanitize_text_field($ip);
    }
}
