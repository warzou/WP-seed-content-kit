<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_classification_migration_snapshot($post_id)
{
    $snapshot = array('post_id' => absint($post_id), 'menu_order' => (int) get_post_field('menu_order', $post_id), 'meta' => array(), 'terms' => array());
    $meta_keys = array(
        'seed_directory_status', 'seed_directory_profile_types', 'seed_directory_seeking_models',
        '_seed_directory_status', '_seed_directory_profile_types', '_seed_directory_seeking_models',
    );
    foreach ($meta_keys as $key) {
        $snapshot['meta'][$key] = array(
            'exists' => metadata_exists('post', $post_id, $key),
            'value' => get_post_meta($post_id, $key, true),
        );
    }
    foreach (wp_seed_content_directory_classification_taxonomies() as $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        $snapshot['terms'][$taxonomy] = is_wp_error($terms) ? array() : $terms;
    }
    return $snapshot;
}

function wp_seed_content_directory_classification_migration_entry($post_id)
{
    $snapshot = wp_seed_content_directory_classification_migration_snapshot($post_id);
    $legacy_status = $snapshot['meta']['_seed_directory_status']['value'];
    $legacy_seeking = $snapshot['meta']['seed_directory_seeking_models']['value'];
    if ('' === (string) $legacy_seeking) {
        $legacy_seeking = $snapshot['meta']['_seed_directory_seeking_models']['value'];
    }
    $new_status = wp_seed_content_directory_resolve_status($post_id);
    $old_profiles = $snapshot['meta']['seed_directory_profile_types']['value'];
    if (!wp_seed_content_directory_normalize_profile_slugs($old_profiles, true)) {
        $old_profiles = $snapshot['meta']['_seed_directory_profile_types']['value'];
    }
    $new_profiles = wp_seed_content_directory_normalize_profile_slugs($old_profiles, true);
    $warnings = array();
    if ('' === $new_status) {
        $warnings[] = 'missing_valid_status';
    }
    if (empty($new_profiles)) {
        $warnings[] = 'no_profile_type';
    }
    return array(
        'ID' => absint($post_id),
        'name' => (string) get_the_title($post_id),
        'old_status' => (string) $legacy_status,
        'seeking_models' => !empty($legacy_seeking),
        'new_status' => $new_status,
        'old_profile_types' => is_array($old_profiles) ? $old_profiles : wp_seed_content_directory_normalize_profile_slugs($old_profiles, true),
        'new_profile_types' => $new_profiles,
        'taxonomy_projection_expected' => array(
            'wp_seed_directory_status' => '' === $new_status ? array() : array(wp_seed_content_directory_registry_slug_to_term_slug($new_status)),
            'wp_seed_directory_profile_type' => array_map('wp_seed_content_directory_registry_slug_to_term_slug', $new_profiles),
        ),
        'warnings' => $warnings,
        'snapshot' => $snapshot,
    );
}

function wp_seed_content_directory_classification_migration_dry_run($post_ids = null)
{
    if (null === $post_ids) {
        $post_ids = get_posts(array('post_type' => 'seed_directory', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC'));
    }
    $report = array(
        'mode' => 'dry_run', 'total' => 0, 'entries' => array(),
        'populations' => array('intervenants' => 0, 'active_practitioners' => 0, 'seeking_models' => 0),
        'warnings' => array(),
    );
    foreach ((array) $post_ids as $post_id) {
        $entry = wp_seed_content_directory_classification_migration_entry($post_id);
        $report['entries'][] = $entry;
        if (in_array('intervenant', $entry['new_profile_types'], true)) {
            $report['populations']['intervenants']++;
        }
        if ('en_exercice' === $entry['new_status'] && in_array('praticien', $entry['new_profile_types'], true)) {
            $report['populations']['active_practitioners']++;
        }
        if ('recherche_modeles' === $entry['new_status']) {
            $report['populations']['seeking_models']++;
        }
        foreach ($entry['warnings'] as $warning) {
            $report['warnings'][] = array('ID' => $entry['ID'], 'warning' => $warning);
        }
    }
    $report['total'] = count($report['entries']);
    return $report;
}

function wp_seed_content_directory_classification_migration_apply($dry_run)
{
    if (!is_array($dry_run) || 'dry_run' !== (isset($dry_run['mode']) ? $dry_run['mode'] : '') || empty($dry_run['entries'])) {
        return new WP_Error('wpsck_invalid_directory_migration_plan', __('Plan de migration Annuaire invalide.', 'wp-seed-content-kit'));
    }
    $result = array('mode' => 'apply', 'snapshots' => array(), 'applied' => array(), 'failed' => array());
    foreach ($dry_run['entries'] as $entry) {
        $post_id = absint($entry['ID']);
        $result['snapshots'][$post_id] = $entry['snapshot'];
        if ('' === $entry['new_status']) {
            $result['failed'][] = $post_id;
            continue;
        }
        update_post_meta($post_id, 'seed_directory_status', $entry['new_status']);
        if ($entry['new_profile_types']) {
            update_post_meta($post_id, 'seed_directory_profile_types', $entry['new_profile_types']);
        } else {
            delete_post_meta($post_id, 'seed_directory_profile_types');
        }
        if (wp_seed_content_directory_sync_classification_projection($post_id)) {
            $result['applied'][] = $post_id;
        } else {
            $result['failed'][] = $post_id;
        }
    }
    return $result;
}

function wp_seed_content_directory_classification_migration_rollback($apply_result)
{
    if (!is_array($apply_result) || empty($apply_result['snapshots'])) {
        return new WP_Error('wpsck_invalid_directory_rollback', __('Snapshot de rollback Annuaire invalide.', 'wp-seed-content-kit'));
    }
    $restored = array();
    foreach ($apply_result['snapshots'] as $post_id => $snapshot) {
        foreach ($snapshot['meta'] as $key => $state) {
            if ($state['exists']) {
                update_post_meta($post_id, $key, $state['value']);
            } else {
                delete_post_meta($post_id, $key);
            }
        }
        foreach ($snapshot['terms'] as $taxonomy => $slugs) {
            wp_set_object_terms($post_id, $slugs, $taxonomy, false);
        }
        wp_update_post(array('ID' => absint($post_id), 'menu_order' => (int) $snapshot['menu_order']));
        $restored[] = absint($post_id);
    }
    return array('mode' => 'rollback', 'restored' => $restored);
}
