<?php

// Characterize the current runtime without changing its validator or storage.
require __DIR__ . '/formation-model-harness.php';
function add_shortcode($name, $callback) {}
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return esc_attr($value); }
function wp_date($format, $timestamp) { return date($format, $timestamp); }
function wp_list_pluck($items, $key) { return array_column($items, $key); }
function shortcode_atts($defaults, $args, $name = '') { return array_merge($defaults, $args); }
require dirname(__DIR__) . '/plugin/includes/modules/formation/shortcode.php';
$GLOBALS['fm_posts'] = array();
$GLOBALS['fm_meta'] = array();
$GLOBALS['fm_assertions'] = 0;
$GLOBALS['fm_failures'] = array();
foreach (array(1 => 'seed_program', 2 => 'seed_theme', 3 => 'seed_section', 4 => 'seed_directory', 5 => 'seed_program', 6 => 'seed_section', 7 => 'seed_theme') as $id => $type) {
    $GLOBALS['fm_posts'][$id] = new WP_Post($id, $type, 'Synthetic ' . $id);
}
$GLOBALS['fm_meta'][3]['_seed_formation_program_id'] = 1;
$GLOBALS['fm_meta'][6]['_seed_formation_program_id'] = 5;

function fma_values($year, $date, $theme = 2, $program = 1, $section = 3) {
    return array('program_id' => $program, 'theme_id' => $theme, 'section_id' => $section, 'calendar_year' => $year, 'start_date' => $date, 'end_date' => $date, 'status' => 'planned', 'person_ids' => array(4));
}
function fma_seed($id, $values) {
    // In-memory fixture injection tests the read pipeline, NOT successful CRUD.
    $GLOBALS['fm_posts'][$id] = new WP_Post($id, 'seed_schedule', 'Synthetic schedule');
    foreach (array('program_id', 'theme_id', 'section_id', 'calendar_year', 'start_date', 'end_date', 'status') as $field) {
        $GLOBALS['fm_meta'][$id]['_seed_formation_' . $field] = $values[$field];
    }
    $GLOBALS['fm_meta'][$id]['_seed_formation_theme_title_snapshot'] = 'Supervision';
    $GLOBALS['fm_meta'][$id]['_seed_formation_section_label_snapshot'] = 'Supervision section';
    $GLOBALS['fm_meta'][$id]['_seed_formation_section_order_snapshot'] = 10;
    $GLOBALS['fm_meta'][$id]['_seed_formation_directory_person_ids'] = array(4);
}
$dates = array(
    2026 => array('01-15', '02-26', '04-09', '05-21', '07-02', '10-15', '12-03'),
    2027 => array('01-21', '02-25', '04-08', '05-20', '06-17', '10-14', '12-02'),
);
$first = fma_values(2026, '2026-01-15');
fm_same(array(), wp_seed_content_formation_validate_schedule_values($first), 'First schedule valid');
foreach ($dates as $year => $days) {
    foreach ($days as $i => $day) fma_seed(($year === 2026 ? 100 : 110) + $i, fma_values($year, $year . '-' . $day));
}
$gaps = array();
$gaps['different_dates_rejected'] = in_array('duplicate_schedule', wp_seed_content_formation_validate_schedule_values(fma_values(2026, '2026-08-01')), true);
fm_same(false, $gaps['different_dates_rejected'], 'Different dates now accepted');
fm_assert(in_array('duplicate_schedule', wp_seed_content_formation_validate_schedule_values($first), true), 'Exact duplicate remains rejected');
$gaps['self_update_with_same_theme_peers_rejected'] = in_array('duplicate_schedule', wp_seed_content_formation_validate_schedule_values($first, 100), true);
fm_same(false, $gaps['self_update_with_same_theme_peers_rejected'], 'Self-update with peers now accepted');
$args = array('program_id' => 1, 'year' => 2026, 'theme_id' => 2);
$items = wp_seed_content_formation_get_schedules($args);
fm_same(range(100, 106), array_column($items, 'id'), 'Seven same-theme dates read chronologically');
$groups = wp_seed_content_formation_get_grouped_calendar($args);
fm_same(1, count($groups), 'One section');
fm_same(7, count($groups[0]['items']), 'Grouped retains seven schedules');
$structure = wp_seed_content_formation_get_structure($args);
$gaps['structure_rows_for_one_theme'] = count($structure[0]['items']);
fm_same(1, $gaps['structure_rows_for_one_theme'], 'Structure deduplicates same theme');
fm_assert(!isset($structure[0]['items'][0]['start_date']), 'Structure remains dateless');
fm_same(7, count(wp_seed_content_formation_get_schedules(array('program_id' => 1, 'year' => 2027))), '2027 isolated with seven schedules');
fm_same(array(), wp_seed_content_formation_get_schedules(array('program_id' => 1, 'year' => 2028)), 'Absent year empty');
$other = fma_values(2026, '2026-01-15', 2, 5, 6);
fm_same(array(), wp_seed_content_formation_validate_schedule_values($other), 'Same theme/date in other program valid');
fma_seed(200, $other);
fm_same(7, count(wp_seed_content_formation_get_schedules($args)), 'Other program not leaked');
fm_same(array(), wp_seed_content_formation_validate_schedule_values($other, 200), 'Single schedule self-update excluded from collision');
$GLOBALS['fm_meta'][200]['_seed_formation_status'] = 'cancelled';
fm_assert(in_array('duplicate_schedule', wp_seed_content_formation_validate_schedule_values($other), true), 'Cancelled schedule reserves identity');
$GLOBALS['fm_posts'][200]->post_status = 'trash';
fm_same(array(), wp_seed_content_formation_validate_schedule_values($other), 'Trashed schedule excluded from collision');

