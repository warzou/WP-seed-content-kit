<?php

define('ABSPATH', __DIR__ . '/');

$testimonial_more_admin_meta = array();
$testimonial_more_admin_posts = array(
    900001 => array('post_excerpt' => ''),
);

function sanitize_text_field($value)
{
    return trim(strip_tags((string) $value));
}

function sanitize_textarea_field($value)
{
    return strip_tags((string) $value);
}

function wp_kses_post($value)
{
    return preg_replace('/<(script|iframe)\b[^>]*>.*?<\/\1>/is', '', (string) $value);
}

function wp_unslash($value)
{
    return is_string($value) ? stripslashes($value) : $value;
}

function wp_slash($value)
{
    if (is_array($value)) {
        return array_map('wp_slash', $value);
    }
    return is_string($value) ? addslashes($value) : $value;
}

function wp_verify_nonce($nonce, $action)
{
    return 'valid' === $nonce && 'wp_seed_content_save_testimonial_meta' === $action;
}

function wp_is_post_revision($post_id)
{
    return false;
}

function current_user_can($capability, $post_id = 0)
{
    return true;
}

function update_post_meta($post_id, $key, $value)
{
    global $testimonial_more_admin_meta;
    $testimonial_more_admin_meta[$post_id][$key] = $value;
    return true;
}

function get_post_meta($post_id, $key, $single = false)
{
    global $testimonial_more_admin_meta;
    return isset($testimonial_more_admin_meta[$post_id][$key])
        ? $testimonial_more_admin_meta[$post_id][$key]
        : '';
}

function delete_post_meta($post_id, $key)
{
    global $testimonial_more_admin_meta;
    unset($testimonial_more_admin_meta[$post_id][$key]);
    return true;
}

function wp_update_post($postarr, $wp_error = false)
{
    global $testimonial_more_admin_posts;
    $postarr = wp_unslash($postarr);
    $post_id = isset($postarr['ID']) ? (int) $postarr['ID'] : 0;
    if (isset($postarr['post_excerpt'])) {
        $testimonial_more_admin_posts[$post_id]['post_excerpt'] = $postarr['post_excerpt'];
    }
    return $post_id;
}

function absint($value)
{
    return abs((int) $value);
}

function set_post_thumbnail($post_id, $thumbnail_id)
{
    return true;
}

function delete_post_thumbnail($post_id)
{
    return true;
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
}

require_once __DIR__ . '/../plugin/includes/core/helpers.php';
require_once __DIR__ . '/../plugin/includes/core/content-data.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/builder-meta.php';
require_once __DIR__ . '/../plugin/includes/modules/testimonials/save-meta.php';

$assertions = 0;
$failures = array();

function testimonial_admin_save_same($expected, $actual, $label)
{
    global $assertions, $failures;
    $assertions++;
    if ($expected !== $actual) {
        $failures[] = $label;
    }
}

function testimonial_admin_save($value)
{
    $_POST = array(
        'wp_seed_content_testimonial_nonce' => 'valid',
        'seed_testimonial_text' => addslashes($value),
    );
    global $testimonial_more_admin_posts;
    wp_seed_content_save_testimonial_meta(900001, (object) array(
        'post_type' => 'seed_testimonial',
        'post_excerpt' => $testimonial_more_admin_posts[900001]['post_excerpt'],
    ));
    return get_post_meta(900001, 'seed_testimonial_text', true);
}

$stored = testimonial_admin_save("<p>Introduction</p>\n<!--more-->\n<p>Suite</p>");
testimonial_admin_save_same(true, false !== strpos($stored, '<!--more-->'), 'admin save preserves More marker');
testimonial_admin_save_same(
    "<p>Introduction</p>\n<!--more-->\n<p>Suite</p>",
    $stored,
    'admin save reloads exact simple More content'
);

$parts = wp_seed_content_split_wordpress_more($stored);
testimonial_admin_save_same('<p>Introduction</p>', $parts['intro'], 'intro after admin save');
testimonial_admin_save_same('<p>Suite</p>', $parts['more'], 'more projection after admin save');
testimonial_admin_save_same(true, $parts['has_more'], 'has_more after admin save');
testimonial_admin_save_same(
    false,
    false !== strpos($parts['full'], '<!--more'),
    'full projection omits technical marker'
);

$custom = testimonial_admin_save('<p>A</p><!--more Lire la suite--><p>B</p>');
testimonial_admin_save_same(true, false !== strpos($custom, '<!--more Lire la suite-->'), 'custom More text persists');

$noteaser = testimonial_admin_save('<p>A</p><!--more--><!--noteaser--><p>B</p>');
testimonial_admin_save_same(true, false !== strpos($noteaser, '<!--noteaser-->'), 'noteaser persists');

$unsafe = testimonial_admin_save(
    '<p>A</p><!--random-data--><!--script payload--><script>alert(1)</script><p>B</p>'
);
testimonial_admin_save_same(false, false !== strpos($unsafe, '<!--random-data-->'), 'arbitrary comment removed');
testimonial_admin_save_same(false, false !== strpos($unsafe, '<!--script payload-->'), 'script-like comment removed');
testimonial_admin_save_same(false, false !== stripos($unsafe, '<script'), 'dangerous HTML sanitized');

$definitions = wp_seed_content_testimonial_meta_definitions();
testimonial_admin_save_same(
    'wp_seed_content_sanitize_testimonial_text_meta',
    $definitions['seed_testimonial_text']['sanitize_callback'],
    'admin pipeline uses testimonial text sanitizer'
);
testimonial_admin_save_same(
    '<p>A</p><!--more--><p>B</p>',
    wp_seed_content_sanitize_testimonial_builder_meta(
        '<p>A</p><!--more--><p>B</p>',
        'seed_testimonial_text'
    ),
    'migration and builder meta pipeline preserve More marker'
);

$_POST = array(
    'wp_seed_content_testimonial_nonce' => 'valid',
    'seed_testimonial_text' => addslashes('<p>Text remains independent</p><!--more--><p>More</p>'),
    'wp_seed_content_testimonial_summary' => addslashes("Short summary\nSecond line"),
);
wp_seed_content_save_testimonial_meta(900001, (object) array(
    'post_type' => 'seed_testimonial',
    'post_excerpt' => '',
));
testimonial_admin_save_same(
    "Short summary\nSecond line",
    $testimonial_more_admin_posts[900001]['post_excerpt'],
    'admin short summary saves post_excerpt'
);
testimonial_admin_save_same(
    '<p>Text remains independent</p><!--more--><p>More</p>',
    get_post_meta(900001, 'seed_testimonial_text', true),
    'short summary save preserves testimonial text and More'
);

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $assertions . ' Testimonial More admin save assertions' . PHP_EOL;
