<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['registered_post_types'] = array();
$GLOBALS['registered_post_meta'] = array();
$GLOBALS['registered_binding_sources'] = array();
$GLOBALS['meta'] = array(
    41 => array(
        '_seed_quote_text' => 'Legacy text',
        '_seed_quote_author' => 'Legacy author',
        '_seed_quote_featured' => '1',
    ),
    42 => array(
        '_seed_quote_text' => 'Legacy hidden',
        'seed_quote_text' => '',
        '_seed_quote_featured' => '1',
        'seed_quote_featured' => false,
    ),
);

class WP_Block
{
    public $name = 'core/paragraph';
    public $context = array('postId' => 41, 'postType' => 'seed_quote');
}
function __($value, $domain = null) { return $value; }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
function register_block_bindings_source($name, $args) { $GLOBALS['registered_binding_sources'][$name] = $args; }
function is_wp_error($value) { return false; }
function wp_seed_content_resolve_dynamic_data($field_id, $context)
{
    return $field_id . '@' . (isset($context['current_post_id']) ? $context['current_post_id'] : 0);
}
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function register_post_type($type, $args) { $GLOBALS['registered_post_types'][$type] = $args; }
function register_post_meta($type, $key, $args) { $GLOBALS['registered_post_meta'][$key] = array($type, $args); }
function wp_seed_content_kit_get_post_type_menu_parent($type) { return 'wp-seed'; }
function wp_seed_content_kit_get_capability_map($module) { return array('edit_post' => 'edit_seed_quote'); }
function wp_seed_content_kit_register_manual_order_for_post_type($type) {}
function current_user_can($capability, $post_id = 0) { return 'edit_post' === $capability && 41 === (int) $post_id; }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['meta'][$id]) && array_key_exists($key, $GLOBALS['meta'][$id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['meta'][$id][$key] : ''; }

require_once __DIR__ . '/../plugin/includes/core/helpers.php';
require_once __DIR__ . '/../plugin/includes/modules/quotes/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/quotes/post-type.php';
require_once __DIR__ . '/../plugin/includes/integrations/gutenberg/block-bindings.php';

wp_seed_content_register_quote_post_type();
wp_seed_content_register_gutenberg_block_bindings_source();

$failures = array();
$assertions = 0;
function seed_quote_contract_same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) { $failures[] = $label; }
}

seed_quote_contract_same(true, in_array('custom-fields', $GLOBALS['registered_post_types']['seed_quote']['supports'], true), 'custom fields support');
seed_quote_contract_same(true, $GLOBALS['registered_post_types']['seed_quote']['show_in_rest'], 'CPT REST');
seed_quote_contract_same(
    array('seed_quote_text', 'seed_quote_author', 'seed_quote_era', 'seed_quote_source', 'seed_quote_featured'),
    array_keys($GLOBALS['registered_post_meta']),
    'five public metadata keys'
);
foreach ($GLOBALS['registered_post_meta'] as $key => $registration) {
    seed_quote_contract_same('seed_quote', $registration[0], $key . ' post type');
    seed_quote_contract_same(true, $registration[1]['single'], $key . ' single');
    seed_quote_contract_same(true, $registration[1]['show_in_rest'], $key . ' REST');
}
seed_quote_contract_same('string', $GLOBALS['registered_post_meta']['seed_quote_text'][1]['type'], 'text REST type');
seed_quote_contract_same('boolean', $GLOBALS['registered_post_meta']['seed_quote_featured'][1]['type'], 'featured REST type');
seed_quote_contract_same('Legacy text', wp_seed_content_get_quote_builder_meta(41, 'seed_quote_text'), 'legacy text fallback');
seed_quote_contract_same('', wp_seed_content_get_quote_builder_meta(42, 'seed_quote_text'), 'canonical empty wins');
seed_quote_contract_same(true, wp_seed_content_quote_is_featured(41), 'legacy featured fallback');
seed_quote_contract_same(false, wp_seed_content_quote_is_featured(42), 'canonical false wins');
seed_quote_contract_same(true, wp_seed_content_quote_builder_meta_auth(false, 'seed_quote_text', 41), 'editor authorized');
seed_quote_contract_same(false, wp_seed_content_quote_builder_meta_auth(true, 'seed_quote_text', 42), 'non editor refused');
seed_quote_contract_same(
    true,
    isset($GLOBALS['registered_binding_sources']['wp-seed-content-kit/dynamic-data']),
    'Gutenberg binding source registered'
);
$block = new WP_Block();
foreach (array('quote.quote', 'quote.author', 'quote.era', 'quote.source') as $field_id) {
    seed_quote_contract_same(
        $field_id . '@41',
        wp_seed_content_get_gutenberg_binding_value(array('field_id' => $field_id), $block, 'content'),
        $field_id . ' Gutenberg binding'
    );
}

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $assertions . ' quote builder meta assertions' . PHP_EOL;
