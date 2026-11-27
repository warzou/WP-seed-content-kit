<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_classification_taxonomies()
{
    return array(
        'status' => 'wp_seed_directory_status',
        'profile_type' => 'wp_seed_directory_profile_type',
    );
}

function wp_seed_content_directory_registry_slug_to_term_slug($slug)
{
    return str_replace('_', '-', sanitize_key((string) $slug));
}

function wp_seed_content_directory_term_slug_to_registry_slug($slug)
{
    return str_replace('-', '_', sanitize_title((string) $slug));
}

function wp_seed_content_directory_register_classification_taxonomies()
{
    $labels = array(
        'status' => __('Statuts Annuaire', 'wp-seed-content-kit'),
        'profile_type' => __('Types de profil Annuaire', 'wp-seed-content-kit'),
    );
    foreach (wp_seed_content_directory_classification_taxonomies() as $kind => $taxonomy) {
        register_taxonomy($taxonomy, array('seed_directory'), array(
            'labels' => array('name' => $labels[$kind], 'singular_name' => $labels[$kind]),
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => false,
            'show_admin_column' => false,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'hierarchical' => false,
            'rewrite' => false,
            'query_var' => false,
            'meta_box_cb' => false,
        ));
    }
}
add_action('init', 'wp_seed_content_directory_register_classification_taxonomies', 9);

function wp_seed_content_directory_ensure_projection_term($kind, $slug)
{
    $taxonomies = wp_seed_content_directory_classification_taxonomies();
    $registry = wp_seed_content_directory_get_classification_registry($kind, false);
    if (!isset($taxonomies[$kind], $registry[$slug]) || !taxonomy_exists($taxonomies[$kind])) {
        return 0;
    }
    $taxonomy = $taxonomies[$kind];
    $term_slug = wp_seed_content_directory_registry_slug_to_term_slug($slug);
    $existing = get_term_by('slug', $term_slug, $taxonomy);
    if ($existing && !is_wp_error($existing)) {
        if ($existing->name !== $registry[$slug]['label']) {
            wp_update_term((int) $existing->term_id, $taxonomy, array('name' => $registry[$slug]['label']));
        }
        return (int) $existing->term_id;
    }
    $created = wp_insert_term($registry[$slug]['label'], $taxonomy, array('slug' => $term_slug));
    return is_wp_error($created) ? 0 : (int) $created['term_id'];
}

function wp_seed_content_directory_expected_projection($post_id)
{
    return array(
        'status' => array_filter(array(wp_seed_content_directory_resolve_status($post_id))),
        'profile_type' => wp_seed_content_directory_resolve_profile_types($post_id),
    );
}

function wp_seed_content_directory_sync_classification_projection($post_id)
{
    $post_id = absint($post_id);
    if (!$post_id || 'seed_directory' !== get_post_type($post_id)) {
        return false;
    }
    $expected = wp_seed_content_directory_expected_projection($post_id);
    foreach (wp_seed_content_directory_classification_taxonomies() as $kind => $taxonomy) {
        $term_ids = array();
        foreach ($expected[$kind] as $slug) {
            $term_id = wp_seed_content_directory_ensure_projection_term($kind, $slug);
            if ($term_id) {
                $term_ids[] = $term_id;
            }
        }
        if ('status' === $kind) {
            $term_ids = array_slice($term_ids, 0, 1);
        }
        $result = wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
        if (is_wp_error($result)) {
            return false;
        }
    }
    return true;
}

function wp_seed_content_directory_maybe_sync_classification_projection($post_id, $post)
{
    if (!is_object($post) || 'seed_directory' !== $post->post_type || wp_is_post_revision($post_id)) {
        return;
    }
    wp_seed_content_directory_sync_classification_projection($post_id);
}
add_action('save_post_seed_directory', 'wp_seed_content_directory_maybe_sync_classification_projection', 40, 2);

function wp_seed_content_directory_rebuild_classification_projections($post_ids = null)
{
    if (null === $post_ids) {
        $post_ids = get_posts(array('post_type' => 'seed_directory', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1));
    }
    $result = array('synced' => array(), 'failed' => array());
    foreach ((array) $post_ids as $post_id) {
        $post_id = absint($post_id);
        $bucket = wp_seed_content_directory_sync_classification_projection($post_id) ? 'synced' : 'failed';
        $result[$bucket][] = $post_id;
    }
    return $result;
}

function wp_seed_content_directory_audit_classification_projection($post_id)
{
    $expected = wp_seed_content_directory_expected_projection($post_id);
    $actual = array();
    foreach (wp_seed_content_directory_classification_taxonomies() as $kind => $taxonomy) {
        $slugs = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        $slugs = is_wp_error($slugs) ? array() : $slugs;
        $actual[$kind] = array_map('wp_seed_content_directory_term_slug_to_registry_slug', $slugs);
    }
    return array(
        'post_id' => absint($post_id),
        'expected' => $expected,
        'actual' => $actual,
        'match' => $expected['status'] === $actual['status'] && $expected['profile_type'] === $actual['profile_type'],
    );
}

function wp_seed_content_directory_reject_projection_rest_writes($prepared_post, $request)
{
    if (!is_object($request) || !is_callable(array($request, 'get_params'))) {
        return $prepared_post;
    }
    $params = $request->get_params();
    foreach (wp_seed_content_directory_classification_taxonomies() as $taxonomy) {
        if (array_key_exists($taxonomy, $params)) {
            return new WP_Error('wpsck_projection_read_only', __('Les taxonomies Annuaire sont des projections en lecture seule.', 'wp-seed-content-kit'), array('status' => 400));
        }
    }
    return $prepared_post;
}
add_filter('rest_pre_insert_seed_directory', 'wp_seed_content_directory_reject_projection_rest_writes', 10, 2);
