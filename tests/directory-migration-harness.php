<?php

define('ABSPATH', dirname(__DIR__) . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');
$GLOBALS['ck_a6_can_import'] = true;
function __($text, $domain = null) { return $text; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_email($value) { return filter_var((string) $value, FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_url_raw($value, $protocols = null) { return false !== filter_var($value, FILTER_VALIDATE_URL) ? (string) $value : ''; }
function wp_parse_url($value) { return parse_url($value); }
function current_user_can($capability) { return 'manage_wp_seed_imports' === $capability && $GLOBALS['ck_a6_can_import']; }
function wp_seed_content_sanitize_iso_date($value) {
    if (!is_string($value) || 1 !== preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $parts)) { return ''; }
    return checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]) ? $value : '';
}
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/fields.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/migration.php';

$assertions = 0;
$failures = array();
function ck_a6_assert($condition, $message) { global $assertions, $failures; $assertions++; if (!$condition) { $failures[] = $message; } }
function ck_a6_same($expected, $actual, $message) { ck_a6_assert($expected === $actual, $message . ' got ' . var_export($actual, true)); }
function ck_a6_rehash_entry(&$entry) { $entry['source_hash'] = wp_seed_content_directory_migration_source_hash($entry); }
function ck_a6_rehash_medium(&$medium) { $medium['source_hash'] = wp_seed_content_directory_migration_source_hash($medium); }

$path = __DIR__ . '/fixtures/native-directory-demo-v1.json';
$loaded = wp_seed_content_directory_load_migration_manifest($path);
ck_a6_assert($loaded['valid'], 'Versioned native fixture validates');
$manifest = $loaded['manifest'];
ck_a6_same('1.0.0', $manifest['schema_version'], 'Schema version');
ck_a6_same('native-directory-demo:ck-a6-v1', $manifest['batch_id'], 'Batch ID');
ck_a6_same('native-directory-demo', $manifest['source_system'], 'Source system');
ck_a6_same(16, count($manifest['entries']), 'Sixteen entries');
ck_a6_same(13, count($manifest['media']), 'Thirteen media');
ck_a6_same(19, count(wp_seed_content_directory_get_meta_definitions()), 'Exactly nineteen native meta definitions');

$statuses = array_count_values(array_column($manifest['entries'], 'professional_status'));
$targets = array_count_values(array_column($manifest['entries'], 'target_status'));
ck_a6_same(10, $statuses['practicing'], 'Ten practicing');
ck_a6_same(6, $statuses['seeking_models'], 'Six seeking models');
ck_a6_same(14, $targets['publish'], 'Fourteen publish');
ck_a6_same(2, $targets['draft'], 'Two draft');
$references = array_count_values(array_column($manifest['entries'], 'media_source_id'));
ck_a6_same(13, count($references), 'Thirteen unique media references');
ck_a6_same(16, array_sum($references), 'Sixteen photo associations');
ck_a6_same(3, count(array_filter($references, function ($count) { return 2 === $count; })), 'Three media reused');

$payload_hashes = array();
foreach ($manifest['media'] as $index => $medium) {
    ck_a6_same(wp_seed_content_directory_migration_source_hash($medium), $medium['source_hash'], 'Canonical media hash ' . $index);
    ck_a6_same('image/png', $medium['mime_type'], 'PNG medium ' . $index);
    ck_a6_assert('' !== trim($medium['alt']), 'Alt present ' . $index);
    ck_a6_assert($medium['height'] > $medium['width'], 'Portrait ratio ' . $index);
    $payload_hashes[] = hash('sha256', base64_decode($medium['payload'], true));
}
ck_a6_same(13, count(array_unique($payload_hashes)), 'Thirteen binary-distinct generated images');

foreach ($manifest['entries'] as $index => $entry) {
    ck_a6_same(wp_seed_content_directory_migration_source_hash($entry), $entry['source_hash'], 'Canonical entry hash ' . $index);
    ck_a6_assert(0 === strpos($entry['display_name'], 'SEED CONTENT KIT TEST - ANNUAIRE - '), 'Fictional title prefix ' . $index);
    ck_a6_same('native-directory-demo:' . $entry['source_id'], wp_seed_content_directory_migration_entry_reference($entry['source_id']), 'Private reference ' . $index);
    ck_a6_assert(false === strpos(json_encode($entry), 'wp-seed-directory'), 'No historical plugin namespace ' . $index);
    if ('publish' === $entry['target_status']) { ck_a6_assert($entry['publication_authorized'], 'Published entry authorized ' . $index); }
}

ck_a6_same('{"a":{"a":1,"b":2},"z":"é/x"}', wp_seed_content_directory_migration_canonical_json(array('z' => 'é/x', 'a' => array('b' => 2, 'a' => 1))), 'Canonical JSON sorting and escaping');
ck_a6_same(wp_seed_content_directory_migration_manifest_hash($manifest), wp_seed_content_directory_migration_manifest_hash(json_decode(json_encode($manifest), true)), 'Manifest hash deterministic');
ck_a6_same(wp_seed_content_directory_migration_option_name($manifest['batch_id']), wp_seed_content_directory_migration_option_name($manifest['batch_id']), 'Batch option deterministic');
ck_a6_assert(wp_seed_content_directory_migration_is_authorized(array('context' => wp_seed_content_directory_migration_context())), 'Administrator context authorized');
$GLOBALS['ck_a6_can_import'] = false;
ck_a6_assert(!wp_seed_content_directory_migration_is_authorized(array('context' => wp_seed_content_directory_migration_context())), 'Editor refused');
$GLOBALS['ck_a6_can_import'] = true;
ck_a6_assert(!wp_seed_content_directory_migration_is_authorized(array('context' => 'wrong')), 'Wrong internal context refused');

$invalid_cases = array();
$copy = $manifest; $copy['schema_version'] = '2.0.0'; $invalid_cases['schema'] = $copy;
$copy = $manifest; unset($copy['batch_id']); $invalid_cases['batch'] = $copy;
$copy = $manifest; $copy['entries'][1]['source_id'] = $copy['entries'][0]['source_id']; ck_a6_rehash_entry($copy['entries'][1]); $invalid_cases['duplicate_source'] = $copy;
$copy = $manifest; $copy['entries'][0]['media_source_id'] = 'media-999'; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['unknown_media'] = $copy;
$copy = $manifest; $copy['entries'][0]['source_hash'] = str_repeat('0', 64); $invalid_cases['entry_hash'] = $copy;
$copy = $manifest; $copy['media'][0]['source_hash'] = str_repeat('0', 64); $invalid_cases['media_hash'] = $copy;
$copy = $manifest; $copy['entries'][0]['professional_status'] = 'invalid'; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['status'] = $copy;
$copy = $manifest; $copy['entries'][0]['country'] = 'ZZ'; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['country'] = $copy;
$copy = $manifest; $copy['entries'][0]['phone'] = ''; $copy['entries'][0]['phone_public'] = true; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['public_contact'] = $copy;
$copy = $manifest; $copy['entries'][0]['publication_authorized'] = false; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['authorization'] = $copy;
$copy = $manifest; $copy['media'][0]['alt'] = ''; ck_a6_rehash_medium($copy['media'][0]); $invalid_cases['alt'] = $copy;
$copy = $manifest; $copy['entries'][0]['featured'] = '1'; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['type'] = $copy;
$copy = $manifest; $copy['entries'][0]['target_status'] = 'private'; ck_a6_rehash_entry($copy['entries'][0]); $invalid_cases['target'] = $copy;
$copy = $manifest; $copy['extra'] = true; $invalid_cases['closed_root'] = $copy;
foreach ($invalid_cases as $name => $invalid) { ck_a6_assert(!wp_seed_content_directory_validate_migration_manifest($invalid)['valid'], 'Invalid manifest rejected: ' . $name); }

$source = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/migration.php');
foreach (array('register_rest_route', 'wp_ajax_', 'admin_post_', 'register_activation_hook', 'add_shortcode', 'add_action(', 'add_filter(') as $forbidden) {
    ck_a6_assert(false === strpos($source, $forbidden), 'No automatic/public mechanism: ' . $forbidden);
}
ck_a6_assert(false === strpos($source, 'wp_seed_directory_'), 'No historical Directory namespace');
ck_a6_assert(false !== strpos($source, "current_user_can('manage_wp_seed_imports')"), 'Advanced import capability required');
ck_a6_assert(false !== strpos($source, 'missing_from_source'), 'Missing source is reported');
ck_a6_assert(false !== strpos($source, 'updated_entries'), 'Entry backups are registered');
ck_a6_assert(false !== strpos($source, 'updated_media'), 'Media backups are registered');
ck_a6_assert(false !== strpos($source, 'update_option($option_name, $registry, false)'), 'Batch option is non-autoloaded');

if ($failures) {
    fwrite(STDERR, "CK-A6 migration harness failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}
echo 'PASS ' . $assertions . " CK-A6 native directory migration assertions\n";