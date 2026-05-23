<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Entries\EntryQueryRepository;
use BS23\FormBuilder\Install\Installer;
use BS23\FormBuilder\PostTypes\FormPostType;
use WP_UnitTestCase;

final class EntryQueryRepositoryTest extends WP_UnitTestCase
{
    private EntryQueryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        do_action('init');
        Installer::activate();
        $this->repository = new EntryQueryRepository();
    }

    public function test_repository_lists_and_filters_entries(): void
    {
        $formId = $this->createForm('Contact');
        $otherFormId = $this->createForm('Quote');
        $this->insertEntry($formId, ['email' => 'one@example.com']);
        $this->insertEntry($otherFormId, ['email' => 'two@example.com']);

        $result = $this->repository->list(['form_id' => $formId, 'page' => 1, 'per_page' => 10]);

        $this->assertSame(1, $result['total']);
        $this->assertSame($formId, $result['entries'][0]['form_id']);
        $this->assertSame('one@example.com', $result['entries'][0]['entry_data']['email']);
    }

    public function test_summary_and_delete_operations(): void
    {
        $formId = $this->createForm('Contact');
        $first = $this->insertEntry($formId, ['email' => 'one@example.com']);
        $second = $this->insertEntry($formId, ['email' => 'two@example.com']);

        $summary = $this->repository->summary();

        $this->assertGreaterThanOrEqual(2, $summary['total']);
        $this->assertNotEmpty($summary['trend']);
        $this->assertNotEmpty($summary['forms']);

        $this->assertTrue($this->repository->delete($first));
        $this->assertSame(1, $this->repository->bulkDelete([$second]));
    }

    private function createForm(string $title): int
    {
        return (int) self::factory()->post->create([
            'post_type' => FormPostType::NAME,
            'post_title' => $title,
            'post_status' => 'publish',
        ]);
    }

    private function insertEntry(int $formId, array $data): int
    {
        global $wpdb;

        $wpdb->insert(Installer::entriesTableName(), [
            'form_id' => $formId,
            'entry_data' => wp_json_encode($data),
            'user_id' => 0,
            'user_ip' => '127.0.0.1',
            'user_agent' => 'Test',
            'created_at' => current_time('mysql'),
        ]);

        return (int) $wpdb->insert_id;
    }
}
