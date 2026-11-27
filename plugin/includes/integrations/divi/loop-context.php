<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_get_loop_object_post_id($loop_object)
{
    if ($loop_object instanceof WP_Post) {
        return $loop_object->ID;
    }
    if (is_object($loop_object) && isset($loop_object->ID)) {
        return $loop_object->ID;
    }
    if (is_array($loop_object)) {
        return isset($loop_object['ID'])
            ? $loop_object['ID']
            : (isset($loop_object['id']) ? $loop_object['id'] : 0);
    }

    return 0;
}

function wp_seed_content_divi_get_dynamic_content_context($data_args, $post_type)
{
    if (!is_array($data_args) || !is_string($post_type) || '' === $post_type) {
        return array('current_post_id' => 0);
    }

    if (array_key_exists('loop_id', $data_args) && null !== $data_args['loop_id']) {
        $post_id = $data_args['loop_id'];
    } elseif (isset($data_args['loop_object'])) {
        $post_id = wp_seed_content_divi_get_loop_object_post_id($data_args['loop_object']);
    } else {
        $post_id = array_key_exists('post_id', $data_args) ? $data_args['post_id'] : 0;
    }

    if (!function_exists('_wp_seed_content_normalize_dynamic_data_post_id')) {
        return array('current_post_id' => 0);
    }

    $post_id = _wp_seed_content_normalize_dynamic_data_post_id($post_id);
    if (!$post_id) {
        return array('current_post_id' => 0);
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post || $post_type !== $post->post_type) {
        return array('current_post_id' => 0);
    }

    return array(
        'current_post_id' => (int) $post->ID,
        'current_post_type' => $post_type,
    );
}

function wp_seed_content_divi_should_defer_dynamic_content($value, $resolver_context)
{
    return empty($resolver_context['current_post_id'])
        && is_string($value)
        && false !== strpos($value, '$variable(');
}

function wp_seed_content_divi_get_dynamic_content_wrapper_post_id($data_args)
{
    if (
        !is_array($data_args)
        || !array_key_exists('post_id', $data_args)
        || !function_exists('_wp_seed_content_normalize_dynamic_data_post_id')
    ) {
        return 0;
    }

    return _wp_seed_content_normalize_dynamic_data_post_id($data_args['post_id']);
}

