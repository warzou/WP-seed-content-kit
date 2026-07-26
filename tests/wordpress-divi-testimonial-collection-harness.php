<?php

$root = dirname(__DIR__);
$integration = file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-collection.php');
$module = file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-collection/Module.php');
$bootstrap = file_get_contents($root . '/plugin/wp-seed-content-kit.php');
$renderer = file_get_contents($root . '/plugin/includes/modules/testimonials/collection-renderer.php');
$builder = file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-collection/visual-builder.js');
$metadata = json_decode(file_get_contents($root . '/plugin/includes/integrations/divi/testimonial-collection/module.json'), true);

$assertions = 0;
$failures = array();
$assert = function ($condition, $label) use (&$assertions, &$failures) {
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
};

preg_match('/^\s*\*\s*Version:\s*(\S+)/m', $bootstrap, $plugin_header_matches);
preg_match("/define\('WP_SEED_CONTENT_KIT_VERSION',\s*'([^']+)'\);/", $bootstrap, $plugin_constant_matches);
$assert(
    isset($plugin_header_matches[1], $plugin_constant_matches[1])
    && $plugin_header_matches[1] === $plugin_constant_matches[1],
    'Plugin header and canonical version constant match'
);
$assert(false !== strpos($bootstrap, "collection-renderer.php"), 'Shared testimonial renderer loaded before shortcode');
$assert(false !== strpos($bootstrap, "testimonial-collection.php"), 'Divi integration loaded only with testimonial module');
$assert(false !== strpos($integration, "et_builder_d5_enabled"), 'Divi 5 availability guard exists');
$assert(false !== strpos($integration, "register_rest_route"), 'WordPress Builder preview route exists');
$assert(false !== strpos($integration, "current_user_can('edit_pages')"), 'Preview requires page editing capability');
$assert(false !== strpos($integration, "'post_status' => 'publish'"), 'Template selector requests published Templates only');
$assert(false !== strpos($integration, "'testimonials' !== wp_seed_content_get_template_module"), 'Template selector rejects other modules');
$assert(false !== strpos($integration, "current_user_can('manage_wp_seed_templates')"), 'Template selection follows architecture capability');
$assert(false !== strpos($integration, "'template_slug'"), 'Preview route avoids reserved WordPress Template parameter');
$assert(false !== strpos($builder, 'hooks.didAction'), 'Visual Builder handles late package loading idempotently');
$assert(false !== strpos($integration, 'wp_seed_content_render_testimonial_collection($args, false)'), 'Builder preview uses canonical renderer without duplicate assets');
$assert(false !== strpos($module, "wp_seed_content_render_testimonial_collection"), 'Frontend module uses canonical renderer');
$assert(false !== strpos($renderer, "wp_seed_content_get_testimonials"), 'Renderer uses canonical Collection API');
$assert(false === strpos($integration . $module . $builder, 'wp_ajax_'), 'No AJAX endpoint introduced');
$assert(false === strpos($integration . $module . $builder, 'do_shortcode'), 'Divi module never executes a generated shortcode');
$assert(is_array($metadata) && isset($metadata['attributes']['collection']), 'Module metadata exposes Collection settings');
$assert(false !== strpos($builder, "role: 'alert'"), 'Builder preview exposes accessible error state');
$assert(false !== strpos($builder, "'aria-live': 'polite'"), 'Builder loading state is announced politely');
$assert(false !== strpos($integration, "enqueue_app_window' => true"), 'Builder script and CSS are scoped to app window');
$assert(false !== strpos($integration, 'static $registered = false'), 'Builder package registration is idempotent');
$assert(false !== strpos($module, "childrenSanitizer' => 'et_core_esc_previously'"), 'Divi receives previously escaped canonical HTML');

if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress Divi testimonial collection assertions' . PHP_EOL;