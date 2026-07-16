<?php

define('ABSPATH', __DIR__ . '/');

class WP_Post
{
    public $ID;
    public $post_type;
    public $post_status;
    public $menu_order;
    public $post_date;
    public $post_title;
    public $post_name;
    public $post_password;

    public function __construct($id, $post_type, $post_status, $menu_order, $post_date, $post_password = '')
    {
        $this->ID = $id;
        $this->post_type = $post_type;
        $this->post_status = $post_status;
        $this->menu_order = $menu_order;
        $this->post_date = $post_date;
        $this->post_title = 'Post ' . $id;
        $this->post_name = 'post-' . $id;
        $this->post_password = (string) $post_password;
    }
}

class WP_Error
{
    public $code;
    public $message;

    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }
}

$GLOBALS['wp_seed_test_modules'] = array(
    'testimonials' => true,
    'quotes' => true,
);
$GLOBALS['wp_seed_test_posts'] = array();
$GLOBALS['wp_seed_test_meta'] = array();
$GLOBALS['wp_seed_test_query_count'] = 0;
$GLOBALS['wp_seed_test_last_query'] = array();
$GLOBALS['wp_seed_test_reverse_id_query'] = false;
$GLOBALS['wp_seed_test_home_url'] = 'https://example.test/';
$GLOBALS['wp_seed_test_timezone'] = new DateTimeZone('UTC');

function __($text, $domain = 'default')
{
    return $text;
}

function absint($value)
{
    return is_scalar($value) ? abs((int) $value) : 0;
}

function get_the_ID()
{
    return 0;
}

function is_user_logged_in()
{
    return false;
}

function current_user_can($capability, $post_id = 0)
{
    return false;
}

function wp_seed_content_kit_is_module_active($module)
{
    return isset($GLOBALS['wp_seed_test_modules'][$module])
        ? (bool) $GLOBALS['wp_seed_test_modules'][$module]
        : false;
}

function get_post($post_id)
{
    return isset($GLOBALS['wp_seed_test_posts'][$post_id])
        ? $GLOBALS['wp_seed_test_posts'][$post_id]
        : null;
}

function get_permalink($post)
{
    $post_id = $post instanceof WP_Post ? $post->ID : (int) $post;

    return 'https://example.test/post-' . $post_id . '/';
}

function get_post_thumbnail_id($post_id)
{
    return 0;
}

function get_post_meta($post_id, $key, $single = false)
{
    if (!isset($GLOBALS['wp_seed_test_meta'][$post_id]) || !array_key_exists($key, $GLOBALS['wp_seed_test_meta'][$post_id])) {
        return $single ? '' : array();
    }

    $value = $GLOBALS['wp_seed_test_meta'][$post_id][$key];
    if (is_bool($value)) {
        $value = $value ? '1' : '';
    } elseif (is_int($value) || is_float($value)) {
        $value = (string) $value;
    }

    return $single ? $value : array($value);
}

function home_url($path = '')
{
    return rtrim($GLOBALS['wp_seed_test_home_url'], '/') . '/' . ltrim($path, '/');
}

function wp_timezone()
{
    return $GLOBALS['wp_seed_test_timezone'];
}

function wp_date($format, $timestamp = null, $timezone = null)
{
    $timestamp = is_int($timestamp) ? $timestamp : time();
    $timezone = $timezone instanceof DateTimeZone ? $timezone : wp_timezone();
    $date = new DateTimeImmutable('@' . $timestamp);

    return $date->setTimezone($timezone)->format($format);
}

