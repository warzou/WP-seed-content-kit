<?php

define('ABSPATH', __DIR__ . '/');

$GLOBALS['seed_role_options'] = array();
$GLOBALS['seed_role_actions'] = array();
$GLOBALS['seed_role_assertions'] = 0;
$GLOBALS['seed_role_failures'] = array();

class Seed_Role_Capability_Role
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

$GLOBALS['seed_role_roles'] = array(
    'administrator' => new Seed_Role_Capability_Role(),
    'editor' => new Seed_Role_Capability_Role(),
    'author' => new Seed_Role_Capability_Role(),
);

function seed_role_assert($condition, $label)
{
    $GLOBALS['seed_role_assertions']++;
    if (!$condition) {
        $GLOBALS['seed_role_failures'][] = $label;
    }
}

function seed_role_same($expected, $actual, $label)
{
    seed_role_assert($expected === $actual, $label);
}

function sanitize_key($value)
{
    return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value));
}

function get_option($key, $default = false)
{
    return array_key_exists($key, $GLOBALS['seed_role_options']) ? $GLOBALS['seed_role_options'][$key] : $default;
}

function update_option($key, $value, $autoload = null)
{
    $GLOBALS['seed_role_options'][$key] = $value;
    return true;
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['seed_role_actions'][$hook][] = array($callback, $priority, $accepted_args);
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
    return isset($GLOBALS['seed_role_roles'][$role]) ? $GLOBALS['seed_role_roles'][$role] : null;
}

require dirname(__DIR__) . '/plugin/includes/core/capabilities.php';

$advanced = wp_seed_content_kit_get_advanced_capabilities();
seed_role_same(array(
    'manage_wp_seed_content_kit',
    'manage_wp_seed_templates',
    'manage_wp_seed_collections',
    'manage_wp_seed_integrations',
    'manage_wp_seed_roles',
    'manage_wp_seed_imports',
), $advanced, 'Advanced capability set is exact');

$definitions = wp_seed_content_kit_get_content_capability_definitions();
seed_role_same(array('testimonials', 'quotes', 'directory'), array_keys($definitions), 'Three native content modules registered');
seed_role_same(array('seed_testimonial', 'seed_testimonials'), array_values($definitions['testimonials']), 'Testimonials capability nouns');
seed_role_same(array('seed_quote', 'seed_quotes'), array_values($definitions['quotes']), 'Quotes capability nouns');
seed_role_same(array('seed_directory_entry', 'seed_directory_entries'), array_values($definitions['directory']), 'Directory capability nouns');

$defaults = wp_seed_content_kit_get_default_module_role_assignments();
foreach (array_keys($definitions) as $module) {
    seed_role_same(array('administrator', 'editor'), $defaults[$module], $module . ' defaults to administrator and editor');
    $primitives = wp_seed_content_kit_get_primitive_capabilities($module);
    $map = wp_seed_content_kit_get_capability_map($module);
    seed_role_same(4, count($primitives), $module . ' has four primitive capabilities');
    seed_role_same(14, count($map), $module . ' has fourteen WordPress capability keys');
    seed_role_same($primitives[0], $map['edit_posts'], $module . ' edit mapping');
    seed_role_same($primitives[0], $map['edit_others_posts'], $module . ' edit others mapping');
    seed_role_same($primitives[0], $map['edit_private_posts'], $module . ' edit private mapping');
    seed_role_same($primitives[0], $map['edit_published_posts'], $module . ' edit published mapping');
    seed_role_same($primitives[0], $map['create_posts'], $module . ' create mapping');
    seed_role_same($primitives[1], $map['publish_posts'], $module . ' publish mapping');
    seed_role_same($primitives[2], $map['read_private_posts'], $module . ' private read mapping');
    seed_role_same($primitives[3], $map['delete_posts'], $module . ' delete mapping');
    seed_role_same($primitives[3], $map['delete_others_posts'], $module . ' delete others mapping');
}
seed_role_same(array(), wp_seed_content_kit_get_primitive_capabilities('unknown'), 'Unknown module has no primitive capability');
seed_role_same(array(), wp_seed_content_kit_get_capability_map('unknown'), 'Unknown module has no map');

wp_seed_content_kit_synchronize_role_capabilities();
foreach ($advanced as $capability) {
    seed_role_assert(isset($GLOBALS['seed_role_roles']['administrator']->capabilities[$capability]), 'Administrator receives ' . $capability);
    seed_role_assert(!isset($GLOBALS['seed_role_roles']['editor']->capabilities[$capability]), 'Editor does not receive ' . $capability);
    seed_role_assert(!isset($GLOBALS['seed_role_roles']['author']->capabilities[$capability]), 'Author does not receive ' . $capability);
}
foreach (array_keys($definitions) as $module) {
    foreach (wp_seed_content_kit_get_primitive_capabilities($module) as $capability) {
        seed_role_assert(isset($GLOBALS['seed_role_roles']['administrator']->capabilities[$capability]), 'Administrator content capability ' . $capability);
        seed_role_assert(isset($GLOBALS['seed_role_roles']['editor']->capabilities[$capability]), 'Editor content capability ' . $capability);
        seed_role_assert(!isset($GLOBALS['seed_role_roles']['author']->capabilities[$capability]), 'Author lacks content capability ' . $capability);
    }
}

