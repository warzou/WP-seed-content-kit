<?php

require __DIR__ . '/collections-adapters-harness.php';

$GLOBALS['wp_seed_divi_assertions'] = 0;
$GLOBALS['wp_seed_divi_failures'] = array();
$GLOBALS['wp_seed_divi_actions'] = array();
$GLOBALS['wp_seed_divi_routes'] = array();
$GLOBALS['wp_seed_divi_available'] = false;

class WP_REST_Server
{
    const READABLE = 'GET';
}

class WP_REST_Request
{
    private $params;

    public function __construct($params = array())
    {
        $this->params = $params;
    }

    public function get_param($key)
    {
        return array_key_exists($key, $this->params) ? $this->params[$key] : null;
    }
}

function add_action($hook, $callback)
{
    $GLOBALS['wp_seed_divi_actions'][$hook][] = $callback;
}

function register_rest_route($namespace, $route, $args)
{
    $GLOBALS['wp_seed_divi_routes'][$namespace . $route] = $args;
}

function rest_ensure_response($value)
{
    return $value;
}

function et_builder_d5_enabled()
{
    return $GLOBALS['wp_seed_divi_available'];
}

function wp_seed_divi_assert($condition, $label)
{
    $GLOBALS['wp_seed_divi_assertions']++;
    if (!$condition) {
        $GLOBALS['wp_seed_divi_failures'][] = $label;
    }
}

function wp_seed_divi_same($expected, $actual, $label)
{
    wp_seed_divi_assert($expected === $actual, $label);
}

require dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-collection.php';

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

