<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['fm_posts'] = array();
$GLOBALS['fm_meta'] = array();
$GLOBALS['fm_assertions'] = 0;
$GLOBALS['fm_failures'] = array();
class Formation_Test_DB {
    public $prefix = 'synthetic_';
    public $events = array();
    public $allow_lock = true;
    public function prepare($sql, $value) { return sprintf($sql, $value); }
    public function get_var($sql) {
        $this->events[] = $sql;
        return strpos($sql, 'GET_LOCK') !== false && !$this->allow_lock ? '0' : '1';
    }
}
$GLOBALS['wpdb'] = new Formation_Test_DB();
function clean_post_cache($id) {}

class WP_Post {
    public $ID; public $post_type; public $post_status; public $post_title; public $post_excerpt = ''; public $post_content = ''; public $post_name = ''; public $menu_order = 0;
    public function __construct($id, $type, $title, $status = 'publish', $order = 0) {
        $this->ID = $id; $this->post_type = $type; $this->post_title = $title; $this->post_status = $status; $this->menu_order = $order;
    }
}
function fm_assert($condition, $label) { $GLOBALS['fm_assertions']++; if (!$condition) $GLOBALS['fm_failures'][] = $label; }
function fm_same($expected, $actual, $label) { fm_assert($expected === $actual, $label); }
function __($text, $domain = null) { return $text; }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) { $GLOBALS['fm_filters'][$hook][] = $callback; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['fm_meta'][$id]) && array_key_exists($key, $GLOBALS['fm_meta'][$id]); }
function register_post_meta($type, $key, $args) {}
function current_user_can($capability) { return empty($GLOBALS['fm_denied'][$capability]); }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function absint($value) { return max(0, (int) $value); }
function wp_parse_args($args, $defaults = array()) { return array_merge($defaults, is_array($args) ? $args : array()); }
function current_time($format) { return '2027-01-20'; }
function get_post($id) { return isset($GLOBALS['fm_posts'][$id]) ? $GLOBALS['fm_posts'][$id] : null; }
function get_post_meta($id, $key, $single = false) { return isset($GLOBALS['fm_meta'][$id][$key]) ? $GLOBALS['fm_meta'][$id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['fm_meta'][$id][$key] = $value; }
function get_post_thumbnail_id($id) { return 0; }
function wp_seed_content_get_media_data($id) { return null; }
function wp_seed_content_get_directory_data($id) { $post = get_post($id); return $post ? array('name' => $post->post_title) : array(); }
function wp_seed_content_resolve_data_post($id, $type, $args = array()) { $post = get_post($id); return $post instanceof WP_Post && $type === $post->post_type && ('publish' === $post->post_status || !empty($args['allow_unpublished'])) ? $post : null; }
function wp_seed_content_get_post_data_fields($post) { return array('id' => $post->ID, 'title' => $post->post_title, 'slug' => '', 'status' => $post->post_status, 'permalink' => ''); }
function wp_seed_content_kit_is_module_active($module) { return 'formation' === $module && empty($GLOBALS['fm_module_disabled']); }
if (!function_exists('wp_date')) { function wp_date($format, $timestamp = null) { return date($format, $timestamp === null ? time() : $timestamp); } }
function get_posts($args) {
    $result = array();
    foreach ($GLOBALS['fm_posts'] as $post) {
        if (isset($args['post_type']) && !in_array($post->post_type, (array) $args['post_type'], true)) continue;
        $statuses = isset($args['post_status']) ? (array) $args['post_status'] : array('publish');
        if (!in_array($post->post_status, $statuses, true)) continue;
        if (!empty($args['post__not_in']) && in_array($post->ID, $args['post__not_in'], true)) continue;
        if (isset($args['meta_key']) && (string) get_post_meta($post->ID, $args['meta_key'], true) !== (string) $args['meta_value']) continue;
        if (!empty($args['meta_query'])) {
            foreach ($args['meta_query'] as $query) if ((string) get_post_meta($post->ID, $query['key'], true) !== (string) $query['value']) continue 2;
        }
        $result[] = !empty($args['fields']) && 'ids' === $args['fields'] ? $post->ID : $post;
    }
    if (isset($args['posts_per_page']) && $args['posts_per_page'] > 0) return array_slice($result, 0, $args['posts_per_page']);
    return $result;
}

require dirname(__DIR__) . '/plugin/includes/modules/formation/model.php';
require dirname(__DIR__) . '/plugin/includes/modules/formation/data.php';
require dirname(__DIR__) . '/plugin/includes/modules/formation/collections.php';

$GLOBALS['fm_posts'][1] = new WP_Post(1, 'seed_program', 'Programme');
$GLOBALS['fm_posts'][2] = new WP_Post(2, 'seed_theme', 'La rencontre');
$GLOBALS['fm_posts'][3] = new WP_Post(3, 'seed_section', 'Première année', 'publish', 10);
$GLOBALS['fm_posts'][4] = new WP_Post(4, 'seed_directory', 'Sophie');
$GLOBALS['fm_meta'][3]['_seed_formation_program_id'] = 1;

$valid = array('program_id' => 1, 'theme_id' => 2, 'section_id' => 3, 'calendar_year' => 2027, 'start_date' => '2027-01-22', 'end_date' => '2027-01-24', 'status' => 'planned', 'person_ids' => array(4));
fm_same(array(), wp_seed_content_formation_validate_schedule_values($valid), 'Valid schedule');
fm_same('', wp_seed_content_formation_sanitize_iso_date('2027-02-30'), 'Impossible date rejected');
fm_same(2027, wp_seed_content_formation_relevant_calendar_year(array(2028, 2027, 2027, 2025), 2026), 'Nearest future calendar year selected');
fm_same(2025, wp_seed_content_formation_relevant_calendar_year(array(2023, 2025), 2026), 'Latest past calendar year selected');
fm_same(2026, wp_seed_content_formation_relevant_calendar_year(array(), 2026), 'Current year retained without schedules');
$invalid = $valid; $invalid['end_date'] = '2027-01-10';
fm_assert(in_array('date_order', wp_seed_content_formation_validate_schedule_values($invalid), true), 'End before start rejected');
$invalid = $valid; $invalid['calendar_year'] = 2028;
fm_assert(in_array('calendar_year_mismatch', wp_seed_content_formation_validate_schedule_values($invalid), true), 'Calendar year follows start date');

foreach (array(
    10 => array(2, 3, '2027-01-22', '2027-01-24', 'planned', '', 20),
    11 => array(5, 6, '2027-01-15', '2027-01-17', 'planned', '', 10),
    12 => array(7, 8, '2027-03-01', '2027-03-03', 'cancelled', '', 30),
) as $id => $row) {
    if (!isset($GLOBALS['fm_posts'][$row[0]])) $GLOBALS['fm_posts'][$row[0]] = new WP_Post($row[0], 'seed_theme', 'Theme ' . $row[0]);
    if (!isset($GLOBALS['fm_posts'][$row[1]])) {
        $GLOBALS['fm_posts'][$row[1]] = new WP_Post($row[1], 'seed_section', 'Section ' . $row[1], 'publish', $row[6]);
        $GLOBALS['fm_meta'][$row[1]]['_seed_formation_program_id'] = 1;
    }
    $GLOBALS['fm_posts'][$id] = new WP_Post($id, 'seed_schedule', 'Schedule', 'publish', $row[6]);
    $GLOBALS['fm_meta'][$id] = array(
        '_seed_formation_program_id' => 1, '_seed_formation_theme_id' => $row[0], '_seed_formation_section_id' => $row[1],
        '_seed_formation_calendar_year' => 2027, '_seed_formation_start_date' => $row[2], '_seed_formation_end_date' => $row[3],
        '_seed_formation_status' => $row[4], '_seed_formation_annual_display_title' => $row[5],
        '_seed_formation_directory_person_ids' => array(4), '_seed_formation_section_order_snapshot' => $row[6],
        '_seed_formation_section_label_snapshot' => 'Section ' . $row[1], '_seed_formation_theme_title_snapshot' => 'Theme ' . $row[0],
    );
}
fm_assert(wp_seed_content_formation_schedule_exists(1, 2027, 2, 3, '2027-01-22', '2027-01-24'), 'Uniqueness sees existing dated schedule');
fm_assert(!wp_seed_content_formation_schedule_exists(1, 2027, 2, 3, '2027-01-22', '2027-01-24', 10), 'Uniqueness excludes current schedule');
$items = wp_seed_content_formation_get_schedules(array('program_id' => 1, 'year' => 2027));
fm_same(array(11, 10), array_column($items, 'id'), 'Chronological stable order excludes cancelled');
$groups = wp_seed_content_formation_get_grouped_calendar(array('program_id' => 1, 'year' => 2027));
fm_same(array(6, 3), array_column($groups, 'section_id'), 'Groups use historical section order');
fm_same(10, wp_seed_content_formation_get_next_schedule(3, array('program_id' => 1, 'today' => '2027-01-20'))['id'], 'Next schedule by section');
$structure = wp_seed_content_formation_get_structure(array('program_id' => 1, 'year' => 2027));
fm_assert(!isset($structure[0]['items'][0]['start_date']), 'Structure projection contains no dates');
fm_same(array(6, 3, 8), array_column($structure, 'section_id'), 'Structure retains cancelled themes');
fm_same('Theme 7', $structure[2]['items'][0]['title'], 'Structure uses pedagogical theme title');

$GLOBALS['fm_meta'][10]['_seed_formation_original_start_date'] = '';
$GLOBALS['fm_meta'][10]['_seed_formation_original_end_date'] = '';
wp_seed_content_formation_preserve_original_dates(10, 'planned', 'postponed', '2027-01-22', '2027-01-24');
wp_seed_content_formation_preserve_original_dates(10, 'postponed', 'postponed', '2027-02-01', '2027-02-03');
fm_same('2027-01-22', $GLOBALS['fm_meta'][10]['_seed_formation_original_start_date'], 'Original start immutable after first postponement');

if ($GLOBALS['fm_failures']) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['fm_failures']) . ' / ' . $GLOBALS['fm_assertions'] . PHP_EOL . implode(PHP_EOL, $GLOBALS['fm_failures']) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $GLOBALS['fm_assertions'] . ' Formation model assertions' . PHP_EOL;
