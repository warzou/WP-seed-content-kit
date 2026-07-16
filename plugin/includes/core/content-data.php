<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_resolve_data_post($post_id, $expected_post_type, $args = array())
{
    $post_id = absint($post_id);
    if (!$post_id) {
        return null;
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post || $post->post_type !== $expected_post_type) {
        return null;
    }

    if ('publish' === $post->post_status) {
        return $post;
    }

    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'allow_unpublished' => false,
        )
    );

    if (
        true !== $args['allow_unpublished']
        || !is_user_logged_in()
        || !current_user_can('edit_post', $post_id)
    ) {
        return null;
    }

    return $post;
}

function wp_seed_content_get_post_data_fields($post)
{
    if (!$post instanceof WP_Post) {
        return array();
    }

    $permalink = get_permalink($post);

    return array(
        'id' => (int) $post->ID,
        'title' => (string) $post->post_title,
        'slug' => (string) $post->post_name,
        'status' => (string) $post->post_status,
        'permalink' => is_string($permalink) ? $permalink : '',
    );
}

function wp_seed_content_get_media_data($attachment_id)
{
    $attachment_id = absint($attachment_id);
    if (!$attachment_id) {
        return null;
    }

    $attachment = get_post($attachment_id);
    if (
        !$attachment instanceof WP_Post
        || 'attachment' !== $attachment->post_type
        || !wp_attachment_is_image($attachment_id)
    ) {
        return null;
    }

    $url = function_exists('wp_get_original_image_url') ? wp_get_original_image_url($attachment_id) : false;
    if (!is_string($url) || '' === $url) {
        $url = wp_get_attachment_url($attachment_id);
    }

    $metadata = wp_get_attachment_metadata($attachment_id);
    $width = is_array($metadata) && isset($metadata['width']) ? absint($metadata['width']) : 0;
    $height = is_array($metadata) && isset($metadata['height']) ? absint($metadata['height']) : 0;
    $mime_type = get_post_mime_type($attachment_id);

    return array(
        'id' => $attachment_id,
        'url' => is_string($url) ? $url : '',
        'alt' => (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        'width' => $width,
        'height' => $height,
        'mime_type' => is_string($mime_type) ? $mime_type : '',
    );
}

function wp_seed_content_get_quote_data($post_id, $args = array())
{
    $post = wp_seed_content_resolve_data_post($post_id, 'seed_quote', $args);
    if (!$post) {
        return array();
    }

    return array_merge(
        wp_seed_content_get_post_data_fields($post),
        array(
            'quote' => (string) wp_seed_content_get_meta($post->ID, '_seed_quote_text'),
            'author' => (string) wp_seed_content_get_meta($post->ID, '_seed_quote_author'),
            'era' => (string) wp_seed_content_get_meta($post->ID, '_seed_quote_era'),
            'source' => (string) wp_seed_content_get_meta($post->ID, '_seed_quote_source'),
            'featured' => wp_seed_content_is_truthy_meta($post->ID, '_seed_quote_featured'),
            'display_order' => (int) $post->menu_order,
        )
    );
}

function wp_seed_content_get_testimonial_data($post_id, $args = array())
{
    $post = wp_seed_content_resolve_data_post($post_id, 'seed_testimonial', $args);
    if (!$post) {
        return array();
    }

    $thumbnail_id = get_post_thumbnail_id($post->ID);

    return array_merge(
        wp_seed_content_get_post_data_fields($post),
        array(
            'text' => (string) wp_seed_content_get_meta($post->ID, '_seed_testimonial_text'),
            'name' => (string) wp_seed_content_get_meta($post->ID, '_seed_testimonial_name'),
            'testimonial_date' => wp_seed_content_sanitize_iso_date(wp_seed_content_get_meta($post->ID, '_seed_testimonial_date')),
            'context' => (string) wp_seed_content_get_meta($post->ID, '_seed_testimonial_context'),
            'photo' => wp_seed_content_get_media_data($thumbnail_id),
            'featured' => wp_seed_content_is_truthy_meta($post->ID, '_seed_featured'),
            'display_order' => (int) $post->menu_order,
        )
    );
}
