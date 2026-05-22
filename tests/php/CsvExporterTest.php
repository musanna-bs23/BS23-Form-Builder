<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Export\CsvExporter;
use WP_UnitTestCase;

final class CsvExporterTest extends WP_UnitTestCase
{
    public function test_csv_export_includes_headers_and_entry_values(): void
    {
        $csv = (new CsvExporter())->export([
            [
                'id' => 10,
                'form_id' => 20,
                'form_title' => 'Contact',
                'created_at' => '2026-05-22 10:00:00',
                'user_id' => 0,
                'user_ip' => '127.0.0.1',
                'user_agent' => 'Test',
                'entry_data' => ['email' => 'person@example.com', 'topics' => ['News', 'Offers']],
            ],
        ]);

        $this->assertStringContainsString('Entry ID,Form ID,Form Title', $csv);
        $this->assertStringContainsString('person@example.com', $csv);
        $this->assertStringContainsString('"News, Offers"', $csv);
    }
}
