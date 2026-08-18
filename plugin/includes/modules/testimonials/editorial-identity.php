<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_testimonial_get_technical_title($name)
{
    $name = sanitize_text_field((string) $name);

    return '' === $name
        ? __('Témoignage', 'wp-seed-content-kit')
        : sprintf(__('Témoignage — %s', 'wp-seed-content-kit'), $name);
}

function wp_seed_content_testimonial_sync_technical_title($post_id, $post)
{
    static $syncing = false;

    if (
        $syncing
        || !$post instanceof WP_Post
        || 'seed_testimonial' !== $post->post_type
        || wp_is_post_revision($post_id)
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    ) {
        return;
    }

    $name = wp_seed_content_get_testimonial_builder_meta($post_id, 'seed_testimonial_name');
    $technical_title = wp_seed_content_testimonial_get_technical_title($name);
    if ($technical_title === (string) $post->post_title) {
        return;
    }

    $syncing = true;
    wp_update_post(array('ID' => (int) $post_id, 'post_title' => $technical_title));
    $syncing = false;
}
add_action('save_post', 'wp_seed_content_testimonial_sync_technical_title', 20, 2);

function wp_seed_content_testimonial_admin_columns($columns)
{
    $result = array();

    foreach ($columns as $key => $label) {
        if ('title' === $key) {
            $result['testimonial_name'] = __('Nom', 'wp-seed-content-kit');
            $result['testimonial_context'] = __('Contexte', 'wp-seed-content-kit');
            $result['testimonial_photo'] = __('Image', 'wp-seed-content-kit');
            $result['testimonial_modified'] = __('Modification', 'wp-seed-content-kit');
            continue;
        }
        if ('date' !== $key) {
            $result[$key] = $label;
        }
    }

    return $result;
}
add_filter('manage_seed_testimonial_posts_columns', 'wp_seed_content_testimonial_admin_columns', 30);

function wp_seed_content_testimonial_render_admin_column($column, $post_id)
{
    if ('testimonial_name' === $column) {
        $name = wp_seed_content_get_testimonial_builder_meta($post_id, 'seed_testimonial_name');
        $label = '' !== $name ? $name : __('(Sans nom)', 'wp-seed-content-kit');
        $edit_link = get_edit_post_link($post_id);
        echo $edit_link
            ? '<strong><a class="row-title" href="' . esc_url($edit_link) . '">' . esc_html($label) . '</a></strong>'
            : esc_html($label);
        return;
    }

    if ('testimonial_context' === $column) {
        $context = wp_seed_content_get_testimonial_builder_meta($post_id, 'seed_testimonial_context');
        echo '' !== $context ? esc_html($context) : '&mdash;';
        return;
    }

    if ('testimonial_photo' === $column) {
        $thumbnail = get_the_post_thumbnail($post_id, array(60, 60), array('style' => 'width:60px;height:60px;object-fit:cover;'));
        echo $thumbnail ? $thumbnail : '&mdash;';
        return;
    }

    if ('testimonial_modified' === $column) {
        $modified = get_post_modified_time(get_option('date_format'), false, $post_id, true);
        echo $modified ? esc_html($modified) : '&mdash;';
    }
}
add_action('manage_seed_testimonial_posts_custom_column', 'wp_seed_content_testimonial_render_admin_column', 20, 2);

function wp_seed_content_testimonial_primary_column($default_column, $screen_id)
{
    return 'edit-seed_testimonial' === $screen_id ? 'testimonial_name' : $default_column;
}
add_filter('list_table_primary_column', 'wp_seed_content_testimonial_primary_column', 10, 2);

function wp_seed_content_testimonial_admin_search($search, $query)
{
    global $wpdb;

    if (
        !is_admin()
        || !$query instanceof WP_Query
        || !$query->is_main_query()
        || 'seed_testimonial' !== (string) $query->get('post_type')
    ) {
        return $search;
    }

    $term = (string) $query->get('s');
    if ('' === $term) {
        return $search;
    }

    $meta_search = $wpdb->prepare(
        "EXISTS (SELECT 1 FROM {$wpdb->postmeta} AS seed_testimonial_name_meta WHERE seed_testimonial_name_meta.post_id = {$wpdb->posts}.ID AND seed_testimonial_name_meta.meta_key IN (%s, %s) AND seed_testimonial_name_meta.meta_value LIKE %s)",
        'seed_testimonial_name',
        '_seed_testimonial_name',
        '%' . $wpdb->esc_like($term) . '%'
    );
    $standard_search = preg_replace('/^\s*AND\s+/i', '', trim((string) $search), 1);

    return '' === $standard_search
        ? ' AND (' . $meta_search . ')'
        : ' AND ((' . $standard_search . ') OR ' . $meta_search . ')';
}
add_filter('posts_search', 'wp_seed_content_testimonial_admin_search', 20, 2);
