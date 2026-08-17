<?php
/**
 * Thin Divi 5 Loop Builder adapter for the canonical public Directory collection.
 */

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_directory_loop_post_types($post_types, $route = '')
{
    if (!is_array($post_types)) {
        return $post_types;
    }

    if ('' === $route) {
        if (!defined('REST_REQUEST') || !REST_REQUEST) {
            return $post_types;
        }
        if (isset($GLOBALS['wp']->query_vars['rest_route'])) {
            $route = (string) $GLOBALS['wp']->query_vars['rest_route'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $request_path = (string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
            $route = preg_replace('#^.*?/wp-json#', '', $request_path);
        }
    }
    $loop_routes = array('/divi/v1/loop/query-types', '/divi/v1/loop/query-results');
    if (!in_array($route, $loop_routes, true)) {
        return $post_types;
    }

    if (!in_array('seed_directory', $post_types, true)) {
        $post_types[] = 'seed_directory';
    }

    return $post_types;
}
add_filter('et_builder_third_party_post_types', 'wp_seed_content_divi_directory_loop_post_types');

function wp_seed_content_divi_directory_open_loop_results_request($response, $handler, $request)
{
    if (!is_object($request) || !is_callable(array($request, 'get_route'))) {
        return $response;
    }
    if ('/divi/v1/loop/query-results' !== $request->get_route()) {
        return $response;
    }
    if (empty($GLOBALS['wp_post_types']['seed_directory'])) {
        return $response;
    }

    $GLOBALS['wp_seed_content_directory_loop_publicly_queryable'] =
        (bool) $GLOBALS['wp_post_types']['seed_directory']->publicly_queryable;
    $GLOBALS['wp_post_types']['seed_directory']->publicly_queryable = true;

    return $response;
}
add_filter('rest_request_before_callbacks', 'wp_seed_content_divi_directory_open_loop_results_request', 10, 3);

function wp_seed_content_divi_directory_close_loop_results_request($response, $handler, $request)
{
    if (!is_object($request) || !is_callable(array($request, 'get_route'))) {
        return $response;
    }
    if ('/divi/v1/loop/query-results' !== $request->get_route()) {
        return $response;
    }
    if (!isset($GLOBALS['wp_seed_content_directory_loop_publicly_queryable'])) {
        return $response;
    }
    if (!empty($GLOBALS['wp_post_types']['seed_directory'])) {
        $GLOBALS['wp_post_types']['seed_directory']->publicly_queryable =
            (bool) $GLOBALS['wp_seed_content_directory_loop_publicly_queryable'];
    }
    unset($GLOBALS['wp_seed_content_directory_loop_publicly_queryable']);

    return $response;
}
add_filter('rest_request_after_callbacks', 'wp_seed_content_divi_directory_close_loop_results_request', 10, 3);

function wp_seed_content_divi_directory_collection_virtual_fields()
{
    return array(
        'wp_seed_content_directory_status' => 'status',
        'wp_seed_content_directory_profile_type' => 'profile_type',
        'wp_seed_content_directory_profile_type_operator' => 'profile_type_operator',
        'wp_seed_content_directory_seeking_models' => 'seeking_models',
        'wp_seed_content_directory_department' => 'department',
        'wp_seed_content_directory_country' => 'country',
        'wp_seed_content_directory_featured' => 'featured',
    );
}

function wp_seed_content_divi_directory_collection_orderby_fields()
{
    return array(
        'wp_seed_content_directory_display_order' => 'menu_order',
        'wp_seed_content_directory_name' => 'name',
        'wp_seed_content_directory_status_order' => 'status',
        'wp_seed_content_directory_profile_type_order' => 'profile_type',
        'wp_seed_content_directory_post_date' => 'date',
        'wp_seed_content_directory_id' => 'id',
    );
}

function wp_seed_content_divi_directory_collection_order_options($options)
{
    $options = is_array($options) ? $options : array();
    $labels = array(
        'wp_seed_content_directory_display_order' => __('Ordre d’affichage Annuaire', 'wp-seed-content-kit'),
        'wp_seed_content_directory_name' => __('Nom Annuaire', 'wp-seed-content-kit'),
        'wp_seed_content_directory_post_date' => __('Date de publication Annuaire', 'wp-seed-content-kit'),
        'wp_seed_content_directory_id' => __('Identifiant Annuaire', 'wp-seed-content-kit'),
    );
    $existing = array();
    foreach ($options as $option) {
        if (is_array($option) && isset($option['value'])) {
            $existing[(string) $option['value']] = true;
        }
    }
    foreach ($labels as $value => $label) {
        if (!isset($existing[$value])) {
            $options[] = array('value' => $value, 'label' => $label);
        }
    }

    return $options;
}
add_filter('et_builder_loop_order_by_options_seed_directory', 'wp_seed_content_divi_directory_collection_order_options');

function wp_seed_content_divi_extract_directory_collection_meta_query($meta_query, &$options)
{
    if (!is_array($meta_query)) {
        return array();
    }
    $virtual = wp_seed_content_divi_directory_collection_virtual_fields();
    $clean = array();
    $relation = isset($meta_query['relation']) && 'OR' === strtoupper((string) $meta_query['relation']) ? 'OR' : 'AND';
    foreach ($meta_query as $key => $clause) {
        if ('relation' === $key || !is_array($clause)) {
            continue;
        }
        $meta_key = sanitize_key((string) (isset($clause['key']) ? $clause['key'] : (isset($clause['metaKey']) ? $clause['metaKey'] : '')));
        if (isset($virtual[$meta_key])) {
            $options[$virtual[$meta_key]] = (string) (isset($clause['value']) ? $clause['value'] : (isset($clause['metaValue']) ? $clause['metaValue'] : ''));
        } else {
            $clean[] = $clause;
        }
    }
    if (count($clean) > 1) {
        $clean['relation'] = $relation;
    }

    return $clean;
}

function wp_seed_content_divi_directory_collection_orderby($value)
{
    $value = sanitize_key((string) $value);
    $custom = wp_seed_content_divi_directory_collection_orderby_fields();
    if (isset($custom[$value])) {
        return $custom[$value];
    }
    $native = array('menu_order' => 'menu_order', 'title' => 'name', 'date' => 'date', 'id' => 'id');

    return isset($native[$value]) ? $native[$value] : 'menu_order';
}

function wp_seed_content_divi_extract_directory_tax_query($tax_query, &$options)
{
    if (!is_array($tax_query)) {
        return array();
    }
    $controlled = array_flip(function_exists('wp_seed_content_directory_classification_taxonomies')
        ? wp_seed_content_directory_classification_taxonomies()
        : array('status' => 'wp_seed_directory_status', 'profile_type' => 'wp_seed_directory_profile_type'));
    $clean = array();
    foreach ($tax_query as $key => $clause) {
        if ('relation' === $key || !is_array($clause)) {
            continue;
        }
        $taxonomy = isset($clause['taxonomy']) ? sanitize_key($clause['taxonomy']) : '';
        if (!isset($controlled[$taxonomy])) {
            $clean[] = $clause;
            continue;
        }
        $kind = $controlled[$taxonomy];
        $terms = isset($clause['terms']) ? (array) $clause['terms'] : array();
        $field = isset($clause['field']) ? sanitize_key($clause['field']) : 'term_id';
        $slugs = array();
        foreach ($terms as $term) {
            if ('term_id' === $field || 'id' === $field) {
                $term_object = get_term(absint($term), $taxonomy);
                $term = $term_object && !is_wp_error($term_object) ? $term_object->slug : '';
            }
            $slug = function_exists('wp_seed_content_directory_term_slug_to_registry_slug') ? wp_seed_content_directory_term_slug_to_registry_slug($term) : str_replace('-', '_', sanitize_key($term));
            $registry = function_exists('wp_seed_content_directory_get_classification_registry')
                ? wp_seed_content_directory_get_classification_registry($kind, false)
                : array_map(function ($label) { return array('label' => $label); }, 'status' === $kind ? wp_seed_content_directory_get_statuses() : wp_seed_content_directory_get_profile_types());
            if (isset($registry[$slug])) {
                $slugs[] = $slug;
            }
        }
        if ('status' === $kind) {
            $options['status'] = isset($slugs[0]) ? $slugs[0] : '__invalid__';
        } else {
            $options['profile_types'] = $slugs ? $slugs : array('__invalid__');
            $options['profile_type_operator'] = 'AND' === strtoupper(isset($clause['operator']) ? $clause['operator'] : 'IN') ? 'and' : 'or';
        }
    }
    return $clean;
}

function wp_seed_content_divi_apply_directory_collection_query($query_args, $requested_orderby = '')
{
    if (!is_array($query_args) || !wp_seed_content_divi_is_single_post_type_query($query_args, 'seed_directory')) {
        return $query_args;
    }

    $options = array(
        'status' => 'all', 'profile_type' => '', 'profile_types' => array(), 'profile_type_operator' => 'or',
        'seeking_models' => 'all', 'department' => '', 'country' => '', 'featured' => 'all',
    );
    $query_args['meta_query'] = wp_seed_content_divi_extract_directory_collection_meta_query(
        isset($query_args['meta_query']) ? $query_args['meta_query'] : array(),
        $options
    );
    if (empty($query_args['meta_query'])) {
        unset($query_args['meta_query']);
    }
    $query_args['tax_query'] = wp_seed_content_divi_extract_directory_tax_query(
        isset($query_args['tax_query']) ? $query_args['tax_query'] : array(),
        $options
    );
    if (empty($query_args['tax_query'])) {
        unset($query_args['tax_query']);
    }
    $raw_orderby = '' !== $requested_orderby ? $requested_orderby : (isset($query_args['orderby']) ? $query_args['orderby'] : '');
    $ids = wp_seed_content_directory_get_entries(array(
        'status' => $options['status'],
        'profile_type' => $options['profile_type'],
        'profile_types' => $options['profile_types'],
        'profile_type_operator' => $options['profile_type_operator'],
        'seeking_models' => $options['seeking_models'],
        'department' => $options['department'],
        'country' => $options['country'],
        'featured' => $options['featured'],
        'limit' => 0,
        'orderby' => wp_seed_content_divi_directory_collection_orderby($raw_orderby),
        'order' => isset($query_args['order']) ? strtolower((string) $query_args['order']) : 'asc',
    ));

    return wp_seed_content_divi_apply_collection_ids($query_args, $ids);
}

function wp_seed_content_divi_filter_directory_collection_loop_data($loop_data)
{
    if (is_array($loop_data) && isset($loop_data['query_args']) && is_array($loop_data['query_args'])) {
        $loop_data['query_args'] = wp_seed_content_divi_apply_directory_collection_query($loop_data['query_args']);
    }

    return $loop_data;
}
add_filter('divi_loop_data_before_execution', 'wp_seed_content_divi_filter_directory_collection_loop_data', 20, 3);

function wp_seed_content_divi_filter_directory_collection_rest_query_args($query_args, $params)
{
    $orderby = is_array($params) && isset($params['order_by']) ? (string) $params['order_by'] : '';
    return wp_seed_content_divi_apply_directory_collection_query($query_args, $orderby);
}
add_filter('divi_module_options_loop_post_type_results_query_args', 'wp_seed_content_divi_filter_directory_collection_rest_query_args', 20, 2);

function wp_seed_content_divi_directory_query_has_virtual_fields($query_args)
{
    if (!empty($query_args['tax_query']) && is_array($query_args['tax_query'])) {
        $controlled = array_flip(function_exists('wp_seed_content_directory_classification_taxonomies')
            ? wp_seed_content_directory_classification_taxonomies()
            : array('status' => 'wp_seed_directory_status', 'profile_type' => 'wp_seed_directory_profile_type'));
        foreach ($query_args['tax_query'] as $clause) {
            if (is_array($clause) && isset($clause['taxonomy']) && isset($controlled[sanitize_key($clause['taxonomy'])])) {
                return true;
            }
        }
    }
    if (!is_array($query_args) || empty($query_args['meta_query']) || !is_array($query_args['meta_query'])) {
        return false;
    }
    $virtual = wp_seed_content_divi_directory_collection_virtual_fields();
    foreach ($query_args['meta_query'] as $clause) {
        if (!is_array($clause)) {
            continue;
        }
        $meta_key = sanitize_key((string) (isset($clause['key']) ? $clause['key'] : ''));
        if (isset($virtual[$meta_key])) {
            return true;
        }
    }

    return false;
}

function wp_seed_content_divi_filter_directory_collection_wp_query($query)
{
    if (!is_object($query) || !isset($query->query_vars) || !is_array($query->query_vars)) {
        return;
    }
    if (!wp_seed_content_divi_is_single_post_type_query($query->query_vars, 'seed_directory')) {
        return;
    }
    if (!wp_seed_content_divi_directory_query_has_virtual_fields($query->query_vars)) {
        return;
    }

    $adapted = wp_seed_content_divi_apply_directory_collection_query($query->query_vars);
    foreach ($adapted as $key => $value) {
        $query->set($key, $value);
    }
    if (!isset($adapted['meta_query'])) {
        $query->set('meta_query', array());
    }
}
add_action('pre_get_posts', 'wp_seed_content_divi_filter_directory_collection_wp_query', 20);
