<?php
/** Explicit, single-level Divi Schedule queries. No parent loop inference. */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/formation-calendar-filters.php';

function wp_seed_content_divi_formation_fields()
{
    $fields = array();
    foreach (wp_seed_content_get_dynamic_data_fields() as $id => $definition) {
        if (0 === strpos($id, 'formation.schedule.')) {
            $fields['wpsck_' . str_replace('.', '_', $id)] = $definition;
        }
    }
    return $fields;
}

function wp_seed_content_divi_formation_sources($sources)
{
    if (!wp_seed_content_kit_is_module_available_for_discovery('formation')) return $sources;
    foreach (wp_seed_content_divi_formation_fields() as $name => $field) {
        $sources['seed_schedule'][$name] = array('field_id' => $field['id'], 'type' => $field['type']);
    }
    return $sources;
}
add_filter('wp_seed_content_divi_loop_dynamic_data_sources', 'wp_seed_content_divi_formation_sources');

function wp_seed_content_divi_load_formation_fields()
{
    static $loaded = array();
    if (!class_exists('ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionBase')
        || !interface_exists('ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionInterface')) return;
    require_once __DIR__ . '/class-dynamic-content-formation-field.php';
    foreach (wp_seed_content_divi_formation_fields() as $name => $field) {
        if (isset($loaded[$name])) continue;
        $loaded[$name] = new WP_Seed_Content_Divi_Dynamic_Content_Formation_Field($name, $field);
        $loaded[$name]->load();
    }
}
add_action('init', 'wp_seed_content_divi_load_formation_fields', 10);

function wp_seed_content_divi_formation_loop_route()
{
    if (!defined('REST_REQUEST') || !REST_REQUEST) return '';
    if (isset($GLOBALS['wp']->query_vars['rest_route'])) return (string) $GLOBALS['wp']->query_vars['rest_route'];
    return isset($_SERVER['REQUEST_URI']) ? preg_replace('#^.*?/wp-json#', '', (string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH)) : '';
}

function wp_seed_content_divi_formation_loop_post_types($types, $route = '')
{
    if (!is_array($types) || !wp_seed_content_kit_is_module_available_for_discovery('formation')) return $types;
    $route = '' === $route ? wp_seed_content_divi_formation_loop_route() : $route;
    if (in_array($route, array('/divi/v1/loop/query-types', '/divi/v1/loop/query-results'), true) && !in_array('seed_schedule', $types, true)) $types[] = 'seed_schedule';
    return $types;
}
add_filter('et_builder_third_party_post_types', 'wp_seed_content_divi_formation_loop_post_types');

function wp_seed_content_divi_formation_open_request($response, $handler, $request)
{
    if (!is_object($request) || !is_callable(array($request, 'get_route'))
        || !wp_seed_content_kit_is_module_available_for_discovery('formation') || empty($GLOBALS['wp_post_types']['seed_schedule'])) return $response;
    $route = $request->get_route();
    if ('/divi/v1/loop/query-types' === $route) {
        // Divi enumerates show_ui CPTs before applying its allowlist.
        $property = 'show_ui';
    } elseif ('/divi/v1/loop/query-results' === $route && is_callable(array($request, 'get_param')) && 'seed_schedule' === $request->get_param('post_type')) {
        $property = 'publicly_queryable';
    } else {
        return $response;
    }
    $GLOBALS['wpsck_formation_queryable_stack'][spl_object_hash($request)][] = array($property => $GLOBALS['wp_post_types']['seed_schedule']->$property);
    $GLOBALS['wp_post_types']['seed_schedule']->$property = true;
    return $response;
}
add_filter('rest_request_before_callbacks', 'wp_seed_content_divi_formation_open_request', 10, 3);

function wp_seed_content_divi_formation_close_request($response, $handler, $request)
{
    if (is_object($request) && is_callable(array($request, 'get_route'))
        && !empty($GLOBALS['wpsck_formation_queryable_stack'][spl_object_hash($request)]) && !empty($GLOBALS['wp_post_types']['seed_schedule'])) {
        foreach (array_pop($GLOBALS['wpsck_formation_queryable_stack'][spl_object_hash($request)]) as $property => $value) {
            $GLOBALS['wp_post_types']['seed_schedule']->$property = $value;
        }
        if (!$GLOBALS['wpsck_formation_queryable_stack'][spl_object_hash($request)]) unset($GLOBALS['wpsck_formation_queryable_stack'][spl_object_hash($request)]);
    }
    return $response;
}
add_filter('rest_request_after_callbacks', 'wp_seed_content_divi_formation_close_request', 10, 3);

