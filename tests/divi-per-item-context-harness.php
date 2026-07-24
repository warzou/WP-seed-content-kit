<?php
/**
 * Autonomous harness for per-item Divi testimonial context.
 *
 * Run with: php tests/divi-per-item-context-harness.php
 */

define('ABSPATH', __DIR__);

class WP_Post
{
    public $ID;
    public $post_type;
    public $post_status;

    public function __construct($id, $post_type = 'seed_testimonial', $post_status = 'publish')
    {
        $this->ID = (int) $id;
        $this->post_type = $post_type;
        $this->post_status = $post_status;
    }
}

class WP_Error
{
    private $code;

    public function __construct($code)
    {
        $this->code = (string) $code;
    }

    public function get_error_code()
    {
        return $this->code;
    }
}

function is_wp_error($value)
{
    return $value instanceof WP_Error;
}

function absint($value)
{
    return abs((int) $value);
}

function sanitize_key($value)
{
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value));
}

function sanitize_text_field($value)
{
    return trim(strip_tags((string) $value));
}

function wp_json_encode($value, $flags = 0)
{
    return json_encode($value, $flags);
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    return true;
}

function get_post($post_id)
{
    return isset($GLOBALS['wp_seed_divi_context_posts'][$post_id])
        ? $GLOBALS['wp_seed_divi_context_posts'][$post_id]
        : null;
}

function wp_seed_content_testimonial_is_publicly_visible($post_id)
{
    return !isset($GLOBALS['wp_seed_divi_context_private'][$post_id]);
}

function parse_blocks($content)
{
    if ('throw-parse' === $content) {
        throw new RuntimeException('synthetic parse failure');
    }

    return isset($GLOBALS['wp_seed_divi_context_blocks'][$content])
        ? $GLOBALS['wp_seed_divi_context_blocks'][$content]
        : array(
            array(
                'blockName' => null,
                'attrs' => array(),
                'innerBlocks' => array(),
                'innerHTML' => $content,
                'innerContent' => array($content),
            ),
        );
}

function serialize_blocks($blocks)
{
    return json_encode($blocks, JSON_UNESCAPED_SLASHES);
}

$assertions = 0;
$failures = array();

function wp_seed_divi_context_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function wp_seed_divi_context_same($expected, $actual, $label)
{
    wp_seed_divi_context_assert(
        $expected === $actual,
        $label . ' expected=' . var_export($expected, true)
        . ' actual=' . var_export($actual, true)
    );
}

function wp_seed_divi_variable($name, $settings = array(), $post_id = null)
{
    $value = array(
        'name' => $name,
        'settings' => $settings,
    );
    if (null !== $post_id) {
        $value['post_id'] = $post_id;
    }

    return '$variable(' . json_encode(
        array(
            'type' => 'content',
            'value' => $value,
        ),
        JSON_UNESCAPED_SLASHES
    ) . ')$';
}

function wp_seed_divi_serialized_variable($name, $settings = array(), $post_id = null)
{
    return str_replace('"', '\\u0022', wp_seed_divi_variable($name, $settings, $post_id));
}

require dirname(__DIR__) . '/plugin/includes/core/render-context.php';
require dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-layout-context.php';

$GLOBALS['wp_seed_divi_context_posts'] = array(
    101 => new WP_Post(101),
    102 => new WP_Post(102),
    103 => new WP_Post(103),
    201 => new WP_Post(201, 'page'),
    202 => new WP_Post(202, 'seed_testimonial', 'draft'),
    203 => new WP_Post(203),
);
$GLOBALS['wp_seed_divi_context_private'] = array(203 => true);

WP_Seed_Content_Render_Context::reset();
wp_seed_divi_context_same(0, WP_Seed_Content_Render_Context::depth(), 'stack starts empty');

