<?php
class WP_Error {
    private $code; private $message; private $data;
    public function __construct($code, $message = '', $data = null) { $this->code=$code; $this->message=$message; $this->data=$data; }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
    public function get_error_data() { return $this->data; }
}
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_slash($value) { return $value; }
function add_shortcode($name, $callback) {}
function get_the_title($id) { return get_post($id)->post_title; }
function get_post_status($id) { return get_post($id)->post_status; }
function wp_date($format, $timestamp = null) { return date($format, $timestamp ?: time()); }
function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return esc_attr($s); }
function shortcode_atts($defaults, $args, $name = '') { return array_merge($defaults, $args); }
function wp_list_pluck($items, $key) { return array_column($items, $key); }
function wp_insert_post($payload, $return_error = false) {
    static $next = 1000;
    $id = isset($payload['ID']) ? $payload['ID'] : ++$next;
    $old = get_post($id); $old_status = $old ? $old->post_status : 'new';
    if (!$old) $GLOBALS['fm_posts'][$id] = new WP_Post($id, $payload['post_type'], $payload['post_title'], $payload['post_status']);
    foreach ($payload as $key => $value) $GLOBALS['fm_posts'][$id]->$key = $value;
    wp_seed_content_formation_capture_schedule_history($GLOBALS['fm_posts'][$id]->post_status, $old_status, $GLOBALS['fm_posts'][$id]);
    return $id;
}
function wp_update_post($payload, $return_error = false) { return wp_insert_post($payload, $return_error); }
function wp_trash_post($id) {
    $post = get_post($id);
    if (false === wp_seed_content_formation_guard_referenced_delete(null, $post)) return false;
    $post->post_status = 'trash'; return $post;
}
function wp_delete_post($id, $force = false) {
    $post = get_post($id);
    if (false === wp_seed_content_formation_guard_referenced_delete(null, $post)) return false;
    unset($GLOBALS['fm_posts'][$id], $GLOBALS['fm_meta'][$id]); return $post;
}
require __DIR__ . '/formation-model-harness.php';
require dirname(__DIR__) . '/plugin/includes/modules/formation/admin.php';
require dirname(__DIR__) . '/plugin/includes/modules/formation/shortcode.php';
require __DIR__ . '/formation-backend-cases.php';
$GLOBALS['fm_posts'][90] = new WP_Post(90, 'seed_directory', 'Second person');
$result = wpsck_formation_backend_cases();
$before = serialize(array($GLOBALS['fm_posts'], $GLOBALS['fm_meta']));
$GLOBALS['wpdb']->allow_lock = false;
$locked = wp_seed_content_formation_store_schedule(array());
$result['checks']['lock_unavailable_fails_closed'] = is_wp_error($locked) && $locked->get_error_code() === 'schedule_busy';
$result['checks']['lock_unavailable_no_writes'] = $before === serialize(array($GLOBALS['fm_posts'], $GLOBALS['fm_meta']));
$GLOBALS['wpdb']->allow_lock = true;
$GLOBALS['wpdb']->events = array();
wp_seed_content_formation_store_schedule(array());
$result['checks']['lock_released_after_validation_failure'] = count($GLOBALS['wpdb']->events) === 2 && strpos($GLOBALS['wpdb']->events[1], 'RELEASE_LOCK') !== false;
$GLOBALS['fm_denied']['publish_seed_schedules'] = true;
$result['checks']['edit_only_cannot_publish'] = is_wp_error(wp_seed_content_formation_store_schedule(array()));
$GLOBALS['fm_denied']['edit_seed_schedules'] = true;
$result['checks']['no_edit_capability_rejected'] = is_wp_error(wp_seed_content_formation_store_schedule(array()));
$failures = array_keys(array_filter($result['checks'], function ($value) { return !$value; }));
echo json_encode(array('assertions' => count($result['checks']), 'failures' => $failures), JSON_PRETTY_PRINT) . PHP_EOL;
exit($failures ? 1 : 0);
