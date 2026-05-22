<?php
/**
 * Plugin Name: BS23 Form Builder
 * Description: Drag-and-drop WordPress form builder.
 * Version: 0.1.0
 * Author: BS23
 * License: GPL-2.0-or-later
 * Text Domain: bs23-form-builder
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('BS23_FORM_BUILDER_FILE', __FILE__);
define('BS23_FORM_BUILDER_DIR', plugin_dir_path(__FILE__));
define('BS23_FORM_BUILDER_URL', plugin_dir_url(__FILE__));
define('BS23_FORM_BUILDER_VERSION', '0.1.0');

require_once BS23_FORM_BUILDER_DIR . 'includes/Plugin.php';
require_once BS23_FORM_BUILDER_DIR . 'includes/PostTypes/FormPostType.php';
require_once BS23_FORM_BUILDER_DIR . 'includes/Builder/SchemaValidator.php';
require_once BS23_FORM_BUILDER_DIR . 'includes/Rest/FormRestController.php';

add_action('plugins_loaded', static function (): void {
    (new BS23\FormBuilder\Plugin())->register();
});
