<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_quote_meta_definitions()
{
    return array(
        '_seed_quote_text' => array('type' => 'textarea'),
        '_seed_quote_author' => array('type' => 'text'),
        '_seed_quote_era' => array('type' => 'text'),
        '_seed_quote_source' => array('type' => 'text'),
        '_seed_quote_featured' => array('type' => 'checkbox'),
    );
}

function wp_seed_content_save_quote_meta($post_id, $post)
{
    if ('seed_quote' !== $post->post_type) {
        return;
    }

    if (!isset($_POST['wp_seed_content_quote_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_quote_nonce'])), 'wp_seed_content_save_quote_meta')) {
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

    foreach (wp_seed_content_quote_meta_definitions() as $key => $definition) {
        $type = isset($definition['type']) ? $definition['type'] : 'text';
        $raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
        $value = wp_seed_content_sanitize_meta_value($raw, $definition);

        if ('checkbox' === $type && !$value) {
            delete_post_meta($post_id, $key);
            continue;
        }

        if ('' === $value) {
            delete_post_meta($post_id, $key);
            continue;
        }

        update_post_meta($post_id, $key, $value);
    }
}
add_action('save_post', 'wp_seed_content_save_quote_meta', 10, 2);
