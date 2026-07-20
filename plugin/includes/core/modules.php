<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_default_module_options()
{
    return array(
        'testimonials' => true,
        'quotes' => true,
        'directory' => true,
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
        'directory' => (bool) $options['directory'],
    );
}

function wp_seed_content_kit_get_default_module_menu_visibility()
{
    return array(
        'seed_testimonial' => array(
            'show_in_menu' => false,
            'roles' => array('administrator'),
        ),
        'seed_quote' => array(
            'show_in_menu' => false,
            'roles' => array('administrator'),
        ),
        'seed_directory' => array(
            'show_in_menu' => false,
            'roles' => array('administrator'),
        ),
    );
}

function wp_seed_content_kit_get_user_roles()
{
    if (!function_exists('wp_roles')) {
        return array();
    }

    $roles = wp_roles()->roles;
    if (!is_array($roles)) {
        return array();
    }

    ksort($roles);

    return array_keys($roles);
}

function wp_seed_content_kit_get_module_menu_visibility()
{
    $stored = get_option('wp_seed_content_kit_module_menu_visibility', array());
    if (!is_array($stored)) {
        $stored = array();
    }

    $defaults = wp_seed_content_kit_get_default_module_menu_visibility();
    $all_roles = wp_seed_content_kit_get_user_roles();

    $visibility = array();

    foreach ($defaults as $post_type => $default_visibility) {
        $current = isset($stored[$post_type]) ? $stored[$post_type] : $default_visibility;
        $show_in_menu = false;
        $roles = array();

        if (is_array($current)) {
            if (isset($current['show_in_menu'])) {
                $show_in_menu = wp_seed_content_bool_attr($current['show_in_menu'], false);
            }
            if (isset($current['roles']) && is_array($current['roles'])) {
                foreach ((array) $current['roles'] as $role) {
                    $role = sanitize_key((string) $role);
                    if (in_array($role, $all_roles, true)) {
                        $roles[] = $role;
                    }
                }
            }
        } elseif ('root' === sanitize_text_field((string) $current)) {
            $show_in_menu = true;
            $roles = array('administrator');
        }

        if (empty($roles)) {
            $roles = (array) $default_visibility['roles'];
        }

        $visibility[$post_type] = array(
            'show_in_menu' => (bool) $show_in_menu,
            'roles' => array_values(array_unique(array_map('sanitize_key', $roles))),
        );
    }

    return $visibility;
}

function wp_seed_content_kit_get_module_menu_visibility_for_post_type($post_type)
{
    $post_type = sanitize_key($post_type);
    $visibility = wp_seed_content_kit_get_module_menu_visibility();

    if (!isset($visibility[$post_type])) {
        return array(
            'show_in_menu' => false,
            'roles' => array(),
        );
    }

    return $visibility[$post_type];
}

function wp_seed_content_kit_get_module_menu_location($post_type)
{
    $config = wp_seed_content_kit_get_module_menu_visibility_for_post_type($post_type);
    if (!empty($config['show_in_menu'])) {
        return 'root';
    }
    return 'plugin';
}

function wp_seed_content_kit_get_post_type_menu_parent($post_type)
{
    $post_type = sanitize_key($post_type);

    if (in_array($post_type, array('seed_testimonial', 'seed_quote', 'seed_directory'), true)) {
        return 'wp-seed-content-kit';
    }

    return false;
}

function wp_seed_content_kit_get_admin_menu_icon($type)
{
    $icons = array(
        'parent' => 'dashicons-screenoptions',
        'testimonials' => 'dashicons-testimonial',
        'quotes' => 'dashicons-format-quote',
        'directory' => 'dashicons-admin-users',
    );

    $type = sanitize_key($type);
    if (!isset($icons[$type])) {
        $type = 'parent';
    }

    return $icons[$type];
}

function wp_seed_content_kit_is_module_active($module)
{
    $module = sanitize_key($module);

    if ('cards' === $module) {
        return true;
    }

    if ('audio' === $module) {
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
            'seo_opt_out' => true,
            'shortcode' => '[seed_testimonials]',
            'post_type' => 'seed_testimonial',
            'menu_icon' => wp_seed_content_kit_get_admin_menu_icon('testimonials'),
            'menu_supported' => true,
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'quotes' => array(
            'label' => __('Citations', 'wp-seed-content-kit'),
            'active' => wp_seed_content_kit_is_module_active('quotes'),
            'planned' => false,
            'activable' => true,
            'seo_opt_out' => true,
            'shortcode' => '[seed_quotes]',
            'post_type' => 'seed_quote',
            'menu_icon' => wp_seed_content_kit_get_admin_menu_icon('quotes'),
            'menu_supported' => true,
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'directory' => array(
            'label' => __('Annuaire', 'wp-seed-content-kit'),
            'description' => __('Gestion et affichage de fiches d’annuaire.', 'wp-seed-content-kit'),
            'active' => wp_seed_content_kit_is_module_active('directory'),
            'planned' => false,
            'activable' => true,
            'seo_opt_out' => true,
            'shortcode' => '[seed_directory]',
            'post_type' => 'seed_directory',
            'menu_icon' => wp_seed_content_kit_get_admin_menu_icon('directory'),
            'menu_supported' => true,
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
        'audio' => array(
            'label' => __('Créations sonores', 'wp-seed-content-kit'),
            'active' => false,
            'planned' => true,
            'activable' => false,
            'shortcode' => '',
            'menu_icon' => 'dashicons-media-audio',
            'menu_supported' => false,
            'usage' => wp_seed_content_kit_get_builder_usage_help(),
        ),
    );
}