function wp_seed_content_divi_formation_order_options($options)
{
    if (!wp_seed_content_kit_is_module_available_for_discovery('formation')) return $options;
    $options = is_array($options) ? $options : array();
    $existing = array_column($options, 'value');
    foreach (array('wpsck_schedule_date' => __('Date de programmation', 'wp-seed-content-kit'), 'wpsck_schedule_order' => __('Ordre de programmation', 'wp-seed-content-kit')) as $value => $label) {
        if (!in_array($value, $existing, true)) $options[] = array('value' => $value, 'label' => $label);
    }
    return $options;
}
add_filter('et_builder_loop_order_by_options_seed_schedule', 'wp_seed_content_divi_formation_order_options');

function wp_seed_content_divi_formation_query($query, $params = array())
{
    if (!wp_seed_content_divi_is_single_post_type_query($query, 'seed_schedule')) return $query;
    if (isset($params['order_by']) && in_array($params['order_by'], array('wpsck_schedule_date', 'wpsck_schedule_order'), true)) $query['orderby'] = $params['order_by'];
    if (!wp_seed_content_kit_is_module_active('formation') || !function_exists('wp_seed_content_formation_get_schedules')) {
        $query['post__in'] = array(0);
        return $query;
    }
    if (isset($params['wpsck_calendar_filters'])) {
        $value = is_string($params['wpsck_calendar_filters']) ? json_decode($params['wpsck_calendar_filters'], true) : $params['wpsck_calendar_filters'];
        if (!is_array($value)) { $query['post__in'] = array(0); return $query; }
        $query = wp_seed_content_divi_calendar_filter_query($query, $value);
    }
    $args = array('include_cancelled' => true, 'order' => isset($query['order']) ? $query['order'] : 'ASC');
    $args['orderby'] = isset($query['orderby']) && in_array($query['orderby'], array('wpsck_schedule_order', 'menu_order'), true) ? 'display_order' : 'start_date';
    $map = array('_seed_formation_program_id' => 'program_id', '_seed_formation_calendar_year' => 'year', '_seed_formation_section_id' => 'section_id', '_seed_formation_theme_id' => 'theme_id', '_seed_formation_status' => 'status');
    $meta = isset($query['meta_query']) && is_array($query['meta_query']) ? $query['meta_query'] : array();
    // Divi joins its flat Meta fields with OR; distinct calendar dimensions are conjunctive.
    // Do not reinterpret nested, repeated, foreign or non-equality user queries.
    if (isset($meta['relation']) && 'OR' === strtoupper($meta['relation'])) {
        $dimensions = array();
        $conjunctive = true;
        foreach ($meta as $key => $clause) {
            if ('relation' === $key) continue;
            if (!is_array($clause) || empty($clause['key']) || !isset($map[$clause['key']])
                || !isset($clause['value']) || !is_scalar($clause['value'])
                || (isset($clause['compare']) && '=' !== $clause['compare'])
                || isset($dimensions[$clause['key']])) {
                $conjunctive = false;
                break;
            }
            $dimensions[$clause['key']] = true;
        }
        if ($conjunctive && count($dimensions) > 1) {
            $meta['relation'] = 'AND';
            $query['meta_query'] = $meta;
        }
    }
    // Only narrow the canonical query from conjunctive equality clauses. Keep all SQL clauses.
    if (!isset($meta['relation']) || 'AND' === strtoupper($meta['relation'])) foreach ($meta as $clause) {
        if (!is_array($clause) || empty($clause['key']) || !isset($map[$clause['key']]) || !isset($clause['value']) || !is_scalar($clause['value'])) continue;
        if (isset($clause['compare']) && '=' !== $clause['compare']) continue;
        $args[$map[$clause['key']]] = $clause['value'];
    }
    $items = wp_seed_content_formation_get_schedules($args);
    $query = wp_seed_content_divi_apply_collection_ids($query, array_column($items, 'id'));
    $query['post_status'] = 'publish';
    return $query;
}
add_filter('divi_module_options_loop_post_type_results_query_args', 'wp_seed_content_divi_formation_query', 20, 2);

function wp_seed_content_divi_formation_frontend_query($data, $attrs = array())
{
    $loop = isset($attrs['module']['advanced']['loop']['desktop']['value']) ? $attrs['module']['advanced']['loop']['desktop']['value'] : array();
    $params = array('order_by' => isset($loop['orderBy']) ? $loop['orderBy'] : '');
    if (isset($attrs['wpsckCalendarFilters']['innerContent']['desktop']['value'])) $params['wpsck_calendar_filters'] = $attrs['wpsckCalendarFilters']['innerContent']['desktop']['value'];
    if (isset($data['query_args']) && is_array($data['query_args'])) $data['query_args'] = wp_seed_content_divi_formation_query($data['query_args'], $params);
    return $data;
}
add_filter('divi_loop_data_after_execution', 'wp_seed_content_divi_formation_frontend_query', 20, 2);
