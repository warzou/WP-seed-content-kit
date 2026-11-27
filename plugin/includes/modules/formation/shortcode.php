<?php

if (!defined('ABSPATH')) { exit; }

function wp_seed_content_formation_heading_tag($level)
{
    $level = absint($level);
    return 'h' . ($level >= 2 && $level <= 6 ? $level : 2);
}

function wp_seed_content_formation_render_item($item, $show_section, $heading_level = 3)
{
    $heading_tag = wp_seed_content_formation_heading_tag($heading_level);
    $html = '<article class="wpsck-formation__item">';
    $html .= '<p class="wpsck-formation__date"><time datetime="' . esc_attr($item['start_date']) . '">' . esc_html(wp_seed_content_formation_format_date_range($item['start_date'], $item['end_date'])) . '</time></p>';
    $html .= '<' . $heading_tag . ' class="wpsck-formation__title">' . esc_html($item['display_title']) . '</' . $heading_tag . '>';
    if ($show_section) $html .= '<p class="wpsck-formation__section">' . esc_html($item['section_label']) . '</p>';
    if ($item['people']) $html .= '<p class="wpsck-formation__people">' . esc_html(implode(', ', wp_list_pluck($item['people'], 'name'))) . '</p>';
    if ('postponed' === $item['status']) $html .= '<p class="wpsck-formation__status"><strong>' . esc_html($item['status_label']) . '</strong></p>';
    return $html . '</article>';
}

function wp_seed_content_formation_enqueue_native_assets()
{
    if (!function_exists('wp_enqueue_style')) return;
    wp_enqueue_style('wpsck-formation-native', WP_SEED_CONTENT_KIT_URL . 'assets/css/formation-native.css', array(), WP_SEED_CONTENT_KIT_VERSION . '-formation-v3');
}

function wp_seed_content_formation_shortcode($attributes)
{
    if (!wp_seed_content_kit_is_module_active('formation')) return '';
    $attributes = shortcode_atts(array('program' => 0, 'year' => 0, 'mode' => 'grouped', 'heading_level' => 2), $attributes, 'seed_program_schedule');
    $program_id = absint($attributes['program']);
    $year = absint($attributes['year']);
    $mode = sanitize_key($attributes['mode']);
    $heading_level = min(6, max(2, absint($attributes['heading_level'])));
    if (!$program_id || !$year || !in_array($mode, array('structure', 'grouped', 'calendar'), true)) return '';
    wp_seed_content_formation_enqueue_native_assets();
    $args = array('program_id' => $program_id, 'year' => $year);
    $html = '<div class="wpsck-formation wpsck-formation--' . esc_attr($mode) . '">';
    if ('calendar' === $mode) {
        $html .= '<ol class="wpsck-formation__list">';
        foreach (wp_seed_content_formation_get_schedules($args) as $item) $html .= '<li>' . wp_seed_content_formation_render_item($item, true, $heading_level) . '</li>';
        $html .= '</ol>';
    } else {
        $groups = 'structure' === $mode ? wp_seed_content_formation_get_structure($args) : wp_seed_content_formation_get_grouped_calendar($args);
        foreach ($groups as $group) {
            $group_heading = wp_seed_content_formation_heading_tag($heading_level);
            $item_heading_level = min(6, $heading_level + 1);
            $html .= '<section class="wpsck-formation__group"><' . $group_heading . ' class="wpsck-formation__group-title">' . esc_html($group['label']) . '</' . $group_heading . '>';
            if ('structure' === $mode) {
                $html .= '<ul class="wpsck-formation__theme-list">';
                foreach ($group['items'] as $item) $html .= '<li>' . esc_html($item['title']) . '</li>';
                $html .= '</ul>';
            } else {
                $html .= '<ol class="wpsck-formation__list">';
                foreach ($group['items'] as $item) $html .= '<li>' . wp_seed_content_formation_render_item($item, false, $item_heading_level) . '</li>';
                $html .= '</ol>';
            }
            $html .= '</section>';
        }
    }
    return $html . '</div>';
}
add_shortcode('seed_program_schedule', 'wp_seed_content_formation_shortcode');
