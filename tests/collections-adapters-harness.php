<?php

require __DIR__ . '/collections-harness.php';

$GLOBALS['wp_seed_test_assertions'] = 0;
$GLOBALS['wp_seed_test_failures'] = array();
$GLOBALS['wp_seed_test_posts'] = array();
$GLOBALS['wp_seed_test_meta'] = array();
$GLOBALS['wp_seed_test_query_count'] = 0;
$GLOBALS['wp_seed_test_last_query'] = array();
$GLOBALS['wp_seed_test_wp_query_count'] = 0;
$GLOBALS['wp_seed_test_last_wp_query'] = array();
$GLOBALS['wp_seed_test_shortcodes'] = array();
$GLOBALS['wp_seed_test_assets_enqueued'] = 0;
$GLOBALS['wp_seed_test_template_modules'] = array();
$GLOBALS['wp_seed_test_template_sources'] = array();
$GLOBALS['wp_seed_test_template_layouts'] = array();
$GLOBALS['wp_seed_test_do_blocks_calls'] = 0;
$GLOBALS['post'] = null;

if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

function shortcode_atts($pairs, $atts, $shortcode = '')
{
    $atts = is_array($atts) ? $atts : array();
    $result = array();

    foreach ($pairs as $name => $default) {
        $result[$name] = array_key_exists($name, $atts) ? $atts[$name] : $default;
    }

    return $result;
}

function sanitize_text_field($value)
{
    return is_scalar($value) ? trim(strip_tags((string) $value)) : '';
}

function sanitize_key($value)
{
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value));
}

function sanitize_title($value)
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9_\-]+/', '-', $value);

    return trim($value, '-');
}

function esc_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_attr($value)
{
    return esc_html($value);
}

function esc_html__($text, $domain = 'default')
{
    return esc_html(__($text, $domain));
}

function wp_parse_args($args, $defaults = array())
{
    return array_merge($defaults, is_array($args) ? $args : array());
}

function add_shortcode($tag, $callback)
{
    $GLOBALS['wp_seed_test_shortcodes'][$tag] = $callback;
}

function wp_seed_content_enqueue_assets()
{
    $GLOBALS['wp_seed_test_assets_enqueued']++;
}

function setup_postdata($post)
{
    if ($post instanceof WP_Post) {
        $GLOBALS['post'] = $post;
        return true;
    }

    return false;
}

function wp_reset_postdata()
{
    $GLOBALS['post'] = null;
}

function get_option($name, $default = false)
{
    return 'date_format' === $name ? 'd/m/Y' : $default;
}

function has_post_thumbnail($post_id = null)
{
    return false;
}

function get_the_post_thumbnail($post_id = null, $size = 'post-thumbnail', $attr = '')
{
    return '';
}

function wp_get_attachment_url($attachment_id)
{
    return '';
}

function get_page_by_path($slug, $output = OBJECT, $post_type = 'page')
{
    foreach ($GLOBALS['wp_seed_test_posts'] as $post) {
        if ($post instanceof WP_Post && $post->post_type === $post_type && $post->post_name === $slug) {
            return $post;
        }
    }

    return null;
}

function apply_filters($hook_name, $value)
{
    return $value;
}

function wp_kses_post($value)
{
    return (string) $value;
}

function wp_strip_all_tags($value)
{
    return strip_tags((string) $value);
}

function do_blocks($content)
{
    $GLOBALS['wp_seed_test_do_blocks_calls']++;

    return preg_replace('/<!--\s*\/?wp:[^>]+-->/', '', (string) $content);
}

function shortcode_parse_atts($text)
{
    $atts = array();
    if (preg_match_all('/([a-zA-Z0-9_-]+)="([^"]*)"/', (string) $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $atts[$match[1]] = $match[2];
        }
    }

    return $atts;
}

function do_shortcode($content)
{
    return preg_replace_callback(
        '/\[(seed_testimonials|seed_quotes)([^\]]*)\]/',
        function ($matches) {
            $tag = $matches[1];
            if (!isset($GLOBALS['wp_seed_test_shortcodes'][$tag])) {
                return $matches[0];
            }

            return call_user_func(
                $GLOBALS['wp_seed_test_shortcodes'][$tag],
                shortcode_parse_atts($matches[2])
            );
        },
        (string) $content
    );
}

