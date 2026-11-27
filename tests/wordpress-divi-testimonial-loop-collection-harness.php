<?php
/**
 * Isolated WordPress/Divi integration checks for testimonial Loop Builder.
 *
 * Run inside an already loaded WordPress request, or set
 * WP_SEED_WORDPRESS_LOAD to an isolated wp-load.php.
 */

if (!defined('ABSPATH')) {
    $wp_load = getenv('WP_SEED_WORDPRESS_LOAD');
    if (!is_string($wp_load) || '' === $wp_load || !is_file($wp_load)) {
        echo "Set WP_SEED_WORDPRESS_LOAD to an isolated WordPress wp-load.php.\n";
        exit(2);
    }

    require $wp_load;
}

$assertions = 0;
$failures = array();
$created = array();
$original_user = get_current_user_id();

function seed_loop_wp_assert($condition, $message)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function seed_loop_wp_create_testimonial($name, $context, $order, $status = 'publish', $password = '')
{
    global $created;

    $post_id = wp_insert_post(
        array(
            'post_type' => 'seed_testimonial',
            'post_status' => $status,
            'post_title' => 'Loop fixture ' . $name,
            'post_password' => $password,
            'menu_order' => (int) $order,
        ),
        true
    );

    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }

    $post_id = (int) $post_id;
    $created[] = $post_id;
    update_post_meta($post_id, '_seed_testimonial_name', $name);
    update_post_meta($post_id, '_seed_testimonial_text', 'Public text ' . $name);
    update_post_meta($post_id, '_seed_testimonial_context', $context);
    update_post_meta($post_id, '_seed_testimonial_publication_consent', '1');

    return $post_id;
}

