<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['directory_contract_meta'] = array(
    71 => array('_seed_directory_status' => 'practicing', '_seed_directory_city' => 'Lyon', '_seed_directory_featured' => '1', '_seed_directory_profession' => 'Psychopraticienne'),
    72 => array('_seed_directory_city' => 'Legacy', 'seed_directory_city' => '', '_seed_directory_featured' => '1', 'seed_directory_featured' => false, '_seed_directory_profession' => 'Legacy', 'seed_directory_professional_label' => ''),
);
$GLOBALS['directory_registered_meta'] = array();
$GLOBALS['directory_registered_types'] = array();
$GLOBALS['directory_bindings'] = array();

class WP_Block { public $name = 'core/paragraph'; public $context = array('postId' => 71, 'postType' => 'seed_directory'); }
function __($value, $domain = null) { return $value; }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
function register_block_bindings_source($name, $args) { $GLOBALS['directory_bindings'][$name] = $args; }
function register_post_type($type, $args) { $GLOBALS['directory_registered_types'][$type] = $args; }
function register_post_meta($type, $key, $args) { $GLOBALS['directory_registered_meta'][$key] = array($type, $args); }
function wp_seed_content_kit_get_post_type_menu_parent($type) { return 'wp-seed'; }
function wp_seed_content_directory_get_capability_map() { return array(); }
function wp_seed_content_kit_register_manual_order_for_post_type($type) {}
function current_user_can($capability, $post_id = 0) { return 71 === (int) $post_id; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['directory_contract_meta'][$id]) && array_key_exists($key, $GLOBALS['directory_contract_meta'][$id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['directory_contract_meta'][$id][$key] : ''; }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_email($value) { return (string) $value; }
function wp_parse_url($value) { return parse_url($value); }
function esc_url_raw($value, $protocols = null) { return (string) $value; }
function wp_seed_content_sanitize_iso_date($value) { return (string) $value; }
function wp_seed_content_resolve_dynamic_data($field_id, $context) {
    if ('directory.photo' === $field_id) { return array('id' => 91, 'url' => 'https://example.test/photo.jpg', 'alt' => 'Portrait'); }
    return $field_id . ':' . (isset($context['current_post_id']) ? $context['current_post_id'] : 0);
}
function is_wp_error($value) { return false; }
function absint($value) { return abs((int) $value); }
function wp_seed_content_directory_individual_contact_provider_definitions() {
    return array(
        'website' => array(
            'display_field_id' => 'directory.website',
            'href_field_id' => 'directory.website_href',
            'has_href' => true,
        ),
    );
}

require_once __DIR__ . '/../plugin/includes/modules/directory/fields.php';
require_once __DIR__ . '/../plugin/includes/modules/directory/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/directory/post-type.php';
require_once __DIR__ . '/../plugin/includes/integrations/gutenberg/block-bindings.php';

wp_seed_content_directory_register_post_type();
wp_seed_content_register_gutenberg_block_bindings_source();
$failures = array(); $assertions = 0;
function directory_contract_same($expected, $actual, $label) { global $failures, $assertions; $assertions++; if ($expected !== $actual) { $failures[] = $label; } }

$type = $GLOBALS['directory_registered_types']['seed_directory'];
directory_contract_same(true, $type['show_in_rest'], 'CPT REST enabled');
directory_contract_same(false, $type['public'], 'CPT remains private');
directory_contract_same(true, in_array('custom-fields', $type['supports'], true), 'Custom fields supported');
directory_contract_same(true, in_array('excerpt', $type['supports'], true), 'Canonical summary exposed through REST excerpt support');
directory_contract_same(true, in_array('editor', $type['supports'], true), 'Canonical full presentation exposed through REST content support');
directory_contract_same(9, count(wp_seed_content_directory_builder_meta_definitions()), 'Nine portable public metas');
directory_contract_same(false, isset(wp_seed_content_directory_builder_meta_definitions()['seed_directory_phone']), 'Private contact not registered as portable meta');
directory_contract_same(false, isset(wp_seed_content_directory_builder_meta_definitions()['read_more_open']), 'No builder-specific read-more storage');
directory_contract_same(false, isset(wp_seed_content_directory_builder_meta_definitions()['accordion_state']), 'No builder-specific accordion storage');
directory_contract_same('Lyon', wp_seed_content_directory_get_builder_meta(71, 'seed_directory_city'), 'Legacy fallback');
directory_contract_same('', wp_seed_content_directory_get_builder_meta(72, 'seed_directory_city'), 'Canonical empty wins');
directory_contract_same(true, wp_seed_content_directory_get_builder_meta(71, 'seed_directory_featured'), 'Legacy boolean fallback');
directory_contract_same(false, wp_seed_content_directory_get_builder_meta(72, 'seed_directory_featured'), 'Canonical false wins');
directory_contract_same('Psychopraticienne', wp_seed_content_directory_get_builder_meta(71, 'seed_directory_professional_label'), 'Professional label legacy fallback');
directory_contract_same('', wp_seed_content_directory_get_builder_meta(72, 'seed_directory_professional_label'), 'Canonical empty professional label wins');
directory_contract_same('Psychopraticienne', wp_seed_content_directory_sanitize_builder_meta(' <b>Psychopraticienne</b> ', 'seed_directory_professional_label'), 'Professional label uses plain text sanitization');
foreach ($GLOBALS['directory_registered_meta'] as $key => $registration) {
    directory_contract_same('seed_directory', $registration[0], $key . ' post type');
    directory_contract_same('_' !== substr($key, 0, 1), (bool) $registration[1]['show_in_rest'], $key . ' REST');
}
directory_contract_same(true, isset($GLOBALS['directory_bindings']['wp-seed-content-kit/dynamic-data']), 'Gutenberg source registered');
$block = new WP_Block();
directory_contract_same('directory.name:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.name'), $block, 'content'), 'Directory text binding');
directory_contract_same('directory.professional_label:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.professional_label'), $block, 'content'), 'Directory professional label binding');
directory_contract_same('directory.summary:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.summary'), $block, 'content'), 'Directory summary binding');
directory_contract_same('directory.presentation:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.presentation'), $block, 'content'), 'Directory full presentation binding');
directory_contract_same('directory.presentation_intro:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.presentation_intro'), $block, 'content'), 'Directory intro binding');
directory_contract_same('directory.presentation_more:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.presentation_more'), $block, 'content'), 'Directory continuation binding');
directory_contract_same(null, wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.has_more'), $block, 'content'), 'Boolean is not exposed as a text binding');
directory_contract_same('directory.website:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.website'), $block, 'content'), 'Directory contact display binding');
$block->name = 'core/button';
directory_contract_same('directory.website_href:71', wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.website_href'), $block, 'url'), 'Directory URL binding');
$block->name = 'core/image';
directory_contract_same(91, wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.photo'), $block, 'id'), 'Directory image binding');

if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' Directory builder meta contract assertions' . PHP_EOL;