$first_id = _wp_seed_content_render_context_id('testimonials', 101, 301, 401);
$first_context = array(
    'module' => 'testimonials',
    'post_id' => 101,
    'template_id' => 301,
    'layout_id' => 401,
    'render_id' => $first_id,
);
wp_seed_divi_context_assert(WP_Seed_Content_Render_Context::push($first_context), 'first push');
wp_seed_divi_context_same(1, WP_Seed_Content_Render_Context::depth(), 'depth after first push');
wp_seed_divi_context_same(101, WP_Seed_Content_Render_Context::current()['post_id'], 'current post id');
wp_seed_divi_context_same(1, WP_Seed_Content_Render_Context::current()['depth'], 'stored current depth');
wp_seed_divi_context_same(false, WP_Seed_Content_Render_Context::push($first_context), 'direct recursion rejected');
wp_seed_divi_context_same(false, WP_Seed_Content_Render_Context::pop('wrong'), 'out-of-order pop rejected');
wp_seed_divi_context_same(1, WP_Seed_Content_Render_Context::depth(), 'failed pop preserves stack');
wp_seed_divi_context_assert(WP_Seed_Content_Render_Context::pop($first_id), 'matching pop');
wp_seed_divi_context_same(0, WP_Seed_Content_Render_Context::depth(), 'stack restored');
wp_seed_divi_context_same(false, WP_Seed_Content_Render_Context::push(array()), 'invalid context rejected');

for ($depth = 1; $depth <= WP_Seed_Content_Render_Context::MAX_DEPTH; $depth++) {
    $render_id = _wp_seed_content_render_context_id('testimonials', 1000 + $depth);
    wp_seed_divi_context_assert(
        WP_Seed_Content_Render_Context::push(
            array(
                'module' => 'testimonials',
                'post_id' => 1000 + $depth,
                'render_id' => $render_id,
            )
        ),
        'bounded push ' . $depth
    );
}
wp_seed_divi_context_same(
    false,
    WP_Seed_Content_Render_Context::push(
        array(
            'module' => 'testimonials',
            'post_id' => 9999,
            'render_id' => 'overflow',
        )
    ),
    'maximum depth enforced'
);
WP_Seed_Content_Render_Context::reset();
wp_seed_divi_context_same(0, WP_Seed_Content_Render_Context::depth(), 'test reset');

$allowed = wp_seed_content_divi_testimonial_dynamic_content_names();
wp_seed_divi_context_same(
    array(
        'wp_seed_content_testimonial_photo',
        'wp_seed_content_testimonial_text',
        'wp_seed_content_testimonial_name',
        'wp_seed_content_testimonial_context',
        'wp_seed_content_testimonial_date',
    ),
    $allowed,
    'exact provider allowlist'
);

foreach ($allowed as $name) {
    $failed = false;
    $source = wp_seed_divi_variable(
        $name,
        array('before' => 'Before (with braces {kept})')
    );
    $injected = _wp_seed_content_inject_testimonial_post_id_into_divi_string(
        $source,
        101,
        $failed
    );
    wp_seed_divi_context_same(false, $failed, $name . ' parses');
    wp_seed_divi_context_assert(
        false !== strpos($injected, '"post_id":"101"'),
        $name . ' receives post id'
    );
    wp_seed_divi_context_assert(
        false !== strpos($injected, 'Before (with braces {kept})'),
        $name . ' settings preserved'
    );
}

$existing = wp_seed_divi_variable('wp_seed_content_testimonial_text', array(), 999);
$failed = false;
$overridden = _wp_seed_content_inject_testimonial_post_id_into_divi_string(
    $existing,
    102,
    $failed
);
wp_seed_divi_context_assert(false !== strpos($overridden, '"post_id":"102"'), 'existing post id overridden');
wp_seed_divi_context_assert(false === strpos($overridden, '"post_id":999'), 'stale numeric post id removed');

