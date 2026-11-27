<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Copy legacy private quote fields to registered public metadata.
 *
 * Existing public metadata is authoritative, including an intentionally empty
 * string or false boolean. Legacy keys remain available as read-only fallback.
 */
function wp_seed_content_migrate_quote_builder_meta($quote_ids)
{
    $ids = function_exists('_wp_seed_content_collections_normalize_ids')
        ? _wp_seed_content_collections_normalize_ids($quote_ids)
        : array();
    $result = array(
        'updated' => array(),
        'unchanged' => array(),
        'skipped' => array(),
        'errors' => array(),
        'previous' => array(),
        'aborted' => false,
    );
    $definitions = wp_seed_content_quote_builder_meta_definitions();
    $plans = array();

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post instanceof WP_Post || 'seed_quote' !== $post->post_type) {
            $result['skipped'][] = $id;
            continue;
        }

        $result['previous'][(string) $id] = array();
        $plans[(string) $id] = array();

        foreach ($definitions as $public_key => $definition) {
            $public_exists = metadata_exists('post', $id, $public_key);
            $result['previous'][(string) $id][$public_key] = array(
                'exists' => $public_exists,
                'value' => $public_exists ? get_post_meta($id, $public_key, true) : null,
            );

            if ($public_exists || !metadata_exists('post', $id, $definition['legacy_key'])) {
                continue;
            }

            $legacy_value = get_post_meta($id, $definition['legacy_key'], true);
            $sanitized = wp_seed_content_sanitize_quote_builder_meta($legacy_value, $public_key);
            if ('boolean' !== $definition['type'] && $sanitized !== (string) $legacy_value) {
                $result['errors'][(string) $id][$public_key] = 'legacy_value_requires_editorial_change';
                continue;
            }

            $plans[(string) $id][$public_key] = $sanitized;
        }
    }

    if (!empty($result['errors'])) {
        $result['aborted'] = true;
        return $result;
    }

    foreach ($plans as $raw_id => $values) {
        $id = absint($raw_id);
        if (empty($values)) {
            $result['unchanged'][] = $id;
            continue;
        }

        foreach ($values as $public_key => $value) {
            update_post_meta($id, $public_key, $value);
        }
        $result['updated'][] = $id;
    }

    return $result;
}

function wp_seed_content_rollback_quote_builder_meta($migration_result)
{
    $result = array('restored' => array(), 'skipped' => array());
    $previous = is_array($migration_result) && isset($migration_result['previous']) && is_array($migration_result['previous'])
        ? $migration_result['previous']
        : array();
    $definitions = wp_seed_content_quote_builder_meta_definitions();

    foreach ($previous as $raw_id => $states) {
        $id = absint($raw_id);
        $post = $id ? get_post($id) : null;
        if (!$post instanceof WP_Post || 'seed_quote' !== $post->post_type || !is_array($states)) {
            $result['skipped'][] = $id;
            continue;
        }

        foreach ($definitions as $public_key => $definition) {
            if (!isset($states[$public_key]) || !is_array($states[$public_key])) {
                continue;
            }

            if (!empty($states[$public_key]['exists'])) {
                update_post_meta($id, $public_key, $states[$public_key]['value']);
            } else {
                delete_post_meta($id, $public_key);
            }
        }
        $result['restored'][] = $id;
    }

    return $result;
}
