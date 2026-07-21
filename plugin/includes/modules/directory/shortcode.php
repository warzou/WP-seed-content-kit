<?php

if (!defined('ABSPATH')) {
    exit;
}

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

function wp_seed_content_directory_normalize_shortcode_atts($atts)
{
    $atts = shortcode_atts(array(
        'status' => 'all',
        'department' => '',
        'country' => '',
        'featured' => 'all',
        'limit' => '0',
        'orderby' => 'display_order',
        'order' => 'asc',
        'ids' => '',
        'template' => '',
    ), $atts, 'seed_directory');

    $limit_raw = trim(sanitize_text_field((string) $atts['limit']));
    if (!preg_match('/^\d+$/D', $limit_raw)) {
        return null;
    }
    $ids = wp_seed_content_directory_parse_shortcode_ids($atts['ids']);
    if (null === $ids) {
        return null;
    }

    $template_raw = is_scalar($atts['template']) ? trim((string) $atts['template']) : '';
    $template = sanitize_title($template_raw);
    if ('' !== $template_raw && '' === $template) {
        return null;
    }

    $args = array(
        'status' => strtolower(sanitize_key($atts['status'])),
        'department' => sanitize_text_field($atts['department']),
        'country' => sanitize_text_field($atts['country']),
        'featured' => strtolower(sanitize_key($atts['featured'])),
        'limit' => min(100, (int) $limit_raw),
        'orderby' => strtolower(sanitize_key($atts['orderby'])),
        'order' => strtolower(sanitize_key($atts['order'])),
        'ids' => $ids,
    );
    if (null === wp_seed_content_directory_normalize_collection_args($args)) {
        return null;
    }

    return array('collection' => $args, 'template' => $template);
}

function wp_seed_content_directory_shortcode($atts = array())
{
    if (!wp_seed_content_kit_is_module_active('directory')) {
        return '';
    }

    $normalized = wp_seed_content_directory_normalize_shortcode_atts($atts);
    if (null === $normalized) {
        return '';
    }

    $ids = wp_seed_content_directory_get_entries($normalized['collection']);
    if (empty($ids)) {
        wp_seed_content_directory_enqueue_structure_assets();
        return '<div class="wp-seed-directory"><p class="wp-seed-directory__empty">' . esc_html__('Aucune fiche n’est disponible pour le moment.', 'wp-seed-content-kit') . '</p></div>';
    }

    if (function_exists('update_meta_cache')) {
        update_meta_cache('post', $ids);
    }

    $groups = array('practicing' => array(), 'seeking_models' => array());
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

    if (empty($groups['practicing']) && empty($groups['seeking_models'])) {
        wp_seed_content_directory_enqueue_structure_assets();
        return '<div class="wp-seed-directory"><p class="wp-seed-directory__empty">' . esc_html__('Aucune fiche n’est disponible pour le moment.', 'wp-seed-content-kit') . '</p></div>';
    }

    wp_seed_content_directory_enqueue_structure_assets();
    if ($native_rendered) {
        wp_seed_content_directory_enqueue_native_card_assets();
    }

    $labels = array(
        'practicing' => __('En exercice', 'wp-seed-content-kit'),
        'seeking_models' => __('En recherche de modèles', 'wp-seed-content-kit'),
    );

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
add_shortcode('seed_directory', 'wp_seed_content_directory_shortcode');
add_shortcode('wp_seed_directory', 'wp_seed_content_directory_shortcode');