function wp_seed_content_divi_loop_dynamic_data_sources()
{
    $sources = array(
        'seed_testimonial' => array(
            'wpsck_testimonial_visual' => array('field_id' => 'testimonial.photo', 'type' => 'image'),
            'wpsck_testimonial_title' => array('field_id' => 'testimonial.title', 'type' => 'text'),
            'wpsck_testimonial_summary' => array('field_id' => 'testimonial.summary', 'type' => 'text'),
            'wpsck_testimonial_full' => array('field_id' => 'testimonial.text', 'type' => 'text'),
            'wpsck_testimonial_intro' => array('field_id' => 'testimonial.intro', 'type' => 'text'),
            'wpsck_testimonial_more' => array('field_id' => 'testimonial.more', 'type' => 'text'),
            'wpsck_testimonial_has_more' => array('field_id' => 'testimonial.has_more', 'type' => 'text'),
            'wpsck_testimonial_name' => array('field_id' => 'testimonial.name', 'type' => 'text'),
            'wpsck_testimonial_context' => array('field_id' => 'testimonial.context', 'type' => 'text'),
            'wpsck_testimonial_date' => array('field_id' => 'testimonial.testimonial_date', 'type' => 'text'),
            'wpsck_testimonial_id' => array('field_id' => 'testimonial.id', 'type' => 'text'),
            'wpsck_testimonial_anchor' => array('field_id' => 'testimonial.anchor', 'type' => 'text'),
        ),
        'seed_quote' => array(
            'wpsck_quote_text' => array('field_id' => 'quote.quote', 'type' => 'text'),
            'wpsck_quote_author' => array('field_id' => 'quote.author', 'type' => 'text'),
            'wpsck_quote_era' => array('field_id' => 'quote.era', 'type' => 'text'),
            'wpsck_quote_source' => array('field_id' => 'quote.source', 'type' => 'text'),
        ),
        'seed_directory' => array(
            'wpsck_directory_visual' => array('field_id' => 'directory.photo', 'type' => 'image'),
            'wpsck_directory_name' => array('field_id' => 'directory.name', 'type' => 'text'),
            'wpsck_directory_professional_label' => array('field_id' => 'directory.professional_label', 'type' => 'text'),
            'wpsck_directory_summary' => array('field_id' => 'directory.summary', 'type' => 'text'),
            'wpsck_directory_presentation' => array('field_id' => 'directory.presentation', 'type' => 'text'),
            'wpsck_directory_presentation_intro' => array('field_id' => 'directory.presentation_intro', 'type' => 'text'),
            'wpsck_directory_presentation_more' => array('field_id' => 'directory.presentation_more', 'type' => 'text'),
            'wpsck_directory_status' => array('field_id' => 'directory.status', 'type' => 'text'),
            'wpsck_directory_profile_types' => array('field_id' => 'directory.profile_types', 'type' => 'text'),
            'wpsck_directory_seeking_models' => array('field_id' => 'directory.seeking_models', 'type' => 'text'),
            'wpsck_directory_location' => array('field_id' => 'directory.location', 'type' => 'text'),
            'wpsck_directory_id' => array('field_id' => 'directory.id', 'type' => 'text'),
            'wpsck_directory_anchor' => array('field_id' => 'directory.anchor', 'type' => 'text'),
        ),
    );

    if (function_exists('wp_seed_content_directory_individual_contact_provider_definitions')) {
        $provider_definitions = wp_seed_content_directory_individual_contact_provider_definitions();
        foreach ($provider_definitions as $definition) {
            $display_name = substr($definition['display_provider_id'], strlen('loop_'));
            $sources['seed_directory'][$display_name] = array(
                'field_id' => $definition['display_field_id'],
                'type' => 'text',
            );
        }
        foreach ($provider_definitions as $definition) {
            if (!empty($definition['has_href'])) {
                $href_name = substr($definition['href_provider_id'], strlen('loop_'));
                $sources['seed_directory'][$href_name] = array(
                    'field_id' => $definition['href_field_id'],
                    'type' => 'url',
                );
            }
        }
    }

    return function_exists('apply_filters')
        ? apply_filters('wp_seed_content_divi_loop_dynamic_data_sources', $sources)
        : $sources;
}

function wp_seed_content_divi_legacy_loop_provider_names()
{
    return array(
        'wp_seed_content_testimonial_photo' => 'loop_wpsck_testimonial_visual',
        'wp_seed_content_testimonial_title' => 'loop_wpsck_testimonial_title',
        'wp_seed_content_testimonial_summary' => 'loop_wpsck_testimonial_summary',
        'wp_seed_content_testimonial_text' => 'loop_wpsck_testimonial_full',
        'wp_seed_content_testimonial_name' => 'loop_wpsck_testimonial_name',
        'wp_seed_content_testimonial_context' => 'loop_wpsck_testimonial_context',
        'wp_seed_content_testimonial_date' => 'loop_wpsck_testimonial_date',
        'wp_seed_content_testimonial_id' => 'loop_wpsck_testimonial_id',
        'wp_seed_content_testimonial_anchor' => 'loop_wpsck_testimonial_anchor',
        'wp_seed_content_quote_quote' => 'loop_wpsck_quote_text',
        'wp_seed_content_quote_author' => 'loop_wpsck_quote_author',
        'wp_seed_content_quote_era' => 'loop_wpsck_quote_era',
        'wp_seed_content_quote_source' => 'loop_wpsck_quote_source',
        'wpsck_directory_presentation_more' => 'loop_wpsck_directory_presentation_more',
    );
}

function wp_seed_content_divi_dynamic_content_name_matches($canonical_name, $candidate_name)
{
    if ($canonical_name === $candidate_name) {
        return true;
    }

    $legacy_names = array_flip(wp_seed_content_divi_legacy_loop_provider_names());

    return isset($legacy_names[$canonical_name])
        && $legacy_names[$canonical_name] === $candidate_name;
}

function wp_seed_content_divi_normalize_legacy_loop_provider_tokens($value)
{
    if (is_string($value)) {
        foreach (wp_seed_content_divi_legacy_loop_provider_names() as $legacy_name => $canonical_name) {
            $value = str_replace(
                array(
                    '"name":"' . $legacy_name . '"',
                    '\\u0022name\\u0022:\\u0022' . $legacy_name . '\\u0022',
                ),
                array(
                    '"name":"' . $canonical_name . '"',
                    '\\u0022name\\u0022:\\u0022' . $canonical_name . '\\u0022',
                ),
                $value
            );
        }

        return $value;
    }

    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        $value[$key] = wp_seed_content_divi_normalize_legacy_loop_provider_tokens($item);
    }

    return $value;
}

