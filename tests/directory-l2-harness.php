<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');

$GLOBALS['seed_l2_assertions'] = 0;
$GLOBALS['seed_l2_failures'] = array();
$GLOBALS['seed_l2_options'] = array();
$GLOBALS['seed_l2_post_types'] = array();
$GLOBALS['seed_l2_filters'] = array();
$GLOBALS['seed_l2_actions'] = array();
$GLOBALS['seed_l2_manual_order'] = array();
$GLOBALS['seed_l2_shortcode_calls'] = array();
$GLOBALS['seed_l2_writes'] = 0;

class Seed_L2_Role
{
    public $capabilities = array();

    public function add_cap($capability)
    {
        $this->capabilities[$capability] = true;
    }

    public function remove_cap($capability)
    {
        unset($this->capabilities[$capability]);
    }
}

$GLOBALS['seed_l2_roles'] = array(
    'administrator' => new Seed_L2_Role(),
    'editor' => new Seed_L2_Role(),
    'author' => new Seed_L2_Role(),
);

function seed_l2_assert($condition, $label)
{
    $GLOBALS['seed_l2_assertions']++;
    if (!$condition) {
        $GLOBALS['seed_l2_failures'][] = $label;
    }
}

function seed_l2_same($expected, $actual, $label)
{
    seed_l2_assert($expected === $actual, $label);
}

function __($text, $domain = null)
{
    return $text;
}

function sanitize_key($value)
{
    return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value));
}

function sanitize_text_field($value)
{
    return trim(strip_tags((string) $value));
}

function wp_parse_args($args, $defaults = array())
{
    return array_merge($defaults, is_array($args) ? $args : array());
}

function get_option($key, $default = false)
{
    return array_key_exists($key, $GLOBALS['seed_l2_options']) ? $GLOBALS['seed_l2_options'][$key] : $default;
}

function wp_roles()
{
    return (object) array(
        'roles' => array(
            'administrator' => array('name' => 'Administrator'),
            'editor' => array('name' => 'Editor'),
            'author' => array('name' => 'Author'),
        ),
    );
}

function get_role($role)
{
    return isset($GLOBALS['seed_l2_roles'][$role]) ? $GLOBALS['seed_l2_roles'][$role] : null;
}

function register_post_type($post_type, $args)
{
    $GLOBALS['seed_l2_post_types'][$post_type] = $args;
    return (object) $args;
}

function register_post_meta($post_type, $key, $args)
{
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['seed_l2_filters'][$hook][] = array($callback, $priority, $accepted_args);
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['seed_l2_actions'][$hook][] = array($callback, $priority, $accepted_args);
}

function wp_seed_content_kit_register_manual_order_for_post_type($post_type)
{
    $GLOBALS['seed_l2_manual_order'][] = $post_type;
}

function add_shortcode($tag, $callback)
{
    $GLOBALS['seed_l2_shortcode_calls'][$tag] = $callback;
}

function wp_insert_post($args)
{
    $GLOBALS['seed_l2_writes']++;
    return 1;
}

require WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-contract.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/core/capabilities.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/core/modules.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/bootstrap.php';

$defaults = wp_seed_content_kit_get_default_module_options();
seed_l2_same(true, $defaults['testimonials'], 'Testimonials default unchanged');
seed_l2_same(true, $defaults['quotes'], 'Quotes default unchanged');
seed_l2_same(true, $defaults['directory'], 'Directory enabled by default');
seed_l2_same(array('testimonials', 'quotes', 'directory'), array_keys(wp_seed_content_kit_get_module_options()), 'Canonical module options');