$serialized_failed = false;
$serialized = wp_seed_divi_serialized_variable(
    'wp_seed_content_testimonial_name',
    array('before' => 'Serialized-')
);
$serialized_injected = _wp_seed_content_inject_testimonial_post_id_into_divi_string(
    $serialized,
    103,
    $serialized_failed
);
wp_seed_divi_context_same(false, $serialized_failed, 'serialized block variable decoded');
wp_seed_divi_context_assert(
    false !== strpos($serialized_injected, '\\u0022post_id\\u0022:\\u0022103\\u0022'),
    'serialized block variable receives testimonial id before parsing'
);
wp_seed_divi_context_assert(
    false !== strpos($serialized_injected, '\\u0022before\\u0022:\\u0022Serialized-\\u0022'),
    'serialized block variable settings preserved'
);

$unrelated = array(
    wp_seed_divi_variable('wp_seed_content_quote_text'),
    wp_seed_divi_variable('wp_seed_content_directory_name'),
    wp_seed_divi_variable('post_title'),
    '$variable({"type":"color","value":{"name":"gcid-primary"}})$',
);
foreach ($unrelated as $index => $source) {
    $failed = false;
    wp_seed_divi_context_same(
        $source,
        _wp_seed_content_inject_testimonial_post_id_into_divi_string($source, 101, $failed),
        'unrelated variable unchanged ' . $index
    );
    wp_seed_divi_context_same(false, $failed, 'unrelated variable accepted ' . $index);
}

$failed = false;
$malformed = '$variable({"type":"content","value":{"name":"wp_seed_content_testimonial_text"}}';
_wp_seed_content_inject_testimonial_post_id_into_divi_string($malformed, 101, $failed);
wp_seed_divi_context_same(true, $failed, 'malformed testimonial variable rejected');

$source_token = wp_seed_divi_variable('wp_seed_content_testimonial_name');
$blocks = array(
    array(
        'blockName' => 'divi/group',
        'attrs' => array('unrelated' => 'same'),
        'innerBlocks' => array(
            array(
                'blockName' => 'divi/text',
                'attrs' => array(
                    'content' => array(
                        'desktop' => array('value' => $source_token),
                    ),
                ),
                'innerBlocks' => array(),
                'innerHTML' => '<p>Original</p>',
                'innerContent' => array('<p>Original</p>'),
            ),
        ),
        'innerHTML' => '',
        'innerContent' => array(null),
    ),
);
$GLOBALS['wp_seed_divi_context_blocks']['nested-layout'] = $blocks;
$prepared = wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 101);
wp_seed_divi_context_assert(is_string($prepared), 'nested layout serializes');
wp_seed_divi_context_assert(false !== strpos($prepared, '\\"post_id\\":\\"101\\"'), 'nested attribute injected');
wp_seed_divi_context_same($blocks, $GLOBALS['wp_seed_divi_context_blocks']['nested-layout'], 'parsed source unchanged');

$first = wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 101);
$second = wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 102);
$third = wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 103);
wp_seed_divi_context_assert($first !== $second, 'cache identity differs card 1/2');
wp_seed_divi_context_assert($second !== $third, 'cache identity differs card 2/3');
wp_seed_divi_context_assert($first !== $third, 'cache identity differs card 1/3');

wp_seed_divi_context_same(
    'invalid_testimonial_context',
    wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 0)->get_error_code(),
    'zero post id rejected'
);
wp_seed_divi_context_same(
    'invalid_testimonial_context',
    wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 201)->get_error_code(),
    'wrong post type rejected'
);
wp_seed_divi_context_same(
    'invalid_testimonial_context',
    wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 202)->get_error_code(),
    'draft rejected'
);
wp_seed_divi_context_same(
    'invalid_testimonial_context',
    wp_seed_content_prepare_divi_testimonial_layout_content('nested-layout', 203)->get_error_code(),
    'private testimonial rejected'
);

