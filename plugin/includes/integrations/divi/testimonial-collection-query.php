<?php
/**
 * Thin Divi 5 Loop Builder adapter for public testimonial collections.
 *
 * @package WPSeedContentKit
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stable virtual fields accepted by Divi's native Meta Query controls.
 *
 * These names are never persisted as post meta. They are removed before
 * WordPress executes the query.
 *
 * @return array
 */
function wp_seed_content_divi_testimonial_collection_virtual_fields()
{
    return array(
        'wp_seed_content_testimonial_featured' => 'featured',
        'wp_seed_content_testimonial_selection_mode' => 'selection_mode',
        'wp_seed_content_testimonial_context' => 'context',
    );
}

/**
 * Stable Divi order choices mapped to the canonical collection contract.
 *
 * @return array
 */
function wp_seed_content_divi_testimonial_collection_orderby_fields()
{
    return array(
        'wp_seed_content_testimonial_display_order' => 'display_order',
        'wp_seed_content_testimonial_date' => 'testimonial_date',
        'wp_seed_content_testimonial_post_date' => 'date',
        'wp_seed_content_testimonial_id' => 'id',
        'wp_seed_content_testimonial_random' => 'random',
    );
}

/**
 * Add canonical testimonial ordering to Divi's Loop Builder.
 *
 * @param array $options Existing Divi options.
 *
 * @return array
 */
function wp_seed_content_divi_testimonial_collection_order_options($options)
{
    $options = is_array($options) ? $options : array();
    $labels = array(
        'wp_seed_content_testimonial_display_order' => __('Ordre d’affichage du témoignage', 'wp-seed-content-kit'),
        'wp_seed_content_testimonial_date' => __('Date du témoignage', 'wp-seed-content-kit'),
        'wp_seed_content_testimonial_post_date' => __('Date de publication du témoignage', 'wp-seed-content-kit'),
        'wp_seed_content_testimonial_id' => __('Identifiant du témoignage', 'wp-seed-content-kit'),
        'wp_seed_content_testimonial_random' => __('Témoignages aléatoires', 'wp-seed-content-kit'),
    );

    $existing = array();
    foreach ($options as $option) {
        if (is_array($option) && isset($option['value'])) {
            $existing[(string) $option['value']] = true;
        }
    }

    foreach ($labels as $value => $label) {
        if (isset($existing[$value])) {
            continue;
        }

        $options[] = array(
            'value' => $value,
            'label' => $label,
        );
    }

    return $options;
}
add_filter(
    'et_builder_loop_order_by_options_seed_testimonial',
    'wp_seed_content_divi_testimonial_collection_order_options'
);

/**
 * Remove virtual clauses while extracting canonical collection values.
 *
 * @param array $meta_query Divi/WordPress meta query.
 * @param array $options    Extracted collection options.
 *
 * @return array
 */
function wp_seed_content_divi_extract_testimonial_collection_meta_query($meta_query, &$options)
{
    if (!is_array($meta_query)) {
        return array();
    }

    $virtual_fields = wp_seed_content_divi_testimonial_collection_virtual_fields();
    $clean = array();
    $relation = isset($meta_query['relation'])
        && 'OR' === strtoupper((string) $meta_query['relation'])
        ? 'OR'
        : 'AND';

    foreach ($meta_query as $key => $clause) {
        if ('relation' === $key || !is_array($clause)) {
            continue;
        }

        $meta_key = sanitize_key((string) ($clause['key'] ?? $clause['metaKey'] ?? ''));

        if (isset($virtual_fields[$meta_key])) {
            $option = $virtual_fields[$meta_key];
            $options[$option] = (string) ($clause['value'] ?? $clause['metaValue'] ?? '');
            continue;
        }

        $clean[] = $clause;
    }

    if (count($clean) > 1) {
        $clean['relation'] = $relation;
    }

    return $clean;
}

/**
 * Whether a query targets only the testimonial post type.
 *
 * @param array $query_args WordPress query arguments.
 *
 * @return bool
 */
function wp_seed_content_divi_is_testimonial_collection_query($query_args)
{
    $post_types = isset($query_args['post_type']) ? (array) $query_args['post_type'] : array();
    $post_types = array_values(array_unique(array_map('sanitize_key', $post_types)));

    return array('seed_testimonial') === $post_types;
}

/**
 * Resolve Divi's order choice to the canonical collection order.
 *
 * @param string $value Divi order-by value.
 *
 * @return string
 */
function wp_seed_content_divi_testimonial_collection_orderby($value)
{
    $value = sanitize_key((string) $value);
    $custom = wp_seed_content_divi_testimonial_collection_orderby_fields();

    if (isset($custom[$value])) {
        return $custom[$value];
    }

    $native = array(
        'menu_order' => 'display_order',
        'date' => 'date',
        'id' => 'id',
        'rand' => 'random',
        'random' => 'random',
    );

    return isset($native[$value]) ? $native[$value] : 'display_order';
}

