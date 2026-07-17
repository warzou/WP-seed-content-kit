<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_quotes_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'mode' => '',
        'limit' => '',
        'featured' => 'all',
        'template' => '',
        'orderby' => 'random',
        'order' => 'DESC',
    ), $atts, 'seed_quotes');

    if ('daily' === strtolower(sanitize_key($atts['mode']))) {
        return wp_seed_content_render_daily_quote_shortcode($atts);
    }

    $limit_raw = isset($atts['limit']) ? sanitize_text_field((string) $atts['limit']) : '';
    $limit_raw = trim($limit_raw);
    if ('' === $limit_raw) {
        $limit = 1;
        $orderby_default = 'random';
    } else {
        $limit = absint($limit_raw);
        $orderby_default = sanitize_key($atts['orderby']);
        $orderby_default = in_array($orderby_default, array('random', 'author', 'date'), true) ? $orderby_default : 'random';
    }

    if ('0' === $limit_raw) {
        $posts_per_page = -1;
    } elseif ('' === $limit_raw) {
        $posts_per_page = 1;
    } elseif (0 === $limit) {
        $posts_per_page = 1;
    } else {
        $posts_per_page = $limit;
    }

    $order = strtolower(sanitize_key($atts['order']));
    $order = in_array($order, array('asc', 'desc'), true) ? strtoupper($order) : 'DESC';

    $orderby = sanitize_key($atts['orderby']);
    if ('' === $orderby_raw = $orderby) {
        $orderby = $orderby_default;
    }
    $orderby = in_array($orderby, array('random', 'author', 'date', 'menu_order'), true) ? $orderby : $orderby_default;

    $template = sanitize_title($atts['template']);
    $query_orderby = 'date';
    $meta_query = array();
    $meta_key = '';

    if ('true' === strtolower((string) $atts['featured'])) {
        $meta_query[] = array(
            'key' => '_seed_quote_featured',
            'value' => '1',
            'compare' => '=',
        );
    }

    if ('author' === $orderby) {
        $query_orderby = 'meta_value';
        $meta_key = '_seed_quote_author';
    } elseif ('menu_order' === $orderby) {
        $query_orderby = 'menu_order';
    } elseif ('random' === $orderby) {
        $query_orderby = 'rand';
        $order = 'DESC';
    } elseif ('date' === $orderby) {
        $query_orderby = 'date';
    }

    wp_seed_content_enqueue_assets();

    $query_args = array(
        'post_type' => 'seed_quote',
        'post_status' => 'publish',
        'has_password' => false,
        'posts_per_page' => $posts_per_page,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'orderby' => $query_orderby,
        'order' => $order,
    );

    if ($meta_key) {
        $query_args['meta_key'] = $meta_key;
    }

    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        return '<p class="seed-quotes__empty">' . esc_html__('Aucune citation à afficher pour le moment.', 'wp-seed-content-kit') . '</p>';
    }

    $is_template_mode = '' !== $template && wp_seed_content_is_quote_template_valid($template);
    $collection_class = $is_template_mode ? 'seed-quotes__collection seed-quotes__collection--template' : 'seed-quotes__collection';

    ob_start();
    ?>
    <section class="seed-quotes" data-orderby="<?php echo esc_attr($orderby); ?>" data-order="<?php echo esc_attr($order); ?>">
        <div class="<?php echo esc_attr($collection_class); ?>">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                if ($is_template_mode) {
                    echo wp_seed_content_render_quote_item(get_the_ID(), $template); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    echo wp_seed_content_render_quote_item(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('seed_quotes', 'wp_seed_content_quotes_shortcode');

function wp_seed_content_render_daily_quote_shortcode($atts)
{
    $template = sanitize_title($atts['template']);

    wp_seed_content_enqueue_assets();

    $quote_id = wp_seed_content_get_daily_quote();
    if ($quote_id <= 0) {
        return '<p class="seed-quotes__empty">' . esc_html__('Aucune citation à afficher pour le moment.', 'wp-seed-content-kit') . '</p>';
    }

    $quote = get_post($quote_id);
    if (!$quote instanceof WP_Post) {
        return '<p class="seed-quotes__empty">' . esc_html__('Aucune citation à afficher pour le moment.', 'wp-seed-content-kit') . '</p>';
    }

    global $post;
    $post = $quote;
    setup_postdata($post);

    $is_template_mode = '' !== $template && wp_seed_content_is_quote_template_valid($template);
    $collection_class = $is_template_mode ? 'seed-quotes__collection seed-quotes__collection--template' : 'seed-quotes__collection';

    ob_start();
    ?>
    <section class="seed-quotes" data-orderby="daily" data-order="DESC">
        <div class="<?php echo esc_attr($collection_class); ?>">
            <?php
            if ($is_template_mode) {
                echo wp_seed_content_render_quote_item($quote_id, $template); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } else {
                echo wp_seed_content_render_quote_item($quote_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}

function wp_seed_content_render_quote_item($post_id, $template = '')
{
    $fallback = wp_seed_content_render_quote_card($post_id);

    if ('' === $template || !function_exists('wp_seed_content_get_quote_template_placeholders')) {
        return $fallback;
    }

    return wp_seed_content_render_template_by_slug(
        $template,
        wp_seed_content_get_quote_template_placeholders($post_id),
        $fallback
    );
}

function wp_seed_content_render_template_quote_item($post_id, $template)
{
    if ('' === $template) {
        return wp_seed_content_render_quote_item($post_id, '');
    }

    $content = wp_seed_content_render_quote_item($post_id, $template);
    return '<article class="seed-quote-template-item">' . $content . '</article>';
}

function wp_seed_content_is_quote_template_valid($slug)
{
    $template = wp_seed_content_get_template_by_slug($slug);
    if (!$template) {
        return false;
    }

    return 'quotes' === wp_seed_content_get_template_module($template->ID);
}