try {
    global $wp_version;

    $theme = wp_get_theme('Divi');
    seed_loop_wp_assert(PHP_VERSION_ID >= 80400 && PHP_VERSION_ID < 80500, 'Runtime is not PHP 8.4.x.');
    seed_loop_wp_assert('7.0.2' === (string) $wp_version, 'WordPress is not 7.0.2.');
    seed_loop_wp_assert($theme->exists(), 'Divi is not installed.');
    seed_loop_wp_assert('5.9.0' === (string) $theme->get('Version'), 'Divi is not 5.9.0.');
    seed_loop_wp_assert('Divi' === get_stylesheet(), 'Divi is not active.');
    seed_loop_wp_assert(function_exists('wp_seed_content_get_testimonials'), 'Canonical testimonial collection is unavailable.');
    seed_loop_wp_assert(function_exists('wp_seed_content_divi_apply_testimonial_collection_query'), 'Loop Builder adapter is unavailable.');
    seed_loop_wp_assert(false !== has_filter('divi_loop_data_before_execution', 'wp_seed_content_divi_filter_testimonial_collection_loop_data'), 'Frontend pre-cache Loop Builder hook is missing.');
    seed_loop_wp_assert(false !== has_filter('divi_module_options_loop_post_type_results_query_args', 'wp_seed_content_divi_filter_testimonial_collection_rest_query_args'), 'Visual Builder Loop hook is missing.');
    seed_loop_wp_assert(function_exists('wp_seed_content_divi_testimonial_collection_is_available'), 'Historical Divi Collection module is unavailable.');
    seed_loop_wp_assert(false !== has_action('divi_module_library_modules_dependency_tree', 'wp_seed_content_register_divi_testimonial_collection_module'), 'Historical Divi Collection registration hook is missing.');
    seed_loop_wp_assert(false !== has_action('rest_api_init', 'wp_seed_content_register_divi_testimonial_preview_route'), 'Historical Divi preview route hook is missing.');

    $administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($administrators)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }
    wp_set_current_user((int) $administrators[0]);

    $context = 'loop-' . wp_generate_password(8, false, false);
    $home_a = seed_loop_wp_create_testimonial('Home A', $context, 1);
    $home_b = seed_loop_wp_create_testimonial('Home B', $context, 2);
    $home_c = seed_loop_wp_create_testimonial('Home C', $context, 3);
    $other = seed_loop_wp_create_testimonial('Other', $context . '-other', 4);
    $draft = seed_loop_wp_create_testimonial('Draft', $context, 5, 'draft');
    $protected = seed_loop_wp_create_testimonial('Protected', $context, 6, 'publish', 'private');
    $unconsented = seed_loop_wp_create_testimonial('Unconsented', $context, 7);
    delete_post_meta($unconsented, '_seed_testimonial_publication_consent');

    $manual = wp_seed_content_get_testimonials(
        array(
            'ids' => array($home_a, $home_b, $home_c, $other, $draft, $protected, $unconsented),
            'limit' => 0,
        )
    );
    seed_loop_wp_assert(array($home_a, $home_b, $home_c, $other) === $manual, 'Current canonical eligibility changed.');
    seed_loop_wp_assert('1' === get_post_meta($home_a, '_seed_testimonial_publication_consent', true), 'Canonical consent was not stored.');
    seed_loop_wp_assert(in_array($home_a, $manual, true), 'Consented testimonial disappeared.');
    seed_loop_wp_assert(!in_array($unconsented, $manual, true), 'Missing consent did not fail closed.');
    seed_loop_wp_assert(!in_array($draft, $manual, true), 'Draft testimonial leaked.');
    seed_loop_wp_assert(!in_array($protected, $manual, true), 'Password-protected testimonial leaked.');

    $home_query = array(
        'post_type' => array('seed_testimonial'),
        'posts_per_page' => 2,
        'paged' => 1,
        'offset' => 1,
        'orderby' => 'wp_seed_content_testimonial_display_order',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'wp_seed_content_testimonial_context',
                'value' => $context,
            ),
        ),
    );
    $frontend = wp_seed_content_divi_filter_testimonial_collection_loop_data(
        array('query_args' => $home_query, 'query_type' => 'post_types')
    );
    $visual_builder = wp_seed_content_divi_filter_testimonial_collection_rest_query_args(
        $home_query,
        array('order_by' => 'wp_seed_content_testimonial_display_order')
    );
    $controller = '\ET\Builder\Packages\Module\Options\Loop\QueryResults\QueryResultsController';
    seed_loop_wp_assert(class_exists($controller), 'Divi Visual Builder query controller is unavailable.');
    $request = new WP_REST_Request('GET', '/divi/v1/loop/query-results');
    $request->set_query_params(
        array(
            'query_type' => 'post_type',
            'post_type' => 'seed_testimonial',
            'posts_per_page' => 2,
            'post_offset' => 1,
            'order_by' => 'wp_seed_content_testimonial_display_order',
            'order' => 'ascending',
            'meta_query' => array(
                array(
                    'metaKey' => 'wp_seed_content_testimonial_context',
                    'metaValue' => $context,
                    'compare' => '=',
                    'type' => 'CHAR',
                ),
            ),
        )
    );
    $visual_builder_response = $controller::index($request);
    $visual_builder_data = $visual_builder_response->get_data();
    $visual_builder_ids = array_map(
        'intval',
        wp_list_pluck($visual_builder_data['items'], 'id')
    );
    seed_loop_wp_assert(
        200 === $visual_builder_response->get_status(),
        'Visual Builder query returned a non-200 response.'
    );
    seed_loop_wp_assert(
        array($home_b, $home_c) === $visual_builder_ids,
        'Visual Builder controller returned incorrect IDs.'
    );
    seed_loop_wp_assert(2 === (int) $visual_builder_data['per_page'], 'Visual Builder controller changed pagination.');
    seed_loop_wp_assert(array($home_a, $home_b, $home_c) === $frontend['query_args']['post__in'], 'Frontend Loop IDs are incorrect.');
    seed_loop_wp_assert($frontend['query_args']['post__in'] === $visual_builder['post__in'], 'Frontend and Visual Builder differ.');
    seed_loop_wp_assert(2 === $frontend['query_args']['posts_per_page'], 'Divi posts_per_page changed.');
    seed_loop_wp_assert(1 === $frontend['query_args']['paged'], 'Divi paged value changed.');
    seed_loop_wp_assert(1 === $frontend['query_args']['offset'], 'Divi offset changed.');

    $executed = new WP_Query($frontend['query_args']);
    seed_loop_wp_assert(array($home_b, $home_c) === array_map('intval', wp_list_pluck($executed->posts, 'ID')), 'Pagination/offset execution differs.');

    $excluded_query = $home_query;
    $excluded_query['post__not_in'] = array($home_b);
    unset($excluded_query['offset']);
    $excluded = wp_seed_content_divi_apply_testimonial_collection_query($excluded_query);
    seed_loop_wp_assert(array($home_a, $home_c) === $excluded['post__in'], 'Native exclusion changed.');

    $descending_query = $home_query;
    $descending_query['order'] = 'DESC';
    unset($descending_query['offset']);
    $descending = wp_seed_content_divi_apply_testimonial_collection_query($descending_query);
    seed_loop_wp_assert(array($home_c, $home_b, $home_a) === $descending['post__in'], 'Requested canonical order changed.');

    $other_query = $home_query;
    $other_query['meta_query'][0]['value'] = $context . '-other';
    unset($other_query['offset']);
    $second_loop = wp_seed_content_divi_filter_testimonial_collection_loop_data(
        array('query_args' => $other_query, 'query_type' => 'post_types')
    );
    seed_loop_wp_assert(array($other) === $second_loop['query_args']['post__in'], 'Second Loop IDs are incorrect.');
    seed_loop_wp_assert($frontend['query_args']['post__in'] !== $second_loop['query_args']['post__in'], 'Two loops leaked collection state.');

    $name_a = wp_seed_content_resolve_dynamic_data('testimonial.name', array('current_post_id' => $home_a));
    $name_b = wp_seed_content_resolve_dynamic_data('testimonial.name', array('current_post_id' => $home_b));
    seed_loop_wp_assert('Home A' === $name_a, 'Historical provider failed for first loop item.');
    seed_loop_wp_assert('Home B' === $name_b, 'Historical provider failed for second loop item.');
    seed_loop_wp_assert($name_a !== $name_b, 'Provider context leaked between loop items.');
    seed_loop_wp_assert(0 === WP_Seed_Content_Render_Context::depth(), 'Per-item context stack is not empty.');
} catch (Throwable $error) {
    $failures[] = 'Unhandled exception: ' . $error->getMessage();
} finally {
    wp_set_current_user($original_user);
    foreach (array_reverse($created) as $post_id) {
        wp_delete_post($post_id, true);
    }
}

if (!empty($failures)) {
    echo 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL;
    foreach ($failures as $failure) {
        echo '- ' . $failure . PHP_EOL;
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress/Divi testimonial Loop Builder assertions' . PHP_EOL;
