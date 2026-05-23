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
        if (! class_exists(FormWidget::class)) {
            return;
        }

        $widgetsManager->register(new FormWidget());
    }
}
