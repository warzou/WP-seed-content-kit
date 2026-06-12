<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_testimonials_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limit' => 3,
        'columns' => 3,
        'featured' => 'all',
        'context' => '',
        'template' => '',
    ), $atts, 'seed_testimonials');

    $limit = max(1, min(24, absint($atts['limit'])));
    $columns = wp_seed_content_clamp_columns($atts['columns']);
    $template = sanitize_title($atts['template']);
    $meta_query = array();

    if ('true' === strtolower((string) $atts['featured'])) {
        $meta_query[] = array(
            'key' => '_seed_featured',
            'value' => '1',
            'compare' => '=',
        );
    } elseif ('false' === strtolower((string) $atts['featured'])) {
        $meta_query[] = array(
            'key' => '_seed_featured',
            'compare' => 'NOT EXISTS',
        );
    }

    $context = sanitize_text_field($atts['context']);
    if ($context) {
        $meta_query[] = array(
            'key' => '_seed_testimonial_context',
            'value' => $context,
            'compare' => '=',
        );
    }

    wp_seed_content_enqueue_assets();

    $query_args = array(
        'post_type' => 'seed_testimonial',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    );

    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        return '<p class="seed-testimonials__empty">' . esc_html__('Aucun témoignage à afficher pour le moment.', 'wp-seed-content-kit') . '</p>';
    }

    ob_start();
    ?>
    <section class="seed-testimonials" data-columns="<?php echo esc_attr($columns); ?>">
        <div class="seed-testimonials__grid seed-testimonials__grid--cols-<?php echo esc_attr($columns); ?>">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                echo wp_seed_content_render_testimonial_item(get_the_ID(), $template); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('seed_testimonials', 'wp_seed_content_testimonials_shortcode');

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