function get_posts($args = array())
{
    $GLOBALS['wp_seed_test_query_count']++;
    $GLOBALS['wp_seed_test_last_query'] = $args;
    $posts = array_values($GLOBALS['wp_seed_test_posts']);

    $posts = array_values(
        array_filter(
            $posts,
            function ($post) use ($args) {
                if (isset($args['post_type']) && $post->post_type !== $args['post_type']) {
                    return false;
                }

                if (isset($args['post_status'])) {
                    $statuses = is_array($args['post_status']) ? $args['post_status'] : array($args['post_status']);
                    if (!in_array($post->post_status, $statuses, true)) {
                        return false;
                    }
                }

                if (array_key_exists('has_password', $args)) {
                    $post_has_password = '' !== (string) $post->post_password;
                    if (true === $args['has_password'] && !$post_has_password) {
                        return false;
                    }
                    if (false === $args['has_password'] && $post_has_password) {
                        return false;
                    }
                }

                if (isset($args['post__in']) && !in_array($post->ID, $args['post__in'], true)) {
                    return false;
                }

                return true;
            }
        )
    );

    if (isset($args['orderby']) && 'post__in' === $args['orderby']) {
        $positions = array_flip($args['post__in']);
        usort(
            $posts,
            function ($left, $right) use ($positions) {
                return $positions[$left->ID] <=> $positions[$right->ID];
            }
        );
    } else {
        usort(
            $posts,
            function ($left, $right) {
                return $left->ID <=> $right->ID;
            }
        );
        if (isset($args['order']) && 'DESC' === strtoupper($args['order'])) {
            $posts = array_reverse($posts);
        }
    }

    if (isset($args['posts_per_page']) && $args['posts_per_page'] >= 0) {
        $posts = array_slice($posts, 0, $args['posts_per_page']);
    }

    if (isset($args['fields']) && 'ids' === $args['fields']) {
        $ids = array_map(
            function ($post) {
                return (int) $post->ID;
            },
            $posts
        );

        return $GLOBALS['wp_seed_test_reverse_id_query'] ? array_reverse($ids) : $ids;
    }

    return $posts;
}

function wp_seed_test_add_post($id, $type, $status, $menu_order, $post_date, $post_password = '')
{
    $GLOBALS['wp_seed_test_posts'][$id] = new WP_Post($id, $type, $status, $menu_order, $post_date, $post_password);
}

function wp_seed_test_set_meta($post_id, $key, $value)
{
    if (!isset($GLOBALS['wp_seed_test_meta'][$post_id])) {
        $GLOBALS['wp_seed_test_meta'][$post_id] = array();
    }
    $GLOBALS['wp_seed_test_meta'][$post_id][$key] = $value;
}

$root = getenv('WP_SEED_CONTENT_KIT_TEST_ROOT');
if (!is_string($root) || '' === $root) {
    $root = dirname(__DIR__);
}

require $root . '/plugin/includes/core/helpers.php';
require $root . '/plugin/includes/core/content-data.php';
require $root . '/plugin/includes/core/dynamic-data.php';
require $root . '/plugin/includes/core/collections.php';

$GLOBALS['wp_seed_test_assertions'] = 0;
$GLOBALS['wp_seed_test_failures'] = array();

function wp_seed_test_same($expected, $actual, $label)
{
    $GLOBALS['wp_seed_test_assertions']++;
    if ($expected !== $actual) {
        $GLOBALS['wp_seed_test_failures'][] = $label
            . ' expected=' . var_export($expected, true)
            . ' actual=' . var_export($actual, true);
    }
}

function wp_seed_test_true($actual, $label)
{
    wp_seed_test_same(true, (bool) $actual, $label);
}

function wp_seed_test_ids_have_no_password($ids, $label)
{
    $protected = false;
    foreach ($ids as $id) {
        if (
            isset($GLOBALS['wp_seed_test_posts'][$id])
            && '' !== (string) $GLOBALS['wp_seed_test_posts'][$id]->post_password
        ) {
            $protected = true;
            break;
        }
    }

    wp_seed_test_same(false, $protected, $label);
}

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

