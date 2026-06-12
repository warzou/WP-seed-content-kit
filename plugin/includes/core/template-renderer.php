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
    if (!$template || 'publish' !== $template->post_status || '' === trim($template->post_content)) {
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

    $content = strtr($template->post_content, $replacements);

    return apply_filters('the_content', $content);
}

function wp_seed_content_prepare_template_replacements($placeholders)
{
    if (!is_array($placeholders)) {
        return array();
    }

    $replacements = array();
    foreach ($placeholders as $key => $placeholder) {
        $key = sanitize_key($key);
        if (!in_array($key, array('photo', 'name', 'text'), true)) {
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
