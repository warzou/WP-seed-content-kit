<?php

define('ABSPATH', __DIR__ . '/');
class WP_Post { public $ID; public $post_type; public function __construct($id, $type) { $this->ID = $id; $this->post_type = $type; } }
$GLOBALS['directory_migration_posts'] = array(71 => new WP_Post(71, 'seed_directory'), 72 => new WP_Post(72, 'seed_directory'), 73 => new WP_Post(73, 'post'));
$GLOBALS['directory_migration_meta'] = array(
    71 => array('_seed_directory_status' => 'practicing', '_seed_directory_city' => 'Lyon', '_seed_directory_profile_types' => array('praticien'), '_seed_directory_featured' => '1', '_seed_directory_profession' => 'Psychopraticienne'),
    72 => array('_seed_directory_city' => 'Legacy ignored', 'seed_directory_city' => '', '_seed_directory_featured' => '1', 'seed_directory_featured' => false, '_seed_directory_profession' => 'Legacy ignored', 'seed_directory_professional_label' => ''),
);
function __($value, $domain = null) { return $value; }
function absint($value) { return abs((int) $value); }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_email($value) { return (string) $value; }
function wp_parse_url($value) { return parse_url($value); }
function esc_url_raw($value, $protocols = null) { return (string) $value; }
function wp_seed_content_sanitize_iso_date($value) { return (string) $value; }
function get_post($id) { return isset($GLOBALS['directory_migration_posts'][$id]) ? $GLOBALS['directory_migration_posts'][$id] : null; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['directory_migration_meta'][$id]) && array_key_exists($key, $GLOBALS['directory_migration_meta'][$id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['directory_migration_meta'][$id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['directory_migration_meta'][$id][$key] = $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['directory_migration_meta'][$id][$key]); return true; }
function _wp_seed_content_collections_normalize_ids($ids) { return array_values(array_unique(array_filter($ids, function ($id) { return is_int($id) && $id > 0; }))); }

require_once __DIR__ . '/../plugin/includes/modules/directory/fields.php';
require_once __DIR__ . '/../plugin/includes/modules/directory/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/directory/builder-meta-migration.php';
$failures = array(); $assertions = 0;
function directory_migration_same($expected, $actual, $label) { global $failures, $assertions; $assertions++; if ($expected !== $actual) { $failures[] = $label; } }

$before = serialize($GLOBALS['directory_migration_meta']);
$result = wp_seed_content_migrate_directory_builder_meta(array(71, 72, 73, 999));
directory_migration_same(array(71), $result['updated'], 'Legacy profile migrated');
directory_migration_same(array(72), $result['unchanged'], 'Canonical profile unchanged');
directory_migration_same(array(73, 999), $result['skipped'], 'Invalid targets skipped');
directory_migration_same('Lyon', $GLOBALS['directory_migration_meta'][71]['seed_directory_city'], 'Legacy text copied');
directory_migration_same(array('praticien'), $GLOBALS['directory_migration_meta'][71]['seed_directory_profile_types'], 'Legacy array copied');
directory_migration_same(true, $GLOBALS['directory_migration_meta'][71]['seed_directory_featured'], 'Legacy boolean normalized');
directory_migration_same('Psychopraticienne', $GLOBALS['directory_migration_meta'][71]['seed_directory_professional_label'], 'Legacy profession copied to canonical professional label');
directory_migration_same('', $GLOBALS['directory_migration_meta'][72]['seed_directory_city'], 'Canonical empty wins');
directory_migration_same(false, $GLOBALS['directory_migration_meta'][72]['seed_directory_featured'], 'Canonical false wins');
directory_migration_same('', $GLOBALS['directory_migration_meta'][72]['seed_directory_professional_label'], 'Canonical empty professional label wins');
directory_migration_same('Legacy ignored', $GLOBALS['directory_migration_meta'][72]['_seed_directory_profession'], 'Legacy profession retained');
directory_migration_same('Legacy ignored', $GLOBALS['directory_migration_meta'][72]['_seed_directory_city'], 'Legacy retained');
$second = wp_seed_content_migrate_directory_builder_meta(array(71, 72));
directory_migration_same(array(), $second['updated'], 'Second run writes nothing');
$rollback = wp_seed_content_rollback_directory_builder_meta($result);
directory_migration_same(array(71, 72), $rollback['restored'], 'Rollback scope exact');
directory_migration_same($before, serialize($GLOBALS['directory_migration_meta']), 'Rollback exact');

if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' Directory builder meta migration assertions' . PHP_EOL;
