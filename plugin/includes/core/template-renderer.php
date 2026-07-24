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

function wp_seed_content_render_template_by_slug(
    $slug,
    $placeholders,
    $fallback_html = '',
    $render_context = array()
)
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
        $layout_html = wp_seed_content_render_template_using_divi_layout(
            $template->ID,
            $replacements,
            $render_context
        );
        if ('' !== trim((string) $layout_html)) {
            return $layout_html;
        }

        if (
            is_array($render_context)
            && isset($render_context['module'])
            && 'testimonials' === $render_context['module']
        ) {
            return $fallback_html;
        }
    }

    $content = strtr($template->post_content, $replacements);

    return apply_filters('the_content', $content);
}

function wp_seed_content_render_template_using_divi_layout(
    $template_id,
    array $replacements = array(),
    $render_context = array()
)
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
    if (
        is_array($render_context)
        && isset($render_context['module'], $render_context['post_id'])
        && 'testimonials' === $render_context['module']
    ) {
        if (!class_exists('WP_Seed_Content_Render_Context')) {
            return '';
        }

        $active_context = WP_Seed_Content_Render_Context::current();
        if (
            empty($active_context)
            || 'testimonials' !== $active_context['module']
            || absint($render_context['post_id']) !== $active_context['post_id']
            || $template_id !== $active_context['template_id']
            || $layout_id !== $active_context['layout_id']
        ) {
            return '';
        }

        if (!function_exists('wp_seed_content_prepare_divi_testimonial_layout_content')) {
            return '';
        }

        $content = wp_seed_content_prepare_divi_testimonial_layout_content(
            $content,
            $render_context['post_id']
        );
        if (is_wp_error($content)) {
            return '';
        }
    }

    $rendered = apply_filters('the_content', $content);
    $rendered = function_exists('do_blocks') ? do_blocks($rendered) : $rendered;
    $rendered = do_shortcode($rendered);

    if (
        false !== strpos((string) $rendered, '$variable(')
        || preg_match(
            '/var\(--wp_seed_content_testimonial_(?:photo|text|name|context|date)\)/',
            (string) $rendered
        )
    ) {
        return '';
    }

    if (
        class_exists('WP_Seed_Content_Render_Context')
        && !WP_Seed_Content_Render_Context::dynamic_resolution_complete()
    ) {
        return '';
    }

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
        if (!in_array($key, array('photo', 'photo_url', 'name', 'text', 'photo_alt', 'context', 'date', 'quote', 'author', 'era', 'source'), true)) {
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
