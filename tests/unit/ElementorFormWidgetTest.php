<?php
declare(strict_types=1);

namespace Elementor {
    abstract class Widget_Base
    {
        private array $settings = [];
        public array $controls = [];
        public array $sections = [];

        public function setTestSettings(array $settings): void
        {
            $this->settings = $settings;
        }

        public function get_settings_for_display($setting = null)
        {
            if ($setting === null) {
                return $this->settings;
            }

            return $this->settings[$setting] ?? null;
        }

        protected function start_controls_section(string $sectionId, array $args = []): void
        {
            $this->sections[$sectionId] = $args;
        }

        protected function add_control(string $controlId, array $args = []): void
        {
            $this->controls[$controlId] = $args;
        }

        protected function end_controls_section(): void
        {
        }
    }

    final class Controls_Manager
    {
        public const SELECT = 'select';
        public const RAW_HTML = 'raw_html';
        public const TAB_CONTENT = 'content';
        public const TAB_STYLE = 'style';
    }
}

namespace {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return $text;
    }

    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    function absint($value): int
    {
        return max(0, (int) $value);
    }

    function get_posts(array $args): array
    {
        $GLOBALS['bs23_test_get_posts_args'] = $args;

        return $GLOBALS['bs23_test_posts'] ?? [];
    }

    function do_shortcode(string $shortcode): string
    {
        $GLOBALS['bs23_test_shortcode'] = $shortcode;

        return '<form class="bs23-form"></form>';
    }
}

namespace BS23\FormBuilder\Tests\Unit {
    use BS23\FormBuilder\Elementor\FormWidget;
    use PHPUnit\Framework\TestCase;

    final class ElementorFormWidgetTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['bs23_test_posts'] = [];
            unset($GLOBALS['bs23_test_get_posts_args'], $GLOBALS['bs23_test_shortcode']);
        }

        public function test_widget_exposes_elementor_metadata(): void
        {
            $widget = new TestableFormWidget();

            self::assertSame('bs23_form_builder', $widget->get_name());
            self::assertSame('BS23 Form', $widget->get_title());
            self::assertSame('eicon-form-horizontal', $widget->get_icon());
            self::assertContains('general', $widget->get_categories());
        }

        public function test_widget_registers_form_select_options_from_published_forms(): void
        {
            $GLOBALS['bs23_test_posts'] = [
                (object) ['ID' => 11, 'post_title' => 'Contact'],
                (object) ['ID' => 12, 'post_title' => 'Quote'],
            ];

            $widget = new TestableFormWidget();
            $widget->registerControlsForTest();

            self::assertSame('bs23_form', $GLOBALS['bs23_test_get_posts_args']['post_type']);
            self::assertSame('publish', $GLOBALS['bs23_test_get_posts_args']['post_status']);
            self::assertSame(\Elementor\Controls_Manager::SELECT, $widget->controls['form_id']['type']);
            self::assertSame([
                11 => 'Contact',
                12 => 'Quote',
            ], $widget->controls['form_id']['options']);
        }

        public function test_widget_renders_placeholder_when_no_form_is_selected(): void
        {
            $widget = new TestableFormWidget();
            $widget->setTestSettings(['form_id' => 0]);

            ob_start();
            $widget->renderForTest();
            $html = (string) ob_get_clean();

            self::assertStringContainsString('Select a BS23 form', $html);
            self::assertArrayNotHasKey('bs23_test_shortcode', $GLOBALS);
        }

        public function test_widget_renders_selected_form_shortcode(): void
        {
            $widget = new TestableFormWidget();
            $widget->setTestSettings(['form_id' => 77]);

            ob_start();
            $widget->renderForTest();
            $html = (string) ob_get_clean();

            self::assertSame('[bs23_form id="77"]', $GLOBALS['bs23_test_shortcode']);
            self::assertStringContainsString('class="bs23-form"', $html);
        }
    }

    final class TestableFormWidget extends FormWidget
    {
        public function registerControlsForTest(): void
        {
            $this->register_controls();
        }

        public function renderForTest(): void
        {
            $this->render();
        }
    }
}
