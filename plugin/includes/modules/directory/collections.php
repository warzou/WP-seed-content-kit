<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_normalize_collection_ids($ids)
{
    if (!is_array($ids)) {
        return null;
    }

    $normalized = array();
    foreach ($ids as $id) {
        if (!is_int($id) || $id <= 0) {
            return null;
        }
        $normalized[$id] = $id;
    }

    return array_values($normalized);
}

function wp_seed_content_directory_normalize_collection_args($args)
{
    if (!is_array($args)) {
        return null;
    }

    $defaults = array(
        'status' => 'all',
        'department' => '',
        'country' => '',
        'featured' => 'all',
        'limit' => 0,
        'orderby' => 'display_order',
        'order' => 'asc',
        'ids' => array(),
    );
    $args = array_merge($defaults, $args);

    $status = is_string($args['status']) ? strtolower($args['status']) : '';
    $featured = is_string($args['featured']) ? strtolower($args['featured']) : '';
    $orderby = is_string($args['orderby']) ? strtolower($args['orderby']) : '';
    $order = is_string($args['order']) ? strtolower($args['order']) : '';
    if (!in_array($status, array('all', 'practicing', 'seeking_models'), true)
        || !in_array($featured, array('all', 'only', 'exclude'), true)
        || !in_array($orderby, array('display_order', 'name', 'date', 'id'), true)
        || !in_array($order, array('asc', 'desc'), true)
        || !is_int($args['limit'])
        || $args['limit'] < 0
    ) {
        return null;
    }

    $department_raw = is_scalar($args['department']) ? trim((string) $args['department']) : '';
    $country_raw = is_scalar($args['country']) ? trim((string) $args['country']) : '';
    $department = '' === $department_raw ? '' : wp_seed_content_directory_sanitize_meta_value('_seed_directory_department', $department_raw);
    $country = '' === $country_raw ? '' : wp_seed_content_directory_sanitize_meta_value('_seed_directory_country', $country_raw);
    if (('' !== $department_raw && '' === $department) || ('' !== $country_raw && '' === $country)) {
        return null;
    }

    $ids = wp_seed_content_directory_normalize_collection_ids($args['ids']);
    if (null === $ids) {
        return null;
    }

    return array(
        'status' => $status,
        'department' => $department,
        'country' => $country,
        'featured' => $featured,
        'limit' => min(100, $args['limit']),
        'orderby' => $orderby,
        'order' => $order,
        'ids' => $ids,
    );
}

function wp_seed_content_directory_compare_entries($left, $right, $orderby, $order)
{
    if ('name' === $orderby) {
        $comparison = strcasecmp((string) $left->post_title, (string) $right->post_title);
    } elseif ('date' === $orderby) {
        $comparison = strcmp((string) $left->post_date, (string) $right->post_date);
    } elseif ('id' === $orderby) {
        $comparison = (int) $left->ID <=> (int) $right->ID;
    } else {
        $comparison = (int) $left->menu_order <=> (int) $right->menu_order;
        if (0 === $comparison) {
            $comparison = strcasecmp((string) $left->post_title, (string) $right->post_title);
        }
    }

    if (0 === $comparison) {
        $comparison = (int) $left->ID <=> (int) $right->ID;
    }

    return 'desc' === $order ? -$comparison : $comparison;
}

function wp_seed_content_directory_get_entries($args = array())
{
    if (!wp_seed_content_kit_is_module_active('directory')) {
        return array();
    }

    $args = wp_seed_content_directory_normalize_collection_args($args);
    if (null === $args) {
        return array();
    }

    $query = array(
        'post_type' => 'seed_directory',
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
    );
    if (!empty($args['ids'])) {
        $query['post__in'] = $args['ids'];
    }

    $posts = get_posts($query);
    $selected = array();
    foreach ($posts as $post) {
        if (!$post instanceof WP_Post || !wp_seed_content_directory_is_publicly_eligible($post->ID)) {
            continue;
        }

        $status = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_status');
        $department = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_department');
        $country = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_country');
        $featured = '1' === get_post_meta($post->ID, '_seed_directory_featured', true);
        if (('all' !== $args['status'] && $status !== $args['status'])
            || ('' !== $args['department'] && $department !== $args['department'])
            || ('' !== $args['country'] && $country !== $args['country'])
            || ('only' === $args['featured'] && !$featured)
            || ('exclude' === $args['featured'] && $featured)
        ) {
            continue;
        }
        $selected[] = $post;
    }

    usort($selected, function ($left, $right) use ($args) {
        return wp_seed_content_directory_compare_entries($left, $right, $args['orderby'], $args['order']);
    });

    $ids = array_map(function ($post) {
        return (int) $post->ID;
    }, $selected);

    return $args['limit'] > 0 ? array_slice($ids, 0, $args['limit']) : $ids;
}
