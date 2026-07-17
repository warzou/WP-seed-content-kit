<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_testimonials_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'ids' => '',
        'limit' => 3,
        'columns' => 3,
        'featured' => 'all',
        'context' => '',
        'orderby' => 'date',
        'order' => 'DESC',
        'template' => '',
    ), $atts, 'seed_testimonials');

    $ids_request = wp_seed_content_normalize_testimonial_shortcode_ids($atts['ids']);
    $limit_raw = trim(sanitize_text_field((string) $atts['limit']));
    $limit = '0' === $limit_raw ? 0 : max(1, min(24, absint($limit_raw)));
    $columns = wp_seed_content_clamp_columns($atts['columns']);
    $template = sanitize_title($atts['template']);
    $orderby = sanitize_key($atts['orderby']);
    $orderby = 'menu_order' === $orderby ? 'display_order' : $orderby;
    $orderby = in_array($orderby, array('display_order', 'date', 'testimonial_date', 'id'), true) ? $orderby : 'date';
    $order = strtolower(sanitize_key($atts['order']));
    $order = in_array($order, array('asc', 'desc'), true) ? $order : 'desc';
    $featured = strtolower(trim(sanitize_text_field((string) $atts['featured'])));
    $featured_aliases = array(
        'all' => 'all',
        'true' => 'only',
        'only' => 'only',
        'false' => 'exclude',
        'exclude' => 'exclude',
    );
    $featured = isset($featured_aliases[$featured]) ? $featured_aliases[$featured] : 'all';
    $context = sanitize_text_field($atts['context']);
    // Preserve historical shortcode truthiness: the string "0" does not enable this filter.
    $has_context = (bool) $context;

    wp_seed_content_enqueue_assets();

    $collection_args = array(
        'featured' => $featured,
        'limit' => !$ids_request['active'] && $has_context ? 0 : $limit,
        'orderby' => $orderby,
        'order' => $order,
    );

    if ($ids_request['active']) {
        $collection_args['ids'] = $ids_request['ids'];
    }

    $testimonial_ids = wp_seed_content_get_testimonials($collection_args);

    if (!$ids_request['active'] && $has_context) {
        $testimonial_ids = wp_seed_content_filter_testimonial_ids_by_context($testimonial_ids, $context);
        if ($limit > 0) {
            $testimonial_ids = array_slice($testimonial_ids, 0, $limit);
        }
    }

    if (empty($testimonial_ids)) {
        return '<p class="seed-testimonials__empty">' . esc_html__('Aucun témoignage à afficher pour le moment.', 'wp-seed-content-kit') . '</p>';
    }

    ob_start();

    $is_template_mode = '' !== $template && wp_seed_content_is_testimonial_template_valid($template);
    $collection_class = $is_template_mode ? 'seed-testimonials__collection seed-testimonials__collection--template' : 'seed-testimonials__grid seed-testimonials__grid--cols-' . esc_attr($columns);
    $section_class = $is_template_mode ? 'seed-testimonials seed-testimonials--template' : 'seed-testimonials';
    ?>
    <section class="<?php echo esc_attr($section_class); ?>" data-columns="<?php echo esc_attr($columns); ?>">
        <div class="<?php echo esc_attr($collection_class); ?>">
            <?php
            global $post;
            foreach ($testimonial_ids as $testimonial_id) {
                $post = get_post($testimonial_id);
                if (!$post instanceof WP_Post) {
                    continue;
                }
                setup_postdata($post);
                if ($is_template_mode) {
                    echo wp_seed_content_render_template_testimonial_item($testimonial_id, $template); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    continue;
                }
                echo wp_seed_content_render_testimonial_item($testimonial_id, ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('seed_testimonials', 'wp_seed_content_testimonials_shortcode');

function wp_seed_content_normalize_testimonial_shortcode_ids($raw_ids)
{
    $raw_ids = is_scalar($raw_ids) ? trim((string) $raw_ids) : '';
    if ('' === $raw_ids) {
        return array(
            'active' => false,
            'ids' => array(),
        );
    }

    $ids = array();
    $seen = array();

    foreach (explode(',', $raw_ids) as $raw_id) {
        $raw_id = trim($raw_id);
        if (!preg_match('/^[1-9][0-9]*$/D', $raw_id)) {
            continue;
        }

        $id = (int) $raw_id;
        if ($id <= 0 || (string) $id !== $raw_id || isset($seen[$id])) {
            continue;
        }

        $seen[$id] = true;
        $ids[] = $id;
    }

    return array(
        'active' => true,
        'ids' => !empty($ids) ? $ids : array(0),
    );
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
