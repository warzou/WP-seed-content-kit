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

function wp_seed_content_directory_normalize_profile_type_filter($value)
{
    if (is_string($value)) {
        $value = '' === trim($value) ? array() : explode(',', $value);
    }
    if (!is_array($value)) {
        return null;
    }

    $registered = wp_seed_content_directory_get_profile_types();
    $normalized = array();
    foreach ($value as $profile_type) {
        if (!is_scalar($profile_type)) {
            return null;
        }
        $profile_type = sanitize_key(trim((string) $profile_type));
        if ('' === $profile_type || !isset($registered[$profile_type])) {
            return null;
        }
        $normalized[$profile_type] = $profile_type;
    }

    return array_values($normalized);
}

function wp_seed_content_directory_normalize_seeking_models_filter($value)
{
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    if (is_int($value)) {
        return 1 === $value ? '1' : (0 === $value ? '0' : null);
    }
    if (!is_scalar($value)) {
        return null;
    }

    $value = strtolower(trim((string) $value));
    if ('' === $value || 'all' === $value) {
        return 'all';
    }
    if (in_array($value, array('1', 'true', 'only'), true)) {
        return '1';
    }
    if (in_array($value, array('0', 'false', 'exclude'), true)) {
        return '0';
    }

    return null;
}

function wp_seed_content_directory_normalize_collection_args($args)
{
    if (!is_array($args)) {
        return null;
    }

    $defaults = array(
        'status' => 'all',
        'profile_type' => '',
        'profile_types' => array(),
        'profile_type_operator' => 'or',
        'seeking_models' => 'all',
        'department' => '',
        'country' => '',
        'featured' => 'all',
        'limit' => 0,
        'offset' => 0,
        'orderby' => 'display_order',
        'order' => 'asc',
        'ids' => array(),
        'exclude_ids' => array(),
    );
    $args = array_merge($defaults, $args);

    $status = is_string($args['status']) ? strtolower($args['status']) : '';
    $featured = is_string($args['featured']) ? strtolower($args['featured']) : '';
    $orderby = is_string($args['orderby']) ? strtolower($args['orderby']) : '';
    $order = is_string($args['order']) ? strtolower($args['order']) : '';
    $profile_type_operator = is_string($args['profile_type_operator'])
        ? strtolower($args['profile_type_operator'])
        : '';
    if (!in_array($status, array('all', 'practicing', 'seeking_models'), true)
        || !in_array($featured, array('all', 'only', 'exclude'), true)
        || !in_array($orderby, array('display_order', 'name', 'date', 'id'), true)
        || !in_array($order, array('asc', 'desc'), true)
        || !in_array($profile_type_operator, array('or', 'and'), true)
        || !is_int($args['limit'])
        || $args['limit'] < 0
        || !is_int($args['offset'])
        || $args['offset'] < 0
    ) {
        return null;
    }

    $profile_types = wp_seed_content_directory_normalize_profile_type_filter(
        $args['profile_types']
    );
    $profile_type = wp_seed_content_directory_normalize_profile_type_filter(
        $args['profile_type']
    );
    $seeking_models = wp_seed_content_directory_normalize_seeking_models_filter(
        $args['seeking_models']
    );
    if (null === $profile_types || null === $profile_type || null === $seeking_models) {
        return null;
    }
    $profile_types = array_values(array_unique(array_merge($profile_types, $profile_type)));

    $department_raw = is_scalar($args['department']) ? trim((string) $args['department']) : '';
    $country_raw = is_scalar($args['country']) ? trim((string) $args['country']) : '';
    $department = '' === $department_raw ? '' : wp_seed_content_directory_sanitize_meta_value('_seed_directory_department', $department_raw);
    $country = '' === $country_raw ? '' : wp_seed_content_directory_sanitize_meta_value('_seed_directory_country', $country_raw);
    if (('' !== $department_raw && '' === $department) || ('' !== $country_raw && '' === $country)) {
        return null;
    }

    $ids = wp_seed_content_directory_normalize_collection_ids($args['ids']);
    $exclude_ids = wp_seed_content_directory_normalize_collection_ids($args['exclude_ids']);
    if (null === $ids || null === $exclude_ids) {
        return null;
    }

    return array(
        'status' => $status,
        'profile_types' => $profile_types,
        'profile_type_operator' => $profile_type_operator,
        'seeking_models' => $seeking_models,
        'department' => $department,
        'country' => $country,
        'featured' => $featured,
        'limit' => min(100, $args['limit']),
        'offset' => min(10000, $args['offset']),
        'orderby' => $orderby,
        'order' => $order,
        'ids' => $ids,
        'exclude_ids' => $exclude_ids,
    );
}

