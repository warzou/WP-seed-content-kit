<?php
/**
 * Thin Divi 5 Loop Builder adapter for public quote collections.
 *
 * @package WPSeedContentKit
 */

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_quote_collection_virtual_fields()
{
    return array(
        'wp_seed_content_quote_featured' => 'featured',
    );
}

function wp_seed_content_divi_quote_collection_orderby_fields()
{
    return array(
        'wp_seed_content_quote_menu_order' => 'menu_order',
        'wp_seed_content_quote_author_order' => 'author',
        'wp_seed_content_quote_post_date' => 'date',
        'wp_seed_content_quote_id' => 'id',
        'wp_seed_content_quote_random' => 'random',
    );
}

function wp_seed_content_divi_quote_collection_order_options($options)
{
    $options = is_array($options) ? $options : array();
    $labels = array(
        'wp_seed_content_quote_menu_order' => __('Ordre d’affichage de la citation', 'wp-seed-content-kit'),
        'wp_seed_content_quote_author_order' => __('Auteur de la citation', 'wp-seed-content-kit'),
        'wp_seed_content_quote_post_date' => __('Date de publication de la citation', 'wp-seed-content-kit'),
        'wp_seed_content_quote_id' => __('Identifiant de la citation', 'wp-seed-content-kit'),
        'wp_seed_content_quote_random' => __('Citations aléatoires', 'wp-seed-content-kit'),
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
add_filter(
    'et_builder_loop_order_by_options_seed_quote',
    'wp_seed_content_divi_quote_collection_order_options'
);

function wp_seed_content_divi_extract_quote_collection_meta_query($meta_query, &$options)
{
    if (!is_array($meta_query)) {
        return array();
    }

    $virtual_fields = wp_seed_content_divi_quote_collection_virtual_fields();
    $clean = array();
    $relation = isset($meta_query['relation']) && 'OR' === strtoupper((string) $meta_query['relation'])
        ? 'OR'
        : 'AND';

    foreach ($meta_query as $key => $clause) {
        if ('relation' === $key || !is_array($clause)) {
            continue;
        }

        $meta_key = sanitize_key((string) (isset($clause['key'])
            ? $clause['key']
            : (isset($clause['metaKey']) ? $clause['metaKey'] : '')));
        if (isset($virtual_fields[$meta_key])) {
            $options[$virtual_fields[$meta_key]] = (string) (isset($clause['value'])
                ? $clause['value']
                : (isset($clause['metaValue']) ? $clause['metaValue'] : ''));
            continue;
        }

        $clean[] = $clause;
    }

    if (count($clean) > 1) {
        $clean['relation'] = $relation;
    }

    return $clean;
}

function wp_seed_content_divi_quote_collection_orderby($value)
{
    $value = sanitize_key((string) $value);
    $custom = wp_seed_content_divi_quote_collection_orderby_fields();
    if (isset($custom[$value])) {
        return $custom[$value];
    }

    $native = array(
        'menu_order' => 'menu_order',
        'date' => 'date',
        'id' => 'id',
        'rand' => 'random',
        'random' => 'random',
    );

    return isset($native[$value]) ? $native[$value] : 'menu_order';
}

function wp_seed_content_divi_apply_quote_collection_query(
    $query_args,
    $requested_orderby = '',
    $random_seed = ''
) {
    if (
        !is_array($query_args)
        || !wp_seed_content_divi_is_single_post_type_query($query_args, 'seed_quote')
    ) {
        return $query_args;
    }

    $options = array('featured' => 'all');
    $query_args['meta_query'] = wp_seed_content_divi_extract_quote_collection_meta_query(
        isset($query_args['meta_query']) ? $query_args['meta_query'] : array(),
        $options
    );
    if (empty($query_args['meta_query'])) {
        unset($query_args['meta_query']);
    }

    $raw_orderby = '' !== $requested_orderby
        ? $requested_orderby
        : (isset($query_args['orderby']) ? $query_args['orderby'] : '');
    $quote_ids = wp_seed_content_get_quotes(array(
        'featured' => $options['featured'],
        'limit' => 0,
        'orderby' => wp_seed_content_divi_quote_collection_orderby($raw_orderby),
        'order' => isset($query_args['order']) ? $query_args['order'] : 'ASC',
        'random_seed' => $random_seed,
    ));

    return wp_seed_content_divi_apply_collection_ids($query_args, $quote_ids);
}

function wp_seed_content_divi_filter_quote_collection_loop_data($loop_data)
{
    if (!is_array($loop_data) || empty($loop_data['query_args']) || !is_array($loop_data['query_args'])) {
        return $loop_data;
    }

    $loop_data['query_args'] = wp_seed_content_divi_apply_quote_collection_query(
        $loop_data['query_args']
    );

    return $loop_data;
}
add_filter(
    'divi_loop_data_before_execution',
    'wp_seed_content_divi_filter_quote_collection_loop_data',
    20,
    3
);

function wp_seed_content_divi_filter_quote_collection_rest_query_args($query_args, $params)
{
    $requested_orderby = is_array($params) && isset($params['order_by'])
        ? (string) $params['order_by']
        : '';

    return wp_seed_content_divi_apply_quote_collection_query(
        $query_args,
        $requested_orderby,
        'divi-loop-builder-preview'
    );
}
add_filter(
    'divi_module_options_loop_post_type_results_query_args',
    'wp_seed_content_divi_filter_quote_collection_rest_query_args',
    20,
    2
);