function wp_seed_content_get_template_module($post_id)
{
    return isset($GLOBALS['wp_seed_test_template_modules'][$post_id])
        ? $GLOBALS['wp_seed_test_template_modules'][$post_id]
        : '';
}

function wp_seed_content_get_template_layout_source($post_id)
{
    return isset($GLOBALS['wp_seed_test_template_sources'][$post_id])
        ? $GLOBALS['wp_seed_test_template_sources'][$post_id]
        : 'native';
}

function wp_seed_content_get_template_divi_layout_id($post_id)
{
    return isset($GLOBALS['wp_seed_test_template_layouts'][$post_id])
        ? (int) $GLOBALS['wp_seed_test_template_layouts'][$post_id]
        : 0;
}

class WP_Query
{
    public $posts = array();
    private $position = -1;

    public function __construct($args = array())
    {
        $GLOBALS['wp_seed_test_wp_query_count']++;
        $GLOBALS['wp_seed_test_last_wp_query'] = $args;

        $posts = array_values($GLOBALS['wp_seed_test_posts']);
        $posts = array_values(
            array_filter(
                $posts,
                function ($post) use ($args) {
                    if (isset($args['post_type']) && $post->post_type !== $args['post_type']) {
                        return false;
                    }
                    if (isset($args['post_status']) && $post->post_status !== $args['post_status']) {
                        return false;
                    }
                    if (isset($args['has_password']) && false === $args['has_password'] && '' !== $post->post_password) {
                        return false;
                    }
                    if (!empty($args['meta_query'])) {
                        foreach ($args['meta_query'] as $condition) {
                            $value = get_post_meta($post->ID, $condition['key'], true);
                            if (isset($condition['compare']) && 'NOT EXISTS' === $condition['compare']) {
                                if (isset($GLOBALS['wp_seed_test_meta'][$post->ID])
                                    && array_key_exists($condition['key'], $GLOBALS['wp_seed_test_meta'][$post->ID])) {
                                    return false;
                                }
                            } elseif ((string) $value !== (string) $condition['value']) {
                                return false;
                            }
                        }
                    }

                    return true;
                }
            )
        );

        $orderby = isset($args['orderby']) ? $args['orderby'] : 'date';
        if ('rand' !== $orderby) {
            usort(
                $posts,
                function ($left, $right) use ($orderby, $args) {
                    if ('menu_order' === $orderby) {
                        $comparison = (int) $left->menu_order <=> (int) $right->menu_order;
                    } elseif ('meta_value' === $orderby) {
                        $meta_key = isset($args['meta_key']) ? $args['meta_key'] : '';
                        $comparison = strcmp(
                            (string) get_post_meta($left->ID, $meta_key, true),
                            (string) get_post_meta($right->ID, $meta_key, true)
                        );
                    } else {
                        $comparison = strcmp((string) $left->post_date, (string) $right->post_date);
                    }
                    if (0 === $comparison) {
                        $comparison = (int) $left->ID <=> (int) $right->ID;
                    }

                    return isset($args['order']) && 'DESC' === strtoupper($args['order'])
                        ? -$comparison
                        : $comparison;
                }
            );
        } else {
            usort(
                $posts,
                function ($left, $right) {
                    return (int) $left->ID <=> (int) $right->ID;
                }
            );
        }

        $limit = isset($args['posts_per_page']) ? (int) $args['posts_per_page'] : -1;
        if ($limit >= 0) {
            $posts = array_slice($posts, 0, $limit);
        }

        $this->posts = $posts;
    }

    public function have_posts()
    {
        return $this->position + 1 < count($this->posts);
    }

    public function the_post()
    {
        $this->position++;
        if (!isset($this->posts[$this->position])) {
            return null;
        }

        $GLOBALS['post'] = $this->posts[$this->position];

        return $GLOBALS['post'];
    }
}

