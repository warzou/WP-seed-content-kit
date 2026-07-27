<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_profile_facets_schema_option()
{
    return 'wp_seed_content_directory_profile_facets_schema';
}

function wp_seed_content_directory_upgrade_profile_facets()
{
    $option = wp_seed_content_directory_profile_facets_schema_option();
    $current = (int) get_option($option, 0);
    if ($current >= 1) {
        return array('status' => 'unchanged', 'updated' => 0);
    }

    $updated = 0;
    $post_ids = get_posts(array(
        'post_type' => 'seed_directory',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'suppress_filters' => true,
        'no_found_rows' => true,
    ));
    foreach ($post_ids as $post_id) {
        $post_id = absint($post_id);
        if ($post_id <= 0 || metadata_exists('post', $post_id, '_seed_directory_seeking_models')) {
            continue;
        }
        if ('seeking_models' !== get_post_meta($post_id, '_seed_directory_status', true)) {
            continue;
        }
        if (add_post_meta($post_id, '_seed_directory_seeking_models', '1', true)) {
            $updated++;
        }
    }

    update_option($option, 1, false);

    return array('status' => 'migrated', 'updated' => $updated);
}
add_action('init', 'wp_seed_content_directory_upgrade_profile_facets', 30);
