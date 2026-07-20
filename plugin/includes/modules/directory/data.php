<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_public_contacts($post_id)
{
    $post_id = absint($post_id);
    if (!wp_seed_content_directory_is_publicly_eligible($post_id)) {
        return array();
    }

    $contacts = array();
    $keys = array(
        'phone' => '_seed_directory_phone',
        'email' => '_seed_directory_email',
        'website' => '_seed_directory_website',
        'facebook' => '_seed_directory_facebook',
        'instagram' => '_seed_directory_instagram',
    );
    foreach ($keys as $public_key => $meta_key) {
        if ('1' !== get_post_meta($post_id, $meta_key . '_visible', true)) {
            continue;
        }
        $value = wp_seed_content_directory_sanitize_meta_value($meta_key, get_post_meta($post_id, $meta_key, true));
        if ('' !== $value) {
            $contacts[$public_key] = $value;
        }
    }

    return $contacts;
}

function wp_seed_content_directory_get_admin_data($post_id)
{
    $post_id = absint($post_id);
    $post = get_post($post_id);
    if (!$post || 'seed_directory' !== $post->post_type || !current_user_can('edit_seed_directory_entry', $post_id)) {
        return new WP_Error('wp_seed_content_directory_forbidden', __('Accès refusé à cette fiche.', 'wp-seed-content-kit'));
    }

    $data = array(
        'id' => $post_id,
        'name' => (string) $post->post_title,
        'presentation' => (string) $post->post_excerpt,
        'order' => (int) $post->menu_order,
        'wordpress_status' => (string) $post->post_status,
        'photo_id' => (int) get_post_thumbnail_id($post_id),
    );
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        $data[substr($key, strlen('_seed_directory_'))] = wp_seed_content_directory_get_meta_value($post_id, $key);
    }

    return $data;
}
