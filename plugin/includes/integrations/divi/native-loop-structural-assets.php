<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_native_loop_structural_style_version()
{
    $path = WP_SEED_CONTENT_KIT_DIR . 'assets/css/native-loop-structural.css';
    $hash = is_readable($path) ? hash_file('sha256', $path) : false;

    if (!is_string($hash) || '' === $hash) {
        return WP_SEED_CONTENT_KIT_VERSION;
    }

    return WP_SEED_CONTENT_KIT_VERSION . '-' . substr($hash, 0, 12);
}

function wp_seed_content_enqueue_divi_native_loop_structural_style()
{
    wp_enqueue_style(
        'wp-seed-content-native-loop-structural',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/native-loop-structural.css',
        array(),
        wp_seed_content_native_loop_structural_style_version()
    );
}
add_action('wp_enqueue_scripts', 'wp_seed_content_enqueue_divi_native_loop_structural_style');

function wp_seed_content_register_divi_native_loop_structural_builder_style()
{
    static $registered = false;

    if (
        $registered
        || !function_exists('et_core_is_fb_enabled')
        || !et_core_is_fb_enabled()
        || !class_exists('ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager')
    ) {
        return;
    }

    $registered = true;
    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
        array(
            'name' => 'wp-seed-content-kit-divi-native-loop-structural',
            'version' => wp_seed_content_native_loop_structural_style_version(),
            'style' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'assets/css/native-loop-structural.css',
                'deps' => array(),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ),
        )
    );
}
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    'wp_seed_content_register_divi_native_loop_structural_builder_style'
);
