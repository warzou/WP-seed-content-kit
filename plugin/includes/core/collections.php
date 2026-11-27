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

    $selection_mode = isset($args['selection_mode']) && is_string($args['selection_mode'])
        ? strtolower($args['selection_mode'])
        : '';
    if (!in_array($selection_mode, array('all', 'featured', 'random', 'featured_or_random'), true)) {
        $selection_mode = 'only' === $featured ? 'featured' : 'all';
    }

    $limit = isset($args['limit']) && is_int($args['limit']) && $args['limit'] > 0
        ? $args['limit']
        : 0;

    $orderby = isset($args['orderby']) && is_string($args['orderby'])
        ? strtolower($args['orderby'])
        : 'display_order';
    if (!in_array($orderby, array('display_order', 'date', 'testimonial_date', 'id', 'random'), true)) {
        $orderby = 'display_order';
    }
    if ('random' === $orderby && !isset($args['selection_mode'])) {
        $selection_mode = 'random';
    }

    $order = isset($args['order']) && is_string($args['order'])
        ? strtolower($args['order'])
        : 'asc';
    if (!in_array($order, array('asc', 'desc'), true)) {
        $order = 'asc';
    }

    $context = isset($args['context']) && is_scalar($args['context'])
        ? trim((string) $args['context'])
        : '';
    $random_seed = isset($args['random_seed']) && is_scalar($args['random_seed'])
        ? trim((string) $args['random_seed'])
        : '';

    return array(
        'ids' => _wp_seed_content_collections_normalize_ids($ids_value),
        'manual_selection' => $manual_selection,
        'invalid_manual_selection' => $invalid_manual_selection,
        'featured' => $featured,
        'selection_mode' => $selection_mode,
        'context' => $context,
        'limit' => $limit,
        'orderby' => $orderby,
        'order' => $order,
        'random_seed' => $random_seed,
    );
}

function _wp_seed_content_collections_apply_limit($ids, $limit)
{
    if ($limit <= 0) {
        return $ids;
    }

    return array_slice($ids, 0, $limit);
}

function wp_seed_content_testimonial_publication_consent_meta_key()
{
    return '_seed_testimonial_publication_consent';
}

function wp_seed_content_testimonial_featured_meta_key()
{
    return '_seed_testimonial_featured';
}

function wp_seed_content_testimonial_is_publicly_visible($post_id)
{
    $post_id = absint($post_id);
    $post = $post_id ? get_post($post_id) : null;
    if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type || 'publish' !== $post->post_status || '' !== (string) $post->post_password) {
        return false;
    }

    return '1' === (string) wp_seed_content_get_meta($post_id, wp_seed_content_testimonial_publication_consent_meta_key());
}

function wp_seed_content_testimonial_is_featured($post_id)
{
    return '1' === (string) wp_seed_content_get_meta(absint($post_id), wp_seed_content_testimonial_featured_meta_key());
}

