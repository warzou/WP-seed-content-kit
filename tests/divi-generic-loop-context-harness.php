<?php

define('ABSPATH', __DIR__ . '/');

$GLOBALS['wpsck_generic_filters'] = array();
$GLOBALS['wpsck_generic_assertions'] = 0;
$GLOBALS['wpsck_generic_failures'] = array();

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['wpsck_generic_filters'][$hook][] = $callback;
}

function apply_filters($hook, $value)
{
    if ('wp_seed_content_divi_loop_dynamic_data_sources' === $hook) {
        $value['seed_directory'] = array(
            'wpsck_directory_name' => array('field_id' => 'directory.name', 'type' => 'text'),
        );
    }

    return $value;
}

function absint($value)
{
    return abs((int) $value);
}

function is_wp_error($value)
{
    return false;
}

class WP_Post
{
    public $ID;
    public $post_type;
    public $post_status;
    public $post_password;

    public function __construct($id, $post_type, $status = 'publish', $password = '')
    {
        $this->ID = (int) $id;
        $this->post_type = $post_type;
        $this->post_status = $status;
        $this->post_password = $password;
    }
}

function get_post($post_id)
{
    $posts = array(
        101 => new WP_Post(101, 'seed_testimonial'),
        102 => new WP_Post(102, 'seed_testimonial'),
        201 => new WP_Post(201, 'seed_quote'),
        202 => new WP_Post(202, 'seed_quote'),
        203 => new WP_Post(203, 'seed_quote', 'draft'),
        301 => new WP_Post(301, 'seed_directory'),
    );

    return isset($posts[(int) $post_id]) ? $posts[(int) $post_id] : null;
}

function wp_seed_content_testimonial_is_publicly_visible($post_id)
{
    return in_array((int) $post_id, array(101, 102), true);
}

function wp_seed_content_resolve_dynamic_data($field_id, $context)
{
    $post_id = isset($context['current_post_id']) ? (int) $context['current_post_id'] : 0;

    if ('testimonial.photo' === $field_id) {
        return array('url' => 'testimonial-' . $post_id . '.jpg');
    }

    return $field_id . ':' . $post_id;
}

function _wp_seed_content_normalize_dynamic_data_post_id($post_id)
{
    return (int) $post_id > 0 ? (int) $post_id : 0;
}

class WpsckGenericLoopRequest
{
    private $route;

    public function __construct($route)
    {
        $this->route = $route;
    }

    public function get_route()
    {
        return $this->route;
    }
}

class WpsckGenericLoopResponse
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function set_data($data)
    {
        $this->data = $data;
    }
}

function wpsck_generic_same($expected, $actual, $label)
{
    $GLOBALS['wpsck_generic_assertions']++;
    if ($expected !== $actual) {
        $GLOBALS['wpsck_generic_failures'][] = $label;
    }
}

function wpsck_generic_loop_value($token, $item)
{
    if (!preg_match('/^\$variable\((.+)\)\$$/', $token, $matches)) {
        return null;
    }

    $definition = json_decode($matches[1], true);
    $name = isset($definition['value']['name']) ? $definition['value']['name'] : '';
    if (0 !== strpos($name, 'loop_')) {
        return null;
    }

    $key = preg_replace('/^loop_/', '', $name);

    return isset($item[$key]) ? $item[$key] : null;
}

require_once __DIR__ . '/../plugin/includes/integrations/divi/loop-context.php';

$response = new WpsckGenericLoopResponse(array(
    'data' => array(
        'items' => array(
            array('id' => 101, 'post_type' => 'seed_testimonial'),
            array('id' => 102, 'post_type' => 'seed_testimonial'),
            array('id' => 201, 'post_type' => 'seed_quote'),
            array('id' => 202, 'post_type' => 'seed_quote'),
            array('id' => 203, 'post_type' => 'seed_quote'),
            array('id' => 301, 'post_type' => 'seed_directory'),
        ),
    ),
));
wp_seed_content_divi_add_loop_dynamic_data(
    $response,
    null,
    new WpsckGenericLoopRequest('/divi/v1/loop/query-results')
);
$items = $response->get_data()['data']['items'];

$source_contract_is_valid = true;
foreach (wp_seed_content_divi_loop_dynamic_data_sources() as $definitions) {
    foreach ($definitions as $alias => $definition) {
        $provider_name = 'loop_' . $alias;
        if (
            0 === strpos($alias, 'loop_')
            || 0 !== strpos($provider_name, 'loop_')
            || $alias !== substr($provider_name, 5)
            || empty($definition['field_id'])
        ) {
            $source_contract_is_valid = false;
        }
    }
}
wpsck_generic_same(true, $source_contract_is_valid, 'Shared loop provider and QueryResults alias contract');

wpsck_generic_same('testimonial.text:101', $items[0]['wpsck_testimonial_full'], 'Testimonial item one');
wpsck_generic_same('testimonial.text:102', $items[1]['wpsck_testimonial_full'], 'Testimonial item two');
wpsck_generic_same('quote.quote:201', $items[2]['wpsck_quote_text'], 'Quote item one');
wpsck_generic_same('quote.quote:202', $items[3]['wpsck_quote_text'], 'Quote item two');
wpsck_generic_same(false, isset($items[4]['wpsck_quote_text']), 'Draft Quote excluded');
wpsck_generic_same('directory.name:301', $items[5]['wpsck_directory_name'], 'Synthetic Directory source');

