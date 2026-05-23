<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use WP_REST_Request;
use WP_UnitTestCase;

final class EntriesRestControllerTest extends WP_UnitTestCase
{
    public function test_entries_rest_requires_permission(): void
    {
        do_action('rest_api_init');

        $response = rest_do_request(new WP_REST_Request('GET', '/bs23-form-builder/v1/entries'));

        $this->assertSame(401, $response->get_status());
    }
}
