<?php
/**
 * GitHub Releases update integration.
 *
 * Uses Plugin Update Checker to expose release assets through the standard
 * WordPress plugin update UI.
 */

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_filter_puc_request_info_options($options)
{
    if (!is_array($options)) {
        $options = array();
    }

    if (!isset($options['headers']) || !is_array($options['headers'])) {
        $options['headers'] = array();
    }

    $version = defined('WP_SEED_CONTENT_KIT_VERSION') ? WP_SEED_CONTENT_KIT_VERSION : 'unknown';
    $site_url = function_exists('home_url') ? home_url('/') : site_url('/');

    $options['timeout'] = 10;
    $options['headers']['Accept'] = 'application/vnd.github+json';
    $options['headers']['User-Agent'] = sprintf(
        'WP-Seed-Content-Kit/%s; %s',
        $version,
        esc_url_raw($site_url)
    );

    return $options;
}
add_filter('puc_request_info_options-wp-seed-content-kit', 'wp_seed_content_kit_filter_puc_request_info_options');

$wp_seed_content_kit_puc_file = WP_SEED_CONTENT_KIT_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

if (file_exists($wp_seed_content_kit_puc_file)) {
    require_once $wp_seed_content_kit_puc_file;

    $wp_seed_content_kit_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/warzou/WP-seed-content-kit/',
        WP_SEED_CONTENT_KIT_FILE,
        'wp-seed-content-kit'
    );

    $wp_seed_content_kit_update_checker->getVcsApi()->enableReleaseAssets('/wp-seed-content-kit\.zip$/i');
}
