<?php

define('ABSPATH', __DIR__);

$GLOBALS['seed_divi_directory_assertions'] = 0;
$GLOBALS['seed_divi_directory_failures'] = array();
$GLOBALS['seed_divi_directory_actions'] = array();
$GLOBALS['seed_divi_directory_routes'] = array();
$GLOBALS['seed_divi_directory_available'] = false;
$GLOBALS['seed_divi_directory_can_edit'] = false;
$GLOBALS['seed_divi_directory_nonce'] = 'valid-nonce';
$GLOBALS['seed_divi_directory_last_render'] = null;

class WP_REST_Server
{
    const READABLE = 'GET';
}

class WP_REST_Request
{
    private $params;
    private $headers;

    public function __construct($params = array(), $headers = array())
    {
        $this->params = $params;
        $this->headers = $headers;
    }

    public function get_param($key)
    {
        return array_key_exists($key, $this->params) ? $this->params[$key] : null;
    }

    public function get_header($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : '';
    }
}

class WP_REST_Response
{
    public $data;
    public $headers = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function header($key, $value)
    {
        $this->headers[$key] = $value;
    }
}

class WP_Error
{
    public $code;
    public $message;
    public $data;

    public function __construct($code, $message, $data = array())
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}

class WP_Post
{
    public $ID;
    public $post_name;

    public function __construct($id, $slug)
    {
        $this->ID = $id;
        $this->post_name = $slug;
    }
}

function add_action($hook, $callback)
{
    $GLOBALS['seed_divi_directory_actions'][$hook][] = $callback;
}

function register_rest_route($namespace, $route, $args)
{
    $GLOBALS['seed_divi_directory_routes'][$namespace . $route] = $args;
}

function rest_ensure_response($value)
{
    return new WP_REST_Response($value);
}

function et_builder_d5_enabled()
{
    return $GLOBALS['seed_divi_directory_available'];
}

function current_user_can($capability)
{
    if ('edit_pages' === $capability) {
        return $GLOBALS['seed_divi_directory_can_edit'];
    }
    return 'manage_wp_seed_templates' === $capability;
}

function wp_verify_nonce($nonce, $action)
{
    return 'wp_rest' === $action && $nonce === $GLOBALS['seed_divi_directory_nonce'];
}

function wp_unslash($value)
{
    return $value;
}

function __($value)
{
    return $value;
}

function sanitize_title($value)
{
    return strtolower(preg_replace('/[^a-z0-9-]+/i', '-', trim((string) $value)));
}

function get_posts()
{
    return array(new WP_Post(12, 'directory-card'), new WP_Post(13, 'wrong-module'));
}

function wp_seed_content_get_template_module($id)
{
    return 12 === (int) $id ? 'directory' : 'testimonials';
}

function get_the_title($post)
{
    return 12 === (int) $post->ID ? 'Carte Annuaire' : 'Autre';
}

function wp_seed_content_directory_normalize_shortcode_atts($args)
{
    if (isset($args['status']) && 'invalid' === $args['status']) {
        return null;
    }

    return array(
        'collection' => $args,
        'template' => isset($args['template']) ? $args['template'] : '',
    );
}

function wp_seed_content_render_normalized_directory_collection($normalized, $enqueue_assets)
{
    $GLOBALS['seed_divi_directory_last_render'] = array($normalized, $enqueue_assets);
    return '<div class="wp-seed-directory" data-render="canonical"></div>';
}

function seed_divi_directory_assert($condition, $label)
{
    $GLOBALS['seed_divi_directory_assertions']++;
    if (!$condition) {
        $GLOBALS['seed_divi_directory_failures'][] = $label;
    }
}

function seed_divi_directory_same($expected, $actual, $label)
{
    seed_divi_directory_assert($expected === $actual, $label);
}

