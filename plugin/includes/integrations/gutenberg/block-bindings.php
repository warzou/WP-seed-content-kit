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
        'quote.quote',
        'quote.author',
        'quote.era',
        'quote.source',
        'testimonial.text',
        'testimonial.name',
        'testimonial.context',
    );

    if (!is_array($source_args) || !array_key_exists('field_id', $source_args)) {
        return null;
    }

    if (!is_string($source_args['field_id'])) {
        return null;
    }

    $field_id = trim($source_args['field_id']);
    if ('' === $field_id || !in_array($field_id, $allowed_fields, true)) {
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

    if (!in_array($block_name, array('core/paragraph', 'core/heading'), true)) {
        return null;
    }

    if (!is_string($attribute_name) || 'content' !== $attribute_name) {
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

    return is_string($value) ? $value : null;
}
