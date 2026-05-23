<?php
declare(strict_types=1);

$_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WordPress test suite not found. Set WP_TESTS_DIR.\n");
    exit(1);
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', static function (): void {
    require dirname(__DIR__) . '/bs23-form-builder.php';
});

require $_tests_dir . '/includes/bootstrap.php';
