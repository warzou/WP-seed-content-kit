<?php
define('ABSPATH', __DIR__ . '/');
$posts = array();
$meta = array();
$assertions = 0;
function add_action($hook, $callback, $priority = 10, $args = 1) {}
function get_posts($args) { return array_values($GLOBALS['posts']); }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? $GLOBALS['posts'][$id] : null; }
function get_post_meta($id, $key, $single = true) { return isset($GLOBALS['meta'][$id][$key]) ? $GLOBALS['meta'][$id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$id][$key] = $value; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); }
function wp_is_post_revision($id) { return false; }
function wp_seed_content_directory_get_meta_value($id, $key) { return isset($GLOBALS['meta'][$id][$key]) ? $GLOBALS['meta'][$id][$key] : array(); }
function wp_update_post($data) { $GLOBALS['posts'][$data['ID']]->menu_order = $data['menu_order']; return $data['ID']; }
function same($expected, $actual, $label) { $GLOBALS['assertions']++; if ($expected !== $actual) { throw new Exception($label . ': ' . var_export($actual, true)); } }
function fixture($id, $order, $types = array('intervenant'), $status = 'draft') {
    $post = (object) array('ID' => $id, 'post_type' => 'seed_directory', 'post_status' => $status, 'menu_order' => $order);
    $GLOBALS['posts'][$id] = $post;
    $GLOBALS['meta'][$id]['_seed_directory_profile_types'] = $types;
    return $post;
}
require dirname(__DIR__) . '/plugin/includes/modules/directory/initial-order.php';
require dirname(__DIR__) . '/plugin/includes/core/manual-order.php';
$_POST = array();
fixture(1, 10); fixture(2, 20); fixture(3, 30);
fixture(4, 900, array('praticien'));
$new = fixture(5, 0);
wp_seed_content_kit_manual_order_ensure_auto_position(5, $new, false, 'seed_directory');
same(0, $new->menu_order, 'shared allocator defers Directory to profile-aware save');
wp_seed_content_directory_initial_order_created(5, $new, false, null);
same(40, $new->menu_order, 'new intervenant ignores practitioner maximum');
same(30, $posts[3]->menu_order, 'existing position untouched');
same('', get_post_meta(5, '_wpsck_directory_initial_order_pending'), 'pending marker cleared');
unset($posts[3], $posts[5]);
$posts[2]->menu_order = 50;
$new = fixture(6, 0);
wp_seed_content_directory_initial_order_created(6, $new, false, null);
same(60, $new->menu_order, 'gaps not filled');
unset($posts[6]);
$new = fixture(7, 0);
wp_seed_content_directory_initial_order_created(7, $new, false, null);
same(60, $new->menu_order, 'deletion followed by new creation uses current maximum');
$new->menu_order = 15;
wp_seed_content_kit_manual_order_repair_legacy('seed_directory');
same(15, $new->menu_order, 'shared repair cannot renumber Directory');
wp_seed_content_directory_initial_order_created(7, $new, true, clone $new);
same(15, $new->menu_order, 'manual later edit retained');
$new->post_status = 'publish';
wp_seed_content_directory_initial_order_created(7, $new, true, (object) array('post_status' => 'draft'));
same(15, $new->menu_order, 'reactivation retained');
$_POST = array('seed_directory_menu_order' => '0');
$manual = fixture(8, 0);
wp_seed_content_directory_initial_order_created(8, $manual, false, null);
same(0, $manual->menu_order, 'explicit manual zero retained');
$_POST = array();
$duplicate = fixture(9, 15);
wp_seed_content_directory_initial_order_created(9, $duplicate, false, null);
same(15, $duplicate->menu_order, 'duplicator explicit copied position retained');
$delayed = fixture(10, 0, array());
wp_seed_content_directory_initial_order_created(10, $delayed, true, (object) array('post_status' => 'auto-draft'));
same(0, $delayed->menu_order, 'REST waits for metabox profile');
same('1', get_post_meta(10, '_wpsck_directory_initial_order_pending'), 'new record only pending marker');
$meta[10]['_seed_directory_profile_types'] = array('intervenant');
wp_seed_content_directory_finish_initial_order(10);
same(60, $delayed->menu_order, 'metabox finalizes initial allocation');
same('', get_post_meta(10, '_wpsck_directory_initial_order_pending'), 'delayed marker cleared');
$posts = array(); $meta = array();
fixture(11, 900, array('intervenant'), 'trash');
fixture(12, 800, array('intervenant'), 'auto-draft');
$empty = fixture(13, 0);
wp_seed_content_directory_initial_order_created(13, $empty, false, null);
same(10, $empty->menu_order, 'empty usable population starts at ten');
$posts = array(); $meta = array(); $_POST = array();
same(0, count($posts), 'fixtures cleaned');
echo 'PASS ' . $assertions . ' initial order assertions' . PHP_EOL;
