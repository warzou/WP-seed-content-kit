<?php
/**
 * Plugin Name: WP Seed Content Kit
 * Description: Modular editorial content and reusable displays for WordPress.
 * Version: 0.2.3
 * Author: WP Seed Content Kit
 * Text Domain: wp-seed-content-kit
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_SEED_CONTENT_KIT_VERSION', '0.2.3');
define('WP_SEED_CONTENT_KIT_FILE', __FILE__);
define('WP_SEED_CONTENT_KIT_DIR', plugin_dir_path(__FILE__));
define('WP_SEED_CONTENT_KIT_URL', plugin_dir_url(__FILE__));

require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/helpers.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/modules.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/assets.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/templates.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-renderer.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/core/update-checker.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/cards/render.php';
require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/cards/shortcode.php';

if (is_admin()) {
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/admin/modules-page.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/admin/generators-page.php';
}

if (wp_seed_content_kit_is_module_active('testimonials')) {
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/post-type.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/meta-boxes.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/save-meta.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/render.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/template-data.php';
    require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/shortcode.php';
}

function wp_seed_content_kit_activate()
{
    wp_seed_content_register_template_post_type();

    if (wp_seed_content_kit_is_module_active('testimonials')) {
        wp_seed_content_register_testimonial_post_type();
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
}
add_action('init', 'wp_seed_content_kit_init');
