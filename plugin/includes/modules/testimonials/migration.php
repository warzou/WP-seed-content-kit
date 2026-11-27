<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_migrate_testimonial_publication_consent($testimonial_ids)
{
    $ids = function_exists('_wp_seed_content_collections_normalize_ids')
        ? _wp_seed_content_collections_normalize_ids($testimonial_ids)
        : array();
    $result = array('updated' => array(), 'unchanged' => array(), 'skipped' => array(), 'previous' => array());
    $meta_key = wp_seed_content_testimonial_publication_consent_meta_key();

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type) {
            $result['skipped'][] = $id;
            continue;
        }

        $exists = metadata_exists('post', $id, $meta_key);
        $previous = $exists ? (string) get_post_meta($id, $meta_key, true) : null;
        $result['previous'][(string) $id] = array('exists' => $exists, 'value' => $previous);

        if ('1' === $previous) {
            $result['unchanged'][] = $id;
            continue;
        }

        update_post_meta($id, $meta_key, '1');
        $result['updated'][] = $id;
    }

    return $result;
}

function wp_seed_content_rollback_testimonial_publication_consent($migration_result)
{
    $result = array('restored' => array(), 'skipped' => array());
    $previous = is_array($migration_result) && isset($migration_result['previous']) && is_array($migration_result['previous'])
        ? $migration_result['previous']
        : array();
    $meta_key = wp_seed_content_testimonial_publication_consent_meta_key();

    foreach ($previous as $raw_id => $state) {
        $id = absint($raw_id);
        if (!$id || !is_array($state) || !array_key_exists('exists', $state)) {
            $result['skipped'][] = $id;
            continue;
        }
        if (!empty($state['exists'])) {
            update_post_meta($id, $meta_key, isset($state['value']) ? (string) $state['value'] : '');
        } else {
            delete_post_meta($id, $meta_key);
        }
        $result['restored'][] = $id;
    }

    return $result;
}

/**
 * Preflight or apply the legacy short-summary migration.
 *
 * Only post_excerpt can be written. A divergent pair aborts the complete
 * batch before any write so an editor can choose the authoritative value.
 *
 * @param array $testimonial_ids Testimonial IDs to inspect.
 * @param bool  $apply           Whether to apply the safe migration plan.
 *
 * @return array Migration plan/result and exact previous excerpts.
 */
function wp_seed_content_migrate_testimonial_summaries($testimonial_ids, $apply = false)
{
    $ids = function_exists('_wp_seed_content_collections_normalize_ids')
        ? _wp_seed_content_collections_normalize_ids($testimonial_ids)
        : array();
    $result = array(
        'updated' => array(),
        'planned' => array(),
        'preserved' => array(),
        'empty' => array(),
        'skipped' => array(),
        'errors' => array(),
        'previous' => array(),
        'aborted' => false,
        'applied' => false,
    );
    $plans = array();

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type) {
            $result['skipped'][] = $id;
            continue;
        }

        $excerpt = isset($post->post_excerpt) ? (string) $post->post_excerpt : '';
        $legacy = (string) get_post_meta($id, '_seed_testimonial_summary', true);
        $result['previous'][(string) $id] = $excerpt;

        if ('' !== $excerpt && '' !== $legacy && $excerpt !== $legacy) {
            $result['errors'][(string) $id] = 'summary_values_differ';
            continue;
        }
        if ('' !== $excerpt) {
            $result['preserved'][] = $id;
            continue;
        }
        if ('' === $legacy) {
            $result['empty'][] = $id;
            continue;
        }

        $plans[(string) $id] = $legacy;
        $result['planned'][] = $id;
    }

    if (!empty($result['errors'])) {
        $result['aborted'] = true;
        return $result;
    }
    if (!$apply) {
        return $result;
    }

    foreach ($plans as $raw_id => $summary) {
        $id = absint($raw_id);
        $updated = wp_update_post(
            wp_slash(array(
                'ID' => $id,
                'post_excerpt' => $summary,
            )),
            true
        );
        if (is_wp_error($updated)) {
            $result['errors'][(string) $id] = $updated->get_error_code();
            continue;
        }
        $result['updated'][] = $id;
    }
    $result['applied'] = empty($result['errors']);

    return $result;
}

