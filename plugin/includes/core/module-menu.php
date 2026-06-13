<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_register_module_menus()
{
    if (current_user_can('manage_options')) {
        return;
    }

    $modules = wp_seed_content_kit_get_modules();
    $user = wp_get_current_user();
    $position = 58.01;

    if (empty($user->roles)) {
        return;
    }

    foreach ($modules as $module) {
        if (empty($module['menu_supported']) || empty($module['active']) || empty($module['post_type'])) {
            continue;
        }

        $post_type = $module['post_type'];
        $post_type_object = get_post_type_object($post_type);
        if (!$post_type_object) {
            continue;
        }

        $visibility = wp_seed_content_kit_get_module_menu_visibility_for_post_type($post_type);
        if (empty($visibility['show_in_menu'])) {
            continue;
        }

        $roles = isset($visibility['roles']) && is_array($visibility['roles']) ? $visibility['roles'] : array();
        if (empty($roles)) {
            $roles = array('administrator');
        }

        if (empty(array_intersect($user->roles, array_map('sanitize_key', $roles)))) {
            continue;
        }

        $capability = isset($post_type_object->cap->edit_posts) ? $post_type_object->cap->edit_posts : 'edit_posts';
        if (!current_user_can($capability)) {
            continue;
        }

        $slug = 'wp-seed-content-kit-module-' . sanitize_key($post_type);
        add_menu_page(
            $module['label'],
            $module['label'],
            $capability,
            $slug,
            'wp_seed_content_kit_render_module_redirect_page',
            isset($module['menu_icon']) ? $module['menu_icon'] : 'dashicons-admin-post',
            $position
        );

        $position += 0.01;
    }
}
add_action('admin_menu', 'wp_seed_content_kit_register_module_menus', 30);

function wp_seed_content_kit_render_module_redirect_page()
{
    if (!isset($_GET['page'])) {
        return;
    }

    $page = sanitize_text_field(wp_unslash($_GET['page']));
    $module_type = str_replace('wp-seed-content-kit-module-', '', $page);

    if (empty($module_type)) {
        return;
    }

    if ('seed_testimonial' === $module_type) {
        $post_type = 'seed_testimonial';
    } elseif ('seed_quote' === $module_type) {
        $post_type = 'seed_quote';
    } else {
        wp_die(esc_html__('Module inconnu.', 'wp-seed-content-kit'));
    }

    $post_type_object = get_post_type_object($post_type);
    $capability = $post_type_object && isset($post_type_object->cap->edit_posts) ? $post_type_object->cap->edit_posts : 'edit_posts';
    if (!current_user_can($capability)) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation d’accéder à ce module.', 'wp-seed-content-kit'));
    }

    wp_safe_redirect(admin_url('edit.php?post_type=' . $post_type));
    exit;
}
