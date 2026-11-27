<?php

$GLOBALS['quote_loop_filters'] = array();
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['quote_loop_filters'][] = array($hook, $callback, $priority, $accepted_args);
}
function sanitize_key($value)
{
    return strtolower(preg_replace('/[^a-z0-9_\-]/', '', (string) $value));
}

require __DIR__ . '/collections-harness.php';
require_once __DIR__ . '/../plugin/includes/integrations/divi/collection-query.php';
require_once __DIR__ . '/../plugin/includes/integrations/divi/quote-collection-query.php';

$failures = array();
$assertions = 0;
function seed_quote_loop_same($expected, $actual, $label)
{
    global $failures, $assertions;
    $assertions++;
    if ($expected !== $actual) { $failures[] = $label; }
}
function seed_quote_loop_true($actual, $label)
{
    seed_quote_loop_same(true, (bool) $actual, $label);
}

wp_seed_test_set_meta(16, 'seed_quote_author', 'Delta');
wp_seed_test_set_meta(29, 'seed_quote_author', 'Alpha');
wp_seed_test_set_meta(30, 'seed_quote_author', 'Charlie');
wp_seed_test_set_meta(31, 'seed_quote_author', 'Bravo');
wp_seed_test_set_meta(29, 'seed_quote_featured', true);
wp_seed_test_set_meta(31, '_seed_quote_featured', '1');
$GLOBALS['wp_seed_test_posts'][16]->menu_order = 4;
$GLOBALS['wp_seed_test_posts'][29]->menu_order = 2;
$GLOBALS['wp_seed_test_posts'][30]->menu_order = 1;
$GLOBALS['wp_seed_test_posts'][31]->menu_order = 3;

$all = wp_seed_content_get_quotes(array('orderby' => 'author', 'order' => 'asc'));
seed_quote_loop_same(array(29, 31, 30, 16), $all, 'author order');
seed_quote_loop_same(array(16, 30, 31, 29), wp_seed_content_get_quotes(array('orderby' => 'author', 'order' => 'desc')), 'author descending');
seed_quote_loop_same(array(16, 29, 30, 31), wp_seed_content_get_quotes(array('orderby' => 'date', 'order' => 'desc')), 'date order');
seed_quote_loop_same(array(30, 29, 31, 16), wp_seed_content_get_quotes(array('orderby' => 'menu_order', 'order' => 'asc')), 'menu order');
seed_quote_loop_same(array(29, 31), wp_seed_content_get_quotes(array('featured' => 'only', 'limit' => 2)), 'featured limit');
$random_a = wp_seed_content_get_quotes(array('orderby' => 'random', 'random_seed' => 'quote-seed'));
$random_b = wp_seed_content_get_quotes(array('orderby' => 'random', 'random_seed' => 'quote-seed'));
seed_quote_loop_same($random_a, $random_b, 'seeded random stable');
seed_quote_loop_same(4, count(array_unique($random_a)), 'random unique');

$query = array(
    'post_type' => array('seed_quote'),
    'posts_per_page' => 3,
    'paged' => 1,
    'orderby' => 'wp_seed_content_quote_author_order',
    'order' => 'ASC',
    'meta_query' => array(
        array('key' => 'wp_seed_content_quote_featured', 'value' => 'only'),
    ),
);
$adapted = wp_seed_content_divi_apply_quote_collection_query($query);
seed_quote_loop_same(array(29, 31), $adapted['post__in'], 'frontend canonical IDs');
seed_quote_loop_same('post__in', $adapted['orderby'], 'canonical order applied');
seed_quote_loop_same(3, $adapted['posts_per_page'], 'Divi limit preserved');
seed_quote_loop_true(!isset($adapted['meta_query']), 'virtual featured removed');

$rest = wp_seed_content_divi_filter_quote_collection_rest_query_args(
    $query,
    array('order_by' => 'wp_seed_content_quote_author_order')
);
seed_quote_loop_same($adapted['post__in'], $rest['post__in'], 'Visual Builder matches frontend');

$bounded = $query;
$bounded['post__in'] = array(29, 30, 31);
$bounded['post__not_in'] = array(31);
seed_quote_loop_same(array(29), wp_seed_content_divi_apply_quote_collection_query($bounded)['post__in'], 'native include/exclude bounds');

$ordinary = array('post_type' => array('post'), 'orderby' => 'date');
seed_quote_loop_same($ordinary, wp_seed_content_divi_apply_quote_collection_query($ordinary), 'ordinary query unchanged');

$hooks = array_map(function ($entry) { return $entry[0]; }, $GLOBALS['quote_loop_filters']);
seed_quote_loop_true(in_array('et_builder_loop_order_by_options_seed_quote', $hooks, true), 'order hook registered');
seed_quote_loop_true(in_array('divi_loop_data_before_execution', $hooks, true), 'frontend hook registered');
seed_quote_loop_true(in_array('divi_module_options_loop_post_type_results_query_args', $hooks, true), 'REST hook registered');

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $assertions . ' Divi quote loop assertions' . PHP_EOL;
