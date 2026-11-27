<?php

if (!defined('ABSPATH')) { exit; }

function wp_seed_content_formation_get_schedules($args = array())
{
    if (!wp_seed_content_kit_is_module_active('formation')) return array();
    $args = wp_parse_args(is_array($args) ? $args : array(), array(
        'program_id' => 0, 'year' => 0, 'theme_id' => 0, 'section_id' => 0, 'person_id' => 0,
        'include_cancelled' => false, 'upcoming' => false, 'today' => current_time('Y-m-d'),
        'status' => '', 'orderby' => 'start_date', 'order' => 'ASC',
    ));
    if (!is_string($args['status']) || ('' !== $args['status'] && !isset(wp_seed_content_formation_statuses()[$args['status']]))) return array();
    $query = array(
        'post_type' => 'seed_schedule', 'post_status' => 'publish', 'posts_per_page' => -1,
        'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'suppress_filters' => true,
        'update_post_meta_cache' => true, 'update_post_term_cache' => false,
    );
    foreach (array('program_id', 'theme_id', 'section_id', 'year') as $filter) {
        if (!empty($args[$filter])) $query['meta_query'][] = array(
            'key' => '_seed_formation_' . ('year' === $filter ? 'calendar_year' : $filter),
            'value' => absint($args[$filter]), 'compare' => '=', 'type' => 'NUMERIC',
        );
    }
    $posts = get_posts($query);
    if (function_exists('_prime_post_caches')) {
        $related = array();
        foreach ($posts as $post) foreach (array('program_id', 'theme_id', 'section_id') as $key) {
            $related[] = absint(get_post_meta($post->ID, '_seed_formation_' . $key, true));
        }
        foreach ($posts as $post) $related = array_merge($related, wp_seed_content_formation_sanitize_id_list(
            get_post_meta($post->ID, '_seed_formation_directory_person_ids', true)
        ));
        _prime_post_caches(array_values(array_unique(array_filter($related))), false, true);
    }
    $items = array();
    foreach ($posts as $post) {
        $item = wp_seed_content_formation_get_schedule_data($post->ID);
        if (!$item) continue;
        if ('' !== $args['status'] && $args['status'] !== $item['status']) continue;
        if ('' === $args['status'] && !$args['include_cancelled'] && 'cancelled' === $item['status']) continue;
        if ($args['program_id'] && absint($args['program_id']) !== $item['program_id']) continue;
        if ($args['year'] && absint($args['year']) !== $item['calendar_year']) continue;
        if ($args['theme_id'] && absint($args['theme_id']) !== $item['theme_id']) continue;
        if ($args['section_id'] && absint($args['section_id']) !== $item['section_id']) continue;
        if ($args['person_id'] && !in_array(absint($args['person_id']), $item['person_ids'], true)) continue;
        if ($args['upcoming'] && $item['end_date'] < wp_seed_content_formation_sanitize_iso_date($args['today'])) continue;
        $items[] = $item;
    }
    $direction = 'DESC' === strtoupper((string) $args['order']) ? -1 : 1;
    usort($items, function ($left, $right) use ($args, $direction) {
        if ('display_order' === $args['orderby'] && $left['display_order'] !== $right['display_order']) {
            return $direction * ($left['display_order'] <=> $right['display_order']);
        }
        foreach (array('start_date', 'end_date') as $key) {
            $comparison = strcmp($left[$key], $right[$key]);
            if (0 !== $comparison) return $direction * $comparison;
        }
        if ($left['display_order'] !== $right['display_order']) return $left['display_order'] <=> $right['display_order'];
        return $left['id'] <=> $right['id'];
    });
    return $items;
}

function wp_seed_content_formation_get_grouped_calendar($args = array())
{
    $groups = array();
    foreach (wp_seed_content_formation_get_schedules($args) as $item) {
        $key = (string) $item['section_id'];
        if (!isset($groups[$key])) $groups[$key] = array('section_id' => $item['section_id'], 'label' => $item['section_label'], 'order' => $item['section_order'], 'items' => array());
        $groups[$key]['items'][] = $item;
    }
    uasort($groups, function ($left, $right) {
        if ($left['order'] !== $right['order']) return $left['order'] <=> $right['order'];
        return $left['section_id'] <=> $right['section_id'];
    });
    foreach ($groups as &$group) {
        usort($group['items'], function ($left, $right) {
            if ($left['display_order'] !== $right['display_order']) return $left['display_order'] <=> $right['display_order'];
            $comparison = strcmp($left['start_date'], $right['start_date']);
            return 0 !== $comparison ? $comparison : $left['id'] <=> $right['id'];
        });
    }
    unset($group);
    return array_values($groups);
}

function wp_seed_content_formation_get_structure($args = array())
{
    $args = array_merge(is_array($args) ? $args : array(), array('include_cancelled' => true));
    $groups = wp_seed_content_formation_get_grouped_calendar($args);
    foreach ($groups as &$group) {
        $themes = array();
        foreach ($group['items'] as $item) {
            $key = $item['program_id'] . ':' . $item['calendar_year'] . ':' . $item['section_id'] . ':' . $item['theme_id'];
            if (!isset($themes[$key]) || $item['display_order'] < $themes[$key]['display_order'] ||
                ($item['display_order'] === $themes[$key]['display_order'] && $item['id'] < $themes[$key]['id'])) {
                $themes[$key] = $item;
            }
        }
        $group['items'] = array_map(function ($item) {
            $title = '' !== $item['theme_title_snapshot'] ? $item['theme_title_snapshot'] : $item['display_title'];
            return array('id' => $item['id'], 'theme_id' => $item['theme_id'], 'title' => $title, 'display_order' => $item['display_order']);
        }, array_values($themes));
    }
    unset($group);
    return $groups;
}

function wp_seed_content_formation_get_next_schedule($section_id, $args = array())
{
    $args = array_merge(is_array($args) ? $args : array(), array('section_id' => absint($section_id), 'upcoming' => true));
    $items = wp_seed_content_formation_get_schedules($args);
    return $items ? $items[0] : array();
}