function wp_seed_adapter_contains($needle, $haystack, $label)
{
    wp_seed_test_same(true, false !== strpos((string) $haystack, (string) $needle), $label);
}

function wp_seed_adapter_not_contains($needle, $haystack, $label)
{
    wp_seed_test_same(false, false !== strpos((string) $haystack, (string) $needle), $label);
}

function wp_seed_adapter_before($first, $second, $haystack, $label)
{
    $first_position = strpos((string) $haystack, (string) $first);
    $second_position = strpos((string) $haystack, (string) $second);

    wp_seed_test_same(
        true,
        false !== $first_position && false !== $second_position && $first_position < $second_position,
        $label
    );
}

function wp_seed_adapter_add_post($id, $type, $status, $menu_order, $post_date, $password = '', $content = '')
{
    $GLOBALS['wp_seed_test_posts'][$id] = new WP_Post(
        $id,
        $type,
        $status,
        $menu_order,
        $post_date,
        $password,
        $content
    );
}

for ($id = 101; $id <= 126; $id++) {
    $day = $id - 100;
    wp_seed_adapter_add_post(
        $id,
        'seed_testimonial',
        'publish',
        $day,
        sprintf('2024-01-%02d 10:00:00', $day)
    );
    wp_seed_test_set_meta($id, '_seed_testimonial_text', 'Texte ' . $id);
    wp_seed_test_set_meta($id, '_seed_testimonial_name', 'Nom ' . $id);
}

wp_seed_test_set_meta(101, '_seed_testimonial_text', "Texte 101 <script>alert('x')</script>\nÉté");
wp_seed_test_set_meta(101, '_seed_testimonial_context', 'Accueil');
wp_seed_test_set_meta(101, '_seed_testimonial_date', '2024-02-29');
wp_seed_test_set_meta(101, '_seed_featured', '1');
wp_seed_test_set_meta(102, '_seed_testimonial_context', 'Accueil');
wp_seed_test_set_meta(102, '_seed_testimonial_date', '2023-01-01');
wp_seed_test_set_meta(102, '_seed_featured', '0');
wp_seed_test_set_meta(103, '_seed_testimonial_date', '2026-02-31');
wp_seed_test_set_meta(103, '_seed_featured', '1');

wp_seed_adapter_add_post(127, 'seed_testimonial', 'publish', 27, '2024-01-27 10:00:00', 'protected');
wp_seed_adapter_add_post(128, 'seed_testimonial', 'draft', 28, '2024-01-28 10:00:00');
wp_seed_adapter_add_post(129, 'post', 'publish', 0, '2024-01-29 10:00:00');
wp_seed_test_set_meta(127, '_seed_testimonial_text', 'PROTECTED TESTIMONIAL');
wp_seed_test_set_meta(128, '_seed_testimonial_text', 'DRAFT TESTIMONIAL');

wp_seed_adapter_add_post(201, 'seed_quote', 'publish', 1, '2024-02-01 10:00:00');
wp_seed_adapter_add_post(202, 'seed_quote', 'publish', 2, '2024-02-02 10:00:00');
wp_seed_adapter_add_post(203, 'seed_quote', 'publish', 3, '2024-02-03 10:00:00', 'protected');
wp_seed_adapter_add_post(204, 'seed_quote', 'draft', 4, '2024-02-04 10:00:00');
wp_seed_test_set_meta(201, '_seed_quote_text', "Citation 201\nÉté");
wp_seed_test_set_meta(201, '_seed_quote_author', 'Auteur 201');
wp_seed_test_set_meta(202, '_seed_quote_text', 'Citation 202');
wp_seed_test_set_meta(202, '_seed_quote_author', 'Auteur 202');
wp_seed_test_set_meta(203, '_seed_quote_text', 'PROTECTED QUOTE');

wp_seed_adapter_add_post(
    1001,
    'seed_template',
    'publish',
    0,
    '2024-01-01 00:00:00',
    '',
    '<div class="testimonial-custom"><h3>{{name}}</h3><p>{{text}}</p><p>{{context}}</p><p>{{date}}</p></div>'
);
$GLOBALS['wp_seed_test_posts'][1001]->post_name = 'testimonial-native';
$GLOBALS['wp_seed_test_template_modules'][1001] = 'testimonials';

