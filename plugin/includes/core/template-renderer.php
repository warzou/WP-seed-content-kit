<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_template_by_slug($slug)
{
    $slug = sanitize_title($slug);
    if ('' === $slug) {
        return null;
    }

    $template = get_page_by_path($slug, OBJECT, 'seed_template');
    if (!$template || 'publish' !== $template->post_status) {
        return null;
    }

    return $template;
}

function wp_seed_content_render_template_by_slug($slug, $placeholders, $fallback_html = '')
{
    $template = wp_seed_content_get_template_by_slug($slug);
    if (!$template) {
        return $fallback_html;
    }

    $replacements = wp_seed_content_prepare_template_replacements($placeholders);
    if (empty($replacements)) {
        return $fallback_html;
    }

    $template_source = function_exists('wp_seed_content_get_template_layout_source') ? wp_seed_content_get_template_layout_source($template->ID) : 'native';
    if ('divi_layout' === $template_source) {
        $layout_html = wp_seed_content_render_template_using_divi_layout($template->ID, $replacements);
        if ('' !== trim((string) $layout_html)) {
            return $layout_html;
        }
    }

    $content = strtr($template->post_content, $replacements);

    return apply_filters('the_content', $content);
}

function wp_seed_content_render_template_using_divi_layout($template_id, array $replacements = array())
{
    $template_id = (int) $template_id;
    if (!$template_id || empty($replacements)) {
        return '';
    }

    $layout_id = function_exists('wp_seed_content_get_template_divi_layout_id')
        ? wp_seed_content_get_template_divi_layout_id($template_id)
        : 0;

    if ($layout_id <= 0) {
        return '';
    }

    $layout = get_post($layout_id);
    if (
        !$layout
        || 'et_pb_layout' !== $layout->post_type
        || 'publish' !== $layout->post_status
        || '' === trim((string) $layout->post_content)
    ) {
        return '';
    }

    $content = strtr((string) $layout->post_content, $replacements);
    $rendered = function_exists('do_blocks') ? do_blocks($content) : $content;
    $rendered = do_shortcode($rendered);

    if ('' === trim(wp_strip_all_tags((string) $rendered))) {
        return '';
    }

    return $rendered;
}

function wp_seed_content_prepare_template_replacements($placeholders)
{
    if (!is_array($placeholders)) {
        return array();
    }

    $replacements = array();
    foreach ($placeholders as $key => $placeholder) {
        $key = sanitize_key($key);
        if (!in_array($key, array('photo', 'photo_url', 'name', 'text', 'photo_alt', 'quote', 'author', 'era', 'source'), true)) {
            continue;
        }

        $type = isset($placeholder['type']) ? sanitize_key($placeholder['type']) : 'text';
        $value = isset($placeholder['value']) ? $placeholder['value'] : '';

        $replacements['{{' . $key . '}}'] = wp_seed_content_sanitize_template_placeholder_value($value, $type);
    }

    return $replacements;
}

function wp_seed_content_sanitize_template_placeholder_value($value, $type)
{
    if ('html' === $type) {
        return wp_kses_post((string) $value);
    }

    if ('textarea' === $type) {
        return nl2br(esc_html((string) $value));
    }

    return esc_html((string) $value);
}
