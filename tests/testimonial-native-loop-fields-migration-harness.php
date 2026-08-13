<?php

define('ABSPATH', __DIR__ . '/');
class WP_Post
{
    public $ID;
    public $post_type;
    public $post_title;
    public $post_excerpt;
    public function __construct($id, $type, $title, $excerpt)
    {
        $this->ID = $id;
        $this->post_type = $type;
        $this->post_title = $title;
        $this->post_excerpt = $excerpt;
    }
}
class WP_Error
{
    private $code;
    public function __construct($code) { $this->code = $code; }
    public function get_error_code() { return $this->code; }
}
$GLOBALS['posts'] = array(
    1 => new WP_Post(1, 'seed_testimonial', 'Legacy admin title', ''),
    2 => new WP_Post(2, 'seed_testimonial', 'Already native', 'Already summarized'),
    3 => new WP_Post(3, 'post', 'Article', ''),
);
$GLOBALS['meta'] = array(
    1 => array('_seed_testimonial_title' => 'Editorial title', '_seed_testimonial_summary' => 'Editorial summary'),
    2 => array('_seed_testimonial_title' => 'Already native', '_seed_testimonial_summary' => 'Already summarized'),
);
function absint($value) { return abs((int) $value); }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? $GLOBALS['posts'][$id] : null; }
function get_post_meta($id, $key, $single = false) { return isset($GLOBALS['meta'][$id][$key]) ? $GLOBALS['meta'][$id][$key] : ''; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['meta'][$id]) && array_key_exists($key, $GLOBALS['meta'][$id]); }
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$id][$key] = (string) $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); return true; }
function wp_slash($value) { return $value; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_update_post($postarr, $wp_error = false)
{
    $id = absint(isset($postarr['ID']) ? $postarr['ID'] : 0);
    if (!isset($GLOBALS['posts'][$id])) { return $wp_error ? new WP_Error('missing_post') : 0; }
    if (array_key_exists('post_title', $postarr)) { $GLOBALS['posts'][$id]->post_title = (string) $postarr['post_title']; }
    if (array_key_exists('post_excerpt', $postarr)) { $GLOBALS['posts'][$id]->post_excerpt = (string) $postarr['post_excerpt']; }
    return $id;
}
function _wp_seed_content_collections_normalize_ids($ids)
{
    $out = array();
    foreach ((array) $ids as $id) {
        if (is_int($id) && $id > 0 && !in_array($id, $out, true)) { $out[] = $id; }
    }
    return $out;
}
function wp_seed_content_testimonial_publication_consent_meta_key() { return '_seed_testimonial_publication_consent'; }
require_once __DIR__ . '/../plugin/includes/modules/testimonials/migration.php';
$failures = array();
$assertions = 0;
function same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) { $failures[] = $label; }
}
$before = serialize($GLOBALS['posts']);
$result = wp_seed_content_migrate_testimonial_native_loop_fields(array(1, 2, 3, 999, 1, '2'));
same(array(1), $result['updated'], 'legacy fields migrated');
same(array(2), $result['unchanged'], 'matching native fields unchanged');
same(array(3, 999), $result['skipped'], 'invalid targets skipped');
same(array(), $result['errors'], 'migration has no errors');
same('Editorial title', $GLOBALS['posts'][1]->post_title, 'native title populated');
same('Editorial summary', $GLOBALS['posts'][1]->post_excerpt, 'native excerpt populated');
same('Editorial title', $GLOBALS['meta'][1]['_seed_testimonial_title'], 'legacy title retained');
same('Editorial summary', $GLOBALS['meta'][1]['_seed_testimonial_summary'], 'legacy summary retained');
$second = wp_seed_content_migrate_testimonial_native_loop_fields(array(1, 2));
same(array(), $second['updated'], 'second migration writes nothing');
same(array(1, 2), $second['unchanged'], 'second migration idempotent');
$rollback = wp_seed_content_rollback_testimonial_native_loop_fields($result);
same(array(1, 2), $rollback['restored'], 'rollback restores exact scope');
same(array(), $rollback['errors'], 'rollback has no errors');
same($before, serialize($GLOBALS['posts']), 'rollback restores native fields exactly');
if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' assertions' . PHP_EOL;
