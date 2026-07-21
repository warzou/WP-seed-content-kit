<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_advanced_capabilities()
{
    return array(
        'manage_wp_seed_content_kit',
        'manage_wp_seed_templates',
        'manage_wp_seed_collections',
        'manage_wp_seed_integrations',
        'manage_wp_seed_roles',
        'manage_wp_seed_imports',
    );
}

function wp_seed_content_kit_get_content_capability_definitions()
{
    return array(
        'testimonials' => array(
            'singular' => 'seed_testimonial',
            'plural' => 'seed_testimonials',
        ),
        'quotes' => array(
            'singular' => 'seed_quote',
            'plural' => 'seed_quotes',
        ),
        'directory' => array(
            'singular' => 'seed_directory_entry',
            'plural' => 'seed_directory_entries',
        ),
    );
}

function wp_seed_content_kit_get_primitive_capabilities($module)
{
    $definitions = wp_seed_content_kit_get_content_capability_definitions();
    $module = sanitize_key((string) $module);
    if (!isset($definitions[$module])) {
        return array();
    }

    $plural = $definitions[$module]['plural'];

    return array(
        'edit_' . $plural,
        'publish_' . $plural,
        'read_private_' . $plural,
        'delete_' . $plural,
    );
}

function wp_seed_content_kit_get_capability_map($module)
{
    $definitions = wp_seed_content_kit_get_content_capability_definitions();
    $module = sanitize_key((string) $module);
    if (!isset($definitions[$module])) {
        return array();
    }

    $singular = $definitions[$module]['singular'];
    $plural = $definitions[$module]['plural'];

    return array(
        'edit_post' => 'edit_' . $singular,
        'read_post' => 'read_' . $singular,
        'delete_post' => 'delete_' . $singular,
        'edit_posts' => 'edit_' . $plural,
        'edit_others_posts' => 'edit_' . $plural,
        'publish_posts' => 'publish_' . $plural,
        'read_private_posts' => 'read_private_' . $plural,
        'delete_posts' => 'delete_' . $plural,
        'delete_private_posts' => 'delete_' . $plural,
        'delete_published_posts' => 'delete_' . $plural,
        'delete_others_posts' => 'delete_' . $plural,
        'edit_private_posts' => 'edit_' . $plural,
        'edit_published_posts' => 'edit_' . $plural,
        'create_posts' => 'edit_' . $plural,
    );
}

function wp_seed_content_kit_get_template_capability_map()
{
    return array(
        'edit_post' => 'manage_wp_seed_templates',
        'read_post' => 'manage_wp_seed_templates',
        'delete_post' => 'manage_wp_seed_templates',
        'edit_posts' => 'manage_wp_seed_templates',
        'edit_others_posts' => 'manage_wp_seed_templates',
        'publish_posts' => 'manage_wp_seed_templates',
        'read_private_posts' => 'manage_wp_seed_templates',
        'delete_posts' => 'manage_wp_seed_templates',
        'delete_private_posts' => 'manage_wp_seed_templates',
        'delete_published_posts' => 'manage_wp_seed_templates',
        'delete_others_posts' => 'manage_wp_seed_templates',
        'edit_private_posts' => 'manage_wp_seed_templates',
        'edit_published_posts' => 'manage_wp_seed_templates',
        'create_posts' => 'manage_wp_seed_templates',
    );
}
function wp_seed_content_kit_get_default_module_role_assignments()
{
    return array(
        'testimonials' => array('administrator', 'editor'),
        'quotes' => array('administrator', 'editor'),
        'directory' => array('administrator', 'editor'),
    );
}

function wp_seed_content_kit_get_module_role_assignments()
{
    $defaults = wp_seed_content_kit_get_default_module_role_assignments();
    $stored = get_option('wp_seed_content_kit_module_roles', array());
    if (!is_array($stored)) {
        $stored = array();
    }

    $assignments = array();
    foreach ($defaults as $module => $default_roles) {
        $roles = isset($stored[$module]) && is_array($stored[$module]) ? $stored[$module] : $default_roles;
        $roles = array_values(array_unique(array_filter(array_map('sanitize_key', $roles))));
        if (!in_array('administrator', $roles, true)) {
            $roles[] = 'administrator';
        }
        $assignments[$module] = $roles;
    }

    return $assignments;
}

function wp_seed_content_kit_role_manages_module($role, $module)
{
    $assignments = wp_seed_content_kit_get_module_role_assignments();
    $module = sanitize_key((string) $module);
    $role = sanitize_key((string) $role);

    return isset($assignments[$module]) && in_array($role, $assignments[$module], true);
}

function wp_seed_content_kit_synchronize_role_capabilities($assignments = null)
{
    if (null === $assignments) {
        $assignments = wp_seed_content_kit_get_module_role_assignments();
    }

    $role_keys = array('administrator', 'editor');
    if (function_exists('wp_roles')) {
        $roles = wp_roles()->roles;
        if (is_array($roles)) {
            $role_keys = array_values(array_unique(array_merge($role_keys, array_keys($roles))));
        }
    }

    foreach ($role_keys as $role_key) {
        $role = get_role($role_key);
        if (!$role) {
            continue;
        }

        foreach (wp_seed_content_kit_get_content_capability_definitions() as $module => $definition) {
            $allowed = 'administrator' === $role_key
                || (isset($assignments[$module]) && in_array($role_key, $assignments[$module], true));
            foreach (wp_seed_content_kit_get_primitive_capabilities($module) as $capability) {
                if ($allowed) {
                    $role->add_cap($capability);
                } else {
                    $role->remove_cap($capability);
                }
            }
        }

        foreach (wp_seed_content_kit_get_advanced_capabilities() as $capability) {
            if ('administrator' === $role_key) {
                $role->add_cap($capability);
            } else {
                $role->remove_cap($capability);
            }
        }
    }
}

function wp_seed_content_kit_maybe_upgrade_role_capabilities()
{
    $schema = '1';
    if ($schema === (string) get_option('wp_seed_content_kit_capability_schema', '')) {
        return;
    }

    wp_seed_content_kit_synchronize_role_capabilities();
    update_option('wp_seed_content_kit_capability_schema', $schema, false);
}
add_action('admin_init', 'wp_seed_content_kit_maybe_upgrade_role_capabilities', 5);
