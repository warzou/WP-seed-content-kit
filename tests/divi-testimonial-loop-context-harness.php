<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['filters'] = array();
$GLOBALS['actions'] = array();
function add_filter($hook, $callback, $priority = 10, $args = 1) { $GLOBALS['filters'][$hook][] = $callback; }
function add_action($hook, $callback, $priority = 10, $args = 1) { $GLOBALS['actions'][$hook][] = $callback; }
function absint($value) { return abs((int) $value); }
function is_wp_error($value) { return false; }
function wp_seed_content_testimonial_is_publicly_visible($id) { return 2 !== (int) $id; }
function wp_seed_content_resolve_dynamic_data($field, $context) {
    return 'testimonial.photo' === $field
        ? array('url' => 'photo-' . $context['current_post_id'] . '.jpg')
        : $field . ':' . $context['current_post_id'];
}
class LoopRequest { public function get_route() { return '/divi/v1/loop/query-results'; } }
class LoopResponse { private $data; public function __construct($data) { $this->data = $data; } public function get_data() { return $this->data; } public function set_data($data) { $this->data = $data; } }
require_once __DIR__ . '/../plugin/includes/integrations/divi/testimonial-loop-context.php';
$items = array(
    array('id' => 1, 'post_type' => 'seed_testimonial'),
    array('id' => 3, 'post_type' => 'seed_testimonial'),
    array('id' => 5, 'post_type' => 'seed_testimonial'),
    array('id' => 2, 'post_type' => 'seed_testimonial'),
    array('id' => 4, 'post_type' => 'post'),
);
$response = new LoopResponse(array('data' => array('items' => $items)));
wp_seed_content_divi_add_testimonial_loop_dynamic_data($response, null, new LoopRequest());
$data = $response->get_data(); $items = $data['data']['items'];
$failures = array(); $assertions = 0;
function same($expected, $actual, $label) { global $failures, $assertions; $assertions++; if ($expected !== $actual) { $failures[] = $label; } }
$sources = wp_seed_content_divi_testimonial_loop_sources();
same(9, count($sources), 'nine custom testimonial loop sources');
foreach ($sources as $source => $field) {
    $expected_one = 'testimonial.photo' === $field ? 'photo-1.jpg' : $field . ':1';
    $expected_two = 'testimonial.photo' === $field ? 'photo-3.jpg' : $field . ':3';
    $expected_three = 'testimonial.photo' === $field ? 'photo-5.jpg' : $field . ':5';
    same($expected_one, $items[0][$source], $source . ' item one');
    same($expected_two, $items[1][$source], $source . ' item two');
    same($expected_three, $items[2][$source], $source . ' item three');
    same(false, isset($items[3][$source]), $source . ' private item absent');
}
same(false, isset($items[4]['wpsck_testimonial_full']), 'non testimonial untouched');
same('wpsck_testimonial_visual', array_keys($sources)[0], 'response keys match Divi loop map keys');
same(false, (bool) preg_grep('/^loop_/', array_keys($sources)), 'response keys omit the loop prefix as Divi expects');
foreach (array_keys($sources) as $source) {
    same(true, 0 === strpos('loop_' . $source, 'loop_wpsck_testimonial_'), 'provider name uses the native Divi loop namespace');
}
same(true, isset($GLOBALS['filters']['rest_post_dispatch']), 'REST response hook registered');
same(false, isset($GLOBALS['actions']['divi_visual_builder_assets_before_enqueue_scripts']), 'no Visual Builder wrapper hook registered');
if ($failures) { fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL); exit(1); }
echo 'PASS ' . $assertions . ' assertions' . PHP_EOL;