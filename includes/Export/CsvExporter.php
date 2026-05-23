<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Export;

final class CsvExporter
{
    public function export(array $entries): string
    {
        $fieldKeys = [];
        foreach ($entries as $entry) {
            foreach (($entry['entry_data'] ?? []) as $key => $_value) {
                $fieldKeys[$key] = true;
            }
        }

        $handle = fopen('php://temp', 'r+');
        $headers = array_merge(['Entry ID', 'Form ID', 'Form Title', 'Created At', 'User ID', 'User IP', 'User Agent'], array_keys($fieldKeys));
        fputcsv($handle, $headers);

        foreach ($entries as $entry) {
            $row = [
                $entry['id'],
                $entry['form_id'],
                $entry['form_title'],
                $entry['created_at'],
                $entry['user_id'],
                $entry['user_ip'],
                $entry['user_agent'],
            ];

            foreach (array_keys($fieldKeys) as $key) {
                $value = $entry['entry_data'][$key] ?? '';
                $row[] = $this->formatValue($value);
            }

            fputcsv($handle, $row);
        }

        rewind($handle);

        return (string) stream_get_contents($handle);
    }

    /**
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        if (is_array($value) && isset($value['url'])) {
            $name = (string) ($value['name'] ?? '');
            $url = (string) ($value['url'] ?? '');

            return trim($name . ($name !== '' && $url !== '' ? ' - ' : '') . $url);
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($item): string => $this->formatValue($item), $value));
        }

        return (string) $value;
    }
}