require dirname(__DIR__) . '/plugin/includes/integrations/divi/directory-collection.php';

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    seed_divi_directory_same(false, wp_seed_content_divi_directory_collection_is_available(), 'Divi absent is safe');
    seed_divi_directory_assert(isset($GLOBALS['seed_divi_directory_actions']['divi_module_library_modules_dependency_tree']), 'Module hook registered');
    seed_divi_directory_assert(isset($GLOBALS['seed_divi_directory_actions']['rest_api_init']), 'REST hook registered');
    seed_divi_directory_assert(isset($GLOBALS['seed_divi_directory_actions']['divi_visual_builder_assets_before_enqueue_scripts']), 'Builder asset hook registered');

    wp_seed_content_register_divi_directory_preview_route();
    seed_divi_directory_same(array(), $GLOBALS['seed_divi_directory_routes'], 'No route without Divi 5');

    $GLOBALS['seed_divi_directory_available'] = true;
    wp_seed_content_register_divi_directory_preview_route();
    $route_key = 'wp-seed-content-kit/v1/divi/directory-preview';
    seed_divi_directory_assert(isset($GLOBALS['seed_divi_directory_routes'][$route_key]), 'Private preview route registered');
    $route = $GLOBALS['seed_divi_directory_routes'][$route_key];
    seed_divi_directory_same('GET', $route['methods'], 'Preview route is read-only');
    seed_divi_directory_same('wp_seed_content_can_preview_divi_directory', $route['permission_callback'], 'Explicit permission callback');
    seed_divi_directory_same(14, count($route['args']), 'All collection request parameters declared');

    $request = new WP_REST_Request(array(), array('X-WP-Nonce' => 'valid-nonce'));
    seed_divi_directory_same(false, wp_seed_content_can_preview_divi_directory($request), 'Capability required');
    $GLOBALS['seed_divi_directory_can_edit'] = true;
    seed_divi_directory_same(true, wp_seed_content_can_preview_divi_directory($request), 'Valid nonce and capability accepted');
    seed_divi_directory_same(false, wp_seed_content_can_preview_divi_directory(new WP_REST_Request()), 'Missing nonce rejected');
    seed_divi_directory_same(false, wp_seed_content_can_preview_divi_directory(new WP_REST_Request(array(), array('X-WP-Nonce' => 'bad'))), 'Invalid nonce rejected');

    $attrs = array(
        'collection' => array(
            'innerContent' => array(
                'desktop' => array(
                    'value' => array(
                        'status' => 'practicing',
                        'profile_types' => 'praticien,intervenant',
                        'profile_type_operator' => 'and',
                        'seeking_models' => '1',
                        'department' => '75',
                        'country' => 'FR',
                        'featured' => 'only',
                        'ids' => '9,7',
                        'exclude_ids' => '8',
                        'limit' => '2',
                        'offset' => '1',
                        'orderby' => 'name',
                        'order' => 'desc',
                        'template' => 'directory-card',
                    ),
                ),
            ),
        ),
    );
    $args = wp_seed_content_divi_directory_collection_args($attrs);
    seed_divi_directory_same('practicing', $args['status'], 'Status extracted');
    seed_divi_directory_same('praticien,intervenant', $args['profile_types'], 'Multiple types extracted');
    seed_divi_directory_same('and', $args['profile_type_operator'], 'Type operator extracted');
    seed_divi_directory_same('1', $args['seeking_models'], 'Seeking models extracted');
    seed_divi_directory_same('75', $args['department'], 'Department extracted');
    seed_divi_directory_same('FR', $args['country'], 'Country extracted');
    seed_divi_directory_same('only', $args['featured'], 'Featured extracted');
    seed_divi_directory_same('9,7', $args['ids'], 'IDs extracted');
    seed_divi_directory_same('8', $args['exclude_ids'], 'Excluded IDs extracted');
    seed_divi_directory_same('2', $args['limit'], 'Limit extracted');
    seed_divi_directory_same('1', $args['offset'], 'Offset extracted');
    seed_divi_directory_same('name', $args['orderby'], 'Order field extracted');
    seed_divi_directory_same('desc', $args['order'], 'Direction extracted');
    seed_divi_directory_same('directory-card', $args['template'], 'Template extracted');

    $defaults = wp_seed_content_divi_directory_collection_args(array());
    seed_divi_directory_same('all', $defaults['status'], 'Default all profiles');
    seed_divi_directory_same('', $defaults['profile_types'], 'Default all profile types');
    seed_divi_directory_same('or', $defaults['profile_type_operator'], 'Default OR');
    seed_divi_directory_same('all', $defaults['seeking_models'], 'Default all model statuses');
    seed_divi_directory_same('0', $defaults['limit'], 'Default unlimited');
    seed_divi_directory_same('0', $defaults['offset'], 'Default zero offset');
    seed_divi_directory_same('display_order', $defaults['orderby'], 'Default display order');
    seed_divi_directory_same('asc', $defaults['order'], 'Default ascending');
    seed_divi_directory_same('', $defaults['template'], 'Native Template normalized');

    $preview_request = new WP_REST_Request(array(
        'profile_types' => 'praticien,intervenant',
        'profile_type_operator' => 'and',
        'ids' => '9,7',
        'exclude_ids' => '8',
        'limit' => '2',
        'offset' => '1',
        'template_slug' => 'directory-card',
    ));
    $preview = wp_seed_content_render_divi_directory_preview($preview_request);
    seed_divi_directory_assert($preview instanceof WP_REST_Response, 'Preview returns REST response');
    seed_divi_directory_assert(false !== strpos($preview->data['html'], 'data-render="canonical"'), 'Preview uses canonical renderer output');
    seed_divi_directory_same(false, $GLOBALS['seed_divi_directory_last_render'][1], 'Preview never enqueues frontend assets');
    seed_divi_directory_same('directory-card', $GLOBALS['seed_divi_directory_last_render'][0]['template'], 'Preview Template propagated');
    seed_divi_directory_same('no-store, no-cache, must-revalidate, max-age=0', $preview->headers['Cache-Control'], 'Preview disables public cache');
    seed_divi_directory_same('no-cache', $preview->headers['Pragma'], 'Preview sends legacy no-cache header');

    $invalid = wp_seed_content_render_divi_directory_preview(new WP_REST_Request(array('status' => 'invalid')));
    seed_divi_directory_assert($invalid instanceof WP_Error, 'Invalid collection settings return error');
    seed_divi_directory_same(400, $invalid->data['status'], 'Invalid settings use HTTP 400');

    $options = wp_seed_content_get_divi_directory_template_options();
    seed_divi_directory_assert(isset($options['native'], $options['directory-card']), 'Native and published Directory Templates exposed');
    seed_divi_directory_assert(!isset($options['wrong-module']), 'Other Template modules excluded');

    $root = dirname(__DIR__);
    $integration = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection.php');
    $module = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/Module.php');
    $builder = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/visual-builder.js');
    $metadata = json_decode(file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/module.json'), true);
    seed_divi_directory_assert(is_array($metadata), 'Module metadata is valid JSON');
    seed_divi_directory_same('wp-seed-content-kit/directory-collection', $metadata['name'], 'Stable module block name');
    seed_divi_directory_same('', $metadata['d4Shortcode'], 'No Divi 4 shortcode dependency');
    seed_divi_directory_assert(false !== strpos($module, 'wp_seed_content_render_directory_collection'), 'Frontend module uses shared renderer');
    seed_divi_directory_assert(false !== strpos($integration, 'wp_seed_content_render_normalized_directory_collection'), 'Builder route uses shared renderer');
    seed_divi_directory_assert(false !== strpos($builder, 'useFetch'), 'Builder uses official Divi REST hook');
    seed_divi_directory_assert(false !== strpos($builder, 'AbortController'), 'Obsolete preview requests are cancelled');
    seed_divi_directory_assert(false !== strpos($builder, 'hooks.didAction'), 'Late Divi package load is idempotent');
    seed_divi_directory_assert(false === strpos($integration . $module . $builder, 'do_shortcode'), 'No generated shortcode execution');
    seed_divi_directory_assert(false === strpos($integration . $module . $builder, 'wp_ajax_'), 'No AJAX endpoint');
    seed_divi_directory_assert(false === strpos($builder, 'MutationObserver'), 'No DOM observer');
    seed_divi_directory_assert(false === strpos($builder, 'setTimeout'), 'No arbitrary delay');
    seed_divi_directory_assert(false === strpos($module, 'ET_Builder_Module'), 'No Divi 4 module API');
    seed_divi_directory_assert(false !== strpos($builder, "role: 'alert'"), 'Accessible error status');
    seed_divi_directory_assert(false !== strpos($builder, "'aria-live': 'polite'"), 'Accessible loading status');
} catch (Throwable $exception) {
    $GLOBALS['seed_divi_directory_failures'][] = get_class($exception) . ': ' . $exception->getMessage();
}

restore_error_handler();

if (!empty($GLOBALS['seed_divi_directory_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_divi_directory_failures']) . ' / ' . $GLOBALS['seed_divi_directory_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_divi_directory_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_divi_directory_assertions'] . ' Divi Directory collection assertions' . PHP_EOL;
