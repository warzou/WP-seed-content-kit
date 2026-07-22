<?php

if (!defined('ABSPATH')) {
    echo "CK-A6 WordPress harness: WordPress is not loaded.\n";
    exit(2);
}

$GLOBALS['ck_a6_wp_assertions'] = 0;
$GLOBALS['ck_a6_wp_failures'] = array();
function ck_a6_wp_assert($condition, $message) { $GLOBALS['ck_a6_wp_assertions']++; if (!$condition) { $GLOBALS['ck_a6_wp_failures'][] = $message; } }
function ck_a6_wp_same($expected, $actual, $message) { ck_a6_wp_assert($expected === $actual, $message . ' got ' . var_export($actual, true)); }
function ck_a6_wp_fixture_path() { return dirname(__DIR__) . '/tests/fixtures/native-directory-demo-v1.json'; }
function ck_a6_wp_manifest() { $loaded = wp_seed_content_directory_load_migration_manifest(ck_a6_wp_fixture_path()); ck_a6_wp_assert($loaded['valid'], 'Packaged fixture validates'); return $loaded['manifest']; }
function ck_a6_wp_count_batch($type, $batch, $status = 'any') { return count(get_posts(array('post_type' => $type, 'post_status' => $status, 'fields' => 'ids', 'posts_per_page' => -1, 'suppress_filters' => true, 'meta_key' => '_wp_seed_content_directory_migration_batch_id', 'meta_value' => $batch))); }
function ck_a6_wp_measure($callback) { global $wpdb; $before = (int) $wpdb->num_queries; $start = microtime(true); $result = call_user_func($callback); return array('seconds' => microtime(true) - $start, 'queries' => (int) $wpdb->num_queries - $before, 'result' => $result); }
function ck_a6_wp_cleanup_prefix() {
    global $wpdb;
    $like = $wpdb->esc_like('SEED CONTENT KIT TEST - ANNUAIRE -') . '%';
    $ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s", $like));
    foreach (array_reverse($ids) as $id) { 'attachment' === get_post_type($id) ? wp_delete_attachment($id, true) : wp_delete_post($id, true); }
}

$old_user = get_current_user_id();
$external = array();
$preexisting = 0;
$preexisting_revision_ids = array();
$preserved_media = 0;
$created_editor = 0;
$performance = array();
$manifest = ck_a6_wp_manifest();
$batch = $manifest['batch_id'];
$context = array('context' => wp_seed_content_directory_migration_context());
$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
$editors = get_users(array('role' => 'editor', 'number' => 1, 'fields' => 'ID'));
if (empty($editors)) {
    $created_editor = wp_insert_user(array(
        'user_login' => 'ck_a6_editor',
        'user_pass' => wp_generate_password(24, true, true),
        'user_email' => 'ck-a6-editor@example.test',
        'role' => 'editor',
    ));
    if (!is_wp_error($created_editor)) {
        $editors = array((int) $created_editor);
    }
}
ck_a6_wp_assert(!empty($admins), 'Administrator exists');
ck_a6_wp_assert(!empty($editors), 'Editor exists');
if (!empty($admins)) { wp_set_current_user((int) $admins[0]); }

