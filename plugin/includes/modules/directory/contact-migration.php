<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_migrate_directory_contacts($directory_ids)
{
    $ids = function_exists('_wp_seed_content_collections_normalize_ids')
        ? _wp_seed_content_collections_normalize_ids($directory_ids)
        : array();
    $result = array('updated' => array(), 'unchanged' => array(), 'skipped' => array(), 'previous' => array());
    $key = wp_seed_content_directory_contacts_meta_key();

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post instanceof WP_Post || 'seed_directory' !== $post->post_type) {
            $result['skipped'][] = $id;
            continue;
        }
        $exists = metadata_exists('post', $id, $key);
        $result['previous'][(string) $id] = array(
            'exists' => $exists,
            'value' => $exists ? get_post_meta($id, $key, true) : null,
        );
        if ($exists) {
            $result['unchanged'][] = $id;
            continue;
        }
        $contacts = wp_seed_content_directory_get_legacy_contacts($id);
        update_post_meta($id, $key, $contacts);
        $result['updated'][] = $id;
    }

    return $result;
}

function wp_seed_content_rollback_directory_contacts($migration_result)
{
    $result = array('restored' => array(), 'skipped' => array());
    $previous = is_array($migration_result) && isset($migration_result['previous']) && is_array($migration_result['previous'])
        ? $migration_result['previous']
        : array();
    $key = wp_seed_content_directory_contacts_meta_key();
    foreach ($previous as $raw_id => $state) {
        $id = absint($raw_id);
        $post = $id ? get_post($id) : null;
        if (!$post instanceof WP_Post || 'seed_directory' !== $post->post_type || !is_array($state)) {
            $result['skipped'][] = $id;
            continue;
        }
        if (!empty($state['exists'])) {
            update_post_meta($id, $key, $state['value']);
        } else {
            delete_post_meta($id, $key);
        }
        $result['restored'][] = $id;
    }
    return $result;
}

/**
 * Builds a read-only migration plan from legacy contact values to full links.
 *
 * The plan contains counts and IDs only. It never exposes contact values and
 * never writes metadata; applying it requires a separate, explicit operation.
 */
function wp_seed_content_plan_directory_contact_full_links($directory_ids)
{
    $ids = function_exists('_wp_seed_content_collections_normalize_ids')
        ? _wp_seed_content_collections_normalize_ids($directory_ids)
        : array();
    $result = array(
        'posts' => array(),
        'rows' => 0,
        'already_full' => 0,
        'migratable' => 0,
        'display_only' => 0,
        'invalid' => 0,
        'by_type' => array(),
    );

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post instanceof WP_Post || 'seed_directory' !== $post->post_type) {
            continue;
        }
        $post_migrations = 0;
        foreach (wp_seed_content_directory_get_contacts($id) as $contact) {
            $result['rows']++;
            $definition = wp_seed_content_directory_get_contact_type($contact['type']);
            if (!$definition || empty($definition['href_type'])) {
                $result['display_only']++;
                continue;
            }
            if (wp_seed_content_directory_is_full_contact_link($contact['type'], $contact['value'])) {
                $result['already_full']++;
                continue;
            }
            if ('' === wp_seed_content_directory_contact_href($contact['type'], $contact['value'])) {
                $result['invalid']++;
                continue;
            }
            $result['migratable']++;
            $post_migrations++;
            if (!isset($result['by_type'][$contact['type']])) {
                $result['by_type'][$contact['type']] = 0;
            }
            $result['by_type'][$contact['type']]++;
        }
        if ($post_migrations) {
            $result['posts'][] = $id;
        }
    }
    ksort($result['by_type']);
    return $result;
}
