<?php
/**
 * Per-item Dynamic Content context for testimonial Divi Layouts.
 *
 * @package WPSeedContentKit
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exact Dynamic Content names registered by Content Kit for testimonials.
 *
 * @return array
 */
function wp_seed_content_divi_testimonial_dynamic_content_names()
{
    return array(
        'wp_seed_content_testimonial_photo',
        'wp_seed_content_testimonial_text',
        'wp_seed_content_testimonial_name',
        'wp_seed_content_testimonial_context',
        'wp_seed_content_testimonial_date',
    );
}

/**
 * Validate a testimonial ID before exposing it to Dynamic Content providers.
 *
 * @param mixed $post_id Candidate post ID.
 *
 * @return int
 */
function _wp_seed_content_validate_public_testimonial_context_id($post_id)
{
    $post_id = absint($post_id);
    if ($post_id <= 0) {
        return 0;
    }

    $post = get_post($post_id);
    if (
        !$post instanceof WP_Post
        || 'seed_testimonial' !== $post->post_type
        || 'publish' !== $post->post_status
    ) {
        return 0;
    }

    if (
        function_exists('wp_seed_content_testimonial_is_publicly_visible')
        && !wp_seed_content_testimonial_is_publicly_visible($post_id)
    ) {
        return 0;
    }

    return $post_id;
}

/**
 * Decode a Divi variable payload in direct or block-attribute serialized form.
 *
 * @param string $source          JSON source between `$variable(` and `)$`.
 * @param bool   $was_serialized  Set when WordPress block escaping was decoded.
 *
 * @return array
 */
function _wp_seed_content_decode_divi_variable_payload($source, &$was_serialized)
{
    $was_serialized = false;
    $payload = json_decode($source, true);
    if (is_array($payload)) {
        return $payload;
    }

    $decoded_source = json_decode(
        '"' . str_replace('"', '\\"', (string) $source) . '"'
    );
    if (!is_string($decoded_source)) {
        return array();
    }

    $payload = json_decode($decoded_source, true);
    if (!is_array($payload)) {
        return array();
    }

    $was_serialized = true;

    return $payload;
}

/**
 * Restore a transformed Divi variable to its original serialization layer.
 *
 * @param array $payload        Dynamic Content payload.
 * @param bool  $was_serialized Whether the source used block-attribute escaping.
 * @param bool  $failed         Encoding failure flag.
 *
 * @return string
 */
function _wp_seed_content_encode_divi_variable_payload(
    $payload,
    $was_serialized,
    &$failed
) {
    $encoded = wp_json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if (!is_string($encoded) || '' === $encoded) {
        $failed = true;
        return '';
    }

    $token = '$variable(' . $encoded . ')$';
    if (!$was_serialized) {
        return $token;
    }

    if (!function_exists('serialize_block_attributes')) {
        return str_replace('"', '\\u0022', $token);
    }

    $serialized = serialize_block_attributes(array('value' => $token));
    if (0 !== strpos($serialized, '{"value":"') || '"}' !== substr($serialized, -2)) {
        $failed = true;
        return '';
    }

    return substr($serialized, 10, -2);
}

/**
 * Inject the current testimonial ID into allowlisted Divi variables in a string.
 *
 * @param string $value   Attribute or content value.
 * @param int    $post_id Validated testimonial ID.
 * @param bool   $failed  Set when an allowlisted-looking variable is malformed.
 *
 * @return string
 */
function _wp_seed_content_inject_testimonial_post_id_into_divi_string(
    $value,
    $post_id,
    &$failed
) {
    if (!is_string($value) || false === strpos($value, '$variable(')) {
        return $value;
    }

    $allowed_names = array_flip(wp_seed_content_divi_testimonial_dynamic_content_names());
    $result = '';
    $offset = 0;
    $length = strlen($value);
    $marker = '$variable(';
    $marker_length = strlen($marker);

    while ($offset < $length) {
        $start = strpos($value, $marker, $offset);
        if (false === $start) {
            $result .= substr($value, $offset);
            break;
        }

        $result .= substr($value, $offset, $start - $offset);
        $json_start = $start + $marker_length;
        $cursor = $json_start;
        $depth = 0;
        $in_string = false;
        $escaped = false;
        $json_end = null;

        for (; $cursor < $length; $cursor++) {
            $character = $value[$cursor];

            if ($in_string) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }
                if ('\\' === $character) {
                    $escaped = true;
                    continue;
                }
                if ('"' === $character) {
                    $in_string = false;
                }
                continue;
            }

            if ('"' === $character) {
                $in_string = true;
                continue;
            }
            if ('{' === $character || '[' === $character) {
                $depth++;
                continue;
            }
            if ('}' === $character || ']' === $character) {
                $depth--;
                if (0 === $depth) {
                    $json_end = $cursor;
                    break;
                }
            }
        }

        if (
            null === $json_end
            || $json_end + 2 >= $length
            || ')' !== $value[$json_end + 1]
            || '$' !== $value[$json_end + 2]
        ) {
            if (preg_match('/wp_seed_content_testimonial_(?:photo|text|name|context|date)/', substr($value, $start))) {
                $failed = true;
            }
            $result .= substr($value, $start);
            break;
        }

        $token_end = $json_end + 3;
        $token = substr($value, $start, $token_end - $start);
        $was_serialized = false;
        $payload = _wp_seed_content_decode_divi_variable_payload(
            substr($value, $json_start, $json_end - $json_start + 1),
            $was_serialized
        );
        $name = is_array($payload)
            && isset($payload['type'], $payload['value'])
            && 'content' === $payload['type']
            && is_array($payload['value'])
            && isset($payload['value']['name'])
            ? (string) $payload['value']['name']
            : '';

        if (isset($allowed_names[$name])) {
            if (class_exists('WP_Seed_Content_Render_Context')) {
                WP_Seed_Content_Render_Context::expect_dynamic($name);
            }

            $dynamic_value = $payload['value'];
            $payload['value'] = array(
                'name' => $name,
                'post_id' => (string) $post_id,
            );
            foreach ($dynamic_value as $key => $dynamic_setting) {
                if ('name' === $key || 'post_id' === $key) {
                    continue;
                }
                $payload['value'][$key] = $dynamic_setting;
            }

            $token = _wp_seed_content_encode_divi_variable_payload(
                $payload,
                $was_serialized,
                $failed
            );
            if ($failed) {
                return $value;
            }
        }

        $result .= $token;
        $offset = $token_end;
    }

    return $result;
}

