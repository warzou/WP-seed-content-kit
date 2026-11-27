<?php
/** Builder-owned calendar filters; canonical Formation records are read-only. */
if (!defined('ABSPATH')) { exit; }

function wp_seed_content_divi_calendar_filter_values($value)
{
    if (!is_array($value) || !isset($value['enabled']) || 'on' !== $value['enabled']) return null;
    $result = array();
    foreach (array('program', 'year', 'section', 'theme') as $key) {
        $raw = isset($value[$key]) ? $value[$key] : '';
        if (!is_scalar($raw) || !preg_match('/^(?:[0-9]+)?$/D', (string) $raw)) return false;
        $result[$key] = (int) $raw;
    }
    $result['status'] = isset($value['status']) ? $value['status'] : '';
    $result['sort'] = isset($value['sort']) ? $value['sort'] : 'date_asc';
    if (!is_string($result['status']) || ('' !== $result['status'] && !isset(wp_seed_content_formation_statuses()[$result['status']]))) return false;
    if (!in_array($result['sort'], array('date_asc', 'date_desc', 'display_order'), true)) return false;
    if (!$result['program'] || 'seed_program' !== get_post_type($result['program'])) return false;
    if ($result['year'] && ($result['year'] < 1900 || $result['year'] > 2200)) return false;
    if ($result['section'] && ('seed_section' !== get_post_type($result['section'])
        || (int) get_post_meta($result['section'], '_seed_formation_program_id', true) !== $result['program'])) return false;
    if ($result['theme'] && 'seed_theme' !== get_post_type($result['theme'])) return false;
    return $result;
}

function wp_seed_content_divi_calendar_filter_query($query, $value)
{
    $filters = wp_seed_content_divi_calendar_filter_values($value);
    if (null === $filters) return $query;
    if (false === $filters) { $query['post__in'] = array(0); return $query; }
    $map = array('program' => 'program_id', 'year' => 'calendar_year', 'section' => 'section_id', 'theme' => 'theme_id', 'status' => 'status');
    $keys = array_map(function ($key) { return '_seed_formation_' . $key; }, array_values($map));
    $legacy = isset($query['meta_query']) ? $query['meta_query'] : array();
    $remaining = array();
    // Replace only simple, recognized dimensions. Never silently drop a complex user query.
    foreach ($legacy as $key => $clause) {
        if ('relation' === $key) continue;
        if (is_array($clause) && isset($clause['key']) && in_array($clause['key'], $keys, true)
            && (!isset($clause['compare']) || '=' === $clause['compare']) && isset($clause['value']) && is_scalar($clause['value'])) continue;
        $remaining[] = $clause;
    }
    $meta = array('relation' => 'AND');
    if ($remaining) {
        $remaining['relation'] = isset($legacy['relation']) ? $legacy['relation'] : 'AND';
        $meta[] = $remaining;
    }
    foreach ($map as $key => $suffix) if ('' !== $filters[$key] && 0 !== $filters[$key]) {
        $meta[] = array('key' => '_seed_formation_' . $suffix, 'value' => $filters[$key], 'compare' => '=', 'type' => 'status' === $key ? 'CHAR' : 'NUMERIC');
    }
    $query['meta_query'] = $meta;
    $query['orderby'] = 'display_order' === $filters['sort'] ? 'wpsck_schedule_order' : 'wpsck_schedule_date';
    $query['order'] = 'date_desc' === $filters['sort'] ? 'DESC' : 'ASC';
    return $query;
}

function wp_seed_content_divi_calendar_filter_options()
{
    $data = array('program' => array(), 'section' => array(), 'theme' => array(), 'year' => array(), 'status' => wp_seed_content_formation_statuses());
    foreach (array('program', 'section', 'theme') as $kind) {
        foreach (get_posts(array('post_type' => 'seed_' . $kind, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC', 'suppress_filters' => true)) as $post) {
            $entry = array('id' => (string) $post->ID, 'label' => $post->post_title);
            if ('section' === $kind) $entry['program'] = (string) get_post_meta($post->ID, '_seed_formation_program_id', true);
            $data[$kind][] = $entry;
        }
    }
    foreach (wp_seed_content_formation_get_schedules(array('include_cancelled' => true)) as $item) {
        $year = (string) $item['calendar_year'];
        $data['year'][$year] = $year;
    }
    ksort($data['year'], SORT_NUMERIC);
    return $data;
}

function wp_seed_content_divi_calendar_filter_assets()
{
    if (!wp_seed_content_kit_is_module_available_for_discovery('formation') || !current_user_can('edit_posts')
        || !function_exists('et_core_is_fb_enabled') || !et_core_is_fb_enabled()
        || !class_exists('ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager')) return;
    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(array(
        'name' => 'wpsck-calendar-filters',
        'version' => substr(hash_file('sha256', __DIR__ . '/formation-calendar-filters.js'), 0, 16),
        'script' => array(
            'src' => WP_SEED_CONTENT_KIT_URL . 'includes/integrations/divi/formation-calendar-filters.js',
            'deps' => array('divi-vendor-wp-hooks', 'divi-field-library'),
            'enqueue_top_window' => false, 'enqueue_app_window' => true,
            'args' => array('in_footer' => false),
            'data_app_window' => wp_seed_content_divi_calendar_filter_options(),
        ),
    ));
}
add_action('divi_visual_builder_assets_before_enqueue_scripts', 'wp_seed_content_divi_calendar_filter_assets');

function wp_seed_content_divi_calendar_filter_metadata($settings)
{
    if (isset($settings['name']) && in_array($settings['name'], array('divi/section', 'divi/row', 'divi/column', 'divi/group'), true)) {
        $settings['attributes']['wpsckCalendarFilters'] = array('type' => 'object');
    }
    return $settings;
}
add_filter('block_type_metadata_settings', 'wp_seed_content_divi_calendar_filter_metadata');
