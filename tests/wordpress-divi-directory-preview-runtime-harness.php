<?php

$wp_load = getenv('WP_SEED_WORDPRESS_LOAD');
if (!defined('ABSPATH')) {
    if (!is_string($wp_load) || '' === $wp_load || !is_file($wp_load)) {
        fwrite(STDERR, "Set WP_SEED_WORDPRESS_LOAD.\n");
        exit(2);
    }
    require $wp_load;
}
require_once ABSPATH . 'wp-admin/includes/user.php';

if (!function_exists('et_builder_d5_enabled')) {
    function et_builder_d5_enabled()
    {
        return true;
    }
}

$root = dirname(__DIR__);
$plugin_file = $root . '/plugin/wp-seed-content-kit.php';
$assertions = 0;
$failures = array();
$created_posts = array();
$created_users = array();
$previous_modules = get_option('wp_seed_content_kit_modules', null);
$previous_user_id = get_current_user_id();

function seed_preview_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_preview_wp_same($expected, $actual, $label)
{
    seed_preview_wp_assert(
        $expected === $actual,
        $label . ' (got ' . var_export($actual, true) . ')'
    );
}

function seed_preview_wp_create_entry(
    $name,
    $types,
    $listed = '1',
    $status = 'publish',
    $password = '',
    $seeking = false,
    $order = 0
) {
    global $created_posts;
    $meta = array(
        '_seed_directory_status' => $seeking ? 'seeking_models' : 'practicing',
        '_seed_directory_country' => 'FR',
        '_seed_directory_publication_authorized' => '1',
        '_seed_directory_profile_types' => $types,
    );
    if (null !== $listed) {
        $meta['_seed_directory_publicly_listed'] = $listed;
    }
    if ($seeking) {
        $meta['_seed_directory_seeking_models'] = '1';
    }
    $post_id = wp_insert_post(
        array(
            'post_type' => 'seed_directory',
            'post_status' => $status,
            'post_password' => $password,
            'post_title' => 'SEED PREVIEW ' . $name,
            'post_excerpt' => 'Summary ' . $name,
            'post_content' => '<p>Full ' . $name . '</p>',
            'menu_order' => $order,
            'meta_input' => $meta,
        ),
        true
    );
    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }
    $created_posts[] = (int) $post_id;
    return (int) $post_id;
}

function seed_preview_wp_request($params, $nonce = '')
{
    $request = new WP_REST_Request(
        'GET',
        '/wp-seed-content-kit/v1/divi/directory-preview'
    );
    foreach ($params as $key => $value) {
        $request->set_param($key, $value);
    }
    if ('' !== $nonce) {
        $request->set_header('X-WP-Nonce', $nonce);
    }
    return rest_get_server()->dispatch($request);
}

