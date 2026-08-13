<?php

define('ABSPATH', __DIR__ . '/');
class WP_Post { public $ID; public $post_type; public function __construct($id, $type) { $this->ID = $id; $this->post_type = $type; } }
$GLOBALS['posts'] = array(1 => new WP_Post(1, 'seed_testimonial'), 2 => new WP_Post(2, 'seed_testimonial'), 3 => new WP_Post(3, 'post'));
$GLOBALS['meta'] = array(2 => array('_seed_testimonial_publication_consent' => '1'));
function absint($value) { return abs((int) $value); }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? $GLOBALS['posts'][$id] : null; }
function get_post_meta($id, $key, $single = false) { return isset($GLOBALS['meta'][$id][$key]) ? $GLOBALS['meta'][$id][$key] : ''; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['meta'][$id]) && array_key_exists($key, $GLOBALS['meta'][$id]); }
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$id][$key] = (string) $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); if (isset($GLOBALS['meta'][$id]) && array() === $GLOBALS['meta'][$id]) { unset($GLOBALS['meta'][$id]); } return true; }
function _wp_seed_content_collections_normalize_ids($ids) { $out = array(); foreach ((array) $ids as $id) { if (is_int($id) && $id > 0 && !in_array($id, $out, true)) { $out[] = $id; } } return $out; }
function wp_seed_content_testimonial_publication_consent_meta_key() { return '_seed_testimonial_publication_consent'; }
require_once __DIR__ . '/../plugin/includes/modules/testimonials/migration.php';
$failures = array(); $assertions = 0;
function same($expected, $actual, $label) { global $failures, $assertions; $assertions++; if ($expected !== $actual) { $failures[] = $label; } }
$before = serialize($GLOBALS['meta']);
$result = wp_seed_content_migrate_testimonial_publication_consent(array(1, 2, 3, 999, 1, '2'));
same(array(1), $result['updated'], 'only missing testimonial updated');
same(array(2), $result['unchanged'], 'existing exact consent unchanged');
same(array(3, 999), $result['skipped'], 'non testimonial and missing skipped');
same('1', $GLOBALS['meta'][1]['_seed_testimonial_publication_consent'], 'consent written');
$second = wp_seed_content_migrate_testimonial_publication_consent(array(1, 2));
same(array(), $second['updated'], 'second migration idempotent');
same(array(1, 2), $second['unchanged'], 'second migration unchanged');
$rollback = wp_seed_content_rollback_testimonial_publication_consent($result);
same(array(1, 2), $rollback['restored'], 'rollback restores exact scope');
same($before, serialize($GLOBALS['meta']), 'rollback restores exact metadata state');
if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' assertions' . PHP_EOL;