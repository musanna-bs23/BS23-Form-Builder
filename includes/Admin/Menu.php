<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Admin;

final class Menu
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            __('BS23 Forms', 'bs23-form-builder'),
            __('BS23 Forms', 'bs23-form-builder'),
            'manage_options',
            'bs23-form-builder',
            [$this, 'renderBuilderPage'],
            'dashicons-feedback',
            56
        );
    }

    public function renderBuilderPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'bs23-form-builder'));
        }

        echo '<div id="bs23-form-builder-root">';
        echo '<p>' . esc_html__('Loading BS23 Form Builder...', 'bs23-form-builder') . '</p>';
        echo '</div>';
    }

    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'toplevel_page_bs23-form-builder') {
            return;
        }

        $assetFile = BS23_FORM_BUILDER_DIR . 'assets/admin/build/index.asset.php';
        $asset = file_exists($assetFile) ? require $assetFile : ['dependencies' => [], 'version' => BS23_FORM_BUILDER_VERSION];

        wp_enqueue_script(
            'bs23-form-builder-admin',
            BS23_FORM_BUILDER_URL . 'assets/admin/build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
        wp_enqueue_style(
            'bs23-form-builder-admin',
            BS23_FORM_BUILDER_URL . 'assets/admin/build/index.css',
            [],
            $asset['version']
        );
        wp_localize_script('bs23-form-builder-admin', 'bs23FormBuilder', [
            'restUrl' => esc_url_raw(rest_url('bs23-form-builder/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
}