try {
    require_once $plugin_file;
    $admins = get_users(
        array('role' => 'administrator', 'number' => 1, 'fields' => 'ID')
    );
    if (empty($admins)) {
        throw new RuntimeException('No administrator in isolated WordPress.');
    }
    $admin_id = (int) $admins[0];
    wp_set_current_user($admin_id);
    wp_seed_content_kit_activate();
    do_action('init');
    do_action('rest_api_init');

    $routes = rest_get_server()->get_routes();
    seed_preview_wp_assert(
        isset($routes['/wp-seed-content-kit/v1/divi/directory-preview']),
        'Authenticated Divi Directory preview route is registered'
    );
    $route = $routes['/wp-seed-content-kit/v1/divi/directory-preview'][0];
    seed_preview_wp_assert(
        isset($route['methods']['GET']) && true === $route['methods']['GET'],
        'Preview route accepts GET'
    );
    seed_preview_wp_assert(
        empty($route['methods']['POST']),
        'Preview route rejects POST'
    );

    $alice = seed_preview_wp_create_entry(
        'Alice',
        array('praticien'),
        '1',
        'publish',
        '',
        false,
        1
    );
    $bruno = seed_preview_wp_create_entry(
        'Bruno',
        array('intervenant'),
        '1',
        'publish',
        '',
        false,
        2
    );
    $celine = seed_preview_wp_create_entry(
        'Celine',
        array('praticien', 'intervenant'),
        '1',
        'publish',
        '',
        true,
        3
    );
    $unlisted = seed_preview_wp_create_entry(
        'Unlisted',
        array('praticien'),
        null,
        'publish',
        '',
        false,
        4
    );
    $false_value = seed_preview_wp_create_entry(
        'False',
        array('praticien'),
        '0',
        'publish',
        '',
        false,
        5
    );
    $draft = seed_preview_wp_create_entry(
        'Draft',
        array('praticien'),
        '1',
        'draft',
        '',
        false,
        6
    );
    $private = seed_preview_wp_create_entry(
        'Private',
        array('praticien'),
        '1',
        'private',
        '',
        false,
        7
    );
    $protected = seed_preview_wp_create_entry(
        'Protected',
        array('praticien'),
        '1',
        'publish',
        'secret',
        false,
        8
    );

    wp_set_current_user(0);
    $response = seed_preview_wp_request(array());
    seed_preview_wp_assert(
        in_array($response->get_status(), array(401, 403), true),
        'Anonymous preview is rejected'
    );

    wp_set_current_user($admin_id);
    $response = seed_preview_wp_request(array(), 'invalid');
    seed_preview_wp_assert(
        in_array($response->get_status(), array(401, 403), true),
        'Invalid nonce is rejected'
    );

    $subscriber_id = wp_insert_user(
        array(
            'user_login' => 'seed-preview-subscriber-' . wp_generate_password(8, false),
            'user_pass' => wp_generate_password(24, true),
            'user_email' => 'seed-preview-' . wp_generate_password(8, false) . '@example.test',
            'role' => 'subscriber',
        )
    );
    if (is_wp_error($subscriber_id)) {
        throw new RuntimeException($subscriber_id->get_error_message());
    }
    $created_users[] = (int) $subscriber_id;
    wp_set_current_user((int) $subscriber_id);
    $subscriber_nonce = wp_create_nonce('wp_rest');
    $response = seed_preview_wp_request(array(), $subscriber_nonce);
    seed_preview_wp_assert(
        in_array($response->get_status(), array(401, 403), true),
        'User without edit_pages is rejected'
    );

    wp_set_current_user($admin_id);
    $nonce = wp_create_nonce('wp_rest');
    $params = array(
        'profile_types' => 'praticien,intervenant',
        'profile_type_operator' => 'or',
        'orderby' => 'display_order',
        'order' => 'asc',
        'limit' => '0',
        'offset' => '0',
    );
    $response = seed_preview_wp_request($params, $nonce);
    seed_preview_wp_same(200, $response->get_status(), 'Authorized preview succeeds');
    $data = $response->get_data();
    $html = isset($data['html']) ? $data['html'] : '';
    $normalized = wp_seed_content_directory_normalize_shortcode_atts($params);
    seed_preview_wp_same(
        wp_seed_content_render_normalized_directory_collection($normalized, false),
        $html,
        'Preview HTML exactly matches the canonical frontend renderer'
    );
    seed_preview_wp_assert(false !== strpos($html, 'SEED PREVIEW Alice'), 'Praticien is rendered');
    seed_preview_wp_assert(false !== strpos($html, 'SEED PREVIEW Bruno'), 'Intervenant is rendered');
    seed_preview_wp_assert(false !== strpos($html, 'SEED PREVIEW Celine'), 'Multi-type profile is rendered');
    foreach (array($unlisted, $false_value, $draft, $private, $protected) as $blocked_id) {
        seed_preview_wp_assert(
            false === strpos($html, get_the_title($blocked_id)),
            'Non-public entry is excluded from preview: ' . $blocked_id
        );
    }

    $explicit = array(
        'ids' => implode(',', array($alice, $unlisted, $false_value, $draft, $private, $protected)),
    );
    $response = seed_preview_wp_request($explicit, $nonce);
    $explicit_html = $response->get_data()['html'];
    seed_preview_wp_assert(false !== strpos($explicit_html, 'SEED PREVIEW Alice'), 'Listed explicit ID is rendered');
    seed_preview_wp_assert(false === strpos($explicit_html, 'SEED PREVIEW Unlisted'), 'Explicit ID cannot bypass publicly listed');
    seed_preview_wp_assert(false === strpos($explicit_html, 'SEED PREVIEW Draft'), 'Explicit ID cannot bypass publish status');
    seed_preview_wp_assert(false === strpos($explicit_html, 'SEED PREVIEW Private'), 'Explicit ID cannot bypass private status');
    seed_preview_wp_assert(false === strpos($explicit_html, 'SEED PREVIEW Protected'), 'Explicit ID cannot bypass password protection');

    $intersection = array(
        'ids' => implode(',', array($alice, $bruno, $celine)),
        'exclude_ids' => implode(',', array($alice, $celine)),
        'orderby' => 'display_order',
        'order' => 'asc',
        'limit' => '0',
    );
    $response = seed_preview_wp_request($intersection, $nonce);
    $intersection_html = $response->get_data()['html'];
    $intersection_frontend = wp_seed_content_render_normalized_directory_collection(
        wp_seed_content_directory_normalize_shortcode_atts($intersection),
        false
    );
    seed_preview_wp_same($intersection_frontend, $intersection_html, 'IDs plus exclusions match frontend and Builder preview');
    seed_preview_wp_assert(false !== strpos($intersection_html, 'SEED PREVIEW Bruno'), 'Non-excluded explicit ID remains in preview');
    seed_preview_wp_assert(false === strpos($intersection_html, 'SEED PREVIEW Alice'), 'Excluded explicit ID is absent from preview');
    seed_preview_wp_assert(false === strpos($intersection_html, 'SEED PREVIEW Celine'), 'Every intersecting exclusion is absent from preview');

    $response = seed_preview_wp_request(array('ids' => (string) $alice, 'exclude_ids' => (string) $alice), $nonce);
    $empty_intersection_html = $response->get_data()['html'];
    seed_preview_wp_assert(false === strpos($empty_intersection_html, 'SEED PREVIEW Alice'), 'All excluded IDs return the empty preview state');

    $response = seed_preview_wp_request(
        array(
            'profile_types' => 'praticien,intervenant',
            'profile_type_operator' => 'and',
        ),
        $nonce
    );
    $and_html = $response->get_data()['html'];
    seed_preview_wp_assert(false !== strpos($and_html, 'SEED PREVIEW Celine'), 'AND keeps the multi-type profile');
    seed_preview_wp_assert(false === strpos($and_html, 'SEED PREVIEW Alice'), 'AND excludes a single-type profile');

    $response = seed_preview_wp_request(
        array('orderby' => 'display_order', 'order' => 'desc', 'limit' => '1'),
        $nonce
    );
    $limited_html = $response->get_data()['html'];
    seed_preview_wp_assert(false !== strpos($limited_html, 'SEED PREVIEW Celine'), 'Order and limit use canonical collection logic');
    seed_preview_wp_assert(false === strpos($limited_html, 'SEED PREVIEW Alice'), 'Limit is enforced');

    $response = seed_preview_wp_request(array('limit' => '-1'), $nonce);
    seed_preview_wp_same(400, $response->get_status(), 'Invalid collection parameters fail closed');

    $headers = seed_preview_wp_request(array(), $nonce)->get_headers();
    seed_preview_wp_assert(
        isset($headers['Cache-Control'])
        && false !== strpos($headers['Cache-Control'], 'no-store'),
        'Preview response is not publicly cacheable'
    );
} catch (Throwable $error) {
    $failures[] = $error->getMessage();
} catch (Exception $error) {
    $failures[] = $error->getMessage();
}

foreach (array_reverse($created_posts) as $post_id) {
    wp_delete_post($post_id, true);
}
foreach (array_reverse($created_users) as $user_id) {
    wp_delete_user($user_id);
}
if (null === $previous_modules) {
    delete_option('wp_seed_content_kit_modules');
} else {
    update_option('wp_seed_content_kit_modules', $previous_modules);
}
wp_set_current_user($previous_user_id);

if (!empty($failures)) {
    fwrite(
        STDERR,
        'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL
        . implode(PHP_EOL, $failures) . PHP_EOL
    );
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress Divi Directory preview runtime assertions' . PHP_EOL;
