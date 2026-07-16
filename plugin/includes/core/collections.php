<?php

if (!defined('ABSPATH')) {
    exit;
}

function _wp_seed_content_collections_normalize_ids($ids)
{
    if (!is_array($ids)) {
        return array();
    }

    $normalized = array();
    $seen = array();

    foreach ($ids as $id) {
        if (!is_int($id) || $id <= 0 || isset($seen[$id])) {
            continue;
        }

        $seen[$id] = true;
        $normalized[] = $id;
    }

    return $normalized;
}

function _wp_seed_content_collections_normalize_testimonial_args($args)
{
    $args = is_array($args) ? $args : array();
    $ids_value = array_key_exists('ids', $args) ? $args['ids'] : array();
    $manual_selection = is_array($ids_value)
        ? !empty($ids_value)
        : null !== $ids_value && '' !== $ids_value && false !== $ids_value && 0 !== $ids_value;
    $invalid_manual_selection = $manual_selection && !is_array($ids_value);

    $featured = isset($args['featured']) && is_string($args['featured'])
        ? strtolower($args['featured'])
        : 'all';
    if (!in_array($featured, array('all', 'only', 'exclude'), true)) {
        $featured = 'all';
    }

    $limit = isset($args['limit']) && is_int($args['limit']) && $args['limit'] > 0
        ? $args['limit']
        : 0;

    $orderby = isset($args['orderby']) && is_string($args['orderby'])
        ? strtolower($args['orderby'])
        : 'display_order';
    if (!in_array($orderby, array('display_order', 'date', 'testimonial_date', 'id'), true)) {
        $orderby = 'display_order';
    }

    $order = isset($args['order']) && is_string($args['order'])
        ? strtolower($args['order'])
        : 'asc';
    if (!in_array($order, array('asc', 'desc'), true)) {
        $order = 'asc';
    }

    return array(
        'ids' => _wp_seed_content_collections_normalize_ids($ids_value),
        'manual_selection' => $manual_selection,
        'invalid_manual_selection' => $invalid_manual_selection,
        'featured' => $featured,
        'limit' => $limit,
        'orderby' => $orderby,
        'order' => $order,
    );
}

function _wp_seed_content_collections_apply_limit($ids, $limit)
{
    if ($limit <= 0) {
        return $ids;
    }

    return array_slice($ids, 0, $limit);
}

