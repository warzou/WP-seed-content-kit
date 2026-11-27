<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_enqueue_admin_thumbnails($hook_suffix)
{
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    $list = 'edit.php' === $hook_suffix
        && in_array($screen->post_type, array('seed_directory', 'seed_testimonial'), true);
    $editor = in_array($hook_suffix, array('post.php', 'post-new.php'), true)
        && 'seed_testimonial' === $screen->post_type;
    if (!$list && !$editor) {
        return;
    }

    wp_enqueue_style(
        'wp-seed-content-admin-thumbnails',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/admin-thumbnails.css',
        array(),
        WP_SEED_CONTENT_KIT_VERSION
    );
}
add_action('admin_enqueue_scripts', 'wp_seed_content_enqueue_admin_thumbnails');
