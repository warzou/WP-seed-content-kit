<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('wp_seed_content_divi_loop_dynamic_data_sources')) {
    require_once __DIR__ . '/loop-context.php';
}

function wp_seed_content_divi_testimonial_loop_sources()
{
    $sources = wp_seed_content_divi_loop_dynamic_data_sources();
    $testimonial_sources = isset($sources['seed_testimonial'])
        ? $sources['seed_testimonial']
        : array();

    return array_map(function ($definition) {
        return $definition['field_id'];
    }, $testimonial_sources);
}

function wp_seed_content_divi_add_testimonial_loop_dynamic_data($response, $server, $request)
{
    if (!is_object($request) || !is_callable(array($request, 'get_route')) || '/divi/v1/loop/query-results' !== $request->get_route() || !is_object($response) || !is_callable(array($response, 'get_data')) || !is_callable(array($response, 'set_data'))) {
        return $response;
    }

    return wp_seed_content_divi_add_loop_dynamic_data($response, $server, $request);
}
