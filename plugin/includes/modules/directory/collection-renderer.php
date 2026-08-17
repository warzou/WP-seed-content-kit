<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse a strict comma-separated list of positive post IDs.
 *
 * @param mixed $value Raw list.
 *
 * @return array|null
 */
function wp_seed_content_directory_parse_shortcode_ids($value)
{
    $value = is_scalar($value) ? trim((string) $value) : '';
    if ('' === $value) {
        return array();
    }

    $ids = array();
    foreach (explode(',', $value) as $part) {
        $part = trim($part);
        if (!preg_match('/^[1-9][0-9]*$/D', $part)) {
            return null;
        }
        $id = (int) $part;
        if ((string) $id !== $part) {
            return null;
        }
        $ids[$id] = $id;
    }

    return array_values($ids);
}

/**
 * Normalize collection attributes shared by shortcode and builder integrations.
 *
 * @param array $atts Raw attributes.
 *
 * @return array|null
 */
function wp_seed_content_directory_normalize_shortcode_atts($atts)
{
    $atts = shortcode_atts(array(
        'status' => 'all',
        'profile_type' => '',
        'profile_types' => '',
        'profile_type_operator' => 'or',
        'seeking_models' => 'all',
        'department' => '',
        'country' => '',
        'featured' => 'all',
        'limit' => '0',
        'offset' => '0',
        'orderby' => 'display_order',
        'order' => 'asc',
        'ids' => '',
        'exclude_ids' => '',
        'template' => '',
    ), is_array($atts) ? $atts : array(), 'seed_directory');

    $limit_raw = is_scalar($atts['limit'])
        ? trim(sanitize_text_field((string) $atts['limit']))
        : '';
    $offset_raw = is_scalar($atts['offset'])
        ? trim(sanitize_text_field((string) $atts['offset']))
        : '';
    if (!preg_match('/^\d+$/D', $limit_raw) || !preg_match('/^\d+$/D', $offset_raw)) {
        return null;
    }

    $ids = wp_seed_content_directory_parse_shortcode_ids($atts['ids']);
    $exclude_ids = wp_seed_content_directory_parse_shortcode_ids($atts['exclude_ids']);
    if (null === $ids || null === $exclude_ids) {
        return null;
    }

    $template_raw = is_scalar($atts['template']) ? trim((string) $atts['template']) : '';
    $template = sanitize_title($template_raw);
    if ('' !== $template_raw && '' === $template) {
        return null;
    }

    $args = array(
        'status' => strtolower(sanitize_key($atts['status'])),
        'profile_type' => sanitize_text_field($atts['profile_type']),
        'profile_types' => sanitize_text_field($atts['profile_types']),
        'profile_type_operator' => strtolower(sanitize_key($atts['profile_type_operator'])),
        'seeking_models' => strtolower(sanitize_key($atts['seeking_models'])),
        'department' => sanitize_text_field($atts['department']),
        'country' => sanitize_text_field($atts['country']),
        'featured' => strtolower(sanitize_key($atts['featured'])),
        'limit' => min(100, (int) $limit_raw),
        'offset' => min(10000, (int) $offset_raw),
        'orderby' => strtolower(sanitize_key($atts['orderby'])),
        'order' => strtolower(sanitize_key($atts['order'])),
        'ids' => $ids,
        'exclude_ids' => $exclude_ids,
    );
    if (null === wp_seed_content_directory_normalize_collection_args($args)) {
        return null;
    }

    return array('collection' => $args, 'template' => $template);
}

/**
 * Render an already normalized Directory collection.
 *
 * @param array $normalized     Canonical collection and Template settings.
 * @param bool  $enqueue_assets Whether public assets should be enqueued.
 *
 * @return string
 */
function wp_seed_content_render_normalized_directory_collection($normalized, $enqueue_assets = true)
{
    $empty = '<div class="wp-seed-directory"><p class="wp-seed-directory__empty">'
        . esc_html__('Aucune fiche n’est disponible pour le moment.', 'wp-seed-content-kit')
        . '</p></div>';
    $ids = wp_seed_content_directory_get_entries($normalized['collection']);
    if (empty($ids)) {
        if ($enqueue_assets) {
            wp_seed_content_directory_enqueue_structure_assets();
        }
        return $empty;
    }

    if (function_exists('update_meta_cache')) {
        update_meta_cache('post', $ids);
    }

    $labels = function_exists('wp_seed_content_directory_classification_options')
        ? wp_seed_content_directory_classification_options('status', false)
        : wp_seed_content_directory_get_statuses();
    $groups = array_fill_keys(array_keys($labels), array());
    $native_rendered = false;
    foreach ($ids as $id) {
        $data = wp_seed_content_directory_get_public_data($id);
        if (!is_array($data) || !isset($groups[$data['status']])) {
            continue;
        }
        $rendered = wp_seed_content_directory_render_entry($data, $normalized['template']);
        if ('' === trim($rendered['html'])) {
            continue;
        }
        $groups[$data['status']][] = $rendered['html'];
        $native_rendered = $native_rendered || $rendered['native'];
    }

    if (!array_filter($groups)) {
        if ($enqueue_assets) {
            wp_seed_content_directory_enqueue_structure_assets();
        }
        return $empty;
    }

    if ($enqueue_assets) {
        wp_seed_content_directory_enqueue_structure_assets();
        if ($native_rendered) {
            wp_seed_content_directory_enqueue_native_card_assets();
        }
    }

    ob_start();
    ?>
    <div class="wp-seed-directory">
        <?php foreach ($labels as $status => $label) : ?>
            <?php if (!empty($groups[$status])) : ?>
                <section class="wp-seed-directory__group">
                    <h2 class="wp-seed-directory__heading"><?php echo esc_html($label); ?></h2>
                    <ul class="wp-seed-directory__grid">
                        <?php foreach ($groups[$status] as $html) : ?>
                            <li class="wp-seed-directory__item"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render a Directory collection through the canonical public pipeline.
 *
 * @param array $args           Raw collection attributes.
 * @param bool  $enqueue_assets Whether public assets should be enqueued.
 *
 * @return string
 */
function wp_seed_content_render_directory_collection($args = array(), $enqueue_assets = true)
{
    if (!wp_seed_content_kit_is_module_active('directory')) {
        return '';
    }

    $normalized = wp_seed_content_directory_normalize_shortcode_atts($args);
    if (null === $normalized) {
        return '';
    }

    return wp_seed_content_render_normalized_directory_collection($normalized, $enqueue_assets);
}