try {
    wp_seed_test_add_post(10, 'seed_testimonial', 'publish', 0, '2024-01-02 10:00:00');
    wp_seed_test_add_post(11, 'seed_testimonial', 'publish', 2, '2024-01-01 10:00:00');
    wp_seed_test_add_post(12, 'seed_testimonial', 'publish', 2, '2024-01-01 10:00:00');
    wp_seed_test_add_post(13, 'seed_testimonial', 'publish', -1, '2024-02-01 10:00:00');
    wp_seed_test_add_post(14, 'seed_testimonial', 'draft', 1, '2024-01-03 10:00:00');
    wp_seed_test_add_post(15, 'seed_testimonial', 'private', 1, '2024-01-04 10:00:00');
    wp_seed_test_add_post(16, 'seed_quote', 'publish', 0, '2024-01-05 10:00:00');
    wp_seed_test_add_post(17, 'seed_testimonial', 'publish', 0, '2024-01-02 10:00:00');
    wp_seed_test_add_post(18, 'seed_testimonial', 'publish', 3, '2024-03-01 10:00:00');
    wp_seed_test_add_post(19, 'seed_testimonial', 'publish', 4, '2024-03-01 10:00:00');
    wp_seed_test_add_post(20, 'seed_testimonial', 'publish', 5, '2023-12-01 10:00:00');
    wp_seed_test_add_post(21, 'seed_testimonial', 'publish', 6, '2024-04-01 10:00:00');
    wp_seed_test_add_post(22, 'seed_testimonial', 'publish', -10, '2025-01-01 10:00:00', 'protected');

    wp_seed_test_set_meta(11, '_seed_featured', '');
    wp_seed_test_set_meta(12, '_seed_featured', '0');
    wp_seed_test_set_meta(13, '_seed_featured', 0);
    wp_seed_test_set_meta(17, '_seed_featured', false);
    wp_seed_test_set_meta(18, '_seed_featured', '1');
    wp_seed_test_set_meta(19, '_seed_featured', 1);
    wp_seed_test_set_meta(20, '_seed_featured', true);
    wp_seed_test_set_meta(21, '_seed_featured', 'yes');
    wp_seed_test_set_meta(22, '_seed_featured', '1');

    wp_seed_test_set_meta(11, '_seed_testimonial_date', '');
    wp_seed_test_set_meta(12, '_seed_testimonial_date', '2024-02-31');
    wp_seed_test_set_meta(13, '_seed_testimonial_date', '2023-12-31');
    wp_seed_test_set_meta(17, '_seed_testimonial_date', '2024-03-01');
    wp_seed_test_set_meta(18, '_seed_testimonial_date', '2024-03-01');
    wp_seed_test_set_meta(19, '_seed_testimonial_date', '2024-01-15');
    wp_seed_test_set_meta(20, '_seed_testimonial_date', ' 2024-01-01 ');
    wp_seed_test_set_meta(21, '_seed_testimonial_date', '2025-01-01');
    wp_seed_test_set_meta(22, '_seed_testimonial_date', '2026-01-01');
    wp_seed_test_set_meta(18, '_seed_testimonial_text', 'Testimonial 18');
    wp_seed_test_set_meta(18, '_seed_testimonial_name', 'Test');

    $display_asc = array(13, 10, 17, 11, 12, 18, 19, 20, 21);
    $display_desc = array(21, 20, 19, 18, 11, 12, 10, 17, 13);

    $default_testimonials = wp_seed_content_get_testimonials();
    wp_seed_test_same($display_asc, $default_testimonials, 'defaults');
    wp_seed_test_same(true, in_array(10, $default_testimonials, true), 'public testimonial included');
    wp_seed_test_same(false, in_array(22, $default_testimonials, true), 'password-protected testimonial excluded');
    wp_seed_test_ids_have_no_password($default_testimonials, 'normal collection has no protected testimonial');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(null), 'null args');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials('invalid'), 'string args');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(new stdClass()), 'object args');
    wp_seed_test_same(
        $display_asc,
        wp_seed_content_get_testimonials(
            array(
                'featured' => new stdClass(),
                'limit' => array(1),
                'orderby' => new stdClass(),
                'order' => array('desc'),
                'unknown' => 'ignored',
            )
        ),
        'nested invalid args'
    );
    wp_seed_test_same(
        $display_asc,
        wp_seed_content_get_testimonials(
            array('featured' => 'unknown', 'limit' => -4, 'orderby' => 'unknown', 'order' => 'unknown')
        ),
        'invalid canonical values use defaults'
    );
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('ids' => array())), 'empty ids normal mode');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('ids' => null)), 'null ids normal mode');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('ids' => '')), 'empty scalar ids normal mode');
    wp_seed_test_same(array(), wp_seed_content_get_testimonials(array('ids' => '0')), 'nonempty zero string ids rejected');
    wp_seed_test_same(array(), wp_seed_content_get_testimonials(array('ids' => '18,11')), 'csv ids rejected');
    wp_seed_test_same(array(), wp_seed_content_get_testimonials(array('ids' => new stdClass())), 'object ids rejected');
    wp_seed_test_same(
        array(),
        wp_seed_content_get_testimonials(array('ids' => array(0, -1, '11', array(18), new stdClass()))),
        'entirely invalid manual ids do not fall back'
    );
    wp_seed_test_same(
        array(18, 11),
        wp_seed_content_get_testimonials(
            array(
                'ids' => array(18, 18, 14, 16, 999, -1, 0, '11', array(10), new stdClass(), 11, 15),
                'featured' => 'exclude',
                'orderby' => 'id',
                'order' => 'desc',
            )
        ),
        'manual ids authoritative and ordered'
    );
    wp_seed_test_same(
        array(18, 11),
        wp_seed_content_get_testimonials(array('ids' => array(22, 18, 11))),
        'manual ids exclude protected and preserve public order'
    );
    wp_seed_test_same(
        array(),
        wp_seed_content_get_testimonials(array('ids' => array(22))),
        'manual ids containing only protected testimonials do not fall back'
    );
    wp_seed_test_ids_have_no_password(
        wp_seed_content_get_testimonials(array('ids' => array(18, 22, 11))),
        'manual collection has no protected testimonial'
    );
    wp_seed_test_same(
        array(18),
        wp_seed_content_get_testimonials(array('ids' => array(18, 11), 'limit' => 1)),
        'manual ids limit'
    );

    wp_seed_test_same(array(18, 19, 20), wp_seed_content_get_testimonials(array('featured' => 'only')), 'featured only');
    wp_seed_test_same(array(13, 10, 17, 11, 12, 21), wp_seed_content_get_testimonials(array('featured' => 'exclude')), 'featured exclude');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('featured' => 'all')), 'featured all');
    $protected_guard = wp_seed_content_get_testimonials(
        array('featured' => 'only', 'orderby' => 'id', 'order' => 'desc', 'limit' => 2)
    );
    wp_seed_test_same(array(20, 19), $protected_guard, 'featured sort and limit never reintroduce protected testimonials');
    wp_seed_test_ids_have_no_password($protected_guard, 'filtered collection has no protected testimonial');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('featured' => true)), 'boolean featured alias is not canonical');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('featured' => 'true')), 'string featured alias is not canonical');
    wp_seed_test_same(false, wp_seed_content_is_truthy_meta(10, '_seed_featured'), 'featured absent false');
    wp_seed_test_same(false, wp_seed_content_is_truthy_meta(11, '_seed_featured'), 'featured empty false');
    wp_seed_test_same(false, wp_seed_content_is_truthy_meta(12, '_seed_featured'), 'featured string zero false');
    wp_seed_test_same(false, wp_seed_content_is_truthy_meta(13, '_seed_featured'), 'featured integer zero false');
    wp_seed_test_same(false, wp_seed_content_is_truthy_meta(17, '_seed_featured'), 'featured boolean false');
    wp_seed_test_same(true, wp_seed_content_is_truthy_meta(18, '_seed_featured'), 'featured string one true');
    wp_seed_test_same(true, wp_seed_content_is_truthy_meta(19, '_seed_featured'), 'featured integer one true');
    wp_seed_test_same(true, wp_seed_content_is_truthy_meta(20, '_seed_featured'), 'featured boolean true');
    wp_seed_test_same(false, wp_seed_content_is_truthy_meta(21, '_seed_featured'), 'featured noncanonical value false');

    $featured_meta_before = $GLOBALS['wp_seed_test_meta'];
    foreach (array(10, 11, 12, 13, 17, 18, 19, 20, 21) as $testimonial_id) {
        unset($GLOBALS['wp_seed_test_meta'][$testimonial_id]['_seed_featured']);
    }
    wp_seed_test_same(array(), wp_seed_content_get_testimonials(array('featured' => 'only')), 'featured only has no fallback');
    $GLOBALS['wp_seed_test_meta'] = $featured_meta_before;

    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('orderby' => 'display_order', 'order' => 'asc')), 'display order asc');
    wp_seed_test_same($display_desc, wp_seed_content_get_testimonials(array('orderby' => 'display_order', 'order' => 'desc')), 'display order desc');
    wp_seed_test_same(
        array(20, 11, 12, 10, 17, 13, 18, 19, 21),
        wp_seed_content_get_testimonials(array('orderby' => 'date', 'order' => 'asc')),
        'post date asc'
    );
    wp_seed_test_same(
        array(21, 18, 19, 13, 10, 17, 11, 12, 20),
        wp_seed_content_get_testimonials(array('orderby' => 'date', 'order' => 'desc')),
        'post date desc'
    );
    wp_seed_test_same(
        array(13, 19, 17, 18, 21, 10, 11, 12, 20),
        wp_seed_content_get_testimonials(array('orderby' => 'testimonial_date', 'order' => 'asc')),
        'testimonial date asc invalid last'
    );
    wp_seed_test_same(
        array(21, 17, 18, 19, 13, 10, 11, 12, 20),
        wp_seed_content_get_testimonials(array('orderby' => 'testimonial_date', 'order' => 'desc')),
        'testimonial date desc invalid last'
    );
    wp_seed_test_same(
        array(10, 11, 12, 13, 17, 18, 19, 20, 21),
        wp_seed_content_get_testimonials(array('orderby' => 'id', 'order' => 'asc')),
        'id asc'
    );
    wp_seed_test_same(
        array(21, 20, 19, 18, 17, 13, 12, 11, 10),
        wp_seed_content_get_testimonials(array('orderby' => 'id', 'order' => 'desc')),
        'id desc'
    );
    wp_seed_test_same(array(13, 10, 17), wp_seed_content_get_testimonials(array('limit' => 3)), 'normal limit');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('limit' => 0)), 'zero limit');
    wp_seed_test_same($display_asc, wp_seed_content_get_testimonials(array('limit' => '3')), 'string limit invalid');

    $GLOBALS['wp_seed_test_query_count'] = 0;
    wp_seed_content_get_testimonials(array('featured' => 'only'));
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'normal collection uses one post query');
    wp_seed_test_same(true, $GLOBALS['wp_seed_test_last_query']['update_post_meta_cache'], 'normal query primes meta cache');
    wp_seed_test_same(false, $GLOBALS['wp_seed_test_last_query']['has_password'], 'normal query excludes password-protected posts');
    $GLOBALS['wp_seed_test_query_count'] = 0;
    wp_seed_content_get_testimonials(array('ids' => array(18, 11)));
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'manual collection uses one post query');
    wp_seed_test_same(false, $GLOBALS['wp_seed_test_last_query']['has_password'], 'manual query excludes password-protected posts');

    $GLOBALS['wp_seed_test_modules']['testimonials'] = false;
    $GLOBALS['wp_seed_test_query_count'] = 0;
    wp_seed_test_same(array(), wp_seed_content_get_testimonials(), 'testimonial module guard');
    wp_seed_test_same(array(), wp_seed_content_get_testimonials(array('ids' => array(18))), 'module guard precedes manual ids');
    wp_seed_test_same(0, $GLOBALS['wp_seed_test_query_count'], 'disabled testimonial module performs no query');
    $testimonial_data = wp_seed_content_get_testimonial_data(18);
    wp_seed_test_same('Testimonial 18', $testimonial_data['text'], 'Content Data independent from module state');
    wp_seed_test_same(
        'Testimonial 18',
        wp_seed_content_resolve_dynamic_data(
            'testimonial.text',
            array('current_post_id' => 18, 'current_post_type' => 'seed_testimonial')
        ),
        'Dynamic Data independent from module state'
    );
    $GLOBALS['wp_seed_test_modules']['testimonials'] = true;

    wp_seed_test_add_post(29, 'seed_quote', 'publish', 0, '2024-01-01 00:00:00');
    wp_seed_test_add_post(30, 'seed_quote', 'publish', 0, '2024-01-01 00:00:00');
    wp_seed_test_add_post(31, 'seed_quote', 'publish', 0, '2024-01-01 00:00:00');
    wp_seed_test_add_post(32, 'seed_quote', 'draft', 0, '2024-01-01 00:00:00');
    wp_seed_test_add_post(33, 'seed_quote', 'publish', 0, '2024-01-01 00:00:00', 'protected');

    wp_seed_test_same(121173067, hexdec('738f44b'), 'known 28 bit hash value');
    wp_seed_test_same(1, _wp_seed_content_collections_get_daily_index(3, 'https://example.test/', '2026-07-16'), 'known daily index');
    wp_seed_test_same(7, _wp_seed_content_collections_get_daily_index(10, 'https://example.test/', '2026-07-16'), 'known daily index ten candidates');
    wp_seed_test_same(0, _wp_seed_content_collections_get_daily_index(0, 'https://example.test/', '2026-07-16'), 'zero candidate index');
    wp_seed_test_same(268435455, hexdec('fffffff'), '28 bit maximum is platform safe');
    wp_seed_test_true(PHP_INT_MAX >= 2147483647, 'PHP integer supports 28 bit value');

    $timestamp = gmmktime(12, 30, 0, 1, 1, 2026);
    $paris_date = _wp_seed_content_collections_get_local_date($timestamp, new DateTimeZone('Europe/Paris'));
    $kiritimati_date = _wp_seed_content_collections_get_local_date($timestamp, new DateTimeZone('Pacific/Kiritimati'));
    $adak_date = _wp_seed_content_collections_get_local_date($timestamp, new DateTimeZone('America/Adak'));
    wp_seed_test_same('2026-01-01', $paris_date, 'Europe Paris local date');
    wp_seed_test_same('2026-01-02', $kiritimati_date, 'Pacific Kiritimati local date');
    wp_seed_test_same('2026-01-01', $adak_date, 'America Adak local date');
    wp_seed_test_true(count(array_unique(array($paris_date, $kiritimati_date, $adak_date))) > 1, 'same UTC instant can produce different WordPress dates');

    $GLOBALS['wp_seed_test_timezone'] = new DateTimeZone('Europe/Paris');
    $GLOBALS['wp_seed_test_reverse_id_query'] = true;
    $local_date = _wp_seed_content_collections_get_local_date();
    $candidate_ids = array(16, 29, 30, 31);
    sort($candidate_ids, SORT_NUMERIC);
    $expected_index = hexdec(substr(hash('sha256', home_url('/') . '|' . $local_date), 0, 7)) % count($candidate_ids);
    $expected_quote = $candidate_ids[$expected_index];
    $GLOBALS['wp_seed_test_query_count'] = 0;
    $daily_quote = wp_seed_content_get_daily_quote();
    wp_seed_test_same($expected_quote, $daily_quote, 'daily quote exact formula excludes protected candidates');
    wp_seed_test_same(false, 33 === $daily_quote, 'daily quote never returns a password-protected quote');
    wp_seed_test_ids_have_no_password(array($daily_quote), 'daily quote output has no protected post');
    wp_seed_test_same(true, $GLOBALS['wp_seed_test_reverse_id_query'], 'daily quote tolerates unsorted query results');
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'daily quote uses one post query');
    wp_seed_test_same(false, $GLOBALS['wp_seed_test_last_query']['has_password'], 'daily quote query excludes password-protected posts');
    wp_seed_test_true(is_int(wp_seed_content_get_daily_quote()), 'daily quote return type');
    wp_seed_test_same(wp_seed_content_get_daily_quote(), wp_seed_content_get_daily_quote(null), 'daily quote null args');
    wp_seed_test_same(wp_seed_content_get_daily_quote(), wp_seed_content_get_daily_quote('invalid'), 'daily quote string args');
    wp_seed_test_same(wp_seed_content_get_daily_quote(), wp_seed_content_get_daily_quote(new stdClass()), 'daily quote object args');
    $GLOBALS['wp_seed_test_reverse_id_query'] = false;

    $quote_statuses = array();
    foreach (array(16, 29, 30, 31, 32) as $quote_id) {
        $quote_statuses[$quote_id] = $GLOBALS['wp_seed_test_posts'][$quote_id]->post_status;
        $GLOBALS['wp_seed_test_posts'][$quote_id]->post_status = 'draft';
    }
    wp_seed_test_same(0, wp_seed_content_get_daily_quote(), 'only password-protected quotes return zero');
    $GLOBALS['wp_seed_test_posts'][30]->post_status = 'publish';
    wp_seed_test_same(30, wp_seed_content_get_daily_quote(), 'public quote selected alongside protected quote');
    foreach ($quote_statuses as $quote_id => $status) {
        $GLOBALS['wp_seed_test_posts'][$quote_id]->post_status = $status;
    }

    $posts_before = serialize($GLOBALS['wp_seed_test_posts']);
    $meta_before = serialize($GLOBALS['wp_seed_test_meta']);
    wp_seed_content_get_daily_quote(array('unknown' => new stdClass()));
    wp_seed_test_same($posts_before, serialize($GLOBALS['wp_seed_test_posts']), 'daily quote does not mutate posts');
    wp_seed_test_same($meta_before, serialize($GLOBALS['wp_seed_test_meta']), 'daily quote does not mutate meta');

    $GLOBALS['wp_seed_test_modules']['quotes'] = false;
    $GLOBALS['wp_seed_test_query_count'] = 0;
    wp_seed_test_same(0, wp_seed_content_get_daily_quote(), 'quote module guard');
    wp_seed_test_same(0, $GLOBALS['wp_seed_test_query_count'], 'disabled quote module performs no query');
    $GLOBALS['wp_seed_test_modules']['quotes'] = true;

    wp_seed_test_true(is_array(wp_seed_content_get_testimonials()), 'testimonial return type');
    foreach (wp_seed_content_get_testimonials() as $testimonial_id) {
        wp_seed_test_true(is_int($testimonial_id) && $testimonial_id > 0, 'testimonial IDs are positive integers');
    }
} catch (Throwable $exception) {
    $GLOBALS['wp_seed_test_failures'][] = get_class($exception) . ': ' . $exception->getMessage();
}

restore_error_handler();

if (!empty($GLOBALS['wp_seed_test_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['wp_seed_test_failures']) . ' / ' . $GLOBALS['wp_seed_test_assertions'] . PHP_EOL);
    foreach ($GLOBALS['wp_seed_test_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['wp_seed_test_assertions'] . ' assertions' . PHP_EOL;
