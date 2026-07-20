<?php
/**
 * Plugin Name: WP Seed Content Kit
 * Description: Modular editorial content and reusable displays for WordPress.
 * Version: 0.6.0-dev
 * Requires at least: 6.5
 * Requires PHP: 7.0
 * Author: WP Seed Content Kit
 * Text Domain: wp-seed-content-kit
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_SEED_CONTENT_KIT_VERSION', '0.6.0-dev');
define('WP_SEED_CONTENT_KIT_FILE', __FILE__);
define('WP_SEED_CONTENT_KIT_DIR', plugin_dir_path(__FILE__));
define('WP_SEED_CONTENT_KIT_URL', plugin_dir_url(__FILE__));

require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/helpers.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-contract.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-render-result.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-registry.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/content-data.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/dynamic-data.php';
$wp_seed_content_gutenberg_block_bindings_file = WP_SEED_CONTENT_KIT_DIR . 'includes/integrations/gutenberg/block-bindings.php';
if (file_exists($wp_seed_content_gutenberg_block_bindings_file)) {
    require_once $wp_seed_content_gutenberg_block_bindings_file;
}
$wp_seed_content_divi_dynamic_content_file = WP_SEED_CONTENT_KIT_DIR . 'includes/integrations/divi/dynamic-content.php';
if (file_exists($wp_seed_content_divi_dynamic_content_file)) {
    require_once $wp_seed_content_divi_dynamic_content_file;
}
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/modules.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/collections.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/assets.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/manual-order.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/module-menu.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/templates.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-renderer.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-public-renderer.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/update-checker.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/cards/render.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/cards/shortcode.php';

if (is_admin()) {
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/admin/modules-page.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/admin/generators-page.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/integrations/builders.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/integrations/yoast.php';
}

if (wp_seed_content_kit_is_module_active('testimonials')) {
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/post-type.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/meta-boxes.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/save-meta.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/render.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/template-data.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/shortcode.php';
}
if (wp_seed_content_kit_is_module_active('quotes')) {
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/post-type.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/meta-boxes.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/save-meta.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/render.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/template-data.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/shortcode.php';
}
if (wp_seed_content_kit_is_module_active('directory')) {
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/bootstrap.php';
}

function wp_seed_content_kit_activate()
{
    wp_seed_content_register_template_post_type();

    if (wp_seed_content_kit_is_module_active('testimonials')) {
        wp_seed_content_register_testimonial_post_type();
    }
    if (wp_seed_content_kit_is_module_active('quotes')) {
        wp_seed_content_register_quote_post_type();
    }
    if (wp_seed_content_kit_is_module_active('directory')) {
        wp_seed_content_directory_grant_capabilities();
        wp_seed_content_directory_register_post_type();
    }

    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wp_seed_content_kit_activate');

function wp_seed_content_kit_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_seed_content_kit_deactivate');

function wp_seed_content_kit_init()
{
    wp_seed_content_register_template_post_type();

    if (wp_seed_content_kit_is_module_active('testimonials')) {
        wp_seed_content_register_testimonial_post_type();
    }
    if (wp_seed_content_kit_is_module_active('quotes')) {
        wp_seed_content_register_quote_post_type();
    }
    if (wp_seed_content_kit_is_module_active('directory')) {
        wp_seed_content_directory_register_post_type();
    }
}
add_action('init', 'wp_seed_content_kit_init');