/**
 * Recursively transform block attributes without touching unrelated values.
 *
 * @param mixed $value   Block attribute value.
 * @param int   $post_id Validated testimonial ID.
 * @param bool  $failed  Parsing failure flag.
 *
 * @return mixed
 */
function _wp_seed_content_inject_testimonial_context_into_value($value, $post_id, &$failed)
{
    if (is_string($value)) {
        return _wp_seed_content_inject_testimonial_post_id_into_divi_string(
            $value,
            $post_id,
            $failed
        );
    }

    if (!is_array($value)) {
        return $value;
    }

    $transformed = array();
    foreach ($value as $key => $nested_value) {
        $transformed[$key] = _wp_seed_content_inject_testimonial_context_into_value(
            $nested_value,
            $post_id,
            $failed
        );
    }

    return $transformed;
}

/**
 * Recursively inject context into parsed WordPress blocks.
 *
 * @param array $blocks  Parsed blocks.
 * @param int   $post_id Validated testimonial ID.
 * @param bool  $failed  Parsing failure flag.
 *
 * @return array
 */
function _wp_seed_content_inject_testimonial_context_into_blocks($blocks, $post_id, &$failed)
{
    if (!is_array($blocks)) {
        $failed = true;
        return array();
    }

    foreach ($blocks as $index => $block) {
        if (!is_array($block)) {
            $failed = true;
            continue;
        }

        if (isset($block['attrs']) && is_array($block['attrs'])) {
            $block['attrs'] = _wp_seed_content_inject_testimonial_context_into_value(
                $block['attrs'],
                $post_id,
                $failed
            );
        }

        if (isset($block['innerContent']) && is_array($block['innerContent'])) {
            $block['innerContent'] = _wp_seed_content_inject_testimonial_context_into_value(
                $block['innerContent'],
                $post_id,
                $failed
            );
        }

        if (isset($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            $block['innerBlocks'] = _wp_seed_content_inject_testimonial_context_into_blocks(
                $block['innerBlocks'],
                $post_id,
                $failed
            );
        }

        $blocks[$index] = $block;
    }

    return $blocks;
}

/**
 * Prepare one Divi Layout in memory for a testimonial card.
 *
 * @param string $content Layout post content.
 * @param int    $post_id Testimonial post ID.
 *
 * @return string|WP_Error
 */
function wp_seed_content_prepare_divi_testimonial_layout_content($content, $post_id)
{
    $post_id = _wp_seed_content_validate_public_testimonial_context_id($post_id);
    if ($post_id <= 0) {
        return new WP_Error('invalid_testimonial_context');
    }

    if (
        !is_string($content)
        || '' === trim($content)
        || !function_exists('parse_blocks')
        || !function_exists('serialize_blocks')
    ) {
        return new WP_Error('invalid_divi_layout_content');
    }

    try {
        $failed = false;
        $content = _wp_seed_content_inject_testimonial_post_id_into_divi_string(
            $content,
            $post_id,
            $failed
        );
        if ($failed) {
            return new WP_Error('invalid_divi_dynamic_content');
        }

        $blocks = parse_blocks($content);
        $blocks = _wp_seed_content_inject_testimonial_context_into_blocks(
            $blocks,
            $post_id,
            $failed
        );

        if ($failed) {
            return new WP_Error('invalid_divi_dynamic_content');
        }

        $serialized = serialize_blocks($blocks);
        if (!is_string($serialized) || '' === trim($serialized)) {
            return new WP_Error('invalid_divi_layout_serialization');
        }

        return $serialized;
    } catch (Throwable $exception) {
        return new WP_Error('divi_layout_context_exception');
    }
}

/**
 * Track only Content Kit testimonial providers resolved for the active card.
 *
 * Divi may serve a repeated value from its request-local cache without running
 * provider filters again. The render context remembers prior successful
 * resolutions by field and testimonial ID so those cache hits remain valid.
 *
 * @param string $value     Resolved value.
 * @param array  $data_args Divi Dynamic Content arguments.
 *
 * @return string
 */
function wp_seed_content_track_divi_testimonial_dynamic_resolution($value, $data_args)
{
    if (
        !class_exists('WP_Seed_Content_Render_Context')
        || !is_array($data_args)
        || !isset($data_args['name'])
        || !in_array(
            (string) $data_args['name'],
            wp_seed_content_divi_testimonial_dynamic_content_names(),
            true
        )
    ) {
        return $value;
    }

    $post_id = array_key_exists('loop_id', $data_args)
        && null !== $data_args['loop_id']
        ? $data_args['loop_id']
        : (isset($data_args['post_id']) ? $data_args['post_id'] : 0);
    WP_Seed_Content_Render_Context::resolve_dynamic(
        $data_args['name'],
        $post_id
    );

    return $value;
}
if (function_exists('add_filter')) {
    add_filter(
        'divi_module_dynamic_content_resolved_value',
        'wp_seed_content_track_divi_testimonial_dynamic_resolution',
        999,
        2
    );
}
