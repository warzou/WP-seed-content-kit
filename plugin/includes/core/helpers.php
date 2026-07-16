<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_sanitize_meta_value($value, $definition)
{
    $type = isset($definition['type']) ? $definition['type'] : 'text';

    if ('checkbox' === $type) {
        return $value ? '1' : '';
    }

    if ('textarea' === $type) {
        return sanitize_textarea_field($value);
    }

    if ('date' === $type) {
        return wp_seed_content_sanitize_iso_date($value);
    }

    return sanitize_text_field($value);
}

function wp_seed_content_get_meta($post_id, $key)
{
    return get_post_meta($post_id, $key, true);
}

function wp_seed_content_is_truthy_meta($post_id, $key)
{
    return '1' === wp_seed_content_get_meta($post_id, $key);
}

function wp_seed_content_format_date($date)
{
    $date = wp_seed_content_sanitize_iso_date($date);
    if ('' === $date) {
        return '';
    }

    $timezone = wp_timezone();
    $date_object = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
    if (!$date_object) {
        return '';
    }

    return wp_date(get_option('date_format'), $date_object->getTimestamp(), $timezone);
}

function wp_seed_content_clamp_columns($columns)
{
    $columns = absint($columns);
    if ($columns < 1) {
        return 1;
    }
    if ($columns > 4) {
        return 4;
    }
    return $columns;
}

function wp_seed_content_bool_attr($value, $default = true)
{
    if (is_bool($value)) {
        return $value;
    }

    $value = strtolower((string) $value);
    if (in_array($value, array('1', 'true', 'yes', 'oui'), true)) {
        return true;
    }
    if (in_array($value, array('0', 'false', 'no', 'non'), true)) {
        return false;
    }

    return $default;
}

function wp_seed_content_get_post_excerpt($post_id)
{
    $excerpt = get_the_excerpt($post_id);
    if (!$excerpt) {
        $post = get_post($post_id);
        $excerpt = $post ? wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 24) : '';
    }

    return $excerpt;
}

function wp_seed_content_sanitize_iso_date($value)
{
    if (!is_scalar($value)) {
        return '';
    }
    $value = (string) $value;

    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $matches)) {
        return '';
    }

    return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $value : '';
}