$legacy = '$variable({"type":"content","value":{"name":"wp_seed_content_quote_quote","settings":[]}})$';
$canonical = wp_seed_content_divi_normalize_legacy_loop_provider_tokens($legacy);
$expected = '$variable({"type":"content","value":{"name":"loop_wpsck_quote_text","settings":[]}})$';
wpsck_generic_same($expected, $canonical, 'Legacy Quote token normalized without storage write');
wpsck_generic_same(
    true,
    wp_seed_content_divi_dynamic_content_name_matches('loop_wpsck_quote_text', 'wp_seed_content_quote_quote'),
    'Legacy Quote provider name remains compatible'
);
wpsck_generic_same(
    true,
    wp_seed_content_divi_dynamic_content_name_matches('loop_wpsck_testimonial_full', 'wp_seed_content_testimonial_text'),
    'Legacy Testimonial provider name remains compatible'
);
wpsck_generic_same('quote.quote:201', wpsck_generic_loop_value($canonical, $items[2]), 'Quote clone one resolved');
wpsck_generic_same('quote.quote:202', wpsck_generic_loop_value($canonical, $items[3]), 'Quote clone two resolved');

$escaped = '$variable({\\u0022type\\u0022:\\u0022content\\u0022,\\u0022value\\u0022:{\\u0022name\\u0022:\\u0022wp_seed_content_quote_quote\\u0022}})$';
wpsck_generic_same(
    true,
    false !== strpos(wp_seed_content_divi_normalize_legacy_loop_provider_tokens($escaped), 'loop_wpsck_quote_text'),
    'Escaped Divi block binding normalized'
);

$stored_visual_builder_content = '<!-- wp:divi/text {"content":"' . $escaped . '"} /-->';
$synthetic_page_id = 900001;
$visual_builder_content = wp_seed_content_divi_normalize_visual_builder_post_content(
    $stored_visual_builder_content,
    $synthetic_page_id
);
wpsck_generic_same(
    true,
    false !== strpos($visual_builder_content, 'loop_wpsck_quote_text'),
    'Visual Builder settings content receives canonical loop provider'
);
wpsck_generic_same(
    true,
    false !== strpos($stored_visual_builder_content, 'wp_seed_content_quote_quote'),
    'Visual Builder normalization does not mutate stored content'
);
wpsck_generic_same(
    'Unrelated content',
    wp_seed_content_divi_normalize_visual_builder_post_content('Unrelated content', $synthetic_page_id),
    'Visual Builder normalization preserves unrelated content'
);
$visual_builder_filter = $GLOBALS['wpsck_generic_filters']['divi_visual_builder_settings_data_post_content'][0];
wpsck_generic_same(
    $visual_builder_content,
    call_user_func($visual_builder_filter, $stored_visual_builder_content, $synthetic_page_id),
    'Registered Divi Visual Builder filter runs the compatibility normalizer'
);

$testimonial_token = '$variable({"type":"content","value":{"name":"loop_wpsck_testimonial_full","settings":[]}})$';
wpsck_generic_same('testimonial.text:101', wpsck_generic_loop_value($testimonial_token, $items[0]), 'Testimonial clone one resolved');
wpsck_generic_same('testimonial.text:102', wpsck_generic_loop_value($testimonial_token, $items[1]), 'Testimonial clone two resolved');

$context = wp_seed_content_divi_get_dynamic_content_context(array('loop_id' => 201), 'seed_quote');
wpsck_generic_same(201, $context['current_post_id'], 'loop_id context');
$context = wp_seed_content_divi_get_dynamic_content_context(array('loop_object' => get_post(202)), 'seed_quote');
wpsck_generic_same(202, $context['current_post_id'], 'loop_object context');
$context = wp_seed_content_divi_get_dynamic_content_context(array('post_id' => 201), 'seed_quote');
wpsck_generic_same(201, $context['current_post_id'], 'post_id fallback context');
wpsck_generic_same(
    true,
    wp_seed_content_divi_should_defer_dynamic_content($canonical, array('current_post_id' => 0)),
    'Provider remains deferred before clone context exists'
);
wpsck_generic_same(true, isset($GLOBALS['wpsck_generic_filters']['rest_post_dispatch']), 'Generic REST hook registered');
wpsck_generic_same(
    true,
    isset($GLOBALS['wpsck_generic_filters']['divi_visual_builder_settings_data_post_content']),
    'Divi Visual Builder settings content hook registered'
);

if ($GLOBALS['wpsck_generic_failures']) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['wpsck_generic_failures']) . '/' . $GLOBALS['wpsck_generic_assertions'] . ': ' . implode(', ', $GLOBALS['wpsck_generic_failures']) . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $GLOBALS['wpsck_generic_assertions'] . ' generic Divi Loop context assertions' . PHP_EOL;
