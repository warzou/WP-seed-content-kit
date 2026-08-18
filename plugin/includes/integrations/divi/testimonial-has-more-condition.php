<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_testimonial_has_more_condition_name()
{
    return 'wpsckTestimonialHasMore';
}

function wp_seed_content_divi_testimonial_has_more_condition_value_key()
{
    return 'wpsckResolvedTestimonialHasMore';
}

function wp_seed_content_divi_testimonial_evaluate_has_more_condition($result, $condition_name, $condition_settings, $condition_id)
{
    unset($condition_id);

    if (wp_seed_content_divi_testimonial_has_more_condition_name() !== $condition_name) {
        return $result;
    }

    $value_key = wp_seed_content_divi_testimonial_has_more_condition_value_key();
    $resolved_value = is_array($condition_settings) && isset($condition_settings[$value_key])
        ? (string) $condition_settings[$value_key]
        : '';

    if (false !== strpos($resolved_value, '$variable(')) {
        return true;
    }

    return in_array(strtolower(trim($resolved_value)), array('1', 'true', 'on', 'yes'), true);
}
add_filter(
    'divi_module_options_conditions_is_custom_condition_true',
    'wp_seed_content_divi_testimonial_evaluate_has_more_condition',
    10,
    4
);

function wp_seed_content_divi_testimonial_has_more_condition_asset_version()
{
    $path = __DIR__ . '/testimonial-has-more-condition/visual-builder.js';
    $hash = is_file($path) ? hash_file('sha256', $path) : false;

    return $hash ? substr($hash, 0, 16) : WP_SEED_CONTENT_KIT_VERSION;
}

function wp_seed_content_register_divi_testimonial_has_more_condition_assets()
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
            'name' => 'wp-seed-content-kit-divi-testimonial-has-more-condition',
            'version' => wp_seed_content_divi_testimonial_has_more_condition_asset_version(),
            'script' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'includes/integrations/divi/testimonial-has-more-condition/visual-builder.js',
                'deps' => array('divi-field-library', 'divi-vendor-wp-hooks'),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ),
        )
    );
}
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    'wp_seed_content_register_divi_testimonial_has_more_condition_assets'
);
