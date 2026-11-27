<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_testimonial_builder_meta_definitions()
{
    return array(
        'seed_testimonial_text' => array(
            'type' => 'textarea',
            'legacy_key' => '_seed_testimonial_text',
            'sanitize_callback' => 'wp_seed_content_sanitize_testimonial_text_meta',
        ),
        'seed_testimonial_name' => array(
            'type' => 'text',
            'legacy_key' => '_seed_testimonial_name',
            'sanitize_callback' => 'wp_seed_content_sanitize_testimonial_name_meta',
        ),
        'seed_testimonial_context' => array(
            'type' => 'text',
            'legacy_key' => '_seed_testimonial_context',
            'sanitize_callback' => 'wp_seed_content_sanitize_testimonial_context_meta',
        ),
    );
}

function wp_seed_content_sanitize_testimonial_builder_meta($value, $meta_key)
{
    $definitions = wp_seed_content_testimonial_builder_meta_definitions();
    if (!isset($definitions[$meta_key])) {
        return '';
    }

    if (isset($definitions[$meta_key]['sanitize_callback'])
        && is_callable($definitions[$meta_key]['sanitize_callback'])) {
        return call_user_func($definitions[$meta_key]['sanitize_callback'], $value);
    }

    return wp_seed_content_sanitize_meta_value($value, $definitions[$meta_key]);
}

function wp_seed_content_sanitize_testimonial_text_meta($value)
{
    if (!is_scalar($value)) {
        return '';
    }

    $parts = preg_split(
        '/(<!--more(?:\s+.*?)?-->|<!--noteaser-->)/s',
        (string) $value,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );
    if (!is_array($parts)) {
        return '';
    }

    foreach ($parts as $index => $part) {
        if (preg_match('/^<!--(?:more(?:\s+.*?)?|noteaser)-->$/s', $part)) {
            continue;
        }

        $part = preg_replace(
            '/<!--\s+\/?wp:(?:core\/)?more(?:\s+.*?)?\s*-->/s',
            '',
            $part
        );
        $part = preg_replace('/<!--[\s\S]*?-->/', '', $part);
        $parts[$index] = function_exists('wp_kses_post')
            ? wp_kses_post($part)
            : sanitize_textarea_field($part);
    }

    return implode('', $parts);
}

function wp_seed_content_sanitize_testimonial_name_meta($value)
{
    return wp_seed_content_sanitize_meta_value($value, array('type' => 'text'));
}

function wp_seed_content_sanitize_testimonial_context_meta($value)
{
    return wp_seed_content_sanitize_meta_value($value, array('type' => 'text'));
}

function wp_seed_content_testimonial_builder_meta_auth($allowed, $meta_key, $post_id)
{
    return current_user_can('edit_post', $post_id);
}

function wp_seed_content_register_testimonial_builder_meta()
{
    foreach (wp_seed_content_testimonial_builder_meta_definitions() as $meta_key => $definition) {
        register_post_meta('seed_testimonial', $meta_key, array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => $definition['sanitize_callback'],
            'auth_callback' => 'wp_seed_content_testimonial_builder_meta_auth',
        ));
    }
}

function wp_seed_content_get_testimonial_builder_meta($post_id, $meta_key)
{
    $definitions = wp_seed_content_testimonial_builder_meta_definitions();
    if (!isset($definitions[$meta_key])) {
        return '';
    }

    if (metadata_exists('post', $post_id, $meta_key)) {
        return (string) get_post_meta($post_id, $meta_key, true);
    }

    return (string) get_post_meta($post_id, $definitions[$meta_key]['legacy_key'], true);
}
