<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_quote_builder_meta_definitions()
{
    return array(
        'seed_quote_text' => array(
            'type' => 'textarea',
            'legacy_key' => '_seed_quote_text',
            'sanitize_callback' => 'wp_seed_content_sanitize_quote_text_meta',
        ),
        'seed_quote_author' => array(
            'type' => 'text',
            'legacy_key' => '_seed_quote_author',
            'sanitize_callback' => 'wp_seed_content_sanitize_quote_author_meta',
        ),
        'seed_quote_era' => array(
            'type' => 'text',
            'legacy_key' => '_seed_quote_era',
            'sanitize_callback' => 'wp_seed_content_sanitize_quote_era_meta',
        ),
        'seed_quote_source' => array(
            'type' => 'text',
            'legacy_key' => '_seed_quote_source',
            'sanitize_callback' => 'wp_seed_content_sanitize_quote_source_meta',
        ),
        'seed_quote_featured' => array(
            'type' => 'boolean',
            'legacy_key' => '_seed_quote_featured',
            'sanitize_callback' => 'wp_seed_content_sanitize_quote_featured_meta',
        ),
    );
}

function wp_seed_content_sanitize_quote_builder_meta($value, $meta_key)
{
    $definitions = wp_seed_content_quote_builder_meta_definitions();
    if (!isset($definitions[$meta_key])) {
        return '';
    }

    if ('boolean' === $definitions[$meta_key]['type']) {
        return wp_seed_content_bool_attr($value, false);
    }

    return wp_seed_content_sanitize_meta_value($value, $definitions[$meta_key]);
}

function wp_seed_content_sanitize_quote_text_meta($value)
{
    return wp_seed_content_sanitize_quote_builder_meta($value, 'seed_quote_text');
}

function wp_seed_content_sanitize_quote_author_meta($value)
{
    return wp_seed_content_sanitize_quote_builder_meta($value, 'seed_quote_author');
}

function wp_seed_content_sanitize_quote_era_meta($value)
{
    return wp_seed_content_sanitize_quote_builder_meta($value, 'seed_quote_era');
}

function wp_seed_content_sanitize_quote_source_meta($value)
{
    return wp_seed_content_sanitize_quote_builder_meta($value, 'seed_quote_source');
}

function wp_seed_content_sanitize_quote_featured_meta($value)
{
    return wp_seed_content_sanitize_quote_builder_meta($value, 'seed_quote_featured');
}

function wp_seed_content_quote_builder_meta_auth($allowed, $meta_key, $post_id)
{
    return current_user_can('edit_post', $post_id);
}

function wp_seed_content_register_quote_builder_meta()
{
    foreach (wp_seed_content_quote_builder_meta_definitions() as $meta_key => $definition) {
        register_post_meta('seed_quote', $meta_key, array(
            'type' => 'boolean' === $definition['type'] ? 'boolean' : 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => $definition['sanitize_callback'],
            'auth_callback' => 'wp_seed_content_quote_builder_meta_auth',
        ));
    }
}

function wp_seed_content_get_quote_builder_meta($post_id, $meta_key)
{
    $definitions = wp_seed_content_quote_builder_meta_definitions();
    if (!isset($definitions[$meta_key])) {
        return '';
    }

    $key = metadata_exists('post', $post_id, $meta_key)
        ? $meta_key
        : $definitions[$meta_key]['legacy_key'];
    $value = get_post_meta($post_id, $key, true);

    if ('boolean' === $definitions[$meta_key]['type']) {
        return wp_seed_content_bool_attr($value, false);
    }

    return (string) $value;
}

function wp_seed_content_quote_is_featured($post_id)
{
    return true === wp_seed_content_get_quote_builder_meta($post_id, 'seed_quote_featured');
}