/**
 * Normalizes legacy provider names in the copy of post content loaded by Divi.
 *
 * The stored post_content remains untouched. Divi's client recognizes loop
 * values only when their provider name starts with loop_.
 */
function wp_seed_content_divi_normalize_visual_builder_post_content($post_content, $post_id)
{
    if (!is_string($post_content)) {
        return $post_content;
    }

    return wp_seed_content_divi_normalize_legacy_loop_provider_tokens($post_content);
}

function wp_seed_content_divi_loop_item_is_public($post_type, $post_id)
{
    if ('seed_testimonial' === $post_type) {
        return function_exists('wp_seed_content_testimonial_is_publicly_visible')
            && wp_seed_content_testimonial_is_publicly_visible($post_id);
    }
    if ('seed_directory' === $post_type) {
        return function_exists('wp_seed_content_directory_is_publicly_eligible')
            && wp_seed_content_directory_is_publicly_eligible($post_id);
    }

    $post = get_post($post_id);

    return $post instanceof WP_Post
        && $post_type === $post->post_type
        && 'publish' === $post->post_status
        && '' === (string) $post->post_password;
}

function wp_seed_content_divi_format_loop_dynamic_data_value($value, $type)
{
    if ('image' === $type) {
        return !is_wp_error($value)
            && is_array($value)
            && isset($value['url'])
            && is_string($value['url'])
            ? $value['url']
            : '';
    }

    return !is_wp_error($value) && is_scalar($value) ? (string) $value : '';
}

function wp_seed_content_divi_add_loop_dynamic_data($response, $server, $request)
{
    if (
        !is_object($request)
        || !is_callable(array($request, 'get_route'))
        || !is_object($response)
        || !is_callable(array($response, 'get_data'))
        || !is_callable(array($response, 'set_data'))
    ) {
        return $response;
    }

    $route = $request->get_route();
    $data = $response->get_data();
    if (!is_array($data)) {
        return $response;
    }

    if ('/divi/v1/loop/query-results' === $route) {
        if (isset($data['items']) && is_array($data['items'])) {
            $items =& $data['items'];
        } elseif (isset($data['data']['items']) && is_array($data['data']['items'])) {
            $items =& $data['data']['items'];
        } else {
            return $response;
        }

        $sources_by_post_type = wp_seed_content_divi_loop_dynamic_data_sources();
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }

            $post_type = isset($item['post_type']) ? $item['post_type'] : '';
            $post_id = absint(isset($item['id']) ? $item['id'] : 0);
            if (!$post_id || !isset($sources_by_post_type[$post_type])) {
                continue;
            }
            if (!wp_seed_content_divi_loop_item_is_public($post_type, $post_id)) {
                continue;
            }

            foreach ($sources_by_post_type[$post_type] as $source => $definition) {
                $resolved = wp_seed_content_resolve_dynamic_data(
                    $definition['field_id'],
                    array('current_post_id' => $post_id, 'current_post_type' => $post_type)
                );
                $item[$source] = wp_seed_content_divi_format_loop_dynamic_data_value(
                    $resolved,
                    isset($definition['type']) ? $definition['type'] : 'text'
                );
            }
        }
        unset($item);
    } elseif (0 === strpos($route, '/wp/v2/') || 0 === strpos($route, '/divi/')) {
        $data = wp_seed_content_divi_normalize_legacy_loop_provider_tokens($data);
    }

    $response->set_data($data);

    return $response;
}
if (function_exists('add_filter')) {
    add_filter('the_content', 'wp_seed_content_divi_normalize_legacy_loop_provider_tokens', 7);
    add_filter('et_builder_render_layout', 'wp_seed_content_divi_normalize_legacy_loop_provider_tokens', 7);
    add_filter('rest_post_dispatch', 'wp_seed_content_divi_add_loop_dynamic_data', 10, 3);
    add_filter(
        'divi_visual_builder_settings_data_post_content',
        'wp_seed_content_divi_normalize_visual_builder_post_content',
        10,
        2
    );
}