function _wp_seed_content_collections_randomize_ids($ids, $seed = '')
{
    $ids = array_values($ids);
    if (count($ids) < 2) {
        return $ids;
    }
    if ('' === $seed) {
        shuffle($ids);
        return $ids;
    }
    usort($ids, function ($left, $right) use ($seed) {
        $comparison = strcmp(hash('sha256', $seed . '|' . (int) $left), hash('sha256', $seed . '|' . (int) $right));
        return 0 !== $comparison ? $comparison : (int) $left <=> (int) $right;
    });
    return $ids;
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
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        )
    );

    $published = array();
    foreach ($posts as $post) {
        if (
            $post instanceof WP_Post
            && 'seed_testimonial' === $post->post_type
            && 'publish' === $post->post_status
            && wp_seed_content_testimonial_is_publicly_visible($post->ID)
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

    if ('display_order' === $orderby || 'random' === $orderby) {
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
                    && 'publish' === $post->post_status
                    && wp_seed_content_testimonial_is_publicly_visible($post->ID);
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

    if ('featured' === $args['selection_mode'] || 'exclude' === $args['featured']) {
        $posts = array_values(
            array_filter(
                $posts,
                function ($post) use ($args) {
                    $featured = wp_seed_content_testimonial_is_featured($post->ID);

                    return 'featured' === $args['selection_mode'] ? $featured : !$featured;
                }
            )
        );
    }

    if ('' !== $args['context']) {
        $posts = array_values(
            array_filter(
                $posts,
                function ($post) use ($args) {
                    return wp_seed_content_get_testimonial_builder_meta(
                        $post->ID,
                        'seed_testimonial_context'
                    ) === $args['context'];
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

    if ('random' === $args['selection_mode']) {
        $ids = _wp_seed_content_collections_randomize_ids($ids, $args['random_seed']);
    } elseif ('featured_or_random' === $args['selection_mode']) {
        $featured_ids = array();
        $other_ids = array();
        foreach ($ids as $id) {
            if (wp_seed_content_testimonial_is_featured($id)) {
                $featured_ids[] = $id;
            } else {
                $other_ids[] = $id;
            }
        }
        $ids = array_merge($featured_ids, _wp_seed_content_collections_randomize_ids($other_ids, $args['random_seed']));
    }

    return _wp_seed_content_collections_apply_limit($ids, $args['limit']);
}


function _wp_seed_content_collections_normalize_quote_args($args)
{
    $args = is_array($args) ? $args : array();

    $featured = isset($args['featured']) ? $args['featured'] : 'all';
    if (is_bool($featured)) {
        $featured = $featured ? 'only' : 'all';
    } else {
        $featured = strtolower((string) $featured);
        if ('true' === $featured) {
            $featured = 'only';
        } elseif ('false' === $featured) {
            $featured = 'all';
        }
    }
    if (!in_array($featured, array('all', 'only', 'exclude'), true)) {
        $featured = 'all';
    }

    $limit = isset($args['limit']) && is_int($args['limit']) && $args['limit'] > 0
        ? $args['limit']
        : 0;
    $orderby = isset($args['orderby']) && is_string($args['orderby'])
        ? strtolower($args['orderby'])
        : 'menu_order';
    if (!in_array($orderby, array('random', 'author', 'date', 'menu_order', 'id'), true)) {
        $orderby = 'menu_order';
    }
    $order = isset($args['order']) && is_string($args['order'])
        ? strtolower($args['order'])
        : 'asc';
    if (!in_array($order, array('asc', 'desc'), true)) {
        $order = 'asc';
    }
    $random_seed = isset($args['random_seed']) && is_scalar($args['random_seed'])
        ? trim((string) $args['random_seed'])
        : '';

    return array(
        'featured' => $featured,
        'limit' => $limit,
        'orderby' => $orderby,
        'order' => $order,
        'random_seed' => $random_seed,
    );
}

function _wp_seed_content_collections_compare_quotes($left, $right, $orderby, $order)
{
    $left_id = (int) $left->ID;
    $right_id = (int) $right->ID;

    if ('author' === $orderby) {
        $comparison = strcmp(
            (string) wp_seed_content_get_quote_builder_meta($left_id, 'seed_quote_author'),
            (string) wp_seed_content_get_quote_builder_meta($right_id, 'seed_quote_author')
        );
    } elseif ('date' === $orderby) {
        $comparison = strcmp((string) $left->post_date, (string) $right->post_date);
    } elseif ('menu_order' === $orderby) {
        $comparison = (int) $left->menu_order <=> (int) $right->menu_order;
    } else {
        $comparison = $left_id <=> $right_id;
    }

    if (0 !== $comparison) {
        return 'desc' === $order ? -$comparison : $comparison;
    }

    return $left_id <=> $right_id;
}

function wp_seed_content_get_quotes($args = array())
{
    if (!wp_seed_content_kit_is_module_active('quotes')) {
        return array();
    }

    $args = _wp_seed_content_collections_normalize_quote_args($args);
    $posts = get_posts(array(
        'post_type' => 'seed_quote',
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
    ));

    $posts = array_values(array_filter($posts, function ($post) use ($args) {
        if (
            !$post instanceof WP_Post
            || 'seed_quote' !== $post->post_type
            || 'publish' !== $post->post_status
            || '' !== (string) $post->post_password
        ) {
            return false;
        }

        if ('all' === $args['featured']) {
            return true;
        }

        $featured = wp_seed_content_quote_is_featured($post->ID);
        return 'only' === $args['featured'] ? $featured : !$featured;
    }));

    usort($posts, function ($left, $right) use ($args) {
        return _wp_seed_content_collections_compare_quotes(
            $left,
            $right,
            'random' === $args['orderby'] ? 'id' : $args['orderby'],
            $args['order']
        );
    });

    $ids = array_map(function ($post) {
        return (int) $post->ID;
    }, $posts);

    if ('random' === $args['orderby']) {
        $ids = _wp_seed_content_collections_randomize_ids($ids, $args['random_seed']);
    }

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