$modules = wp_seed_content_kit_get_modules();
seed_l2_assert(isset($modules['directory']), 'Directory registered');
seed_l2_same('Annuaire', $modules['directory']['label'], 'Directory visible label');
seed_l2_same('Gestion et affichage de fiches d’annuaire.', $modules['directory']['description'], 'Directory description');
seed_l2_same(true, $modules['directory']['active'], 'Directory active');
seed_l2_same(false, $modules['directory']['planned'], 'Directory no longer planned');
seed_l2_same(true, $modules['directory']['activable'], 'Directory activable');
seed_l2_same('seed_directory', $modules['directory']['post_type'], 'Directory post type in registry');
seed_l2_same('[seed_directory]', $modules['directory']['shortcode'], 'L4 Directory shortcode exposed in registry');
seed_l2_same('seed_testimonial', $modules['testimonials']['post_type'], 'Testimonials registry unchanged');
seed_l2_same('seed_quote', $modules['quotes']['post_type'], 'Quotes registry unchanged');
seed_l2_same('1.0', wp_seed_content_kit_get_contract_version(), 'Third-party contract version unchanged');

$map = wp_seed_content_directory_get_capability_map();
seed_l2_same(14, count($map), 'Fourteen WordPress capability keys');
seed_l2_same('edit_seed_directory_entry', $map['edit_post'], 'Object edit meta capability');
seed_l2_same('read_seed_directory_entry', $map['read_post'], 'Object read meta capability');
seed_l2_same('delete_seed_directory_entry', $map['delete_post'], 'Object delete meta capability');
foreach (array('edit_posts', 'edit_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts') as $key) {
    seed_l2_same('edit_seed_directory_entries', $map[$key], $key . ' mapping');
}
seed_l2_same('publish_seed_directory_entries', $map['publish_posts'], 'Publish mapping');
seed_l2_same('read_private_seed_directory_entries', $map['read_private_posts'], 'Private read mapping');
foreach (array('delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts') as $key) {
    seed_l2_same('delete_seed_directory_entries', $map[$key], $key . ' mapping');
}
$meta_caps = array('edit_seed_directory_entry', 'read_seed_directory_entry', 'delete_seed_directory_entry');
$primitive_values = array_values(array_unique(array_diff(array_values($map), $meta_caps)));
sort($primitive_values);
$expected_primitives = wp_seed_content_directory_get_primitive_capabilities();
sort($expected_primitives);
seed_l2_same($expected_primitives, $primitive_values, 'Only four primitive capabilities');

wp_seed_content_directory_grant_capabilities();
foreach ($expected_primitives as $capability) {
    seed_l2_assert(isset($GLOBALS['seed_l2_roles']['administrator']->capabilities[$capability]), 'Administrator receives ' . $capability);
    seed_l2_assert(isset($GLOBALS['seed_l2_roles']['editor']->capabilities[$capability]), 'Editor receives ' . $capability);
    seed_l2_assert(!isset($GLOBALS['seed_l2_roles']['author']->capabilities[$capability]), 'Author does not receive ' . $capability);
}

wp_seed_content_directory_register_post_type();
seed_l2_assert(isset($GLOBALS['seed_l2_post_types']['seed_directory']), 'seed_directory CPT registered');
$cpt = $GLOBALS['seed_l2_post_types']['seed_directory'];
foreach (array('public', 'publicly_queryable', 'show_in_rest', 'has_archive', 'rewrite', 'query_var') as $key) {
    seed_l2_same(false, $cpt[$key], $key . ' disabled');
}
seed_l2_same(true, $cpt['exclude_from_search'], 'Excluded from public search');
seed_l2_same(true, $cpt['show_ui'], 'Admin UI enabled');
seed_l2_same('wp-seed-content-kit', $cpt['show_in_menu'], 'Nested under Content Kit');
seed_l2_same(array('title', 'excerpt', 'thumbnail', 'page-attributes', 'revisions'), $cpt['supports'], 'L3 adds native revisions to minimal supports');
seed_l2_same(array('seed_directory_entry', 'seed_directory_entries'), $cpt['capability_type'], 'Capability type');
seed_l2_same(true, $cpt['map_meta_cap'], 'Meta capability mapping enabled');
seed_l2_same($map, $cpt['capabilities'], 'Explicit capability map attached');
seed_l2_same(array('seed_directory'), $GLOBALS['seed_l2_manual_order'], 'Manual order enabled');
seed_l2_assert(false === strpos(implode(' ', $cpt['labels']), 'Directory'), 'No English Directory label');

