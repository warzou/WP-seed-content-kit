<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_testimonial_loop_sources()
{
    return array(
        'wpsck_testimonial_visual' => 'testimonial.photo',
        'wpsck_testimonial_title' => 'testimonial.title',
        'wpsck_testimonial_summary' => 'testimonial.summary',
        'wpsck_testimonial_full' => 'testimonial.text',
        'wpsck_testimonial_name' => 'testimonial.name',
        'wpsck_testimonial_context' => 'testimonial.context',
        'wpsck_testimonial_date' => 'testimonial.testimonial_date',
        'wpsck_testimonial_id' => 'testimonial.id',
        'wpsck_testimonial_anchor' => 'testimonial.anchor',
    );
}

function wp_seed_content_divi_add_testimonial_loop_dynamic_data($response, $server, $request)
{
    if (!is_object($request) || !is_callable(array($request, 'get_route')) || '/divi/v1/loop/query-results' !== $request->get_route() || !is_object($response) || !is_callable(array($response, 'get_data')) || !is_callable(array($response, 'set_data'))) {
        return $response;
    }

    $data = $response->get_data();
    if (!is_array($data)) {
        return $response;
    }
    if (isset($data['items']) && is_array($data['items'])) {
        $items =& $data['items'];
    } elseif (isset($data['data']['items']) && is_array($data['data']['items'])) {
        $items =& $data['data']['items'];
    } else {
        return $response;
    }

    foreach ($items as &$item) {
        if (!is_array($item) || 'seed_testimonial' !== (isset($item['post_type']) ? $item['post_type'] : '')) {
            continue;
        }
        $testimonial_id = absint(isset($item['id']) ? $item['id'] : 0);
        if (!$testimonial_id || !wp_seed_content_testimonial_is_publicly_visible($testimonial_id)) {
            continue;
        }
        foreach (wp_seed_content_divi_testimonial_loop_sources() as $source => $field_id) {
            $value = wp_seed_content_resolve_dynamic_data($field_id, array('current_post_id' => $testimonial_id, 'current_post_type' => 'seed_testimonial'));
            if ('testimonial.photo' === $field_id) {
                $item[$source] = !is_wp_error($value)
                    && is_array($value)
                    && isset($value['url'])
                    && is_string($value['url'])
                    ? $value['url']
                    : '';
            } elseif (!is_wp_error($value) && is_scalar($value)) {
                $item[$source] = (string) $value;
            } else {
                $item[$source] = '';
            }
        }
    }
    unset($item);

    $response->set_data($data);
    return $response;
}
add_filter('rest_post_dispatch', 'wp_seed_content_divi_add_testimonial_loop_dynamic_data', 10, 3);
