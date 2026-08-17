<?php
/**
 * Server-side Gutenberg Block Bindings provider for Dynamic Data.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the WP Seed Content Kit Dynamic Data binding source.
 */
function wp_seed_content_register_gutenberg_block_bindings_source()
{
    if (!function_exists('register_block_bindings_source')) {
        return;
    }

    register_block_bindings_source(
        'wp-seed-content-kit/dynamic-data',
        array(
            'label' => __('WP Seed Content Kit', 'wp-seed-content-kit'),
            'get_value_callback' => 'wp_seed_content_get_gutenberg_binding_value',
            'uses_context' => array('postId', 'postType'),
        )
    );
}
add_action('init', 'wp_seed_content_register_gutenberg_block_bindings_source', 10);

/**
 * Resolves a supported text binding through the shared Dynamic Data resolver.
 *
 * @param mixed $source_args    Persisted binding arguments.
 * @param mixed $block_instance Current block instance.
 * @param mixed $attribute_name Bound block attribute.
 * @return string|null Resolved text, or null when the binding cannot be used.
 */
function wp_seed_content_get_gutenberg_binding_value($source_args, $block_instance, $attribute_name)
{
    static $allowed_fields = array(
        'quote.quote' => 'text',
        'quote.author' => 'text',
        'quote.era' => 'text',
        'quote.source' => 'text',
        'testimonial.text' => 'text',
        'testimonial.name' => 'text',
        'testimonial.context' => 'text',
        'testimonial.testimonial_date' => 'text',
        'directory.photo' => 'image',
        'directory.name' => 'text',
        'directory.professional_label' => 'text',
        'directory.summary' => 'text',
        'directory.presentation' => 'text',
        'directory.presentation_intro' => 'text',
        'directory.presentation_more' => 'text',
        'directory.status' => 'text',
        'directory.profile_types' => 'text',
        'directory.seeking_models' => 'text',
        'directory.location' => 'text',
        'directory.id' => 'text',
        'directory.anchor' => 'text',
    );

    if (function_exists('wp_seed_content_directory_individual_contact_provider_definitions')) {
        foreach (wp_seed_content_directory_individual_contact_provider_definitions() as $definition) {
            $allowed_fields[$definition['display_field_id']] = 'text';
            if (!empty($definition['has_href'])) {
                $allowed_fields[$definition['href_field_id']] = 'url';
            }
        }
    }

    if (!is_array($source_args) || !array_key_exists('field_id', $source_args)) {
        return null;
    }

    if (!is_string($source_args['field_id'])) {
        return null;
    }

    $field_id = trim($source_args['field_id']);
    if ('' === $field_id || !isset($allowed_fields[$field_id])) {
        return null;
    }

    if (!is_object($block_instance) || !is_a($block_instance, 'WP_Block')) {
        return null;
    }

    $block_name = null;
    if (isset($block_instance->name) && is_string($block_instance->name)) {
        $block_name = $block_instance->name;
    } elseif (
        isset($block_instance->block_type)
        && is_object($block_instance->block_type)
        && isset($block_instance->block_type->name)
        && is_string($block_instance->block_type->name)
    ) {
        $block_name = $block_instance->block_type->name;
    }

    $field_type = $allowed_fields[$field_id];
    $valid_target = 'text' === $field_type
        && in_array($block_name, array('core/paragraph', 'core/heading'), true)
        && is_string($attribute_name)
        && 'content' === $attribute_name;
    $valid_target = $valid_target || ('url' === $field_type
        && 'core/button' === $block_name
        && is_string($attribute_name)
        && 'url' === $attribute_name);
    $valid_target = $valid_target || ('image' === $field_type
        && 'core/image' === $block_name
        && is_string($attribute_name)
        && in_array($attribute_name, array('id', 'url', 'alt'), true));
    if (!$valid_target) {
        return null;
    }

    if (!function_exists('wp_seed_content_resolve_dynamic_data')) {
        return null;
    }

    if (array_key_exists('post_id', $source_args)) {
        // An explicit ID remains authoritative, including when it is invalid.
        $context = array(
            'explicit_post_id' => $source_args['post_id'],
        );
    } else {
        $context = array();
        $block_context = isset($block_instance->context) && is_array($block_instance->context)
            ? $block_instance->context
            : array();

        if (array_key_exists('postId', $block_context)) {
            $context['current_post_id'] = $block_context['postId'];
        }

        if (array_key_exists('postType', $block_context)) {
            $context['current_post_type'] = $block_context['postType'];
        }
    }

    $value = wp_seed_content_resolve_dynamic_data($field_id, $context);

    if (is_wp_error($value)) {
        return null;
    }

    if ('image' === $field_type) {
        if (!is_array($value)) {
            return null;
        }
        if ('id' === $attribute_name) {
            return isset($value['id']) ? absint($value['id']) : 0;
        }
        return isset($value[$attribute_name]) && is_string($value[$attribute_name])
            ? $value[$attribute_name]
            : '';
    }

    return is_string($value) ? $value : null;
}
