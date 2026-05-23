<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\Submission\UploadStorage;
use WP_UnitTestCase;

final class UploadStorageTest extends WP_UnitTestCase
{
    public function test_store_upload_moves_file_and_returns_metadata(): void
    {
        $tmp = wp_tempnam('bs23-upload-test.txt');
        file_put_contents($tmp, 'hello');

        $stored = (new UploadStorage())->store(123, 'resume', [
            'name' => 'Resume Test.txt',
            'type' => 'text/plain',
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => 5,
        ]);

        $this->assertSame('Resume-Test.txt', $stored['name']);
        $this->assertSame('text/plain', $stored['type']);
        $this->assertSame(5, $stored['size']);
        $this->assertStringContainsString('bs23-form-builder/123', $stored['url']);
        $this->assertFileExists($stored['path']);
    }
}
