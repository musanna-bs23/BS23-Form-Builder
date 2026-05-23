<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Elementor;

final class Integration
{
    public function register(): void
    {
        if (! did_action('elementor/loaded')) {
            return;
        }

        add_action('elementor/widgets/register', [$this, 'registerWidgets']);
    }

    public function registerWidgets($widgetsManager): void
    {
        if (! class_exists('\Elementor\Widget_Base')) {
            return;
        }

        if (! class_exists(FormWidget::class, false) && defined('BS23_FORM_BUILDER_DIR')) {
            require_once BS23_FORM_BUILDER_DIR . 'includes/Elementor/FormWidget.php';
        }

        if (! class_exists(FormWidget::class, false)) {
            return;
        }

        $widgetsManager->register(new FormWidget());
    }
}
