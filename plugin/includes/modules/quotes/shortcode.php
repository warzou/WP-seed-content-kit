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

    $limit_raw = trim(sanitize_text_field((string) $atts['limit']));
    if ('' === $limit_raw) {
        $limit = 1;
        $orderby_default = 'random';
    } else {
        $limit = absint($limit_raw);
        $orderby_default = sanitize_key($atts['orderby']);
        $orderby_default = in_array($orderby_default, array('random', 'author', 'date'), true)
            ? $orderby_default
            : 'random';
    }

    if ('0' === $limit_raw) {
        $collection_limit = 0;
    } elseif ('' === $limit_raw || 0 === $limit) {
        $collection_limit = 1;
    } else {
        $collection_limit = $limit;
    }

    $order = strtolower(sanitize_key($atts['order']));
    $order = in_array($order, array('asc', 'desc'), true) ? $order : 'desc';

    $orderby = sanitize_key($atts['orderby']);
    $orderby = in_array($orderby, array('random', 'author', 'date', 'menu_order'), true)
        ? $orderby
        : $orderby_default;
    $template = sanitize_title($atts['template']);
    $featured = 'true' === strtolower((string) $atts['featured']) ? 'only' : 'all';

    wp_seed_content_enqueue_assets();

    $quote_ids = wp_seed_content_get_quotes(array(
        'limit' => $collection_limit,
        'featured' => $featured,
        'orderby' => $orderby,
        'order' => $order,
    ));

    if (empty($quote_ids)) {
        return '<p class="seed-quotes__empty">' . esc_html__('Aucune citation à afficher pour le moment.', 'wp-seed-content-kit') . '</p>';
    }

    $is_template_mode = '' !== $template && wp_seed_content_is_quote_template_valid($template);
    $collection_class = $is_template_mode ? 'seed-quotes__collection seed-quotes__collection--template' : 'seed-quotes__collection';

    ob_start();
    ?>
    <section class="seed-quotes" data-orderby="<?php echo esc_attr($orderby); ?>" data-order="<?php echo esc_attr(strtoupper($order)); ?>">
        <div class="<?php echo esc_attr($collection_class); ?>">
            <?php
            global $post;
            foreach ($quote_ids as $quote_id) {
                $quote = get_post($quote_id);
                if (!$quote instanceof WP_Post) {
                    continue;
                }

                $post = $quote;
                setup_postdata($post);
                if ($is_template_mode) {
                    echo wp_seed_content_render_quote_item($quote_id, $template); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    echo wp_seed_content_render_quote_item($quote_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
