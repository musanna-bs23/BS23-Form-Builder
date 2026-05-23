<?php
declare(strict_types=1);

namespace {
    function add_action(string $hook, $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $GLOBALS['bs23_test_actions'][] = compact('hook', 'callback', 'priority', 'acceptedArgs');

        return true;
    }

    function did_action(string $hook): int
    {
        return (int) ($GLOBALS['bs23_test_did_actions'][$hook] ?? 0);
    }
}

namespace BS23\FormBuilder\Tests\Unit {
    use BS23\FormBuilder\Elementor\Integration;
    use PHPUnit\Framework\TestCase;

    final class ElementorIntegrationTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['bs23_test_actions'] = [];
            $GLOBALS['bs23_test_did_actions'] = [];
        }

        public function test_register_does_not_hook_when_elementor_has_not_loaded(): void
        {
            (new Integration())->register();

            self::assertSame([], $GLOBALS['bs23_test_actions']);
        }

        public function test_register_hooks_widget_registration_when_elementor_has_loaded(): void
        {
            $GLOBALS['bs23_test_did_actions']['elementor/loaded'] = 1;

            (new Integration())->register();

            self::assertCount(1, $GLOBALS['bs23_test_actions']);
            self::assertSame('elementor/widgets/register', $GLOBALS['bs23_test_actions'][0]['hook']);
            self::assertSame(10, $GLOBALS['bs23_test_actions'][0]['priority']);
        }

        public function test_register_widgets_skips_when_elementor_widget_base_is_missing(): void
        {
            $manager = new TestWidgetsManager();

            (new Integration())->registerWidgets($manager);

            self::assertSame(0, $manager->registered);
        }
    }

    final class TestWidgetsManager
    {
        public int $registered = 0;

        public function register($widget): void
        {
            $this->registered++;
        }
    }
}
