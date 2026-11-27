<?php

define('ABSPATH', __DIR__ . '/');
$GLOBALS['testimonial_condition_filters'] = array();
$GLOBALS['testimonial_condition_actions'] = array();
$GLOBALS['testimonial_condition_package'] = null;

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['testimonial_condition_filters'][$hook] = array($callback, $priority, $accepted_args);
}

function add_action($hook, $callback)
{
    $GLOBALS['testimonial_condition_actions'][$hook] = $callback;
}

function et_core_is_fb_enabled()
{
    return true;
}

eval('namespace ET\\Builder\\VisualBuilder\\Assets; class PackageBuildManager { public static function register_package_build($params) { $GLOBALS["testimonial_condition_package"] = $params; } }');

define('WP_SEED_CONTENT_KIT_URL', 'https://example.test/wp-content/plugins/wp-seed-content-kit/');
define('WP_SEED_CONTENT_KIT_VERSION', 'test');
require __DIR__ . '/../plugin/includes/integrations/divi/testimonial-has-more-condition.php';

$assertions = 0;
function testimonial_condition_assert($condition, $message)
{
    global $assertions;
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$name = wp_seed_content_divi_testimonial_has_more_condition_name();
$key = wp_seed_content_divi_testimonial_has_more_condition_value_key();
testimonial_condition_assert('wpsckTestimonialHasMore' === $name, 'Stable condition ID changed.');

$clone_values = array('1', '', 'true');
$clone_results = array();
foreach ($clone_values as $index => $value) {
    $clone_results[] = wp_seed_content_divi_testimonial_evaluate_has_more_condition(null, $name, array($key => $value), 'clone-' . $index);
}
testimonial_condition_assert(array(true, false, true) === $clone_results, 'Clone values must resolve TRUE/FALSE/TRUE.');
testimonial_condition_assert(false === wp_seed_content_divi_testimonial_evaluate_has_more_condition(null, $name, array($key => '0'), 'false'), 'Zero must be false.');
testimonial_condition_assert(true === wp_seed_content_divi_testimonial_evaluate_has_more_condition(null, $name, array($key => '$variable({})$'), 'builder'), 'Unresolved Builder token must remain editable.');
testimonial_condition_assert('untouched' === wp_seed_content_divi_testimonial_evaluate_has_more_condition('untouched', 'other', array(), 'other'), 'Other conditions must remain untouched.');
testimonial_condition_assert(isset($GLOBALS['testimonial_condition_filters']['divi_module_options_conditions_is_custom_condition_true']), 'Condition filter missing.');

call_user_func($GLOBALS['testimonial_condition_actions']['divi_visual_builder_assets_before_enqueue_scripts']);
$package = $GLOBALS['testimonial_condition_package'];
testimonial_condition_assert('wp-seed-content-kit-divi-testimonial-has-more-condition' === $package['name'], 'Package name mismatch.');
testimonial_condition_assert(true === $package['script']['enqueue_app_window'] && false === $package['script']['enqueue_top_window'], 'Package window target mismatch.');
testimonial_condition_assert(WP_SEED_CONTENT_KIT_VERSION !== $package['version'], 'Asset version must be content-derived.');

$js = file_get_contents(__DIR__ . '/../plugin/includes/integrations/divi/testimonial-has-more-condition/visual-builder.js');
testimonial_condition_assert(false !== strpos($js, "name: 'loop_wpsck_testimonial_has_more'"), 'Canonical loop provider missing.');
testimonial_condition_assert(false !== strpos($js, 'WPSCK — Témoignages — Témoignage avec suite'), 'Condition label mismatch.');

echo 'PASS ' . $assertions . ' Divi Testimonial has_more condition assertions' . PHP_EOL;
