<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_cards_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limit' => 6,
        'columns' => 3,
        'category' => '',
        'show_image' => 'true',
        'show_excerpt' => 'true',
        'show_button' => 'true',
        'button_label' => __('Lire', 'wp-seed-content-kit'),
    ), $atts, 'seed_cards');

    $limit = max(1, min(24, absint($atts['limit'])));
    $columns = wp_seed_content_clamp_columns($atts['columns']);
    $category = sanitize_key($atts['category']);

    wp_seed_content_enqueue_assets();

    $query_args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    );

    if ($category && 'all' !== $category) {
        $term = get_category_by_slug($category);
        if (!$term) {
            return '<p class="seed-cards__empty">' . esc_html__('No matching category found.', 'wp-seed-content-kit') . '</p>';
        }

        $query_args['category__in'] = array((int) $term->term_id);
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        return '<p class="seed-cards__empty">' . esc_html__('No content to display yet.', 'wp-seed-content-kit') . '</p>';
    }

    $card_args = array(
        'show_image' => wp_seed_content_bool_attr($atts['show_image'], true),
        'show_excerpt' => wp_seed_content_bool_attr($atts['show_excerpt'], true),
        'show_button' => wp_seed_content_bool_attr($atts['show_button'], true),
        'button_label' => sanitize_text_field($atts['button_label']),
    );

    ob_start();
    ?>
    <section class="seed-cards" data-columns="<?php echo esc_attr($columns); ?>">
        <div class="seed-cards__grid seed-cards__grid--cols-<?php echo esc_attr($columns); ?>">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                echo wp_seed_content_render_post_card(get_the_ID(), $card_args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('seed_cards', 'wp_seed_content_cards_shortcode');
