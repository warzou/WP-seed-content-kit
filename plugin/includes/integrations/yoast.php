<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_seo_opt_out_post_types()
{
    static $post_types = null;

    if (null !== $post_types) {
        return $post_types;
    }

    $post_types = array();
    $modules = function_exists('wp_seed_content_kit_get_modules') ? wp_seed_content_kit_get_modules() : array();

    foreach ($modules as $module) {
        if (empty($module['seo_opt_out']) || empty($module['post_type'])) {
            continue;
        }

        $post_type = sanitize_key((string) $module['post_type']);
        if (!in_array($post_type, $post_types, true)) {
            $post_types[] = $post_type;
        }
    }

    return $post_types;
}

function wp_seed_content_kit_is_seo_opt_out_post_type($post_type)
{
    $post_type = sanitize_key((string) $post_type);
    return in_array($post_type, wp_seed_content_kit_get_seo_opt_out_post_types(), true);
}

function wp_seed_content_kit_is_yoast_present()
{
    return class_exists('WPSEO_Meta') || class_exists('WPSEO_Admin') || class_exists('WPSEO_Metabox')
        || defined('WPSEO_FILE') || defined('WPSEO_BASENAME') || defined('WPSEO_VERSION');
}

function wp_seed_content_kit_remove_yoast_metaboxes($post_type)
{
    if (!wp_seed_content_kit_is_yoast_present()) {
        return;
    }
    if (!is_admin() || !wp_seed_content_kit_is_seo_opt_out_post_type($post_type)) {
        return;
    }

    global $wp_meta_boxes;
    $contexts = array('normal', 'advanced', 'side');

    if (!isset($wp_meta_boxes[$post_type])) {
        return;
    }

    foreach ($contexts as $context) {
        if (!isset($wp_meta_boxes[$post_type][$context])) {
            continue;
        }

        foreach ($wp_meta_boxes[$post_type][$context] as $priority => $boxes) {
            if (!is_array($boxes)) {
                continue;
            }

            foreach (array_keys($boxes) as $box_id) {
                if (0 === strpos($box_id, 'wpseo_')) {
                    unset($wp_meta_boxes[$post_type][$context][$priority][$box_id]);
                }
            }
        }
    }
}
add_action('add_meta_boxes', 'wp_seed_content_kit_remove_yoast_metaboxes', 20, 1);

function wp_seed_content_kit_filter_yoast_columns(array $columns)
{
    $yoast_columns = array(
        'wpseo-score',
        'wpseo-score-readability',
        'wpseo-score-linkdex',
        'wpseo-title',
        'wpseo-metadesc',
        'wpseo-focuskw',
    );

    foreach ($yoast_columns as $column_key) {
        if (isset($columns[$column_key])) {
            unset($columns[$column_key]);
        }
    }

    return $columns;
}

function wp_seed_content_kit_disable_yoast_analysis_for_opt_out_post_types($value)
{
    if (!is_admin() || !wp_seed_content_kit_is_yoast_present()) {
        return $value;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || empty($screen->post_type) || empty($screen->base)) {
        return $value;
    }

    if ('post' === $screen->base && wp_seed_content_kit_is_seo_opt_out_post_type($screen->post_type)) {
        return false;
    }

    return $value;
}

function wp_seed_content_kit_register_yoast_opt_out_filters()
{
    if (!is_admin() || !wp_seed_content_kit_is_yoast_present()) {
        return;
    }

    foreach (wp_seed_content_kit_get_seo_opt_out_post_types() as $post_type) {
        $list_hook = 'manage_' . $post_type . '_posts_columns';
        $edit_hook = 'manage_edit_' . $post_type . '_columns';

        add_filter($list_hook, 'wp_seed_content_kit_filter_yoast_columns', 20, 1);
        add_filter($edit_hook, 'wp_seed_content_kit_filter_yoast_columns', 20, 1);
    }

    add_filter('wpseo_use_page_analysis', 'wp_seed_content_kit_disable_yoast_analysis_for_opt_out_post_types');
    add_filter('wpseo_use_readability', 'wp_seed_content_kit_disable_yoast_analysis_for_opt_out_post_types');
}
add_action('admin_init', 'wp_seed_content_kit_register_yoast_opt_out_filters');
