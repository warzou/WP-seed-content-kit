<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_testimonial_meta_definitions()
{
    return array(
        'seed_testimonial_name' => array('type' => 'text', 'canonical_builder_meta' => true),
        'seed_testimonial_text' => array(
            'type' => 'textarea',
            'canonical_builder_meta' => true,
            'sanitize_callback' => 'wp_seed_content_sanitize_testimonial_text_meta',
        ),
        '_seed_testimonial_date' => array('type' => 'date'),
        'seed_testimonial_context' => array('type' => 'text', 'canonical_builder_meta' => true),
        '_seed_testimonial_publication_consent' => array('type' => 'checkbox'),
        '_seed_testimonial_featured' => array('type' => 'checkbox'),
    );
}

function wp_seed_content_save_testimonial_meta($post_id, $post)
{
    static $updating_excerpt = false;

    if ('seed_testimonial' !== $post->post_type) {
        return;
    }

    if ($updating_excerpt) {
        return;
    }

    if (!isset($_POST['wp_seed_content_testimonial_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_testimonial_nonce'])), 'wp_seed_content_save_testimonial_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (array_key_exists('wp_seed_content_testimonial_summary', $_POST)) {
        $summary = sanitize_textarea_field(wp_unslash($_POST['wp_seed_content_testimonial_summary']));
        $current_summary = isset($post->post_excerpt) ? (string) $post->post_excerpt : '';

        if ($summary !== $current_summary) {
            $updating_excerpt = true;
            wp_update_post(
                wp_slash(array(
                    'ID' => (int) $post_id,
                    'post_excerpt' => $summary,
                ))
            );
            $updating_excerpt = false;
        }
    }

    foreach (wp_seed_content_testimonial_meta_definitions() as $key => $definition) {
        $type = isset($definition['type']) ? $definition['type'] : 'text';

        if ('date' === $type) {
            if (!array_key_exists($key, $_POST)) {
                continue;
            }

            $raw = wp_unslash($_POST[$key]);

            if (is_string($raw) && '' === $raw) {
                delete_post_meta($post_id, $key);
                continue;
            }

            $value = wp_seed_content_sanitize_meta_value($raw, $definition);
            if ('' !== $value) {
                update_post_meta($post_id, $key, $value);
            }

            continue;
        }

        $raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
        $value = isset($definition['sanitize_callback']) && is_callable($definition['sanitize_callback'])
            ? call_user_func($definition['sanitize_callback'], $raw)
            : wp_seed_content_sanitize_meta_value($raw, $definition);

        if ('checkbox' === $type && !$value) {
            delete_post_meta($post_id, $key);
            continue;
        }

        if ('' === $value && !empty($definition['canonical_builder_meta'])) {
            update_post_meta($post_id, $key, '');
            continue;
        }

        if ('' === $value) {
            delete_post_meta($post_id, $key);
            continue;
        }

        update_post_meta($post_id, $key, $value);
    }

    if (isset($_POST['wp_seed_content_testimonial_thumbnail_id']) && current_user_can('edit_post', $post_id)) {
        $thumbnail_id = absint(wp_unslash($_POST['wp_seed_content_testimonial_thumbnail_id']));

        if ($thumbnail_id > 0) {
            set_post_thumbnail($post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($post_id);
        }
    }
}
add_action('save_post', 'wp_seed_content_save_testimonial_meta', 10, 2);
