<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use WP_UnitTestCase;

final class FormPostTypeTest extends WP_UnitTestCase
{
    public function test_form_post_type_is_registered(): void
    {
        do_action('init');

        $post_type = get_post_type_object('bs23_form');

        $this->assertNotNull($post_type);
        $this->assertFalse($post_type->public);
        $this->assertTrue($post_type->show_ui);
        $this->assertSame('BS23 Forms', $post_type->labels->name);
    }
}
