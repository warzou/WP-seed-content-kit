<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_is_single_post_type_query($query_args, $post_type)
{
    if (!is_array($query_args) || !is_string($post_type) || '' === $post_type) {
        return false;
    }

    $post_types = isset($query_args['post_type']) ? (array) $query_args['post_type'] : array();
    $post_types = array_values(array_unique(array_map('sanitize_key', $post_types)));

    return array($post_type) === $post_types;
}

/**
 * Apply canonical collection IDs while preserving Divi's own query bounds.
 */
function wp_seed_content_divi_apply_collection_ids($query_args, $collection_ids)
{
    if (!is_array($query_args)) {
        return $query_args;
    }

    $ids = array_values(array_filter(array_map('absint', (array) $collection_ids), function ($id) {
        return $id > 0;
    }));

    if (!empty($query_args['post__in'])) {
        $allowed = array_map('absint', (array) $query_args['post__in']);
        $ids = array_values(array_filter($ids, function ($id) use ($allowed) {
            return in_array($id, $allowed, true);
        }));
    }

    if (!empty($query_args['post__not_in'])) {
        $excluded = array_map('absint', (array) $query_args['post__not_in']);
        $ids = array_values(array_diff($ids, $excluded));
    }

    $query_args['post__in'] = empty($ids) ? array(0) : $ids;
    $query_args['orderby'] = 'post__in';
    $query_args['order'] = 'ASC';

    return $query_args;
}
