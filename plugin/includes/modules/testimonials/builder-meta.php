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

    return wp_seed_content_sanitize_meta_value($value, $definitions[$meta_key]);
}

function wp_seed_content_sanitize_testimonial_text_meta($value)
{
    return wp_seed_content_sanitize_testimonial_builder_meta($value, 'seed_testimonial_text');
}

function wp_seed_content_sanitize_testimonial_name_meta($value)
{
    return wp_seed_content_sanitize_testimonial_builder_meta($value, 'seed_testimonial_name');
}

function wp_seed_content_sanitize_testimonial_context_meta($value)
{
    return wp_seed_content_sanitize_testimonial_builder_meta($value, 'seed_testimonial_context');
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