$custom = array(
    'testimonials' => array('administrator', 'editor'),
    'quotes' => array('administrator'),
    'directory' => array('administrator'),
);
wp_seed_content_kit_synchronize_role_capabilities($custom);
foreach (wp_seed_content_kit_get_primitive_capabilities('testimonials') as $capability) {
    seed_role_assert(isset($GLOBALS['seed_role_roles']['editor']->capabilities[$capability]), 'Configured editor keeps Testimonials ' . $capability);
}
foreach (array('quotes', 'directory') as $module) {
    foreach (wp_seed_content_kit_get_primitive_capabilities($module) as $capability) {
        seed_role_assert(!isset($GLOBALS['seed_role_roles']['editor']->capabilities[$capability]), 'Unconfigured editor loses ' . $capability);
        seed_role_assert(isset($GLOBALS['seed_role_roles']['administrator']->capabilities[$capability]), 'Administrator always keeps ' . $capability);
    }
}

$GLOBALS['seed_role_options']['wp_seed_content_kit_module_roles'] = array(
    'testimonials' => array('author'),
    'quotes' => array('editor'),
    'directory' => array(),
);
$stored = wp_seed_content_kit_get_module_role_assignments();
seed_role_same(array('author', 'administrator'), $stored['testimonials'], 'Stored custom role retained and Administrator forced');
seed_role_same(array('editor', 'administrator'), $stored['quotes'], 'Stored Editor retained and Administrator forced');
seed_role_same(array('administrator'), $stored['directory'], 'Empty stored assignment still retains Administrator');
seed_role_same(true, wp_seed_content_kit_role_manages_module('editor', 'quotes'), 'Role predicate follows stored assignment');
seed_role_same(false, wp_seed_content_kit_role_manages_module('editor', 'directory'), 'Role predicate rejects unassigned module');

$template_map = wp_seed_content_kit_get_template_capability_map();
seed_role_same(14, count($template_map), 'Template map has fourteen WordPress keys');
foreach ($template_map as $capability) {
    seed_role_same('manage_wp_seed_templates', $capability, 'Every Template operation uses the advanced capability');
}

$root = dirname(__DIR__);
$testimonial_source = file_get_contents($root . '/plugin/includes/modules/testimonials/post-type.php');
$quote_source = file_get_contents($root . '/plugin/includes/modules/quotes/post-type.php');
$directory_source = file_get_contents($root . '/plugin/includes/modules/directory/post-type.php');
$template_source = file_get_contents($root . '/plugin/includes/core/templates.php');
$menu_source = file_get_contents($root . '/plugin/includes/admin/modules-page.php');
$module_menu_source = file_get_contents($root . '/plugin/includes/core/module-menu.php');
$usage_source = file_get_contents($root . '/plugin/includes/admin/usage-page.php');

foreach (array($testimonial_source, $quote_source, $directory_source) as $source) {
    seed_role_assert(false !== strpos($source, "'map_meta_cap' => true"), 'Content CPT maps object capabilities');
}
seed_role_assert(false !== strpos($testimonial_source, 'wp_seed_content_kit_get_capability_map'), 'Testimonials use central capability map');
seed_role_assert(false !== strpos($quote_source, 'wp_seed_content_kit_get_capability_map'), 'Quotes use central capability map');
seed_role_assert(false !== strpos($directory_source, 'wp_seed_content_directory_get_capability_map'), 'Directory uses its compatible central-map wrapper');
seed_role_assert(false !== strpos($template_source, 'wp_seed_content_kit_get_template_capability_map'), 'Templates use dedicated capability map');
seed_role_assert(false !== strpos($menu_source, "'manage_wp_seed_content_kit'"), 'Configuration uses dedicated capability');
seed_role_assert(false !== strpos($menu_source, "'manage_wp_seed_integrations'"), 'Usage menu uses dedicated capability');
seed_role_assert(false !== strpos($menu_source, 'wp_seed_content_kit_get_module_content_capability'), 'Module submenus use content capabilities');
seed_role_assert(false === strpos($module_menu_source, 'array_intersect($user->roles'), 'Root menus do not authorize from role names');
seed_role_assert(false !== strpos($module_menu_source, 'current_user_can($capability)'), 'Root menus require actual CPT capability');
seed_role_assert(false !== strpos($usage_source, "current_user_can('manage_wp_seed_integrations')"), 'Usage enforces its advanced capability');
$capability_source = file_get_contents($root . '/plugin/includes/core/capabilities.php');
seed_role_assert(false === strpos($capability_source, 'register_rest_route'), 'Capabilities add no REST route');
seed_role_assert(false === strpos($capability_source, 'wp_ajax_'), 'Capabilities add no AJAX action');

if (!empty($GLOBALS['seed_role_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_role_failures']) . ' / ' . $GLOBALS['seed_role_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_role_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_role_assertions'] . ' CK-A2 role and capability assertions' . PHP_EOL;