fm_same(101, wp_seed_content_formation_get_next_schedule(3, array_merge($args, array('today' => '2026-02-01')))['id'], 'Next ignores past schedule');
$GLOBALS['fm_meta'][101]['_seed_formation_status'] = 'cancelled';
fm_same(102, wp_seed_content_formation_get_next_schedule(3, array_merge($args, array('today' => '2026-02-01')))['id'], 'Next skips cancelled');
wp_seed_content_formation_preserve_original_dates(100, 'planned', 'postponed', '2026-01-15', '2026-01-15');
$GLOBALS['fm_meta'][100]['_seed_formation_status'] = 'postponed';
$GLOBALS['fm_meta'][100]['_seed_formation_start_date'] = '2026-02-27';
$GLOBALS['fm_meta'][100]['_seed_formation_end_date'] = '2026-02-27';
fm_same(100, wp_seed_content_formation_get_next_schedule(3, array_merge($args, array('today' => '2026-02-01')))['id'], 'Next uses new postponed date');
fm_same('2026-01-15', wp_seed_content_formation_get_schedule_data(100)['original_start_date'], 'Original dates retained per schedule');
fm_same('', wp_seed_content_formation_get_schedule_data(102)['original_start_date'], 'Other schedule history untouched');
$GLOBALS['fm_meta'][102]['_seed_formation_end_date'] = '2026-04-10';
fm_same(102, wp_seed_content_formation_get_next_schedule(3, array_merge($args, array('today' => '2026-04-10')))['id'], 'Ongoing schedule remains next through end date');
fm_same(array(), wp_seed_content_formation_get_next_schedule(3, array_merge($args, array('today' => '2026-12-04'))), 'After last schedule no next');
fm_same(110, wp_seed_content_formation_get_next_schedule(3, array('program_id' => 1, 'year' => 2027, 'today' => '2026-12-04'))['id'], 'Explicit next year independent');
fm_same(7, count(wp_seed_content_formation_get_schedules(array_merge($args, array('include_cancelled' => true)))), 'Historical cancelled still readable');
$GLOBALS['fm_posts'][2]->post_title = 'Renamed canonical theme';
fm_same('Supervision', wp_seed_content_formation_get_schedule_data(100)['display_title'], 'Snapshot shields historical display');
$GLOBALS['fm_meta'][100]['_seed_formation_annual_display_title'] = 'Annual label';
fm_same('Annual label', wp_seed_content_formation_get_schedule_data(100)['display_title'], 'Annual label per schedule');
fm_same('Supervision', wp_seed_content_formation_get_schedule_data(110)['display_title'], '2027 label unchanged');
fm_same(array(4), wp_seed_content_formation_get_schedule_data(100)['person_ids'], 'People belong to schedule');

fma_seed(300, fma_values(2027, '2027-12-17', 7));
fma_seed(301, fma_values(2027, '2027-12-19', 7));
$separated = wp_seed_content_formation_get_schedules(array('program_id' => 1, 'year' => 2027, 'theme_id' => 7));
fm_same(array('2027-12-17', '2027-12-19'), array_column($separated, 'start_date'), 'Discontinuous days preserved');
fm_same(array('2027-12-17', '2027-12-19'), array_column($separated, 'end_date'), 'No implicit December 18');
fm_same(301, wp_seed_content_formation_get_next_schedule(3, array('program_id' => 1, 'year' => 2027, 'theme_id' => 7, 'today' => '2027-12-18'))['id'], 'Next between separated dates selects second');
$html = wp_seed_content_formation_shortcode(array('program' => 1, 'year' => 2027, 'mode' => 'calendar'));
fm_same(9, substr_count($html, '<time '), 'Frontend emits each schedule once');
fm_assert(false === strpos($html, 'datetime="2027-12-18"'), 'Frontend does not invent missing day');
fm_same(1, substr_count($html, 'datetime="2027-12-17"'), 'First separated day rendered once');
fm_same(1, substr_count($html, 'datetime="2027-12-19"'), 'Second separated day rendered once');
echo json_encode(array('assertions' => $GLOBALS['fm_assertions'], 'failures' => $GLOBALS['fm_failures'], 'observed_gaps' => $gaps, 'storage_support_claimed' => false), JSON_PRETTY_PRINT) . PHP_EOL;
exit($GLOBALS['fm_failures'] ? 1 : 0);