/**
 * Apply the canonical public collection to one bounded Divi query.
 *
 * Divi retains ownership of posts_per_page and paged. The collection API
 * supplies only the eligible IDs and their deterministic order.
 *
 * @param array  $query_args       Divi-generated WordPress query arguments.
 * @param string $requested_orderby Raw REST order choice, when available.
 *
 * @return array
 */
function wp_seed_content_divi_apply_testimonial_collection_query(
    $query_args,
    $requested_orderby = '',
    $random_seed = ''
) {
    if (
        !is_array($query_args)
        || !wp_seed_content_divi_is_testimonial_collection_query($query_args)
    ) {
        return $query_args;
    }

    $options = array(
        'featured' => 'all',
        'context' => '',
        'selection_mode' => 'all',
    );

    $query_args['meta_query'] = wp_seed_content_divi_extract_testimonial_collection_meta_query(
        $query_args['meta_query'] ?? array(),
        $options
    );

    if (array() === $query_args['meta_query']) {
        unset($query_args['meta_query']);
    }

    $raw_orderby = '' !== $requested_orderby
        ? $requested_orderby
        : ($query_args['orderby'] ?? '');

    $resolved_orderby = wp_seed_content_divi_testimonial_collection_orderby($raw_orderby);
    if ('random' === $resolved_orderby && 'all' === strtolower((string) $options['selection_mode'])) {
        $options['selection_mode'] = 'random';
    }

    $collection_args = array(
        'featured' => $options['featured'],
        'selection_mode' => $options['selection_mode'],
        'context' => $options['context'],
        'limit' => 0,
        'orderby' => $resolved_orderby,
        'order' => $query_args['order'] ?? 'ASC',
        'random_seed' => $random_seed,
    );

    $testimonial_ids = function_exists('wp_seed_content_get_testimonials')
        ? wp_seed_content_get_testimonials($collection_args)
        : array();

    if (!is_array($testimonial_ids)) {
        $testimonial_ids = array();
    }

    $testimonial_ids = array_values(
        array_filter(
            array_map('absint', $testimonial_ids),
            function ($testimonial_id) {
                return $testimonial_id > 0;
            }
        )
    );

    if (!empty($query_args['post__in'])) {
        $allowed = array_map('absint', (array) $query_args['post__in']);
        $testimonial_ids = array_values(
            array_filter(
                $testimonial_ids,
                function ($testimonial_id) use ($allowed) {
                    return in_array($testimonial_id, $allowed, true);
                }
            )
        );
    }

    if (!empty($query_args['post__not_in'])) {
        $excluded = array_map('absint', (array) $query_args['post__not_in']);
        $testimonial_ids = array_values(array_diff($testimonial_ids, $excluded));
    }

    $query_args['post__in'] = array() === $testimonial_ids
        ? array(0)
        : $testimonial_ids;
    $query_args['orderby'] = 'post__in';
    $query_args['order'] = 'ASC';

    return $query_args;
}

/**
 * Adapt the frontend Loop Builder query before Divi checks its query registry.
 *
 * Divi 5.9.0 exposes this public filter before cache lookup and query
 * execution. Using the later filter would allow a cached unadapted query to win.
 *
 * @param array $loop_data Divi loop data.
 *
 * @return array
 */
function wp_seed_content_divi_filter_testimonial_collection_loop_data($loop_data)
{
    if (
        !is_array($loop_data)
        || empty($loop_data['query_args'])
        || !is_array($loop_data['query_args'])
    ) {
        return $loop_data;
    }

    $loop_data['query_args'] = wp_seed_content_divi_apply_testimonial_collection_query(
        $loop_data['query_args']
    );

    return $loop_data;
}
add_filter(
    'divi_loop_data_before_execution',
    'wp_seed_content_divi_filter_testimonial_collection_loop_data',
    20,
    3
);

/**
 * Keep Visual Builder REST queries aligned with frontend loops.
 *
 * @param array $query_args Divi REST query arguments.
 * @param array $params     Sanitized REST parameters.
 *
 * @return array
 */
function wp_seed_content_divi_filter_testimonial_collection_rest_query_args(
    $query_args,
    $params
) {
    $requested_orderby = is_array($params)
        ? (string) ($params['order_by'] ?? '')
        : '';

    return wp_seed_content_divi_apply_testimonial_collection_query(
        $query_args,
        $requested_orderby,
        'divi-loop-builder-preview'
    );
}
add_filter(
    'divi_module_options_loop_post_type_results_query_args',
    'wp_seed_content_divi_filter_testimonial_collection_rest_query_args',
    20,
    2
);