function wp_seed_content_rollback_testimonial_summaries($migration_result)
{
    $result = array('restored' => array(), 'skipped' => array(), 'errors' => array());
    $previous = is_array($migration_result) && isset($migration_result['previous']) && is_array($migration_result['previous'])
        ? $migration_result['previous']
        : array();

    foreach ($previous as $raw_id => $excerpt) {
        $id = absint($raw_id);
        $post = $id ? get_post($id) : null;
        if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type) {
            $result['skipped'][] = $id;
            continue;
        }
        $updated = wp_update_post(
            wp_slash(array(
                'ID' => $id,
                'post_excerpt' => (string) $excerpt,
            )),
            true
        );
        if (is_wp_error($updated)) {
            $result['errors'][(string) $id] = $updated->get_error_code();
            continue;
        }
        $result['restored'][] = $id;
    }

    return $result;
}

/**
 * Copy legacy private fields to registered public builder metadata.
 *
 * Existing public metadata is authoritative, including an intentionally empty
 * value. Legacy keys are retained as a transitional read-only fallback.
 */
function wp_seed_content_migrate_testimonial_builder_meta($testimonial_ids)
{
    $ids = function_exists('_wp_seed_content_collections_normalize_ids')
        ? _wp_seed_content_collections_normalize_ids($testimonial_ids)
        : array();
    $result = array(
        'updated' => array(),
        'unchanged' => array(),
        'skipped' => array(),
        'errors' => array(),
        'previous' => array(),
        'aborted' => false,
    );
    $definitions = wp_seed_content_testimonial_builder_meta_definitions();
    $plans = array();

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type) {
            $result['skipped'][] = $id;
            continue;
        }

        $result['previous'][(string) $id] = array();
        $plans[(string) $id] = array();

        foreach ($definitions as $public_key => $definition) {
            $public_exists = metadata_exists('post', $id, $public_key);
            $result['previous'][(string) $id][$public_key] = array(
                'exists' => $public_exists,
                'value' => $public_exists ? (string) get_post_meta($id, $public_key, true) : null,
            );

            if ($public_exists || !metadata_exists('post', $id, $definition['legacy_key'])) {
                continue;
            }

            $legacy_value = (string) get_post_meta($id, $definition['legacy_key'], true);
            $sanitized = wp_seed_content_sanitize_testimonial_builder_meta($legacy_value, $public_key);
            if ($sanitized !== $legacy_value) {
                $result['errors'][(string) $id][$public_key] = 'legacy_value_requires_editorial_change';
                continue;
            }

            $plans[(string) $id][$public_key] = $legacy_value;
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

function wp_seed_content_rollback_testimonial_builder_meta($migration_result)
{
    $result = array('restored' => array(), 'skipped' => array());
    $previous = is_array($migration_result) && isset($migration_result['previous']) && is_array($migration_result['previous'])
        ? $migration_result['previous']
        : array();
    $definitions = wp_seed_content_testimonial_builder_meta_definitions();

    foreach ($previous as $raw_id => $states) {
        $id = absint($raw_id);
        $post = $id ? get_post($id) : null;
        if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type || !is_array($states)) {
            $result['skipped'][] = $id;
            continue;
        }

        foreach ($definitions as $public_key => $definition) {
            if (!isset($states[$public_key]) || !is_array($states[$public_key])) {
                continue;
            }

            if (!empty($states[$public_key]['exists'])) {
                update_post_meta(
                    $id,
                    $public_key,
                    isset($states[$public_key]['value']) ? (string) $states[$public_key]['value'] : ''
                );
            } else {
                delete_post_meta($id, $public_key);
            }
        }
        $result['restored'][] = $id;
    }

    return $result;
}
