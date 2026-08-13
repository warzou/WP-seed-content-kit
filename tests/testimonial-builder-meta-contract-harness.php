<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['registered_post_types'] = array();
$GLOBALS['registered_post_meta'] = array();
$GLOBALS['meta'] = array(
    7 => array('_seed_testimonial_text' => 'Legacy text'),
    8 => array('_seed_testimonial_text' => 'Legacy hidden', 'seed_testimonial_text' => ''),
);
function __($value, $domain = null) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function register_post_type($type, $args) { $GLOBALS['registered_post_types'][$type] = $args; }
function register_post_meta($type, $key, $args) { $GLOBALS['registered_post_meta'][$key] = array($type, $args); }
function wp_seed_content_kit_get_post_type_menu_parent($type) { return 'wp-seed'; }
function wp_seed_content_kit_get_capability_map($module) { return array('edit_post' => 'edit_seed_testimonial'); }
function wp_seed_content_kit_register_manual_order_for_post_type($type) {}
function current_user_can($capability, $post_id = 0) { return 'edit_post' === $capability && 7 === (int) $post_id; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['meta'][$id]) && array_key_exists($key, $GLOBALS['meta'][$id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['meta'][$id][$key] : ''; }
require_once __DIR__ . '/../plugin/includes/core/helpers.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/post-type.php';
wp_seed_content_register_testimonial_post_type();

$failures = array();
$assertions = 0;
function seed_builder_same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) { $failures[] = $label; }
}
seed_builder_same(true, in_array('custom-fields', $GLOBALS['registered_post_types']['seed_testimonial']['supports'], true), 'custom fields support');
seed_builder_same(true, in_array('excerpt', $GLOBALS['registered_post_types']['seed_testimonial']['supports'], true), 'excerpt support preserved');
seed_builder_same(array('seed_testimonial_text', 'seed_testimonial_name', 'seed_testimonial_context'), array_keys($GLOBALS['registered_post_meta']), 'three public meta keys');
foreach ($GLOBALS['registered_post_meta'] as $key => $registration) {
    seed_builder_same('seed_testimonial', $registration[0], $key . ' post type');
    seed_builder_same('string', $registration[1]['type'], $key . ' string');
    seed_builder_same(true, $registration[1]['single'], $key . ' single');
    seed_builder_same(true, $registration[1]['show_in_rest'], $key . ' REST');
}
seed_builder_same('Legacy text', wp_seed_content_get_testimonial_builder_meta(7, 'seed_testimonial_text'), 'legacy read-only fallback');
seed_builder_same('', wp_seed_content_get_testimonial_builder_meta(8, 'seed_testimonial_text'), 'empty canonical suppresses fallback');
seed_builder_same('', wp_seed_content_get_testimonial_builder_meta(7, 'unknown'), 'unknown key rejected');
seed_builder_same('Safe text', wp_seed_content_sanitize_testimonial_text_meta("  <b>Safe</b> text  "), 'textarea sanitized');
seed_builder_same('Name', wp_seed_content_sanitize_testimonial_name_meta('<b>Name</b>'), 'name sanitized');
seed_builder_same(true, wp_seed_content_testimonial_builder_meta_auth(false, 'seed_testimonial_text', 7), 'editor authorized');
seed_builder_same(false, wp_seed_content_testimonial_builder_meta_auth(true, 'seed_testimonial_text', 8), 'non editor refused');
if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' assertions' . PHP_EOL;
