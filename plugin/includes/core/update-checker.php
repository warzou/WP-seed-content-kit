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