function wp_seed_content_directory_normalize_comparison_text($value)
{
    $value = preg_replace('/\s+/u', ' ', trim(strip_tags((string) $value)));
    if (function_exists('remove_accents')) {
        $value = remove_accents($value);
    }

    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function wp_seed_content_directory_compare_entries($left, $right, $orderby, $order)
{
    if ('name' === $orderby) {
        $comparison = strcmp(
            wp_seed_content_directory_normalize_comparison_text($left->post_title),
            wp_seed_content_directory_normalize_comparison_text($right->post_title)
        );
    } elseif ('date' === $orderby) {
        $comparison = strcmp((string) $left->post_date, (string) $right->post_date);
    } elseif ('id' === $orderby) {
        $comparison = (int) $left->ID <=> (int) $right->ID;
    } else {
        $comparison = (int) $left->menu_order <=> (int) $right->menu_order;
        if (0 === $comparison) {
            $comparison = strcmp(
                wp_seed_content_directory_normalize_comparison_text($left->post_title),
                wp_seed_content_directory_normalize_comparison_text($right->post_title)
            );
        }
    }

    if (0 === $comparison) {
        $comparison = (int) $left->ID <=> (int) $right->ID;
    }

    return 'desc' === $order ? -$comparison : $comparison;
}

function wp_seed_content_directory_entry_matches_profile_types($entry_types, $required_types, $operator)
{
    if (empty($required_types)) {
        return true;
    }
    $entry_types = wp_seed_content_directory_normalize_profile_types($entry_types);
    $matches = array_intersect($required_types, $entry_types);

    return 'and' === $operator
        ? count($matches) === count($required_types)
        : !empty($matches);
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
    } elseif (!empty($args['exclude_ids'])) {
        $query['post__not_in'] = $args['exclude_ids'];
    }

    $excluded_ids = array_fill_keys($args['exclude_ids'], true);
    $posts = get_posts($query);
    $selected = array();
    foreach ($posts as $post) {
        if (!$post instanceof WP_Post || !wp_seed_content_directory_is_publicly_eligible($post->ID)) {
            continue;
        }
        if (isset($excluded_ids[(int) $post->ID])) {
            continue;
        }

        $status = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_status');
        $profile_types = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_profile_types');
        $seeking_models = '1' === get_post_meta($post->ID, '_seed_directory_seeking_models', true);
        $department = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_department');
        $country = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_country');
        $featured = '1' === get_post_meta($post->ID, '_seed_directory_featured', true);
        if (('all' !== $args['status'] && $status !== $args['status'])
            || !wp_seed_content_directory_entry_matches_profile_types(
                $profile_types,
                $args['profile_types'],
                $args['profile_type_operator']
            )
            || ('1' === $args['seeking_models'] && !$seeking_models)
            || ('0' === $args['seeking_models'] && $seeking_models)
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
    if ($args['offset'] > 0) {
        $ids = array_slice($ids, $args['offset']);
    }

    return $args['limit'] > 0 ? array_slice($ids, 0, $args['limit']) : $ids;
}

function wp_seed_content_directory_get_predefined_collections()
{
    return array(
        'all' => array(
            'label' => __('Tous les profils', 'wp-seed-content-kit'),
            'args' => array(),
        ),
        'praticiens' => array(
            'label' => __('Praticiens', 'wp-seed-content-kit'),
            'args' => array('profile_type' => 'praticien'),
        ),
        'intervenants' => array(
            'label' => __('Intervenants', 'wp-seed-content-kit'),
            'args' => array('profile_type' => 'intervenant'),
        ),
        'seeking_models' => array(
            'label' => __('Recherche de modèles', 'wp-seed-content-kit'),
            'args' => array('seeking_models' => '1'),
        ),
        'praticiens_seeking_models' => array(
            'label' => __('Praticiens recherchant des modèles', 'wp-seed-content-kit'),
            'args' => array('profile_type' => 'praticien', 'seeking_models' => '1'),
        ),
        'intervenants_seeking_models' => array(
            'label' => __('Intervenants recherchant des modèles', 'wp-seed-content-kit'),
            'args' => array('profile_type' => 'intervenant', 'seeking_models' => '1'),
        ),
    );
}
