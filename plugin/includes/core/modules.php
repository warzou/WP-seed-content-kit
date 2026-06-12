<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_default_module_options()
{
    return array(
        'testimonials' => true,
    );
}

function wp_seed_content_kit_get_module_options()
{
    $stored = get_option('wp_seed_content_kit_modules', array());
    if (!is_array($stored)) {
        $stored = array();
    }

    $options = wp_parse_args($stored, wp_seed_content_kit_get_default_module_options());

    return array(
        'testimonials' => (bool) $options['testimonials'],
    );
}

function wp_seed_content_kit_is_module_active($module)
{
    $module = sanitize_key($module);

    if ('cards' === $module) {
        return true;
    }

    if ('quotes' === $module) {
        return false;
    }

    $options = wp_seed_content_kit_get_module_options();

    return isset($options[$module]) ? (bool) $options[$module] : false;
}

function wp_seed_content_kit_get_builder_usage_help()
{
    return array(
        __('Gutenberg: bloc Shortcode', 'wp-seed-content-kit'),
        __('Spectra: bloc Shortcode ou Container', 'wp-seed-content-kit'),
        __('Divi: module Code ou Texte', 'wp-seed-content-kit'),
        __('Astra: page ou bloc classique', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_kit_get_modules()
{
    return array(
        'cards' => array(
            'label' => __('Cards', 'wp-seed-content-kit'),
            'active' => true,
            'planned' => false,
            'activable' => false,
            'shortcode' => '[seed_cards]',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'testimonials' => array(
            'label' => __('Testimonials', 'wp-seed-content-kit'),
            'active' => wp_seed_content_kit_is_module_active('testimonials'),
            'planned' => false,
            'activable' => true,
            'shortcode' => '[seed_testimonials]',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'quotes' => array(
            'label' => __('Quotes', 'wp-seed-content-kit'),
            'active' => false,
            'planned' => true,
            'activable' => false,
            'shortcode' => '[seed_quotes]',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
    );
}
