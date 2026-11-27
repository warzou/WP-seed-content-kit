<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['formation_ux_assertions'] = 0;
$GLOBALS['formation_ux_failures'] = array();

function formation_ux_assert($condition, $label)
{
    $GLOBALS['formation_ux_assertions']++;
    if (!$condition) $GLOBALS['formation_ux_failures'][] = $label;
}
function add_shortcode($name, $callback) {}
function absint($value) { return max(0, (int) $value); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function wp_date($format, $timestamp) { return date($format, $timestamp); }
function wp_list_pluck($items, $key) { return array_map(function ($item) use ($key) { return $item[$key]; }, $items); }

require dirname(__DIR__) . '/plugin/includes/modules/formation/data.php';
require dirname(__DIR__) . '/plugin/includes/modules/formation/shortcode.php';

formation_ux_assert('h2' === wp_seed_content_formation_heading_tag(1), 'Heading level clamps below H2');
formation_ux_assert('h6' === wp_seed_content_formation_heading_tag(6), 'Heading level supports H6');
$item = array(
    'start_date' => '2027-02-22', 'end_date' => '2027-02-24', 'display_title' => 'La rencontre',
    'section_label' => 'Première année', 'people' => array(array('name' => 'Marie-Odile Houver')),
    'status' => 'postponed', 'status_label' => 'Reporté',
);
$html = wp_seed_content_formation_render_item($item, true, 4);
formation_ux_assert(false !== strpos($html, '<article class="wpsck-formation__item">'), 'Schedule uses article semantics');
formation_ux_assert(false !== strpos($html, '<h4 class="wpsck-formation__title">La rencontre</h4>'), 'Configured heading level rendered');
formation_ux_assert(false !== strpos($html, '<strong>Reporté</strong>'), 'Postponed status is textual');
formation_ux_assert(false !== strpos($html, 'Marie-Odile Houver'), 'Published people list rendered');

$admin = file_get_contents(dirname(__DIR__) . '/plugin/includes/modules/formation/admin.php');
$admin_css = file_get_contents(dirname(__DIR__) . '/plugin/assets/css/formation-admin.css');
$native_css = file_get_contents(dirname(__DIR__) . '/plugin/assets/css/formation-native.css');
$admin_js = file_get_contents(dirname(__DIR__) . '/plugin/assets/js/formation-admin.js');
$shortcode = file_get_contents(dirname(__DIR__) . '/plugin/includes/modules/formation/shortcode.php');
formation_ux_assert(false !== strpos($admin, 'wp_seed_content_formation_section_usage_count'), 'Section deletion checks schedule usage');
formation_ux_assert(false !== strpos($admin, 'wpsck-formation-section-up'), 'Move up action present');
formation_ux_assert(false !== strpos($admin, 'wpsck-formation-section-down'), 'Move down action present');
formation_ux_assert(false !== strpos($admin, 'original_start_date'), 'Original postponed dates exposed in admin');
formation_ux_assert(false !== strpos($admin, 'wpsck-formation-admin__schedule--'), 'Schedule status class exposed in admin');
formation_ux_assert(false !== strpos($admin, 'wpsck-formation-people__list'), 'People use an explicit checkbox group');
formation_ux_assert(false !== strpos($admin, 'type="checkbox" name="person_ids[]"'), 'People relation keeps canonical field name');
formation_ux_assert(false === strpos($admin_css, '.wpsck-formation-schedule-form__grid input,'), 'Generic form input selector cannot stretch checkboxes');
formation_ux_assert(false !== strpos($admin_css, '.wpsck-formation-people__list input'), 'Checkboxes retain dedicated sizing');
formation_ux_assert(false !== strpos($admin_css, 'input[type="date"]'), 'Date inputs use explicit sizing selector');
formation_ux_assert(false !== strpos($admin_css, 'align-self: start'), 'Date inputs keep intrinsic control height');
formation_ux_assert(false !== strpos($admin, 'wp_seed_content_formation_get_program_calendar_years'), 'Calendar derives a relevant initial year from program data');
formation_ux_assert(false !== strpos($admin, 'wp_seed_content_formation_render_schedule_form($program_id, $year);'), 'Schedule editor renders on calendar page');
formation_ux_assert(strpos($admin, 'wp_seed_content_formation_render_schedule_form($program_id, $year);') < strpos($admin, 'foreach ($groups as $index => $group)'), 'Schedule editor precedes long calendar list');
formation_ux_assert(false !== strpos($admin, 'wpsck-formation-admin__section-nav'), 'Calendar exposes section jump navigation');
formation_ux_assert(false !== strpos($admin_js, 'wpsck_formation_removed_sections[]'), 'Removed section IDs submitted explicitly');
formation_ux_assert(false !== strpos($native_css, 'minmax(0, 1fr)'), 'Native grid cannot exceed viewport');
formation_ux_assert(false !== strpos($native_css, '@media (min-width: 48rem)'), 'Chronological layout has responsive enhancement');
formation_ux_assert(false === strpos($native_css, '#'), 'Native fallback imposes no color palette');
formation_ux_assert(false !== strpos($admin, "WP_SEED_CONTENT_KIT_VERSION . '-formation-v42'"), 'Admin assets use Formation cache version');
preg_match('/\.wpsck-formation-people__list input\s*\{([^}]+)\}/', $admin_css, $checkbox_rule);
formation_ux_assert(isset($checkbox_rule[1]) && !preg_match('/(?:^|[;\s])(?:width|height)\s*:/', $checkbox_rule[1]), 'Checkbox rule preserves paired native WordPress desktop/mobile dimensions');
formation_ux_assert(false !== strpos($shortcode, "WP_SEED_CONTENT_KIT_VERSION . '-formation-v3'"), 'Frontend asset uses Formation cache version');

if ($GLOBALS['formation_ux_failures']) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['formation_ux_failures']) . ' / ' . $GLOBALS['formation_ux_assertions'] . PHP_EOL . implode(PHP_EOL, $GLOBALS['formation_ux_failures']) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $GLOBALS['formation_ux_assertions'] . ' Formation UX assertions' . PHP_EOL;