wp_seed_adapter_add_post(
    1002,
    'seed_template',
    'publish',
    0,
    '2024-01-01 00:00:00',
    '',
    '<div class="quote-custom">{{quote}}|{{author}}|{{era}}|{{source}}</div>'
);
$GLOBALS['wp_seed_test_posts'][1002]->post_name = 'quote-native';
$GLOBALS['wp_seed_test_template_modules'][1002] = 'quotes';

wp_seed_adapter_add_post(1003, 'seed_template', 'publish', 0, '2024-01-01 00:00:00', '', '<p>{{quote}}</p>');
$GLOBALS['wp_seed_test_posts'][1003]->post_name = 'wrong-testimonial';
$GLOBALS['wp_seed_test_template_modules'][1003] = 'quotes';

wp_seed_adapter_add_post(1004, 'seed_template', 'publish', 0, '2024-01-01 00:00:00', '', '<p>{{quote}}</p>');
$GLOBALS['wp_seed_test_posts'][1004]->post_name = 'quote-divi';
$GLOBALS['wp_seed_test_template_modules'][1004] = 'quotes';
$GLOBALS['wp_seed_test_template_sources'][1004] = 'divi_layout';
$GLOBALS['wp_seed_test_template_layouts'][1004] = 1100;

wp_seed_adapter_add_post(
    1100,
    'et_pb_layout',
    'publish',
    0,
    '2024-01-01 00:00:00',
    '',
    '<!-- wp:divi/text --><div class="et_pb_text">{{quote}} — {{author}}</div><!-- /wp:divi/text -->'
);

$root = getenv('WP_SEED_CONTENT_KIT_TEST_ROOT');
if (!is_string($root) || '' === $root) {
    $root = dirname(__DIR__);
}

require $root . '/plugin/includes/core/template-renderer.php';
require $root . '/plugin/includes/modules/testimonials/render.php';
require $root . '/plugin/includes/modules/testimonials/template-data.php';
require $root . '/plugin/includes/modules/testimonials/shortcode.php';
require $root . '/plugin/includes/modules/quotes/render.php';
require $root . '/plugin/includes/modules/quotes/template-data.php';
require $root . '/plugin/includes/modules/quotes/shortcode.php';

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

