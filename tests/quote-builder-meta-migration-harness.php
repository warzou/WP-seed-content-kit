<?php

define('ABSPATH', __DIR__ . '/');
class WP_Post { public $ID; public $post_type; public function __construct($id, $type) { $this->ID = $id; $this->post_type = $type; } }
$GLOBALS['posts'] = array(41 => new WP_Post(41, 'seed_quote'), 42 => new WP_Post(42, 'seed_quote'), 43 => new WP_Post(43, 'post'));
$GLOBALS['meta'] = array(
    41 => array(
        '_seed_quote_text' => 'Legacy text',
        '_seed_quote_author' => 'Legacy author',
        '_seed_quote_era' => 'Legacy era',
        '_seed_quote_source' => 'Legacy source',
        '_seed_quote_featured' => '1',
    ),
    42 => array(
        '_seed_quote_text' => 'Legacy B',
        'seed_quote_text' => 'Public A',
        '_seed_quote_author' => 'Legacy ignored',
        'seed_quote_author' => '',
        '_seed_quote_featured' => '1',
        'seed_quote_featured' => false,
    ),
);
function absint($value) { return abs((int) $value); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? $GLOBALS['posts'][$id] : null; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['meta'][$id]) && array_key_exists($key, $GLOBALS['meta'][$id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['meta'][$id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$id][$key] = $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); return true; }
function _wp_seed_content_collections_normalize_ids($ids) { return array_values(array_unique(array_filter($ids, function ($id) { return is_int($id) && $id > 0; }))); }

require_once __DIR__ . '/../plugin/includes/core/helpers.php';
require_once __DIR__ . '/../plugin/includes/modules/quotes/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/quotes/migration.php';

$failures = array();
$assertions = 0;
function seed_quote_migration_same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) { $failures[] = $label; }
}

$before = serialize($GLOBALS['meta']);
$result = wp_seed_content_migrate_quote_builder_meta(array(41, 42, 43, 999));
seed_quote_migration_same(array(41), $result['updated'], 'missing public fields migrated');
seed_quote_migration_same(array(42), $result['unchanged'], 'canonical divergence left unchanged');
seed_quote_migration_same(array(43, 999), $result['skipped'], 'invalid targets skipped');
seed_quote_migration_same('Legacy text', $GLOBALS['meta'][41]['seed_quote_text'], 'legacy text copied');
seed_quote_migration_same(true, $GLOBALS['meta'][41]['seed_quote_featured'], 'legacy featured normalized');
seed_quote_migration_same('Public A', $GLOBALS['meta'][42]['seed_quote_text'], 'public value wins');
seed_quote_migration_same('', $GLOBALS['meta'][42]['seed_quote_author'], 'public empty wins');
seed_quote_migration_same(false, $GLOBALS['meta'][42]['seed_quote_featured'], 'public false wins');
seed_quote_migration_same('Legacy B', $GLOBALS['meta'][42]['_seed_quote_text'], 'legacy retained');
$second = wp_seed_content_migrate_quote_builder_meta(array(41, 42));
seed_quote_migration_same(array(), $second['updated'], 'second run writes nothing');
seed_quote_migration_same(array(41, 42), $second['unchanged'], 'second run unchanged');
$rollback = wp_seed_content_rollback_quote_builder_meta($result);
seed_quote_migration_same(array(41, 42), $rollback['restored'], 'rollback scope');
seed_quote_migration_same($before, serialize($GLOBALS['meta']), 'rollback exact');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $assertions . ' quote migration assertions' . PHP_EOL;