try {
    wp_seed_divi_same(false, wp_seed_content_divi_testimonial_collection_is_available(), 'Divi module unavailable without Divi 5');
    wp_seed_divi_assert(isset($GLOBALS['wp_seed_divi_actions']['divi_module_library_modules_dependency_tree']), 'Divi dependency hook registered safely');
    wp_seed_divi_assert(isset($GLOBALS['wp_seed_divi_actions']['rest_api_init']), 'Builder preview route hook registered safely');
    wp_seed_divi_assert(isset($GLOBALS['wp_seed_divi_actions']['divi_visual_builder_assets_before_enqueue_scripts']), 'Builder assets hook registered safely');

    wp_seed_content_register_divi_testimonial_preview_route();
    wp_seed_divi_same(array(), $GLOBALS['wp_seed_divi_routes'], 'No preview route without Divi 5');

    $GLOBALS['wp_seed_divi_available'] = true;
    wp_seed_divi_same(true, wp_seed_content_divi_testimonial_collection_is_available(), 'Divi 5 availability detected');
    wp_seed_content_register_divi_testimonial_preview_route();
    $route_key = 'wp-seed-content-kit/v1/divi/testimonials-preview';
    wp_seed_divi_assert(isset($GLOBALS['wp_seed_divi_routes'][$route_key]), 'Authenticated Builder preview route registered with Divi 5');
    wp_seed_divi_same('GET', $GLOBALS['wp_seed_divi_routes'][$route_key]['methods'], 'Preview route is read-only');
    wp_seed_divi_same('wp_seed_content_can_preview_divi_testimonials', $GLOBALS['wp_seed_divi_routes'][$route_key]['permission_callback'], 'Preview route has explicit capability callback');
    wp_seed_divi_same(false, wp_seed_content_can_preview_divi_testimonials(), 'User without page editing capability cannot preview');

    $attrs = array(
        'collection' => array(
            'innerContent' => array(
                'desktop' => array(
                    'value' => array(
                        'ids' => '102,101',
                        'featured' => 'exclude',
                        'context' => 'Suivi',
                        'limit' => '2',
                        'orderby' => 'display_order',
                        'order' => 'asc',
                        'template' => 'testimonial-native',
                        'columns' => '2',
                    ),
                ),
            ),
        ),
    );
    $args = wp_seed_content_divi_testimonial_collection_args($attrs);
    wp_seed_divi_same('102,101', $args['ids'], 'IDs extracted from Divi attributes');
    wp_seed_divi_same('exclude', $args['featured'], 'Featured filter extracted from Divi attributes');
    wp_seed_divi_same('Suivi', $args['context'], 'Context extracted from Divi attributes');
    wp_seed_divi_same('2', $args['limit'], 'Limit extracted from Divi attributes');
    wp_seed_divi_same('display_order', $args['orderby'], 'Sort extracted from Divi attributes');
    wp_seed_divi_same('asc', $args['order'], 'Order extracted from Divi attributes');
    wp_seed_divi_same('testimonial-native', $args['template'], 'Template extracted from Divi attributes');
    wp_seed_divi_same('2', $args['columns'], 'Columns extracted from Divi attributes');

    $defaults = wp_seed_content_divi_testimonial_collection_args(array());
    wp_seed_divi_same('', $defaults['ids'], 'Default IDs are automatic');
    wp_seed_divi_same('all', $defaults['featured'], 'Default featured mode is all');
    wp_seed_divi_same(3, $defaults['limit'], 'Default limit is three');
    wp_seed_divi_same('date', $defaults['orderby'], 'Default sorting is publication date');
    wp_seed_divi_same('desc', $defaults['order'], 'Default order is descending');
    wp_seed_divi_same('', $defaults['template'], 'Default Template is native');
    wp_seed_divi_same(3, $defaults['columns'], 'Default columns are three');

    $invalid = wp_seed_content_normalize_testimonial_collection_args(
        array(
            'limit' => new stdClass(),
            'template' => new stdClass(),
            'orderby' => new stdClass(),
            'order' => new stdClass(),
            'featured' => new stdClass(),
            'context' => new stdClass(),
        )
    );
    wp_seed_divi_same(3, $invalid['limit'], 'Object limit falls back safely');
    wp_seed_divi_same('', $invalid['template'], 'Object Template falls back safely');
    wp_seed_divi_same('date', $invalid['orderby'], 'Object sorting falls back safely');
    wp_seed_divi_same('desc', $invalid['order'], 'Object order falls back safely');
    wp_seed_divi_same('all', $invalid['featured'], 'Object featured filter falls back safely');
    wp_seed_divi_same('', $invalid['context'], 'Object context falls back safely');
    $template_options = wp_seed_content_get_divi_testimonial_template_options();
    wp_seed_divi_assert(isset($template_options['native']), 'Native Template option uses a visible Divi-safe value');
    wp_seed_divi_same('Rendu natif', $template_options['native']['label'], 'Native Template option has explicit label');

    $GLOBALS['wp_seed_test_assets_enqueued'] = 0;
    $shortcode_html = wp_seed_content_testimonials_shortcode(array('ids' => '102', 'columns' => '2'));
    $module_html = wp_seed_content_render_testimonial_collection(array('ids' => '102', 'columns' => '2'));
    wp_seed_divi_same($shortcode_html, $module_html, 'Module renderer is functionally identical to shortcode renderer');
    wp_seed_divi_same(2, $GLOBALS['wp_seed_test_assets_enqueued'], 'Canonical assets enqueue once per independent render request');

    $preview = wp_seed_content_render_divi_testimonial_preview(
        new WP_REST_Request(array('ids' => '102', 'columns' => '2'))
    );
    wp_seed_divi_assert(isset($preview['html']), 'Builder preview returns typed HTML payload');
    wp_seed_divi_assert(false !== strpos($preview['html'], 'Texte 102'), 'Builder preview contains real testimonial rendering');
    wp_seed_divi_same(2, $GLOBALS['wp_seed_test_assets_enqueued'], 'Builder preview does not enqueue frontend assets');

    $empty_preview = wp_seed_content_render_divi_testimonial_preview(new WP_REST_Request(array('ids' => '999')));
    wp_seed_divi_assert(false !== strpos($empty_preview['html'], 'seed-testimonials__empty'), 'Builder preview exposes clean empty state');

    $module_source = file_get_contents(dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-collection/Module.php');
    $builder_source = file_get_contents(dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-collection/visual-builder.js');
    $metadata = json_decode(file_get_contents(dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-collection/module.json'), true);
    wp_seed_divi_assert(is_array($metadata), 'Divi module metadata is valid JSON');
    wp_seed_divi_same('wp-seed-content-kit/testimonial-collection', $metadata['name'], 'Divi module has stable block name');
    wp_seed_divi_same('', $metadata['d4Shortcode'], 'No Divi 4 shortcode dependency');
    wp_seed_divi_assert(false !== strpos($module_source, 'ModuleRegistration::register_module'), 'Server module uses Divi 5 ModuleRegistration');
    wp_seed_divi_assert(false !== strpos($builder_source, 'useFetch'), 'Visual Builder uses Divi REST preview hook');
    wp_seed_divi_assert(false === strpos($builder_source, '[seed_testimonials'), 'Visual Builder does not generate a testimonial shortcode');
    wp_seed_divi_assert(false !== strpos($builder_source, 'AbortController'), 'Visual Builder cancels obsolete preview requests');
    wp_seed_divi_assert(false !== strpos($builder_source, 'hooks.didAction'), 'Late package load registers idempotently after Divi store readiness');
    wp_seed_divi_assert(false !== strpos($builder_source, 'template_slug'), 'Builder uses non-reserved REST Template parameter');
    wp_seed_divi_assert(false === strpos($module_source, 'ET_Builder_Module'), 'No obsolete Divi 4 module API');
} catch (Throwable $exception) {
    $GLOBALS['wp_seed_divi_failures'][] = get_class($exception) . ': ' . $exception->getMessage();
}

restore_error_handler();

if (!empty($GLOBALS['wp_seed_divi_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['wp_seed_divi_failures']) . ' / ' . $GLOBALS['wp_seed_divi_assertions'] . PHP_EOL);
    foreach ($GLOBALS['wp_seed_divi_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['wp_seed_divi_assertions'] . ' Divi testimonial collection assertions' . PHP_EOL;