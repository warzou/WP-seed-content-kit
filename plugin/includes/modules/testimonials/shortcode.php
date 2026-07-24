<?php

if (!defined('ABSPATH')) {
    exit;
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
    $fallback = wp_seed_content_render_testimonial_card($post_id);

    if ('' === $template || !function_exists('wp_seed_content_get_testimonial_template_placeholders')) {
        return $fallback;
    }

    return wp_seed_content_render_template_by_slug(
        $template,
        wp_seed_content_get_testimonial_template_placeholders($post_id),
        $fallback
    );
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
