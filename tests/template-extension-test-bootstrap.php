<?php

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/wordpress/');
}
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

$GLOBALS['wp_seed_contract_actions'] = array();
$GLOBALS['wp_seed_contract_posts'] = array();
$GLOBALS['wp_seed_contract_meta'] = array();

$GLOBALS['wp_seed_contract_registered_styles'] = array('wp-seed-content-kit' => true, 'third-party-card' => true);
$GLOBALS['wp_seed_contract_registered_scripts'] = array('third-party-interaction' => true);
$GLOBALS['wp_seed_contract_enqueued_styles'] = array();
$GLOBALS['wp_seed_contract_enqueued_scripts'] = array();
$GLOBALS['wp_seed_contract_provider_context'] = array();
$GLOBALS['wp_seed_contract_nested_codes'] = array();
$GLOBALS['wp_seed_contract_render_context'] = array();
$GLOBALS['wp_seed_contract_template_queries'] = 0;
$GLOBALS['wp_seed_contract_assertions'] = 0;
$GLOBALS['wp_seed_contract_failures'] = array();

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['wp_seed_contract_actions'][$hook][$priority][] = $callback;
}

function do_action($hook)
{
    if (empty($GLOBALS['wp_seed_contract_actions'][$hook])) {
        return;
    }
    ksort($GLOBALS['wp_seed_contract_actions'][$hook]);
    foreach ($GLOBALS['wp_seed_contract_actions'][$hook] as $callbacks) {
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }
}

function sanitize_key($value)
{
    return preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $value));
}

function sanitize_title($value)
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9_-]+/', '-', $value);
    return trim($value, '-');
}

function sanitize_text_field($value)
{
    return is_scalar($value) ? trim(strip_tags((string) $value)) : '';
}

function sanitize_email($value)
{
    $value = filter_var((string) $value, FILTER_SANITIZE_EMAIL);
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
}

function absint($value)
{
    return abs((int) $value);
}

function esc_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_attr($value)
{
    return esc_html($value);
}

function esc_url_raw($value, $protocols = null)
{
    $value = trim((string) $value);
    return preg_match('#^https?://#i', $value) ? $value : '';
}

function esc_url($value)
{
    $value = esc_url_raw($value);
    return '' === $value ? '' : esc_attr($value);
}

function __($text, $domain = 'default')
{
    return $text;
}

function wp_kses($html, $allowed_html)
{
    $tags = '<' . implode('><', array_keys($allowed_html)) . '>';
    $html = strip_tags((string) $html, $tags);
    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s+(?:src|href)\s*=\s*("|\')javascript:[^"\']*\1/i', '', $html);
    return $html;
}

function wp_kses_post($html)
{
    return wp_kses($html, array('p' => array(), 'br' => array(), 'strong' => array(), 'em' => array(), 'img' => array()));
}

function wp_strip_all_tags($value)
{
    return strip_tags((string) $value);
}

function wp_style_is($handle, $state = 'enqueued')
{
    if ('registered' === $state) {
        return !empty($GLOBALS['wp_seed_contract_registered_styles'][$handle]);
    }
    return !empty($GLOBALS['wp_seed_contract_enqueued_styles'][$handle]);
}

function wp_script_is($handle, $state = 'enqueued')
{
    if ('registered' === $state) {
        return !empty($GLOBALS['wp_seed_contract_registered_scripts'][$handle]);
    }
    return !empty($GLOBALS['wp_seed_contract_enqueued_scripts'][$handle]);
}

function wp_enqueue_style($handle)
{
    $GLOBALS['wp_seed_contract_enqueued_styles'][$handle] = true;
}

function wp_enqueue_script($handle)
{
    $GLOBALS['wp_seed_contract_enqueued_scripts'][$handle] = true;
}

function get_page_by_path($slug, $output = OBJECT, $post_type = 'page')
{
    $GLOBALS['wp_seed_contract_template_queries']++;
    foreach ($GLOBALS['wp_seed_contract_posts'] as $post) {
        if ($post->post_name === $slug && $post->post_type === $post_type) {
            return $post;
        }
    }
    return null;
}

function get_post($post_id)
{
    return isset($GLOBALS['wp_seed_contract_posts'][$post_id]) ? $GLOBALS['wp_seed_contract_posts'][$post_id] : null;
}

function get_post_meta($post_id, $key, $single = false)
{
    return isset($GLOBALS['wp_seed_contract_meta'][$post_id][$key]) ? $GLOBALS['wp_seed_contract_meta'][$post_id][$key] : '';
}



function do_blocks($content)
{
    return preg_replace('/<!--\s*\/?wp:[^>]+-->/', '', (string) $content);
}

function do_shortcode($content)
{
    return (string) $content;
}

function apply_filters($hook, $value)
{
    if ('the_content' !== $hook) {
        return $value;
    }

    return preg_replace_callback(
        '/\[\[render:([a-z0-9_-]+)\]\]/',
        function ($matches) {
            $result = wp_seed_content_kit_render_template(
                $matches[1],
                'third_party',
                $GLOBALS['wp_seed_contract_render_context']
            );
            $GLOBALS['wp_seed_contract_nested_codes'][] = $result->get_code();
            return $result->get_html();
        },
        (string) $value
    );
}

function wp_seed_contract_add_template($id, $slug, $module, $status, $content)
{
    $post = (object) array(
        'ID' => (int) $id,
        'post_name' => $slug,
        'post_type' => 'seed_template',
        'post_status' => $status,
        'post_content' => $content,
    );
    $GLOBALS['wp_seed_contract_posts'][$id] = $post;
    $GLOBALS['wp_seed_contract_meta'][$id]['_wp_seed_content_template_module'] = $module;
    return $post;
}

function wp_seed_contract_same($expected, $actual, $message)
{
    $GLOBALS['wp_seed_contract_assertions']++;
    if ($expected !== $actual) {
        $GLOBALS['wp_seed_contract_failures'][] = $message . ' expected ' . var_export($expected, true) . ' got ' . var_export($actual, true);
    }
}

function wp_seed_contract_true($actual, $message)
{
    wp_seed_contract_same(true, (bool) $actual, $message);
}

function wp_seed_contract_contains($needle, $haystack, $message)
{
    wp_seed_contract_true(false !== strpos((string) $haystack, (string) $needle), $message);
}

function wp_seed_contract_not_contains($needle, $haystack, $message)
{
    wp_seed_contract_true(false === strpos((string) $haystack, (string) $needle), $message);
}

function wp_seed_contract_finish($label)
{
    if (!empty($GLOBALS['wp_seed_contract_failures'])) {
        fwrite(STDERR, 'FAIL ' . count($GLOBALS['wp_seed_contract_failures']) . ' / ' . $GLOBALS['wp_seed_contract_assertions'] . PHP_EOL);
        foreach ($GLOBALS['wp_seed_contract_failures'] as $failure) {
            fwrite(STDERR, '- ' . $failure . PHP_EOL);
        }
        exit(1);
    }
    echo 'PASS ' . $GLOBALS['wp_seed_contract_assertions'] . ' ' . $label . ' assertions' . PHP_EOL;
}