function _wp_seed_content_collections_get_manual_testimonials($ids)
{
    if (empty($ids)) {
        return array();
    }

    $posts = get_posts(
        array(
            'post_type' => 'seed_testimonial',
            'post_status' => 'publish',
            'has_password' => false,
            'post__in' => $ids,
            'posts_per_page' => count($ids),
            'orderby' => 'post__in',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'suppress_filters' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    $published = array();
    foreach ($posts as $post) {
        if (
            $post instanceof WP_Post
            && 'seed_testimonial' === $post->post_type
            && 'publish' === $post->post_status
        ) {
            $published[(int) $post->ID] = true;
        }
    }

    $result = array();
    foreach ($ids as $id) {
        if (isset($published[$id])) {
            $result[] = $id;
        }
    }

    return $result;
}

function _wp_seed_content_collections_compare_testimonials($left, $right, $orderby, $order)
{
    $left_id = (int) $left->ID;
    $right_id = (int) $right->ID;

    if ('id' === $orderby) {
        return 'desc' === $order ? $right_id <=> $left_id : $left_id <=> $right_id;
    }

    if ('display_order' === $orderby) {
        $comparison = (int) $left->menu_order <=> (int) $right->menu_order;
    } elseif ('date' === $orderby) {
        $comparison = strcmp((string) $left->post_date, (string) $right->post_date);
    } else {
        $left_date = wp_seed_content_sanitize_iso_date(
            wp_seed_content_get_meta($left_id, '_seed_testimonial_date')
        );
        $right_date = wp_seed_content_sanitize_iso_date(
            wp_seed_content_get_meta($right_id, '_seed_testimonial_date')
        );

        $left_has_date = '' !== $left_date;
        $right_has_date = '' !== $right_date;

        if ($left_has_date !== $right_has_date) {
            return $left_has_date ? -1 : 1;
        }

        $comparison = $left_has_date ? strcmp($left_date, $right_date) : 0;
    }

    if (0 !== $comparison) {
        return 'desc' === $order ? -$comparison : $comparison;
    }

    return $left_id <=> $right_id;
}

function _wp_seed_content_collections_get_testimonial_posts()
{
    $posts = get_posts(
        array(
            'post_type' => 'seed_testimonial',
            'post_status' => 'publish',
            'has_password' => false,
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'suppress_filters' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        )
    );

    return array_values(
        array_filter(
            $posts,
            function ($post) {
                return $post instanceof WP_Post
                    && 'seed_testimonial' === $post->post_type
                    && 'publish' === $post->post_status;
            }
        )
    );
}

function wp_seed_content_get_testimonials($args = array())
{
    if (!wp_seed_content_kit_is_module_active('testimonials')) {
        return array();
    }

    $args = _wp_seed_content_collections_normalize_testimonial_args($args);

    if ($args['invalid_manual_selection']) {
        return array();
    }

    if ($args['manual_selection']) {
        $ids = _wp_seed_content_collections_get_manual_testimonials($args['ids']);

        return _wp_seed_content_collections_apply_limit($ids, $args['limit']);
    }

    $posts = _wp_seed_content_collections_get_testimonial_posts();

    if ('all' !== $args['featured']) {
        $posts = array_values(
            array_filter(
                $posts,
                function ($post) use ($args) {
                    $featured = wp_seed_content_is_truthy_meta($post->ID, '_seed_featured');

                    return 'only' === $args['featured'] ? $featured : !$featured;
                }
            )
        );
    }

    usort(
        $posts,
        function ($left, $right) use ($args) {
            return _wp_seed_content_collections_compare_testimonials(
                $left,
                $right,
                $args['orderby'],
                $args['order']
            );
        }
    );

    $ids = array_map(
        function ($post) {
            return (int) $post->ID;
        },
        $posts
    );

    return _wp_seed_content_collections_apply_limit($ids, $args['limit']);
}

function _wp_seed_content_collections_get_local_date($timestamp = null, $timezone = null)
{
    $timestamp = is_int($timestamp) ? $timestamp : time();
    $timezone = $timezone instanceof DateTimeZone ? $timezone : wp_timezone();

    return wp_date('Y-m-d', $timestamp, $timezone);
}

function _wp_seed_content_collections_get_daily_index($candidate_count, $site_url, $local_date)
{
    $candidate_count = is_int($candidate_count) && $candidate_count > 0 ? $candidate_count : 0;
    if (0 === $candidate_count) {
        return 0;
    }

    $seed = (string) $site_url . '|' . (string) $local_date;
    $hash = hash('sha256', $seed);
    $value = hexdec(substr($hash, 0, 7));

    return (int) ($value % $candidate_count);
}

function wp_seed_content_get_daily_quote($args = array())
{
    if (!wp_seed_content_kit_is_module_active('quotes')) {
        return 0;
    }

    $candidate_ids = get_posts(
        array(
            'post_type' => 'seed_quote',
            'post_status' => 'publish',
            'has_password' => false,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'suppress_filters' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    $candidate_ids = array_values(
        array_unique(
            array_filter(
                array_map('absint', $candidate_ids),
                function ($id) {
                    return $id > 0;
                }
            )
        )
    );
    sort($candidate_ids, SORT_NUMERIC);

    if (empty($candidate_ids)) {
        return 0;
    }

    $index = _wp_seed_content_collections_get_daily_index(
        count($candidate_ids),
        home_url('/'),
        _wp_seed_content_collections_get_local_date()
    );

    return isset($candidate_ids[$index]) ? (int) $candidate_ids[$index] : 0;
}
