<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/capabilities.php';
require_once __DIR__ . '/fields.php';
require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/collections.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/templates.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/shortcode.php';

function wp_seed_content_directory_filter_row_actions($actions, $post)
{
    if (is_object($post) && isset($post->post_type) && 'seed_directory' === $post->post_type) {
        unset($actions['inline hide-if-no-js'], $actions['view'], $actions['preview']);
    }

    return $actions;
}
add_filter('post_row_actions', 'wp_seed_content_directory_filter_row_actions', 10, 2);

function wp_seed_content_directory_filter_bulk_actions($actions)
{
    unset($actions['edit'], $actions['publish']);
    return $actions;
}
add_filter('bulk_actions-edit-seed_directory', 'wp_seed_content_directory_filter_bulk_actions');

function wp_seed_content_directory_filter_preview_link($preview_link, $post)
{
    if (is_object($post) && isset($post->post_type) && 'seed_directory' === $post->post_type) {
        return '';
    }

    return $preview_link;
}
add_filter('preview_post_link', 'wp_seed_content_directory_filter_preview_link', 10, 2);

function wp_seed_content_directory_filter_core_sitemaps($post_types)
{
    if (is_array($post_types) && isset($post_types['seed_directory'])) {
        unset($post_types['seed_directory']);
    }

    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'wp_seed_content_directory_filter_core_sitemaps');

function wp_seed_content_directory_filter_yoast_sitemap($excluded, $post_type)
{
    return 'seed_directory' === $post_type ? true : $excluded;
}
add_filter('wpseo_sitemap_exclude_post_type', 'wp_seed_content_directory_filter_yoast_sitemap', 10, 2);
