<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_builder_meta_definitions()
{
    return array(
        'seed_directory_status' => array('type' => 'status', 'legacy_key' => '_seed_directory_status'),
        'seed_directory_profile_types' => array('type' => 'profile_types', 'legacy_key' => '_seed_directory_profile_types'),
        'seed_directory_seeking_models' => array('type' => 'boolean', 'legacy_key' => '_seed_directory_seeking_models'),
        'seed_directory_city' => array('type' => 'text', 'legacy_key' => '_seed_directory_city'),
        'seed_directory_postal_code' => array('type' => 'postal_code', 'legacy_key' => '_seed_directory_postal_code'),
        'seed_directory_department' => array('type' => 'department', 'legacy_key' => '_seed_directory_department'),
        'seed_directory_country' => array('type' => 'country', 'legacy_key' => '_seed_directory_country'),
        'seed_directory_featured' => array('type' => 'boolean', 'legacy_key' => '_seed_directory_featured'),
        'seed_directory_professional_label' => array('type' => 'text', 'legacy_key' => '_seed_directory_profession'),
    );
}

function wp_seed_content_directory_builder_meta_public_key($legacy_key)
{
    foreach (wp_seed_content_directory_builder_meta_definitions() as $public_key => $definition) {
        if ($legacy_key === $definition['legacy_key']) {
            return $public_key;
        }
    }

    return '';
}

function wp_seed_content_directory_sanitize_builder_meta($value, $public_key)
{
    $definitions = wp_seed_content_directory_builder_meta_definitions();
    if (!isset($definitions[$public_key])) {
        return '';
    }

    $legacy_key = $definitions[$public_key]['legacy_key'];
    $value = wp_seed_content_directory_sanitize_meta_value($legacy_key, $value);
    if ('boolean' === $definitions[$public_key]['type']) {
        return '1' === $value;
    }

    return $value;
}

function wp_seed_content_directory_builder_meta_auth($allowed, $meta_key, $post_id)
{
    if ('seed_directory_seeking_models' === $meta_key) {
        return false;
    }
    return current_user_can('edit_seed_directory_entry', $post_id);
}

function wp_seed_content_directory_register_builder_meta()
{
    foreach (wp_seed_content_directory_builder_meta_definitions() as $public_key => $definition) {
        $args = array(
            'type' => 'boolean' === $definition['type'] ? 'boolean' : ('profile_types' === $definition['type'] ? 'array' : 'string'),
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => function ($value) use ($public_key) {
                return wp_seed_content_directory_sanitize_builder_meta($value, $public_key);
            },
            'auth_callback' => 'wp_seed_content_directory_builder_meta_auth',
        );
        if ('profile_types' === $definition['type']) {
            $args['show_in_rest'] = array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array('type' => 'string'),
                ),
            );
        }
        register_post_meta('seed_directory', $public_key, $args);
    }
}

function wp_seed_content_directory_get_builder_meta($post_id, $public_key)
{
    $definitions = wp_seed_content_directory_builder_meta_definitions();
    if (!isset($definitions[$public_key])) {
        return '';
    }

    $key = metadata_exists('post', $post_id, $public_key)
        ? $public_key
        : $definitions[$public_key]['legacy_key'];
    $value = get_post_meta($post_id, $key, true);

    if ('boolean' === $definitions[$public_key]['type']) {
        return '1' === $value || true === $value || 1 === $value;
    }
    if ('profile_types' === $definitions[$public_key]['type']) {
        return wp_seed_content_directory_normalize_profile_types($value);
    }

    return (string) $value;
}

function wp_seed_content_directory_sync_builder_meta($post_id, $legacy_key, $value)
{
    $public_key = wp_seed_content_directory_builder_meta_public_key($legacy_key);
    if ('' === $public_key) {
        return;
    }

    $sanitized = wp_seed_content_directory_sanitize_builder_meta($value, $public_key);
    if ('' === $sanitized || array() === $sanitized || false === $sanitized) {
        delete_post_meta($post_id, $public_key);
        return;
    }

    update_post_meta($post_id, $public_key, $sanitized);
}
