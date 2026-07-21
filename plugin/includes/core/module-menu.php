<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_register_module_menus()
{
    $modules = wp_seed_content_kit_get_modules();
    $position = 58.01;

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

        $capability = isset($post_type_object->cap->edit_posts) ? $post_type_object->cap->edit_posts : 'edit_posts';
        if (!current_user_can($capability)) {
            continue;
        }

        $slug = 'edit.php?post_type=' . sanitize_key($post_type);
        add_menu_page(
            $module['label'],
            $module['label'],
            $capability,
            $slug,
            '',
            isset($module['menu_icon']) ? $module['menu_icon'] : 'dashicons-admin-post',
            $position
        );

        $position += 0.01;
    }
}
add_action('admin_menu', 'wp_seed_content_kit_register_module_menus', 30);