try {
    wp_seed_content_directory_rollback_migration_batch($batch, $context);
    ck_a6_wp_cleanup_prefix();
    ck_a6_wp_same(0, ck_a6_wp_count_batch('seed_directory', $batch), 'No automatic entry import');
    ck_a6_wp_same(false, get_option(wp_seed_content_directory_migration_option_name($batch), false), 'No automatic batch');

    wp_set_current_user(!empty($editors) ? (int) $editors[0] : 0);
    $editor_import = wp_seed_content_directory_import_manifest($manifest, $context);
    $editor_rollback = wp_seed_content_directory_rollback_migration_batch($batch, $context);
    ck_a6_wp_same('failed', $editor_import['status'], 'Editor import refused');
    ck_a6_wp_same('forbidden', $editor_import['errors'][0]['code'], 'Editor import error typed');
    ck_a6_wp_same('failed', $editor_rollback['status'], 'Editor rollback refused');
    ck_a6_wp_same(0, ck_a6_wp_count_batch('seed_directory', $batch), 'Editor writes nothing');
    wp_set_current_user((int) $admins[0]);

    $invalid = $manifest;
    $invalid['entries'][7]['country'] = 'ZZ';
    $invalid['entries'][7]['source_hash'] = wp_seed_content_directory_migration_source_hash($invalid['entries'][7]);
    $rejected = wp_seed_content_directory_import_manifest($invalid, $context);
    ck_a6_wp_same('failed', $rejected['status'], 'Invalid manifest rejected globally');
    ck_a6_wp_same(0, ck_a6_wp_count_batch('seed_directory', $batch), 'Invalid manifest creates no entry');
    ck_a6_wp_same(0, ck_a6_wp_count_batch('attachment', $batch), 'Invalid manifest creates no medium');
    ck_a6_wp_same(false, get_option(wp_seed_content_directory_migration_option_name($batch), false), 'Invalid manifest creates no batch');

    $external['page'] = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'CK-A6 external page'));
    $external['template'] = wp_insert_post(array('post_type' => 'seed_template', 'post_status' => 'publish', 'post_title' => 'CK-A6 external template', 'post_name' => 'ck-a6-external-template', 'post_content' => '{{directory.name}}'));
    update_post_meta($external['template'], '_wp_seed_content_template_module', 'directory');
    $external['quote'] = wp_insert_post(array('post_type' => 'seed_quote', 'post_status' => 'draft', 'post_title' => 'CK-A6 external quote'));
    $external['testimonial'] = wp_insert_post(array('post_type' => 'seed_testimonial', 'post_status' => 'draft', 'post_title' => 'CK-A6 external testimonial'));
    $outside_titles = array(); foreach ($external as $key => $id) { $outside_titles[$key] = get_the_title($id); }

    $performance['validation'] = ck_a6_wp_measure(function () use ($manifest) { return wp_seed_content_directory_validate_migration_manifest($manifest); });
    $performance['import'] = ck_a6_wp_measure(function () use ($manifest, $context) { return wp_seed_content_directory_import_manifest($manifest, $context); });
    $first = $performance['import']['result'];
    $performance['media_creation'] = array('seconds' => $first['performance']['media_seconds'], 'queries' => $first['performance']['media_queries']);
    $performance['entry_import'] = array('seconds' => $first['performance']['entries_seconds'], 'queries' => $first['performance']['entries_queries']);
    ck_a6_wp_assert($first['performance']['media_seconds'] >= 0, 'Media creation measured separately');
    ck_a6_wp_assert($first['performance']['entries_seconds'] >= 0, 'Entry import measured separately');
    ck_a6_wp_same('imported', $first['status'], 'Initial import succeeds');
    ck_a6_wp_same(16, $first['created_entries'], 'Initial import creates sixteen entries');
    ck_a6_wp_same(13, $first['created_media'], 'Initial import creates thirteen media');
    ck_a6_wp_same(16, ck_a6_wp_count_batch('seed_directory', $batch), 'Sixteen imported entries stored');
    ck_a6_wp_same(14, ck_a6_wp_count_batch('seed_directory', $batch, 'publish'), 'Fourteen imported entries published');
    ck_a6_wp_same(2, ck_a6_wp_count_batch('seed_directory', $batch, 'draft'), 'Two imported entries draft');
    ck_a6_wp_same(13, ck_a6_wp_count_batch('attachment', $batch), 'Thirteen media stored');

    $registry = get_option(wp_seed_content_directory_migration_option_name($batch));
    ck_a6_wp_same('completed', $registry['status'], 'Batch completed');
    ck_a6_wp_same(16, count($registry['created_entries']), 'Batch tracks created entries');
    ck_a6_wp_same(13, count($registry['created_media']), 'Batch tracks created media');
    ck_a6_wp_assert(!isset(wp_load_alloptions()[wp_seed_content_directory_migration_option_name($batch)]), 'Batch option is not autoloaded');

    $entry_ids = array(); $thumbnail_ids = array();
    foreach ($manifest['entries'] as $entry) {
        $reference = wp_seed_content_directory_migration_entry_reference($entry['source_id']);
        $id = wp_seed_content_directory_migration_find_entry($reference);
        $entry_ids[$entry['source_id']] = $id;
        $thumbnail_ids[] = (int) get_post_thumbnail_id($id);
        ck_a6_wp_assert($id > 0, 'Imported entry found ' . $entry['source_id']);
        ck_a6_wp_same($reference, get_post_meta($id, '_wp_seed_content_directory_migration_source_reference', true), 'Private source reference stored ' . $entry['source_id']);
        ck_a6_wp_same($entry['source_hash'], get_post_meta($id, '_wp_seed_content_directory_migration_source_hash', true), 'Source hash stored ' . $entry['source_id']);
        ck_a6_wp_assert(get_post_thumbnail_id($id) > 0, 'Photo associated ' . $entry['source_id']);
    }
    ck_a6_wp_same(13, count(array_unique($thumbnail_ids)), 'Sixteen associations use thirteen media');

    $public_ids = wp_seed_content_directory_get_entries();
    $batch_public = array_values(array_intersect($public_ids, array_values($entry_ids)));
    ck_a6_wp_same(14, count($batch_public), 'Data API collection has fourteen eligible entries');
    ck_a6_wp_same(9, count(array_intersect(wp_seed_content_directory_get_entries(array('status' => 'practicing')), array_values($entry_ids))), 'Nine public practicing');
    ck_a6_wp_same(5, count(array_intersect(wp_seed_content_directory_get_entries(array('status' => 'seeking_models')), array_values($entry_ids))), 'Five public seeking models');
    foreach ($batch_public as $id) {
        $data = wp_seed_content_directory_get_public_data($id);
        $serialized = serialize($data);
        ck_a6_wp_assert(is_array($data), 'Public Data API entry');
        ck_a6_wp_assert(!empty($data['photo']['alt']), 'Public photo alt present');
        ck_a6_wp_assert(false === strpos($serialized, 'native-directory-demo:'), 'No source reference in Data API');
        ck_a6_wp_assert(false === strpos($serialized, '_wp_seed_content_directory_migration'), 'No migration meta in Data API');
        ck_a6_wp_assert(false === strpos($serialized, 'Note interne fictive'), 'No internal note in Data API');
    }
    foreach ($manifest['entries'] as $entry) { if ('draft' === $entry['target_status']) { ck_a6_wp_same(false, wp_seed_content_directory_get_public_data($entry_ids[$entry['source_id']]), 'Draft absent from Data API'); } }

    $native = do_shortcode('[seed_directory]');
    ck_a6_wp_same(14, substr_count($native, '<article class="wp-seed-directory-card">'), 'Shortcode renders fourteen native cards');
    ck_a6_wp_same(2, substr_count($native, '<section class="wp-seed-directory__group">'), 'Shortcode renders two groups');
    ck_a6_wp_assert(false === strpos($native, 'native-directory-demo:'), 'No source reference in shortcode');
    ck_a6_wp_assert(false === strpos($native, 'Note interne fictive'), 'No internal note in shortcode');
    ck_a6_wp_assert(false === strpos($native, '<form'), 'No public search form');
    ck_a6_wp_same(do_shortcode('[seed_directory limit="2"]'), do_shortcode('[wp_seed_directory limit="2"]'), 'Deprecated alias remains identical');
    ck_a6_wp_same(3, count(wp_seed_content_directory_get_entries(array('limit' => 3))), 'Collection limit works');
    ck_a6_wp_same(array(), wp_seed_content_directory_get_entries(array('country' => 'US')), 'Collection empty state works');

    $template_html = do_shortcode('[seed_directory template="ck-a6-external-template" limit="2"]');
    ck_a6_wp_same(2, substr_count($template_html, 'wp-seed-directory-template-card'), 'Native Template renders two entries');
    ck_a6_wp_assert(false === strpos($template_html, 'native-directory-demo:'), 'No source reference in Template');
    $fallback = do_shortcode('[seed_directory template="missing-ck-a6" limit="2"]');
    ck_a6_wp_same(2, substr_count($fallback, '<article class="wp-seed-directory-card">'), 'Missing Template falls back natively');

    $performance['reimport'] = ck_a6_wp_measure(function () use ($manifest, $context) { return wp_seed_content_directory_import_manifest($manifest, $context); });
    $second = $performance['reimport']['result'];
    ck_a6_wp_same('unchanged', $second['status'], 'Reimport is unchanged');
    ck_a6_wp_same(0, $second['created_entries'], 'Reimport creates no entry');
    ck_a6_wp_same(0, $second['updated_entries'], 'Reimport updates no entry');
    ck_a6_wp_same(16, $second['unchanged_entries'], 'Reimport reports sixteen unchanged entries');
    ck_a6_wp_same(0, $second['created_media'], 'Reimport creates no medium');
    ck_a6_wp_same(0, $second['updated_media'], 'Reimport updates no medium');
    ck_a6_wp_same(13, $second['unchanged_media'], 'Reimport reports thirteen unchanged media');

    $working = $manifest;
    $working['entries'][0]['short_bio'] .= ' Mise a jour ciblee.';
    $working['entries'][0]['source_hash'] = wp_seed_content_directory_migration_source_hash($working['entries'][0]);
    $performance['targeted_update'] = ck_a6_wp_measure(function () use ($working, $context) { return wp_seed_content_directory_import_manifest($working, $context); });
    $updated = $performance['targeted_update']['result'];
    ck_a6_wp_same(1, $updated['updated_entries'], 'One changed entry updated');
    ck_a6_wp_same(15, $updated['unchanged_entries'], 'Fifteen entries unchanged');
    ck_a6_wp_same($entry_ids['entry-001'], wp_seed_content_directory_migration_find_entry('native-directory-demo:entry-001'), 'Targeted update preserves entry ID');

    $media_id = wp_seed_content_directory_migration_find_medium('media-001');
    $working['media'][0]['alt'] .= ' mise a jour';
    $working['media'][0]['payload'] = base64_encode(base64_decode($working['media'][0]['payload'], true) . "\0");
    $working['media'][0]['source_hash'] = wp_seed_content_directory_migration_source_hash($working['media'][0]);
    $media_update = wp_seed_content_directory_import_manifest($working, $context);
    ck_a6_wp_same(1, $media_update['updated_media'], 'One changed medium updated');
    ck_a6_wp_same($media_id, wp_seed_content_directory_migration_find_medium('media-001'), 'Media update preserves ID');
    ck_a6_wp_same(13, ck_a6_wp_count_batch('attachment', $batch), 'Media update creates no duplicate');

    $missing = $working; array_pop($missing['entries']);
    $missing_report = wp_seed_content_directory_import_manifest($missing, $context);
    ck_a6_wp_same(array('entry-016'), $missing_report['missing_from_source'], 'Missing source reported');
    ck_a6_wp_assert(get_post($entry_ids['entry-016']) instanceof WP_Post, 'Missing source is not deleted');

    $external['media_reuse_page'] = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'CK-A6 external media reuse'));
    set_post_thumbnail($external['media_reuse_page'], $media_id); $preserved_media = $media_id;
    $performance['rollback'] = ck_a6_wp_measure(function () use ($batch, $context) { return wp_seed_content_directory_rollback_migration_batch($batch, $context); });
    $rollback = $performance['rollback']['result'];
    ck_a6_wp_same(16, $rollback['deleted_entries'], 'Rollback deletes sixteen created entries');
    ck_a6_wp_same(12, $rollback['deleted_media'], 'Rollback deletes twelve unshared media');
    ck_a6_wp_same(1, $rollback['preserved_media'], 'Rollback preserves reused medium');
    ck_a6_wp_assert(get_post($external['media_reuse_page']) instanceof WP_Post, 'External page preserved');
    ck_a6_wp_assert(get_post($preserved_media) instanceof WP_Post, 'Externally reused medium preserved');
    $again = wp_seed_content_directory_rollback_migration_batch($batch, $context);
    ck_a6_wp_same('unchanged', $again['status'], 'Second rollback idempotent');
    wp_delete_post($external['media_reuse_page'], true); unset($external['media_reuse_page']);
    wp_delete_attachment($preserved_media, true); $preserved_media = 0;

    $preexisting = wp_insert_post(array(
        'post_type' => 'seed_directory', 'post_status' => 'draft', 'post_title' => 'SEED CONTENT KIT TEST - ANNUAIRE - Preexistante',
        'meta_input' => array('_wp_seed_content_directory_migration_source_reference' => 'native-directory-demo:entry-001', '_seed_directory_city' => 'Avant migration'),
    ));
    $preexisting_revision_ids = array_map('intval', array_keys(wp_get_post_revisions($preexisting)));
    $pre = wp_seed_content_directory_import_manifest($manifest, $context);
    ck_a6_wp_same(15, $pre['created_entries'], 'Import creates fifteen around preexisting entry');
    ck_a6_wp_same(1, $pre['updated_entries'], 'Preexisting entry updated');
    ck_a6_wp_same($preexisting, wp_seed_content_directory_migration_find_entry('native-directory-demo:entry-001'), 'Preexisting ID retained');
    $restore = wp_seed_content_directory_rollback_migration_batch($batch, $context);
    ck_a6_wp_same(15, $restore['deleted_entries'], 'Rollback removes only created entries');
    ck_a6_wp_same(1, $restore['restored_entries'], 'Rollback restores preexisting entry');
    ck_a6_wp_same('SEED CONTENT KIT TEST - ANNUAIRE - Preexistante', get_post_field('post_title', $preexisting, 'raw'), 'Preexisting title restored');
    ck_a6_wp_same('Avant migration', get_post_meta($preexisting, '_seed_directory_city', true), 'Preexisting metadata restored');
    ck_a6_wp_same('draft', get_post_status($preexisting), 'Preexisting status restored');
    $restored_revision_ids = array_map('intval', array_keys(wp_get_post_revisions($preexisting)));
    sort($preexisting_revision_ids); sort($restored_revision_ids);
    ck_a6_wp_same($preexisting_revision_ids, $restored_revision_ids, 'Rollback removes only revisions created by the batch');

    foreach ($outside_titles as $key => $title) { ck_a6_wp_same($title, get_the_title($external[$key]), 'Outside content preserved: ' . $key); }
} catch (Throwable $error) {
    $GLOBALS['ck_a6_wp_failures'][] = 'Unhandled exception: ' . get_class($error) . ' ' . $error->getMessage();
} finally {
    if (!empty($admins)) { wp_set_current_user((int) $admins[0]); }
    wp_seed_content_directory_rollback_migration_batch($batch, $context);
    foreach ($external as $id) { if (get_post($id)) { wp_delete_post($id, true); } }
    if ($preserved_media && get_post($preserved_media)) { wp_delete_attachment($preserved_media, true); }
    if ($preexisting && get_post($preexisting)) { wp_delete_post($preexisting, true); }
    if ($created_editor && !is_wp_error($created_editor)) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user((int) $created_editor);
    }
    ck_a6_wp_cleanup_prefix();
    wp_set_current_user($old_user);
}
ck_a6_wp_same(false, get_option(wp_seed_content_directory_migration_option_name($batch), false), 'Batch absent after cleanup');
foreach ($performance as $name => $measurement) { echo 'PERF ' . $name . ' seconds=' . number_format($measurement['seconds'], 6, '.', '') . ' queries=' . $measurement['queries'] . PHP_EOL; }
if ($GLOBALS['ck_a6_wp_failures']) {
    echo "CK-A6 WordPress harness failed:\n- " . implode("\n- ", $GLOBALS['ck_a6_wp_failures']) . "\n";
    exit(1);
}
echo 'PASS ' . $GLOBALS['ck_a6_wp_assertions'] . " CK-A6 WordPress native directory migration assertions\n";