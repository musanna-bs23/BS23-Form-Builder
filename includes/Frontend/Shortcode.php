<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Frontend;

use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\FormRestController;
use BS23\FormBuilder\Settings\FormSettings;

final class Shortcode
{
    private Renderer $renderer;
    private FormSettings $settings;

    public function __construct(Renderer $renderer, ?FormSettings $settings = null)
    {
        $this->renderer = $renderer;
        $this->settings = $settings ?: new FormSettings();
    }

    public function register(): void
    {
        add_shortcode('bs23_form', [$this, 'render']);
    }

    public function render($atts): string
    {
        $atts = shortcode_atts(['id' => 0], is_array($atts) ? $atts : [], 'bs23_form');
        $formId = absint($atts['id']);

        if ($formId < 1 || get_post_type($formId) !== FormPostType::NAME) {
            return '';
        }

        $schema = get_post_meta($formId, FormRestController::META_KEY, true);
        if (! is_array($schema)) {
            return '';
        }

        $this->enqueueAssets();

        return $this->renderer->render($formId, $schema, $this->settings->get($formId));
    }

    private function enqueueAssets(): void
    {
        wp_enqueue_style(
            'bs23-form-builder-frontend',
            BS23_FORM_BUILDER_URL . 'assets/frontend/forms.css',
            [],
            BS23_FORM_BUILDER_VERSION
        );

        $assetFile = BS23_FORM_BUILDER_DIR . 'assets/frontend/build/index.asset.php';
        $asset = file_exists($assetFile) ? require $assetFile : ['dependencies' => [], 'version' => BS23_FORM_BUILDER_VERSION];

        wp_enqueue_script(
            'bs23-form-builder-frontend',
            BS23_FORM_BUILDER_URL . 'assets/frontend/build/index.js',
            $asset['dependencies'] ?? [],
            $asset['version'] ?? BS23_FORM_BUILDER_VERSION,
            true
        );
    }
}
