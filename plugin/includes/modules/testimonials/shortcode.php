<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_Seed_Content_Render_Context')) {
    require_once dirname(__DIR__, 2) . '/core/render-context.php';
}

if (!function_exists('wp_seed_content_prepare_divi_testimonial_layout_content')) {
    $testimonial_layout_context = dirname(__DIR__, 2)
        . '/integrations/divi/testimonial-layout-context.php';
    if (file_exists($testimonial_layout_context)) {
        require_once $testimonial_layout_context;
    }
}

if (!function_exists('wp_seed_content_render_testimonial_collection')) {
    require_once __DIR__ . '/collection-renderer.php';
}

function wp_seed_content_testimonials_shortcode($atts)
{
    return wp_seed_content_render_testimonial_collection(
        shortcode_atts(
            array(
                'ids' => '',
                'limit' => 3,
                'columns' => 3,
                'featured' => 'all',
                'context' => '',
                'orderby' => 'date',
                'order' => 'DESC',
                'template' => '',
            ),
            $atts,
            'seed_testimonials'
        )
    );
}
add_shortcode('seed_testimonials', 'wp_seed_content_testimonials_shortcode');

function wp_seed_content_normalize_testimonial_shortcode_ids($raw_ids)
{
    return wp_seed_content_normalize_testimonial_collection_ids($raw_ids);
}

function wp_seed_content_filter_testimonial_ids_by_context($testimonial_ids, $context)
{
    $filtered_ids = array();

    foreach ((array) $testimonial_ids as $testimonial_id) {
        $data = wp_seed_content_get_testimonial_data($testimonial_id);
        if (isset($data['context']) && (string) $data['context'] === $context) {
            $filtered_ids[] = (int) $testimonial_id;
        }
    }

    return $filtered_ids;
}

function wp_seed_content_render_testimonial_item($post_id, $template)
{
    $post_id = absint($post_id);
    if ($post_id <= 0) {
        return '';
    }

    $template_post = '' !== $template
        ? wp_seed_content_get_template_by_slug($template)
        : null;
    $template_id = $template_post instanceof WP_Post
        ? (int) $template_post->ID
        : 0;
    $layout_id = $template_id > 0
        && function_exists('wp_seed_content_get_template_divi_layout_id')
        ? wp_seed_content_get_template_divi_layout_id($template_post->ID)
        : 0;
    $render_id = _wp_seed_content_render_context_id(
        'testimonials',
        $post_id,
        $template_id,
        $layout_id
    );
    $render_context = array(
        'module' => 'testimonials',
        'post_id' => $post_id,
        'template_id' => $template_id,
        'layout_id' => (int) $layout_id,
        'render_id' => $render_id,
    );

    if (!WP_Seed_Content_Render_Context::push($render_context)) {
        return wp_seed_content_render_testimonial_card($post_id);
    }

    try {
        $fallback = wp_seed_content_render_testimonial_card($post_id);
        if (
            '' === $template
            || !function_exists('wp_seed_content_get_testimonial_template_placeholders')
            || !$template_post instanceof WP_Post
            || 'testimonials' !== wp_seed_content_get_template_module($template_post->ID)
        ) {
            return $fallback;
        }

        return wp_seed_content_render_template_by_slug(
            $template,
            wp_seed_content_get_testimonial_template_placeholders($post_id),
            $fallback,
            $render_context
        );
    } catch (Throwable $exception) {
        return isset($fallback) ? $fallback : '';
    } finally {
        WP_Seed_Content_Render_Context::pop($render_id);
    }
}

function wp_seed_content_render_template_testimonial_item($post_id, $template)
{
    if ('' === $template) {
        return wp_seed_content_render_testimonial_item($post_id, '');
    }

    $content = wp_seed_content_render_testimonial_item($post_id, $template);
    return '<article class="seed-testimonial-template-item">' . $content . '</article>';
}

function wp_seed_content_is_testimonial_template_valid($slug)
{
    $template = wp_seed_content_get_template_by_slug($slug);
    if (!$template) {
        return false;
    }

    return 'testimonials' === wp_seed_content_get_template_module($template->ID);
}
