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

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function get_error_code()
    {
        return $this->code;
    }
}

$GLOBALS['posts'] = array(
    1 => new WP_Post(1, 'seed_testimonial', 'Témoignage — Alice', ''),
    2 => new WP_Post(2, 'seed_testimonial', 'Témoignage — Bob', 'Résumé conservé'),
    3 => new WP_Post(3, 'post', 'Article', ''),
    4 => new WP_Post(4, 'seed_testimonial', 'Témoignage — Claire', ''),
    5 => new WP_Post(5, 'seed_testimonial', 'Témoignage — Diane', 'Choix éditorial'),
);
$GLOBALS['meta'] = array(
    1 => array('_seed_testimonial_summary' => 'Résumé historique'),
    2 => array('_seed_testimonial_summary' => 'Résumé conservé'),
    5 => array('_seed_testimonial_summary' => 'Résumé divergent'),
);

function absint($value) { return abs((int) $value); }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? $GLOBALS['posts'][$id] : null; }
function get_post_meta($id, $key, $single = false) { return isset($GLOBALS['meta'][$id][$key]) ? $GLOBALS['meta'][$id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$id][$key] = (string) $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); return true; }
function wp_slash($value) { return $value; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_update_post($postarr, $wp_error = false)
{
    $id = absint(isset($postarr['ID']) ? $postarr['ID'] : 0);
    if (!isset($GLOBALS['posts'][$id])) {
        return $wp_error ? new WP_Error('missing_post') : 0;
    }
    if (array_key_exists('post_excerpt', $postarr)) {
        $GLOBALS['posts'][$id]->post_excerpt = (string) $postarr['post_excerpt'];
    }
    return $id;
}
function _wp_seed_content_collections_normalize_ids($ids)
{
    $out = array();
    foreach ((array) $ids as $id) {
        if (is_int($id) && $id > 0 && !in_array($id, $out, true)) {
            $out[] = $id;
        }
    }
    return $out;
}
function wp_seed_content_testimonial_publication_consent_meta_key() { return '_seed_testimonial_publication_consent'; }

require_once __DIR__ . '/../plugin/includes/modules/testimonials/migration.php';

$failures = array();
$assertions = 0;
function testimonial_summary_same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) {
        $failures[] = $label;
    }
}

testimonial_summary_same(false, function_exists('wp_seed_content_migrate_testimonial_native_loop_fields'), 'obsolete title/summary migration removed');
testimonial_summary_same(false, function_exists('wp_seed_content_rollback_testimonial_native_loop_fields'), 'obsolete combined rollback removed');

$before = serialize($GLOBALS['posts']);
$dry_run = wp_seed_content_migrate_testimonial_summaries(array(1, 2, 3, 4, 999, 1, '2'), false);
testimonial_summary_same(array(1), $dry_run['planned'], 'legacy-only summary planned');
testimonial_summary_same(array(2), $dry_run['preserved'], 'canonical summary preserved');
testimonial_summary_same(array(4), $dry_run['empty'], 'both-empty summary remains empty');
testimonial_summary_same(array(3, 999), $dry_run['skipped'], 'invalid targets skipped');
testimonial_summary_same(false, $dry_run['applied'], 'dry-run remains read-only');
testimonial_summary_same($before, serialize($GLOBALS['posts']), 'dry-run writes nothing');

$conflict = wp_seed_content_migrate_testimonial_summaries(array(1, 5), true);
testimonial_summary_same(true, $conflict['aborted'], 'divergent summary aborts batch');
testimonial_summary_same('summary_values_differ', $conflict['errors']['5'], 'divergence identified');
testimonial_summary_same(array(), $conflict['updated'], 'conflict writes nothing');
testimonial_summary_same($before, serialize($GLOBALS['posts']), 'conflict preserves all posts');

$apply = wp_seed_content_migrate_testimonial_summaries(array(1, 2, 4), true);
testimonial_summary_same(true, $apply['applied'], 'safe summary plan applied');
testimonial_summary_same(array(1), $apply['updated'], 'legacy-only summary copied');
testimonial_summary_same('Témoignage — Alice', $GLOBALS['posts'][1]->post_title, 'technical title untouched');
testimonial_summary_same('Résumé historique', $GLOBALS['posts'][1]->post_excerpt, 'canonical excerpt populated');
testimonial_summary_same('Résumé historique', $GLOBALS['meta'][1]['_seed_testimonial_summary'], 'legacy fallback retained');

$rollback = wp_seed_content_rollback_testimonial_summaries($apply);
testimonial_summary_same(array(1, 2, 4), $rollback['restored'], 'rollback restores exact scope');
testimonial_summary_same(array(), $rollback['errors'], 'rollback has no errors');
testimonial_summary_same($before, serialize($GLOBALS['posts']), 'rollback restores excerpts exactly');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $assertions . ' Testimonial summary migration assertions' . PHP_EOL;
