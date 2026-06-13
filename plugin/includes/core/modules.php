<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_default_module_options()
{
    return array(
        'testimonials' => true,
        'quotes' => true,
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
        'quotes' => (bool) $options['quotes'],
    );
}

function wp_seed_content_kit_get_default_module_menu_visibility()
{
    return array(
        'seed_testimonial' => 'plugin',
    );
}

function wp_seed_content_kit_get_module_menu_visibility()
{
    $stored = get_option('wp_seed_content_kit_module_menu_visibility', array());
    if (!is_array($stored)) {
        $stored = array();
    }

    $visibility = wp_parse_args($stored, wp_seed_content_kit_get_default_module_menu_visibility());
    $allowed = array('plugin', 'root');

    foreach ($visibility as $post_type => $value) {
        if (!in_array($value, $allowed, true)) {
            $visibility[$post_type] = 'plugin';
        }
    }

    return array(
        'seed_testimonial' => $visibility['seed_testimonial'],
    );
}

function wp_seed_content_kit_get_module_menu_location($post_type)
{
    $post_type = sanitize_key($post_type);
    $visibility = wp_seed_content_kit_get_module_menu_visibility();

    return isset($visibility[$post_type]) ? $visibility[$post_type] : 'plugin';
}

function wp_seed_content_kit_get_post_type_menu_parent($post_type)
{
    if ('root' === wp_seed_content_kit_get_module_menu_location($post_type)) {
        return true;
    }

    return false;
}

function wp_seed_content_kit_is_module_active($module)
{
    $module = sanitize_key($module);

    if ('cards' === $module) {
        return true;
    }

    if (in_array($module, array('directory', 'audio'), true)) {
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
        'testimonials' => array(
            'label' => __('Témoignages', 'wp-seed-content-kit'),
            'active' => wp_seed_content_kit_is_module_active('testimonials'),
            'planned' => false,
            'activable' => true,
            'shortcode' => '[seed_testimonials]',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'quotes' => array(
            'label' => __('Citations', 'wp-seed-content-kit'),
            'active' => wp_seed_content_kit_is_module_active('quotes'),
            'planned' => false,
            'activable' => false,
            'shortcode' => '[seed_quotes]',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'directory' => array(
            'label' => __('Annuaire', 'wp-seed-content-kit'),
            'active' => false,
            'planned' => true,
            'activable' => false,
            'shortcode' => '',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'audio' => array(
            'label' => __('Créations sonores', 'wp-seed-content-kit'),
            'active' => false,
            'planned' => true,
            'activable' => false,
            'shortcode' => '',
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
    );
}
