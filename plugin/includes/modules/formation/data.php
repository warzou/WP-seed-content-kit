<?php

if (!defined('ABSPATH')) { exit; }

function wp_seed_content_formation_format_date_range($start, $end)
{
    if ('' === $start || '' === $end) return '';
    $start_time = strtotime($start . ' 12:00:00');
    $end_time = strtotime($end . ' 12:00:00');
    if (!$start_time || !$end_time) return '';
    if ($start === $end) return wp_date('j F Y', $start_time);
    if (date('Y-m', $start_time) === date('Y-m', $end_time)) return wp_date('j', $start_time) . '–' . wp_date('j F Y', $end_time);
    return wp_date('j F', $start_time) . ' – ' . wp_date('j F Y', $end_time);
}

function wp_seed_content_formation_get_entity_data($post_id, $post_type, $args = array())
{
    $post = wp_seed_content_resolve_data_post($post_id, $post_type, $args);
    if (!$post) return array();
    return array_merge(wp_seed_content_get_post_data_fields($post), array(
        'summary' => (string) $post->post_excerpt,
        'description' => (string) $post->post_content,
        'display_order' => (int) $post->menu_order,
    ));
}

function wp_seed_content_formation_get_program_data($post_id, $args = array())
{
    return wp_seed_content_formation_get_entity_data($post_id, 'seed_program', $args);
}

function wp_seed_content_formation_get_theme_data($post_id, $args = array())
{
    $data = wp_seed_content_formation_get_entity_data($post_id, 'seed_theme', $args);
    if ($data) $data['image'] = wp_seed_content_get_media_data(get_post_thumbnail_id(absint($post_id)));
    return $data;
}

function wp_seed_content_formation_get_section_data($post_id, $args = array())
{
    $data = wp_seed_content_formation_get_entity_data($post_id, 'seed_section', $args);
    if ($data) $data['program_id'] = absint(get_post_meta($post_id, '_seed_formation_program_id', true));
    return $data;
}

function wp_seed_content_formation_get_schedule_data($post_id, $args = array())
{
    $post = wp_seed_content_resolve_data_post($post_id, 'seed_schedule', $args);
    if (!$post) return array();
    $program_id = absint(get_post_meta($post_id, '_seed_formation_program_id', true));
    $theme_id = absint(get_post_meta($post_id, '_seed_formation_theme_id', true));
    $section_id = absint(get_post_meta($post_id, '_seed_formation_section_id', true));
    $theme = get_post($theme_id);
    $program = wp_seed_content_formation_get_program_data($program_id);
    $section = get_post($section_id);
    $snapshot_title = (string) get_post_meta($post_id, '_seed_formation_theme_title_snapshot', true);
    $annual_title = (string) get_post_meta($post_id, '_seed_formation_annual_display_title', true);
    $section_snapshot = (string) get_post_meta($post_id, '_seed_formation_section_label_snapshot', true);
    $section_order_snapshot = get_post_meta($post_id, '_seed_formation_section_order_snapshot', true);
    $status = (string) get_post_meta($post_id, '_seed_formation_status', true);
    if (!isset(wp_seed_content_formation_statuses()[$status])) $status = 'planned';
    $person_ids = wp_seed_content_formation_sanitize_id_list(get_post_meta($post_id, '_seed_formation_directory_person_ids', true));
    $people = array();
    foreach ($person_ids as $person_id) {
        $person = wp_seed_content_get_directory_data($person_id);
        if (is_array($person) && $person) $people[] = array('id' => $person_id, 'name' => isset($person['name']) ? $person['name'] : '');
    }
    return array_merge(wp_seed_content_get_post_data_fields($post), array(
        'program_id' => $program_id, 'theme_id' => $theme_id, 'section_id' => $section_id,
        'calendar_year' => absint(get_post_meta($post_id, '_seed_formation_calendar_year', true)),
        'start_date' => wp_seed_content_formation_sanitize_iso_date(get_post_meta($post_id, '_seed_formation_start_date', true)),
        'end_date' => wp_seed_content_formation_sanitize_iso_date(get_post_meta($post_id, '_seed_formation_end_date', true)),
        'status' => $status, 'status_label' => wp_seed_content_formation_statuses()[$status],
        'original_start_date' => wp_seed_content_formation_sanitize_iso_date(get_post_meta($post_id, '_seed_formation_original_start_date', true)),
        'original_end_date' => wp_seed_content_formation_sanitize_iso_date(get_post_meta($post_id, '_seed_formation_original_end_date', true)),
        'annual_display_title' => $annual_title,
        'date_label' => wp_seed_content_formation_format_date_range(
            wp_seed_content_formation_sanitize_iso_date(get_post_meta($post_id, '_seed_formation_start_date', true)),
            wp_seed_content_formation_sanitize_iso_date(get_post_meta($post_id, '_seed_formation_end_date', true))
        ),
        'theme_label' => '' !== $snapshot_title ? $snapshot_title : ($theme instanceof WP_Post ? (string) $theme->post_title : ''),
        'program_label' => isset($program['title']) ? $program['title'] : '',
        'people_label' => implode(', ', array_column($people, 'name')),
        'display_title' => '' !== $annual_title ? $annual_title : ('' !== $snapshot_title ? $snapshot_title : ($theme instanceof WP_Post ? (string) $theme->post_title : '')),
        'theme_title_snapshot' => $snapshot_title,
        'section_label' => '' !== $section_snapshot ? $section_snapshot : ($section instanceof WP_Post ? (string) $section->post_title : ''),
        'section_label_snapshot' => $section_snapshot,
        'section_order' => '' !== (string) $section_order_snapshot ? (int) $section_order_snapshot : ($section instanceof WP_Post ? (int) $section->menu_order : 0),
        'person_ids' => $person_ids, 'people' => $people, 'display_order' => (int) $post->menu_order,
        'source_type' => 'formation', 'source_id' => (int) $post->ID, 'url' => '', 'category' => 'formation',
    ));
}