WP_Seed_Content_Render_Context::reset();
$signal_id = _wp_seed_content_render_context_id('testimonials', 101, 301, 401);
wp_seed_divi_context_assert(
    WP_Seed_Content_Render_Context::push(
        array(
            'module' => 'testimonials',
            'post_id' => 101,
            'template_id' => 301,
            'layout_id' => 401,
            'render_id' => $signal_id,
        )
    ),
    'resolution signal context pushed'
);
wp_seed_divi_context_same(true, WP_Seed_Content_Render_Context::dynamic_resolution_complete(), 'static Layout signal valid');
wp_seed_divi_context_assert(
    WP_Seed_Content_Render_Context::expect_dynamic('wp_seed_content_testimonial_name'),
    'dynamic field expected'
);
wp_seed_divi_context_same(false, WP_Seed_Content_Render_Context::dynamic_resolution_complete(), 'unresolved dynamic Layout invalid');
wp_seed_divi_context_same(false, WP_Seed_Content_Render_Context::resolve_dynamic('wp_seed_content_testimonial_name', 102), 'cross-card resolution rejected');
wp_seed_divi_context_assert(
    WP_Seed_Content_Render_Context::resolve_dynamic('wp_seed_content_testimonial_name', 101)
    && WP_Seed_Content_Render_Context::dynamic_resolution_complete(),
    'matching provider completes dynamic Layout'
);
WP_Seed_Content_Render_Context::pop($signal_id);
wp_seed_divi_context_same(0, WP_Seed_Content_Render_Context::depth(), 'signal stack restored');

wp_seed_divi_context_same(
    'divi_layout_context_exception',
    wp_seed_content_prepare_divi_testimonial_layout_content('throw-parse', 101)->get_error_code(),
    'parse exception isolated'
);

$GLOBALS['wp_seed_divi_context_blocks']['malformed-layout'] = array(
    array(
        'blockName' => 'divi/text',
        'attrs' => array('content' => $malformed),
        'innerBlocks' => array(),
        'innerHTML' => '',
        'innerContent' => array(''),
    ),
);
wp_seed_divi_context_same(
    'invalid_divi_dynamic_content',
    wp_seed_content_prepare_divi_testimonial_layout_content('malformed-layout', 101)->get_error_code(),
    'raw malformed variable causes local fallback'
);

$rendered_cards = array();
foreach (array(101, 102, 103) as $card_id) {
    $render_id = _wp_seed_content_render_context_id('testimonials', $card_id, 301, 401);
    $context = array(
        'module' => 'testimonials',
        'post_id' => $card_id,
        'template_id' => 301,
        'layout_id' => 401,
        'render_id' => $render_id,
    );
    try {
        wp_seed_divi_context_assert(
            WP_Seed_Content_Render_Context::push($context),
            'card context push ' . $card_id
        );
        if (102 === $card_id) {
            throw new RuntimeException('synthetic middle-card failure');
        }
        $rendered_cards[$card_id] = wp_seed_content_prepare_divi_testimonial_layout_content(
            'nested-layout',
            $card_id
        );
    } catch (Throwable $exception) {
        $rendered_cards[$card_id] = 'native-' . $card_id;
    } finally {
        WP_Seed_Content_Render_Context::pop($render_id);
    }
}
wp_seed_divi_context_assert(false !== strpos($rendered_cards[101], '\\"post_id\\":\\"101\\"'), 'card 1 correct');
wp_seed_divi_context_same('native-102', $rendered_cards[102], 'card 2 falls back');
wp_seed_divi_context_assert(false !== strpos($rendered_cards[103], '\\"post_id\\":\\"103\\"'), 'card 3 correct after exception');
wp_seed_divi_context_same(0, WP_Seed_Content_Render_Context::depth(), 'final stack depth zero');

if (!empty($failures)) {
    fwrite(STDERR, "FAIL\n" . implode("\n", $failures) . "\n");
    exit(1);
}

echo 'PASS: Divi per-item context (' . $assertions . " assertions)\n";