try {
    $GLOBALS['wp_seed_test_query_count'] = 0;
    $default_testimonials = wp_seed_content_testimonials_shortcode(array());
    wp_seed_test_same(3, substr_count($default_testimonials, 'seed-card--testimonial'), 'testimonial historical default limit');
    wp_seed_adapter_before('Texte 126', 'Texte 125', $default_testimonials, 'testimonial historical date desc default');
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'testimonial default uses one collection query');

    $GLOBALS['wp_seed_test_query_count'] = 0;
    $empty_context = wp_seed_content_testimonials_shortcode(array('context' => ''));
    wp_seed_test_same($default_testimonials, $empty_context, 'testimonial empty context preserves unfiltered output');
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'testimonial empty context uses one collection query');

    $GLOBALS['wp_seed_test_query_count'] = 0;
    $zero_context = wp_seed_content_testimonials_shortcode(array('context' => '0'));
    wp_seed_test_same($default_testimonials, $zero_context, 'testimonial zero context preserves historical unfiltered output');
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'testimonial zero context uses one collection query');

    $all_testimonials = wp_seed_content_testimonials_shortcode(
        array('limit' => '0', 'orderby' => 'display_order', 'order' => 'asc')
    );
    wp_seed_test_same(26, substr_count($all_testimonials, 'seed-card--testimonial'), 'testimonial limit zero means all');
    wp_seed_adapter_before('Texte 101', 'Texte 102', $all_testimonials, 'testimonial display order asc');

    $capped_testimonials = wp_seed_content_testimonials_shortcode(array('limit' => '99'));
    wp_seed_test_same(24, substr_count($capped_testimonials, 'seed-card--testimonial'), 'testimonial positive limit capped at 24');

    $empty_limit = wp_seed_content_testimonials_shortcode(array('limit' => ''));
    wp_seed_test_same(1, substr_count($empty_limit, 'seed-card--testimonial'), 'testimonial empty limit preserves historical minimum');

    $negative_limit = wp_seed_content_testimonials_shortcode(array('limit' => '-2'));
    wp_seed_test_same(2, substr_count($negative_limit, 'seed-card--testimonial'), 'testimonial negative limit preserves historical absolute value');

    $invalid_limit = wp_seed_content_testimonials_shortcode(array('limit' => 'invalid'));
    wp_seed_test_same(1, substr_count($invalid_limit, 'seed-card--testimonial'), 'testimonial invalid limit preserves historical minimum');

    $featured_only = wp_seed_content_testimonials_shortcode(array('featured' => 'only', 'limit' => '0'));
    wp_seed_test_same(2, substr_count($featured_only, 'seed-card--testimonial'), 'testimonial featured only');
    wp_seed_adapter_contains('Texte 101', $featured_only, 'testimonial featured only first item');
    wp_seed_adapter_contains('Texte 103', $featured_only, 'testimonial featured only second item');
    wp_seed_adapter_not_contains('Texte 102', $featured_only, 'testimonial featured only excludes explicit zero');

    $featured_true = wp_seed_content_testimonials_shortcode(array('featured' => 'TRUE', 'limit' => '0'));
    wp_seed_test_same($featured_only, $featured_true, 'testimonial historical true alias case insensitive');

    $featured_exclude = wp_seed_content_testimonials_shortcode(array('featured' => 'exclude', 'limit' => '0'));
    wp_seed_test_same(24, substr_count($featured_exclude, 'seed-card--testimonial'), 'testimonial featured exclude');
    wp_seed_adapter_contains('Texte 102', $featured_exclude, 'testimonial exclude includes explicit zero');
    wp_seed_adapter_not_contains('Texte 101', $featured_exclude, 'testimonial exclude removes featured');

    $featured_false = wp_seed_content_testimonials_shortcode(array('featured' => 'FALSE', 'limit' => '0'));
    wp_seed_test_same($featured_exclude, $featured_false, 'testimonial historical false alias case insensitive');

    $featured_all = wp_seed_content_testimonials_shortcode(array('featured' => 'all', 'limit' => '0'));
    $featured_invalid = wp_seed_content_testimonials_shortcode(array('featured' => 'unexpected', 'limit' => '0'));
    wp_seed_test_same($featured_all, $featured_invalid, 'testimonial invalid featured value falls back to all');

    $menu_order = wp_seed_content_testimonials_shortcode(
        array('limit' => '2', 'orderby' => 'menu_order', 'order' => 'ASC')
    );
    wp_seed_adapter_before('Texte 101', 'Texte 102', $menu_order, 'testimonial menu order alias');

    $testimonial_date = wp_seed_content_testimonials_shortcode(
        array('limit' => '2', 'orderby' => 'testimonial_date', 'order' => 'desc')
    );
    wp_seed_adapter_before('Texte 101', 'Texte 102', $testimonial_date, 'testimonial date order');

    $id_order = wp_seed_content_testimonials_shortcode(
        array('limit' => '2', 'orderby' => 'id', 'order' => 'desc')
    );
    wp_seed_adapter_before('Texte 126', 'Texte 125', $id_order, 'testimonial id order');

    $empty_ids = wp_seed_content_testimonials_shortcode(array('ids' => ''));
    wp_seed_test_same(3, substr_count($empty_ids, 'seed-card--testimonial'), 'empty testimonial CSV uses normal mode');

    $GLOBALS['wp_seed_test_query_count'] = 0;
    $invalid_ids = wp_seed_content_testimonials_shortcode(array('ids' => 'foo,0,-1'));
    wp_seed_adapter_contains('seed-testimonials__empty', $invalid_ids, 'invalid nonempty testimonial CSV is empty');
    wp_seed_test_same(0, $GLOBALS['wp_seed_test_query_count'], 'invalid testimonial CSV has no fallback query');

    $manual_ids = wp_seed_content_testimonials_shortcode(
        array('ids' => ' 102,foo,101,102,127,128,129 ', 'limit' => '0')
    );
    wp_seed_test_same(2, substr_count($manual_ids, 'seed-card--testimonial'), 'testimonial mixed CSV keeps public testimonials');
    wp_seed_adapter_before('Texte 102', 'Texte 101', $manual_ids, 'testimonial CSV order and stable dedupe');
    wp_seed_adapter_not_contains('PROTECTED TESTIMONIAL', $manual_ids, 'testimonial CSV excludes protected');
    wp_seed_adapter_not_contains('DRAFT TESTIMONIAL', $manual_ids, 'testimonial CSV excludes draft');

    $manual_limit = wp_seed_content_testimonials_shortcode(array('ids' => '102,101', 'limit' => '1'));
    wp_seed_test_same(1, substr_count($manual_limit, 'seed-card--testimonial'), 'testimonial manual limit');
    wp_seed_adapter_contains('Texte 102', $manual_limit, 'testimonial manual limit keeps first');

    $manual_authoritative = wp_seed_content_testimonials_shortcode(
        array(
            'ids' => '102',
            'featured' => 'only',
            'orderby' => 'id',
            'order' => 'desc',
            'context' => 'Autre',
        )
    );
    wp_seed_adapter_contains('Texte 102', $manual_authoritative, 'testimonial ids ignore featured sort and context');

    $GLOBALS['wp_seed_test_query_count'] = 0;
    $context = wp_seed_content_testimonials_shortcode(
        array('context' => 'Accueil', 'limit' => '1', 'orderby' => 'date', 'order' => 'desc')
    );
    wp_seed_test_same(1, substr_count($context, 'seed-card--testimonial'), 'testimonial context keeps historical limit after filtering');
    wp_seed_adapter_contains('Texte 102', $context, 'testimonial context keeps historical date order');
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'testimonial active context uses one collection query');

    $columns = wp_seed_content_testimonials_shortcode(array('ids' => '102', 'columns' => '99'));
    wp_seed_adapter_contains('data-columns="4"', $columns, 'testimonial columns clamp');
    wp_seed_adapter_contains('seed-testimonials__grid--cols-4', $columns, 'testimonial columns class');

    $native_template = wp_seed_content_testimonials_shortcode(
        array('ids' => '101', 'template' => 'testimonial-native')
    );
    wp_seed_adapter_contains('seed-testimonials--template', $native_template, 'testimonial native template mode');
    wp_seed_adapter_contains('testimonial-custom', $native_template, 'testimonial native template content');
    wp_seed_adapter_contains('Accueil', $native_template, 'testimonial context placeholder');
    wp_seed_adapter_contains('29/02/2024', $native_template, 'testimonial localized date placeholder');
    wp_seed_adapter_contains('&lt;script&gt;', $native_template, 'testimonial template escapes text');
    wp_seed_adapter_not_contains('{{', $native_template, 'testimonial template leaves no raw placeholder');

    $empty_placeholders = wp_seed_content_testimonials_shortcode(
        array('ids' => '126', 'template' => 'testimonial-native')
    );
    wp_seed_adapter_not_contains('{{', $empty_placeholders, 'testimonial empty placeholders are replaced');

    $invalid_template = wp_seed_content_testimonials_shortcode(
        array('ids' => '102', 'template' => 'wrong-testimonial')
    );
    wp_seed_adapter_contains('seed-card--testimonial', $invalid_template, 'invalid testimonial template falls back to native');
    wp_seed_adapter_not_contains('seed-testimonials--template', $invalid_template, 'invalid testimonial template keeps native wrapper');

    $missing_template = wp_seed_content_testimonials_shortcode(
        array('ids' => '102', 'template' => 'missing-testimonial')
    );
    wp_seed_adapter_contains('seed-card--testimonial', $missing_template, 'missing testimonial template falls back to native');
    wp_seed_adapter_not_contains('seed-testimonials--template', $missing_template, 'missing testimonial template keeps native wrapper');

    $GLOBALS['wp_seed_test_modules']['testimonials'] = false;
    $GLOBALS['wp_seed_test_query_count'] = 0;
    $disabled_testimonials = wp_seed_content_testimonials_shortcode(array('ids' => '101'));
    wp_seed_adapter_contains('seed-testimonials__empty', $disabled_testimonials, 'disabled testimonial module empty result');
    wp_seed_test_same(0, $GLOBALS['wp_seed_test_query_count'], 'disabled testimonial module has no query');
    $GLOBALS['wp_seed_test_modules']['testimonials'] = true;

    $GLOBALS['wp_seed_test_wp_query_count'] = 0;
    $default_quotes = wp_seed_content_quotes_shortcode(array());
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_wp_query_count'], 'quote historical default uses WP Query');
    wp_seed_test_same('rand', $GLOBALS['wp_seed_test_last_wp_query']['orderby'], 'quote historical default remains random');
    wp_seed_test_same(false, $GLOBALS['wp_seed_test_last_wp_query']['has_password'], 'quote historical query excludes protected');
    wp_seed_adapter_contains('Citation 201', $default_quotes, 'quote historical renderer');
    wp_seed_adapter_not_contains('PROTECTED QUOTE', $default_quotes, 'quote historical output excludes protected');

    $random_quotes = wp_seed_content_quotes_shortcode(array('orderby' => 'random'));
    wp_seed_test_same('rand', $GLOBALS['wp_seed_test_last_wp_query']['orderby'], 'quote explicit random remains random');
    wp_seed_adapter_contains('data-orderby="random"', $random_quotes, 'quote random public attribute retained');

    $expected_daily_id = wp_seed_content_get_daily_quote();
    $GLOBALS['wp_seed_test_wp_query_count'] = 0;
    $GLOBALS['wp_seed_test_query_count'] = 0;
    $daily_quote = wp_seed_content_quotes_shortcode(array('mode' => 'daily'));
    wp_seed_test_same(0, $GLOBALS['wp_seed_test_wp_query_count'], 'daily quote never uses legacy WP Query');
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_query_count'], 'daily quote uses one collection query');
    wp_seed_adapter_contains('data-orderby="daily"', $daily_quote, 'daily quote wrapper');
    wp_seed_adapter_contains('Citation ' . $expected_daily_id, $daily_quote, 'daily quote renders selected Content Data');
    wp_seed_adapter_not_contains('PROTECTED QUOTE', $daily_quote, 'daily quote excludes protected');

    $daily_spaced_case = wp_seed_content_quotes_shortcode(array('mode' => ' DaIlY '));
    wp_seed_test_same($daily_quote, $daily_spaced_case, 'daily quote mode accepts case and surrounding spaces');

    $GLOBALS['wp_seed_test_wp_query_count'] = 0;
    $unknown_mode = wp_seed_content_quotes_shortcode(array('mode' => 'unknown'));
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_wp_query_count'], 'unknown quote mode uses historical WP Query');
    wp_seed_test_same('rand', $GLOBALS['wp_seed_test_last_wp_query']['orderby'], 'unknown quote mode remains historically random');

    $daily_ignored_attributes = wp_seed_content_quotes_shortcode(
        array(
            'mode' => 'DAILY',
            'limit' => '0',
            'featured' => 'true',
            'orderby' => 'author',
            'order' => 'asc',
        )
    );
    wp_seed_test_same($daily_quote, $daily_ignored_attributes, 'daily quote ignores historical selection attributes');

    $GLOBALS['wp_seed_test_query_count'] = 0;
    $daily_first = wp_seed_content_quotes_shortcode(array('mode' => 'daily'));
    $daily_second = wp_seed_content_quotes_shortcode(array('mode' => 'daily'));
    wp_seed_test_same($daily_first, $daily_second, 'daily quote stable during same WordPress day');
    wp_seed_test_same(2, $GLOBALS['wp_seed_test_query_count'], 'daily quote adds no request cache');

    $daily_template = wp_seed_content_quotes_shortcode(array('mode' => 'daily', 'template' => 'quote-native'));
    wp_seed_adapter_contains('seed-quotes__collection--template', $daily_template, 'daily quote native template mode');
    wp_seed_adapter_contains('quote-custom', $daily_template, 'daily quote native template content');
    wp_seed_adapter_not_contains('{{', $daily_template, 'daily quote native template leaves no raw placeholder');

    $GLOBALS['wp_seed_test_do_blocks_calls'] = 0;
    $daily_divi = wp_seed_content_quotes_shortcode(array('mode' => 'daily', 'template' => 'quote-divi'));
    wp_seed_test_same(1, $GLOBALS['wp_seed_test_do_blocks_calls'], 'daily quote Divi layout uses do blocks');
    wp_seed_adapter_contains('et_pb_text', $daily_divi, 'daily quote Divi HTML');
    wp_seed_adapter_not_contains('{{', $daily_divi, 'daily quote Divi layout leaves no raw placeholder');

    $invalid_quote_template = wp_seed_content_quotes_shortcode(
        array('mode' => 'daily', 'template' => 'testimonial-native')
    );
    wp_seed_adapter_contains('seed-card--quote', $invalid_quote_template, 'invalid daily quote template falls back to native');
    wp_seed_adapter_not_contains('seed-quotes__collection--template', $invalid_quote_template, 'invalid daily quote template keeps native wrapper');

    $quote_statuses = array(
        201 => $GLOBALS['wp_seed_test_posts'][201]->post_status,
        202 => $GLOBALS['wp_seed_test_posts'][202]->post_status,
    );
    $GLOBALS['wp_seed_test_posts'][201]->post_status = 'draft';
    $GLOBALS['wp_seed_test_posts'][202]->post_status = 'draft';
    $daily_empty = wp_seed_content_quotes_shortcode(array('mode' => 'daily'));
    wp_seed_adapter_contains('seed-quotes__empty', $daily_empty, 'daily quote no candidate empty result');
    foreach ($quote_statuses as $quote_id => $status) {
        $GLOBALS['wp_seed_test_posts'][$quote_id]->post_status = $status;
    }

    $GLOBALS['wp_seed_test_modules']['quotes'] = false;
    $GLOBALS['wp_seed_test_query_count'] = 0;
    $daily_disabled = wp_seed_content_quotes_shortcode(array('mode' => 'daily'));
    wp_seed_adapter_contains('seed-quotes__empty', $daily_disabled, 'daily quote disabled module empty result');
    wp_seed_test_same(0, $GLOBALS['wp_seed_test_query_count'], 'daily quote disabled module has no query');
    $GLOBALS['wp_seed_test_modules']['quotes'] = true;

    $gutenberg_testimonials = do_shortcode(
        do_blocks('<!-- wp:shortcode -->[seed_testimonials ids="102"]<!-- /wp:shortcode -->')
    );
    wp_seed_adapter_contains('Texte 102', $gutenberg_testimonials, 'Gutenberg shortcode server render testimonials');

    $gutenberg_daily = do_shortcode(
        do_blocks('<!-- wp:shortcode -->[seed_quotes mode="daily"]<!-- /wp:shortcode -->')
    );
    wp_seed_adapter_contains('data-orderby="daily"', $gutenberg_daily, 'Gutenberg shortcode server render daily quote');

    wp_seed_adapter_contains(
        'Texte 102',
        do_shortcode('[seed_testimonials ids="102"]'),
        'Spectra compatible Core shortcode render'
    );

    $multiple_shortcodes = do_shortcode(
        '[seed_testimonials ids="102"][seed_quotes mode="daily"]'
    );
    wp_seed_adapter_contains('Texte 102', $multiple_shortcodes, 'multiple shortcodes keep testimonial state isolated');
    wp_seed_adapter_contains('data-orderby="daily"', $multiple_shortcodes, 'multiple shortcodes keep quote state isolated');
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

echo 'PASS ' . $GLOBALS['wp_seed_test_assertions'] . ' adapter assertions' . PHP_EOL;
