<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize collection settings shared by shortcodes and builder integrations.
 *
 * @param array $args Raw collection settings.
 *
 * @return array
 */
function wp_seed_content_normalize_testimonial_collection_args($args)
{
    $args = is_array($args) ? $args : array();
    $ids_request = wp_seed_content_normalize_testimonial_collection_ids(
        isset($args['ids']) ? $args['ids'] : ''
    );

    $limit_raw = isset($args['limit']) && is_scalar($args['limit'])
        ? trim(sanitize_text_field((string) $args['limit']))
        : '3';
    $limit = '0' === $limit_raw ? 0 : max(1, min(24, absint($limit_raw)));

    $columns = wp_seed_content_clamp_columns(
        isset($args['columns']) ? $args['columns'] : 3
    );

    $template = sanitize_title(
        isset($args['template']) && is_scalar($args['template']) ? (string) $args['template'] : ''
    );
    $orderby = sanitize_key(
        isset($args['orderby']) && is_scalar($args['orderby']) ? (string) $args['orderby'] : 'date'
    );
    $orderby = 'menu_order' === $orderby ? 'display_order' : $orderby;
    if (!in_array($orderby, array('display_order', 'date', 'testimonial_date', 'id'), true)) {
        $orderby = 'date';
    }

    $order = strtolower(
        sanitize_key(isset($args['order']) && is_scalar($args['order']) ? (string) $args['order'] : 'desc')
    );
    if (!in_array($order, array('asc', 'desc'), true)) {
        $order = 'desc';
    }

    $featured = strtolower(
        trim(sanitize_text_field(isset($args['featured']) && is_scalar($args['featured']) ? (string) $args['featured'] : 'all'))
    );
    $featured_aliases = array(
        'all' => 'all',
        'true' => 'only',
        'only' => 'only',
        'false' => 'exclude',
        'exclude' => 'exclude',
    );
    $featured = isset($featured_aliases[$featured]) ? $featured_aliases[$featured] : 'all';

    $context = sanitize_text_field(
        isset($args['context']) && is_scalar($args['context']) ? (string) $args['context'] : ''
    );
    if (!$context) {
        $context = '';
    }

    return array(
        'ids' => $ids_request['ids'],
        'manual_selection' => $ids_request['active'],
        'featured' => $featured,
        'context' => $context,
        'limit' => $limit,
        'orderby' => $orderby,
        'order' => $order,
        'template' => $template,
        'columns' => $columns,
    );
}

/**
 * Normalize a comma-separated manual testimonial selection.
 *
 * @param mixed $raw_ids Raw IDs value.
 *
 * @return array
 */
function wp_seed_content_normalize_testimonial_collection_ids($raw_ids)
{
    if (is_array($raw_ids)) {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map('absint', $raw_ids),
                    function ($id) {
                        return $id > 0;
                    }
                )
            )
        );

        return array(
            'active' => !empty($ids),
            'ids' => $ids,
        );
    }

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

/**
 * Render a testimonial collection through the canonical collection and item renderers.
 *
 * @param array $args           Raw collection settings.
 * @param bool  $enqueue_assets Whether frontend assets should be enqueued.
 *
 * @return string
 */
function wp_seed_content_render_testimonial_collection($args = array(), $enqueue_assets = true)
{
    $args = wp_seed_content_normalize_testimonial_collection_args($args);

    if ($enqueue_assets) {
        wp_seed_content_enqueue_assets();
    }

    $collection_args = array(
        'featured' => $args['featured'],
        'context' => $args['context'],
        'limit' => $args['limit'],
        'orderby' => $args['orderby'],
        'order' => $args['order'],
    );

    if ($args['manual_selection']) {
        $collection_args['ids'] = $args['ids'];
    }

    $testimonial_ids = wp_seed_content_get_testimonials($collection_args);

    if (empty($testimonial_ids)) {
        return '<p class="seed-testimonials__empty">'
            . esc_html__('Aucun témoignage à afficher pour le moment.', 'wp-seed-content-kit')
            . '</p>';
    }

    $is_template_mode = ''
        !== $args['template']
        && wp_seed_content_is_testimonial_template_valid($args['template']);
    $collection_class = $is_template_mode
        ? 'seed-testimonials__collection seed-testimonials__collection--template'
        : 'seed-testimonials__grid seed-testimonials__grid--cols-' . esc_attr($args['columns']);
    $section_class = $is_template_mode
        ? 'seed-testimonials seed-testimonials--template'
        : 'seed-testimonials';

    ob_start();
    ?>
    <section class="<?php echo esc_attr($section_class); ?>" data-columns="<?php echo esc_attr($args['columns']); ?>">
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
                    echo wp_seed_content_render_template_testimonial_item($testimonial_id, $args['template']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
