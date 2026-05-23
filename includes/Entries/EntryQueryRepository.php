<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Entries;

use BS23\FormBuilder\Install\Installer;

final class EntryQueryRepository
{
    public function list(array $args): array
    {
        global $wpdb;

        $page = max(1, absint($args['page'] ?? 1));
        $perPage = min(100, max(1, absint($args['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $order = strtoupper((string) ($args['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        [$where, $params] = $this->whereSql($args);
        $table = Installer::entriesTableName();

        $totalSql = "SELECT COUNT(*) FROM {$table} e {$where}";
        $total = (int) $wpdb->get_var($params ? $wpdb->prepare($totalSql, $params) : $totalSql);

        $sql = "SELECT e.* FROM {$table} e {$where} ORDER BY e.created_at {$order}, e.id {$order} LIMIT %d OFFSET %d";
        $rows = $wpdb->get_results($wpdb->prepare($sql, array_merge($params, [$perPage, $offset])), ARRAY_A);

        return [
            'entries' => array_map([$this, 'formatRow'], $rows ?: []),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function find(int $id): ?array
    {
        global $wpdb;

        $table = Installer::entriesTableName();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id), ARRAY_A);

        return $row ? $this->formatRow($row) : null;
    }

    public function summary(array $args = []): array
    {
        global $wpdb;

        $table = Installer::entriesTableName();
        $todayStart = gmdate('Y-m-d 00:00:00');
        $weekStart = gmdate('Y-m-d 00:00:00', strtotime('-6 days'));

        return [
            'total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"),
            'today' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $todayStart)),
            'week' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $weekStart)),
            'last_submission' => (string) $wpdb->get_var("SELECT created_at FROM {$table} ORDER BY created_at DESC, id DESC LIMIT 1"),
            'trend' => $this->trend(14),
            'forms' => $this->formBreakdown(),
        ];
    }

    public function trend(int $days = 14): array
    {
        global $wpdb;

        $table = Installer::entriesTableName();
        $start = gmdate('Y-m-d 00:00:00', strtotime('-' . max(0, $days - 1) . ' days'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) entry_date, COUNT(*) total FROM {$table} WHERE created_at >= %s GROUP BY DATE(created_at) ORDER BY entry_date ASC",
            $start
        ), ARRAY_A);
        $map = [];
        foreach ($rows ?: [] as $row) {
            $map[$row['entry_date']] = (int) $row['total'];
        }

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = gmdate('Y-m-d', strtotime("-{$i} days"));
            $trend[] = ['date' => $date, 'total' => $map[$date] ?? 0];
        }

        return $trend;
    }

    public function formBreakdown(): array
    {
        global $wpdb;

        $table = Installer::entriesTableName();
        $posts = $wpdb->posts;
        $rows = $wpdb->get_results(
            "SELECT e.form_id, COALESCE(p.post_title, CONCAT('Form #', e.form_id)) form_title, COUNT(*) total
            FROM {$table} e
            LEFT JOIN {$posts} p ON p.ID = e.form_id
            GROUP BY e.form_id, p.post_title
            ORDER BY total DESC, form_title ASC
            LIMIT 12",
            ARRAY_A
        );

        return array_map(static function (array $row): array {
            return [
                'form_id' => (int) $row['form_id'],
                'form_title' => (string) $row['form_title'],
                'total' => (int) $row['total'],
            ];
        }, $rows ?: []);
    }

    public function delete(int $id): bool
    {
        global $wpdb;

        return $wpdb->delete(Installer::entriesTableName(), ['id' => $id], ['%d']) !== false;
    }

    public function bulkDelete(array $ids): int
    {
        global $wpdb;

        $ids = array_values(array_filter(array_map('absint', $ids)));
        if ($ids === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        return (int) $wpdb->query($wpdb->prepare(
            'DELETE FROM ' . Installer::entriesTableName() . " WHERE id IN ({$placeholders})",
            $ids
        ));
    }

    public function allForExport(array $args): array
    {
        $args['page'] = 1;
        $args['per_page'] = 1000;

        return $this->list($args)['entries'];
    }

    private function whereSql(array $args): array
    {
        global $wpdb;

        $where = [];
        $params = [];

        if (! empty($args['form_id'])) {
            $where[] = 'e.form_id = %d';
            $params[] = absint($args['form_id']);
        }

        if (! empty($args['date_from'])) {
            $where[] = 'e.created_at >= %s';
            $params[] = sanitize_text_field((string) $args['date_from']) . ' 00:00:00';
        }

        if (! empty($args['date_to'])) {
            $where[] = 'e.created_at <= %s';
            $params[] = sanitize_text_field((string) $args['date_to']) . ' 23:59:59';
        }

        if (! empty($args['search'])) {
            $where[] = 'e.entry_data LIKE %s';
            $params[] = '%' . $wpdb->esc_like(sanitize_text_field((string) $args['search'])) . '%';
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function formatRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'form_id' => (int) $row['form_id'],
            'form_title' => get_the_title((int) $row['form_id']) ?: sprintf('Form #%d', (int) $row['form_id']),
            'entry_data' => json_decode((string) $row['entry_data'], true) ?: [],
            'user_id' => (int) $row['user_id'],
            'user_ip' => (string) $row['user_ip'],
            'user_agent' => (string) $row['user_agent'],
            'created_at' => (string) $row['created_at'],
        ];
    }
}