$row_actions = wp_seed_content_directory_filter_row_actions(
    array(
        'edit' => 'Edit',
        'inline hide-if-no-js' => 'Quick Edit',
        'view' => 'View',
        'preview' => 'Preview',
        'trash' => 'Trash',
    ),
    (object) array('post_type' => 'seed_directory')
);
seed_l2_assert(!isset($row_actions['inline hide-if-no-js']), 'Quick Edit removed');
seed_l2_assert(!isset($row_actions['view']), 'View action removed');
seed_l2_assert(!isset($row_actions['preview']), 'Preview action removed');
seed_l2_assert(isset($row_actions['trash']), 'Trash remains available');
$bulk_actions = wp_seed_content_directory_filter_bulk_actions(array('edit' => 'Edit', 'publish' => 'Publish', 'trash' => 'Trash'));
seed_l2_assert(!isset($bulk_actions['edit']), 'Bulk Edit removed');
seed_l2_assert(!isset($bulk_actions['publish']), 'Bulk publish removed');
seed_l2_assert(isset($bulk_actions['trash']), 'Bulk trash remains available');
seed_l2_same('', wp_seed_content_directory_filter_preview_link(
    'https://example.test/preview',
    (object) array('post_type' => 'seed_directory')
), 'Preview link removed');
seed_l2_same('https://example.test/preview', wp_seed_content_directory_filter_preview_link(
    'https://example.test/preview',
    (object) array('post_type' => 'post')
), 'Other preview links unchanged');

$sitemaps = wp_seed_content_directory_filter_core_sitemaps(array('post' => true, 'seed_directory' => true));
seed_l2_assert(!isset($sitemaps['seed_directory']), 'Core sitemap excludes Directory');
seed_l2_same(true, wp_seed_content_directory_filter_yoast_sitemap(false, 'seed_directory'), 'Yoast sitemap excludes Directory');
seed_l2_same(false, wp_seed_content_directory_filter_yoast_sitemap(false, 'post'), 'Yoast filter leaves other post types unchanged');

$GLOBALS['seed_l2_options']['wp_seed_content_kit_modules'] = array(
    'testimonials' => true,
    'quotes' => true,
    'directory' => false,
);
$GLOBALS['seed_l2_saved_entry'] = array('ID' => 42, 'post_type' => 'seed_directory', 'post_status' => 'draft');
seed_l2_same(false, wp_seed_content_kit_is_module_active('directory'), 'Directory can be disabled');
seed_l2_same(42, $GLOBALS['seed_l2_saved_entry']['ID'], 'Disabling preserves entries');
foreach ($expected_primitives as $capability) {
    seed_l2_assert(isset($GLOBALS['seed_l2_roles']['administrator']->capabilities[$capability]), 'Disabling preserves ' . $capability);
}
$GLOBALS['seed_l2_options']['wp_seed_content_kit_modules']['directory'] = true;
wp_seed_content_directory_register_post_type();
seed_l2_same(true, wp_seed_content_kit_is_module_active('directory'), 'Directory can be re-enabled');
seed_l2_same(42, $GLOBALS['seed_l2_saved_entry']['ID'], 'Re-enabling preserves entries');

seed_l2_same(0, $GLOBALS['seed_l2_writes'], 'Activation helpers create no content');
seed_l2_same(array(
    'seed_directory' => 'wp_seed_content_directory_shortcode',
    'wp_seed_directory' => 'wp_seed_content_directory_shortcode',
), $GLOBALS['seed_l2_shortcode_calls'], 'L4 registers canonical shortcode and compatibility alias');
seed_l2_same(false, function_exists('wp_seed_content_directory_get_entry_data'), 'Legacy Directory Data API remains absent');
seed_l2_same(true, function_exists('wp_seed_content_directory_get_public_data'), 'L4 public Directory Data API available');
seed_l2_same(true, function_exists('wp_seed_content_directory_get_entries'), 'L4 Directory Collections available');
seed_l2_same(false, function_exists('wp_seed_content_directory_render'), 'No Directory renderer in L2');

if (!empty($GLOBALS['seed_l2_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_l2_failures']) . ' / ' . $GLOBALS['seed_l2_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_l2_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_l2_assertions'] . ' Annuaire L2 assertions' . PHP_EOL;
