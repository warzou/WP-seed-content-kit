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
    ), $atts, 'seed_testimonials');

    $limit = max(1, min(24, absint($atts['limit'])));
    $columns = wp_seed_content_clamp_columns($atts['columns']);
    $meta_query = array(
        array(
            'key' => '_seed_testimonial_consent',
            'value' => '1',
            'compare' => '=',
        ),
    );

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

    $query = new WP_Query(array(
        'post_type' => 'seed_testimonial',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => $meta_query,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ));

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
                echo wp_seed_content_render_testimonial_card(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('seed_testimonials', 'wp_seed_content_testimonials_shortcode');
