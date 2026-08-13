<?php

define('ABSPATH', __DIR__ . '/');
class WP_Post { public $ID; public $post_type; public function __construct($id, $type) { $this->ID = $id; $this->post_type = $type; } }
$GLOBALS['posts'] = array();
$GLOBALS['meta'] = array();
for ($id = 1; $id <= 23; $id++) {
    $GLOBALS['posts'][$id] = new WP_Post($id, 'seed_testimonial');
    $GLOBALS['meta'][$id] = array(
        '_seed_testimonial_text' => 'Text ' . $id,
        '_seed_testimonial_name' => 'Name ' . $id,
        '_seed_testimonial_context' => 'Context ' . $id,
        '_seed_testimonial_publication_consent' => '1',
        '_seed_testimonial_featured' => 1 === $id % 2 ? '1' : '',
    );
}
$GLOBALS['posts'][24] = new WP_Post(24, 'post');
function absint($value) { return abs((int) $value); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? $GLOBALS['posts'][$id] : null; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['meta'][$id]) && array_key_exists($key, $GLOBALS['meta'][$id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['meta'][$id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$id][$key] = (string) $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); return true; }
function _wp_seed_content_collections_normalize_ids($ids) { $out = array(); foreach ((array) $ids as $id) { if (is_int($id) && $id > 0 && !in_array($id, $out, true)) { $out[] = $id; } } return $out; }
function wp_seed_content_testimonial_publication_consent_meta_key() { return '_seed_testimonial_publication_consent'; }
require_once __DIR__ . '/../plugin/includes/core/helpers.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/migration.php';

$failures = array();
$assertions = 0;
function seed_migration_same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) { $failures[] = $label; }
}
$baseline = serialize($GLOBALS['meta']);
foreach (array(0, 1, 3, 22, 23) as $size) {
    $GLOBALS['meta'] = unserialize($baseline);
    $ids = $size ? range(1, $size) : array();
    $result = wp_seed_content_migrate_testimonial_builder_meta($ids);
    seed_migration_same($size, count($result['updated']), 'updated size ' . $size);
    seed_migration_same($size, count(array_unique($result['updated'])), 'unique size ' . $size);
    seed_migration_same(false, $result['aborted'], 'not aborted size ' . $size);
}
$GLOBALS['meta'] = unserialize($baseline);
$scope = range(1, 22);
$before = serialize($GLOBALS['meta']);
$result = wp_seed_content_migrate_testimonial_builder_meta($scope);
seed_migration_same(22, count($result['updated']), '22 testimonials migrated');
foreach ($scope as $id) {
    seed_migration_same($GLOBALS['meta'][$id]['_seed_testimonial_text'], $GLOBALS['meta'][$id]['seed_testimonial_text'], 'text identical ' . $id);
    seed_migration_same($GLOBALS['meta'][$id]['_seed_testimonial_name'], $GLOBALS['meta'][$id]['seed_testimonial_name'], 'name identical ' . $id);
    seed_migration_same($GLOBALS['meta'][$id]['_seed_testimonial_context'], $GLOBALS['meta'][$id]['seed_testimonial_context'], 'context identical ' . $id);
    seed_migration_same(true, isset($GLOBALS['meta'][$id]['_seed_testimonial_text']), 'legacy retained ' . $id);
}
$second = wp_seed_content_migrate_testimonial_builder_meta($scope);
seed_migration_same(array(), $second['updated'], 'second run writes nothing');
seed_migration_same($scope, $second['unchanged'], 'second run unchanged');
$rollback = wp_seed_content_rollback_testimonial_builder_meta($result);
seed_migration_same($scope, $rollback['restored'], 'rollback exact scope');
seed_migration_same($before, serialize($GLOBALS['meta']), 'rollback exact state');

$GLOBALS['meta'] = unserialize($baseline);
$GLOBALS['meta'][23]['seed_testimonial_text'] = '';
$existing = wp_seed_content_migrate_testimonial_builder_meta(array(23, 24, 999));
seed_migration_same(array(23), $existing['updated'], 'missing public fields added beside canonical empty');
seed_migration_same('', $GLOBALS['meta'][23]['seed_testimonial_text'], 'canonical empty preserved');
seed_migration_same(array(24, 999), $existing['skipped'], 'invalid targets skipped');

$GLOBALS['meta'] = unserialize($baseline);
$GLOBALS['meta'][1]['_seed_testimonial_text'] = '<b>Unsafe</b>';
$unsafe_before = serialize($GLOBALS['meta']);
$unsafe = wp_seed_content_migrate_testimonial_builder_meta(array(1, 2));
seed_migration_same(true, $unsafe['aborted'], 'unsafe migration aborted');
seed_migration_same('legacy_value_requires_editorial_change', $unsafe['errors']['1']['seed_testimonial_text'], 'unsafe reason');
seed_migration_same($unsafe_before, serialize($GLOBALS['meta']), 'preflight prevents partial writes');
if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' assertions' . PHP_EOL;
