<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_public_listing_schema_option()
{
    return 'wp_seed_content_directory_public_listing_schema';
}

function wp_seed_content_directory_public_listing_state_option()
{
    return 'wp_seed_content_directory_public_listing_migration';
}

function wp_seed_content_directory_public_listing_default_state()
{
    return array(
        'version' => 1,
        'status' => 'pending',
        'cursor' => 0,
        'scanned' => 0,
        'updated' => 0,
        'kept' => 0,
        'skipped' => 0,
        'errors' => 0,
        'updated_ids' => array(),
        'started_at' => '',
        'updated_at' => '',
        'completed_at' => '',
    );
}

function wp_seed_content_directory_public_listing_get_state()
{
    $state = get_option(
        wp_seed_content_directory_public_listing_state_option(),
        array()
    );
    if (!is_array($state)) {
        $state = array();
    }

    return array_merge(
        wp_seed_content_directory_public_listing_default_state(),
        $state
    );
}

function wp_seed_content_directory_public_listing_save_state($state)
{
    $state['updated_at'] = gmdate('c');
    update_option(
        wp_seed_content_directory_public_listing_state_option(),
        $state,
        false
    );
}

function wp_seed_content_directory_public_listing_complete_state($state)
{
    $state['status'] = 'complete';
    $state['completed_at'] = gmdate('c');
    wp_seed_content_directory_public_listing_save_state($state);
    update_option(
        wp_seed_content_directory_public_listing_schema_option(),
        1,
        false
    );
    do_action(
        'wp_seed_content_directory_public_listing_migration_complete',
        $state
    );

    return $state;
}

function wp_seed_content_directory_upgrade_public_listing($limit = 100)
{
    if ((int) get_option(
        wp_seed_content_directory_public_listing_schema_option(),
        0
    ) >= 1) {
        $state = wp_seed_content_directory_public_listing_get_state();
        $state['status'] = 'complete';
        return $state;
    }

    $limit = max(1, min(500, absint($limit)));
    $state = wp_seed_content_directory_public_listing_get_state();
    if ('pending' === $state['status']) {
        $state['status'] = 'running';
        $state['started_at'] = gmdate('c');
        wp_seed_content_directory_public_listing_save_state($state);
    }

    $post_ids = get_posts(array(
        'post_type' => 'seed_directory',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'suppress_filters' => true,
        'no_found_rows' => true,
    ));
    $post_ids = array_values(array_unique(array_map('absint', $post_ids)));
    sort($post_ids, SORT_NUMERIC);

    $pending = array();
    foreach ($post_ids as $post_id) {
        if ($post_id > (int) $state['cursor']) {
            $pending[] = $post_id;
        }
    }
    if (empty($pending)) {
        return wp_seed_content_directory_public_listing_complete_state($state);
    }

    $batch = array_slice($pending, 0, $limit);
    foreach ($batch as $post_id) {
        $state['scanned']++;
        $post = get_post($post_id);
        if (!$post || 'seed_directory' !== $post->post_type) {
            $state['errors']++;
        } elseif (metadata_exists(
            'post',
            $post_id,
            '_seed_directory_publicly_listed'
        )) {
            $state['kept']++;
        } elseif (
            'publish' === $post->post_status
            && '' === (string) $post->post_password
        ) {
            $written = add_post_meta(
                $post_id,
                '_seed_directory_publicly_listed',
                '1',
                true
            );
            if ($written) {
                $state['updated']++;
                $state['updated_ids'][] = $post_id;
            } elseif (metadata_exists(
                'post',
                $post_id,
                '_seed_directory_publicly_listed'
            )) {
                $state['kept']++;
            } else {
                $state['errors']++;
            }
        } else {
            $state['skipped']++;
        }

        $state['cursor'] = $post_id;
        wp_seed_content_directory_public_listing_save_state($state);
    }

    if (count($batch) === count($pending)) {
        return wp_seed_content_directory_public_listing_complete_state($state);
    }

    do_action(
        'wp_seed_content_directory_public_listing_migration_batch',
        $state
    );
    return $state;
}

function wp_seed_content_directory_maybe_upgrade_public_listing()
{
    wp_seed_content_directory_upgrade_public_listing(100);
}
add_action(
    'init',
    'wp_seed_content_directory_maybe_upgrade_public_listing',
    35
);
