<?php
// Explicit fixture environment only. Never run automatically on a public request.
if (!defined('WP_CLI') || !WP_CLI || !defined('WPSCK_FORMATION_TRANSACTIONAL_TEST')) return;
global $wpdb;
if (!class_exists('WP_Seed_Content_Divi_Dynamic_Content_Formation_Field')) throw new RuntimeException('Formation Divi provider not loaded');
$ids = array(); $checks = array();
$options = wp_seed_content_kit_get_module_options();
$disabled = function () use ($options) { $options['formation'] = false; return $options; };
foreach ($wpdb->get_results('SHOW TABLE STATUS', ARRAY_A) as $table) {
    if (in_array($table['Name'], array($wpdb->posts, $wpdb->postmeta), true) && strtolower($table['Engine']) !== 'innodb') throw new RuntimeException('Transactional tables required');
}
$wpdb->query('START TRANSACTION');
try {
    foreach (array('seed_program', 'seed_section', 'seed_theme', 'seed_schedule') as $type) {
        $id = wp_insert_post(array('post_type' => $type, 'post_status' => 'publish', 'post_title' => 'WPSCK TEMP calendar integration'), true);
        if (is_wp_error($id)) throw new RuntimeException('Fixture insert failed');
        $ids[$type] = $id;
    }
    $schedule = $ids['seed_schedule'];
    foreach (array('program_id' => $ids['seed_program'], 'section_id' => $ids['seed_section'], 'theme_id' => $ids['seed_theme'],
        'calendar_year' => 2027, 'start_date' => '2027-01-20', 'end_date' => '2027-01-21', 'status' => 'planned', 'annual_display_title' => 'Before') as $key => $value) update_post_meta($schedule, '_seed_formation_' . $key, $value);
    update_post_meta($ids['seed_section'], '_seed_formation_program_id', $ids['seed_program']);
    $name = 'wpsck_formation_schedule_title';
    $provider = new WP_Seed_Content_Divi_Dynamic_Content_Formation_Field($name, wp_seed_content_divi_formation_fields()[$name]);
    $args = array('name' => 'loop_' . $name, 'loop_id' => $schedule, 'post_id' => 0);
    $checks['enabled'] = isset($provider->register_option_callback(array(), 0, '')['loop_' . $name]);
    $checks['before'] = strpos($provider->render_callback('', $args), 'Before') !== false;
    update_post_meta($schedule, '_seed_formation_annual_display_title', 'After');
    $checks['dynamic_update'] = strpos($provider->render_callback('', $args), 'After') !== false;
    $query = wp_seed_content_divi_formation_query(array('post_type' => 'seed_schedule', 'meta_query' => array(
        'relation' => 'OR',
        array('key' => '_seed_formation_program_id', 'value' => $ids['seed_program']),
        array('key' => '_seed_formation_calendar_year', 'value' => 2027),
        array('key' => '_seed_formation_section_id', 'value' => $ids['seed_section']),
    )));
    $checks['filtered_query'] = $query['post__in'] === array($schedule);
    $checks['divi_or_normalized'] = $query['meta_query']['relation'] === 'AND';
    $checks['sql_filtered_query'] = get_posts(array_merge($query, array('fields' => 'ids', 'posts_per_page' => -1))) === array($schedule);
    $other_query = $query;
    $other_query['meta_query'][0]['value'] = $ids['seed_program'] + 1000000;
    unset($other_query['post__in']);
    $checks['cross_program_empty'] = wp_seed_content_divi_formation_query($other_query)['post__in'] === array(0);
    $date_name = 'wpsck_formation_schedule_date';
    $date_provider = new WP_Seed_Content_Divi_Dynamic_Content_Formation_Field($date_name, wp_seed_content_divi_formation_fields()[$date_name]);
    $date_args = array('name' => 'loop_' . $date_name, 'loop_id' => $schedule, 'post_id' => 0);
    $date_before = $date_provider->render_callback('', $date_args);
    update_post_meta($schedule, '_seed_formation_start_date', '2027-02-20');
    update_post_meta($schedule, '_seed_formation_end_date', '2027-02-21');
    $checks['date_update'] = $date_before !== $date_provider->render_callback('', $date_args);
    $checks['date_update_same_card'] = wp_seed_content_divi_formation_query($query)['post__in'] === array($schedule);
    add_filter('pre_option_wp_seed_content_kit_modules', $disabled);
    $checks['disabled'] = array() === $provider->register_option_callback(array(), 0, '');
    $checks['data_preserved'] = get_post_meta($schedule, '_seed_formation_annual_display_title', true) === 'After';
    remove_filter('pre_option_wp_seed_content_kit_modules', $disabled);
    $checks['reenabled'] = isset($provider->register_option_callback(array(), 0, '')['loop_' . $name]);
} finally {
    remove_filter('pre_option_wp_seed_content_kit_modules', $disabled);
    $wpdb->query('ROLLBACK');
    foreach ($ids as $id) clean_post_cache($id);
}
foreach ($ids as $id) $checks['fixture_absent_' . $id] = !$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d", $id));
echo wp_json_encode($checks, JSON_PRETTY_PRINT) . PHP_EOL;
if (in_array(false, $checks, true)) throw new RuntimeException('Calendar integration fixture checks failed');
