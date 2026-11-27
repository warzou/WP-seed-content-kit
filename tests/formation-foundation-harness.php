<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');

$GLOBALS['formation_options'] = array();
$GLOBALS['formation_post_types'] = array();
$GLOBALS['formation_assertions'] = 0;
$GLOBALS['formation_failures'] = array();

function formation_assert($condition, $label)
{
    $GLOBALS['formation_assertions']++;
    if (!$condition) {
        $GLOBALS['formation_failures'][] = $label;
    }
}
function formation_same($expected, $actual, $label)
{
    formation_assert($expected === $actual, $label);
}
function __($text, $domain = null) { return $text; }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function get_option($key, $default = false) { return isset($GLOBALS['formation_options'][$key]) ? $GLOBALS['formation_options'][$key] : $default; }
function wp_parse_args($args, $defaults = array()) { return array_merge($defaults, is_array($args) ? $args : array()); }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
function register_post_type($post_type, $args) { $GLOBALS['formation_post_types'][$post_type] = $args; }
function wp_seed_content_kit_register_manual_order_for_post_type($post_type) {}

require WP_SEED_CONTENT_KIT_DIR . 'includes/core/capabilities.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/core/modules.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/formation/post-types.php';

$defaults = wp_seed_content_kit_get_default_module_options();
formation_same(false, $defaults['formation'], 'Formation is disabled by default');
$module = wp_seed_content_kit_get_modules()['formation'];
formation_same('Parcours & calendrier', $module['label'], 'Visible module name');
formation_same('formation', wp_seed_content_kit_get_capability_family_module('formation_programs'), 'Capability family maps to module');
formation_same('edit_seed_programs', wp_seed_content_kit_get_module_content_capability('formation'), 'Module menu capability');
formation_same(2, count($module['admin_post_types']), 'Program and Theme admin entries');

wp_seed_content_formation_register_post_types();
formation_same(array('seed_program', 'seed_theme', 'seed_section', 'seed_schedule'), array_keys($GLOBALS['formation_post_types']), 'Four CPTs registered');
formation_same(true, $GLOBALS['formation_post_types']['seed_program']['show_ui'], 'Program UI visible');
formation_same(true, $GLOBALS['formation_post_types']['seed_theme']['show_ui'], 'Theme UI visible');
formation_same(false, $GLOBALS['formation_post_types']['seed_section']['show_ui'], 'Section is technical');
formation_same(false, $GLOBALS['formation_post_types']['seed_schedule']['show_ui'], 'Schedule is technical');
formation_same(true, $GLOBALS['formation_post_types']['seed_program']['show_in_rest'], 'Program REST editor');
formation_same(true, $GLOBALS['formation_post_types']['seed_theme']['show_in_rest'], 'Theme REST editor');
formation_assert(in_array('thumbnail', $GLOBALS['formation_post_types']['seed_theme']['supports'], true), 'Theme featured image supported');
formation_assert(!in_array('thumbnail', $GLOBALS['formation_post_types']['seed_program']['supports'], true), 'Program has no forced image');
foreach (array('formation_programs', 'formation_themes', 'formation_sections', 'formation_schedules') as $family) {
    formation_same(14, count(wp_seed_content_kit_get_capability_map($family)), $family . ' capability map');
}

if ($GLOBALS['formation_failures']) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['formation_failures']) . ' / ' . $GLOBALS['formation_assertions'] . PHP_EOL . implode(PHP_EOL, $GLOBALS['formation_failures']) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $GLOBALS['formation_assertions'] . ' Formation foundation assertions' . PHP_EOL;
