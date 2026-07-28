<?php

$root = dirname(__DIR__);
$bootstrap = file_get_contents($root . '/plugin/includes/modules/directory/bootstrap.php');
$renderer = file_get_contents($root . '/plugin/includes/modules/directory/collection-renderer.php');
$shortcode = file_get_contents($root . '/plugin/includes/modules/directory/shortcode.php');
$integration = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection.php');
$module = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/Module.php');
$builder = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/visual-builder.js');
$builder_css = file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/visual-builder.css');
$metadata = json_decode(file_get_contents($root . '/plugin/includes/integrations/divi/directory-collection/module.json'), true);

$assertions = 0;
$failures = array();
$assert = function ($condition, $label) use (&$assertions, &$failures) {
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
};

$assert(false !== strpos($bootstrap, "collection-renderer.php"), 'Shared renderer loaded before shortcode');
$assert(false !== strpos($bootstrap, "directory-collection.php"), 'Divi integration loaded with Directory module');
$assert(false !== strpos($shortcode, 'wp_seed_content_render_directory_collection'), 'Historical shortcode delegates to shared renderer');
$assert(false !== strpos($renderer, 'wp_seed_content_directory_get_entries'), 'Shared renderer uses canonical Collection API');
$assert(false !== strpos($renderer, 'wp_seed_content_directory_get_public_data'), 'Shared renderer uses public Data API');
$assert(false !== strpos($renderer, 'wp_seed_content_directory_render_entry'), 'Shared renderer preserves per-card fallback');
$assert(false !== strpos($integration, "et_builder_d5_enabled"), 'Divi 5 availability guard exists');
$assert(false !== strpos($integration, "register_rest_route"), 'Builder preview route exists');
$assert(false !== strpos($integration, "current_user_can('edit_pages')"), 'Preview requires page editing capability');
$assert(false !== strpos($integration, "wp_verify_nonce"), 'Preview explicitly requires REST nonce');
$assert(false !== strpos($integration, "'post_status' => 'publish'"), 'Template selector requests published Templates only');
$assert(false !== strpos($integration, "'directory' !== wp_seed_content_get_template_module"), 'Template selector rejects other modules');
$assert(false !== strpos($integration, "current_user_can('manage_wp_seed_templates')"), 'Template selection follows Template capability');
$assert(false !== strpos($integration, "'template_slug'"), 'Route avoids reserved WordPress Template parameter');
$assert(false !== strpos($integration, 'no-store, no-cache'), 'Preview response is not publicly cacheable');
$assert(false !== strpos($integration, 'wp_seed_content_render_normalized_directory_collection'), 'Preview uses normalized shared renderer');
$assert(false !== strpos($module, 'wp_seed_content_render_directory_collection'), 'Frontend module uses shared renderer');
$assert(false !== strpos($builder, 'useFetch'), 'Visual Builder uses official Divi REST preview hook');
$assert(false !== strpos($builder, 'AbortController'), 'Visual Builder cancels obsolete requests');
$assert(false !== strpos($builder, 'hooks.didAction'), 'Visual Builder handles late package loading');
$assert(false === strpos($integration . $module . $builder, 'do_shortcode'), 'No shortcode generated or executed');
$assert(false === strpos($integration . $module . $builder, 'wp_ajax_'), 'No unaudited AJAX endpoint');
$assert(false === strpos($builder, 'MutationObserver'), 'No MutationObserver');
$assert(false === strpos($builder, 'setTimeout'), 'No arbitrary delay');
$assert(false === strpos($builder, 'webpack'), 'No internal Webpack API');
$assert(false === strpos($builder, '[seed_directory'), 'No browser-side shortcode');
$assert(false !== strpos($builder, "role: 'alert'"), 'Error state announced');
$assert(false !== strpos($builder, "'aria-live': 'polite'"), 'Loading state announced');
$assert(false !== strpos($integration, "enqueue_app_window' => true"), 'Builder assets scoped to app window');
$assert(false !== strpos($integration, 'static $registered = false'), 'Package registration is idempotent');
$assert(false !== strpos($module, "childrenSanitizer' => 'et_core_esc_previously'"), 'Canonical escaped HTML preserved');
$assert(false !== strpos($builder_css, 'directory.css'), 'Builder loads canonical structure CSS');
$assert(false !== strpos($builder_css, 'directory-card.css'), 'Builder loads canonical native card CSS');
$assert(is_array($metadata) && isset($metadata['attributes']['collection']), 'Metadata exposes collection controls');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['profile_types']), 'Profile type filter exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['profile_type_operator']), 'OR/AND operator exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['seeking_models']), 'Seeking models filter exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['ids']), 'Explicit IDs exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['exclude_ids']), 'Exclusions exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['limit']), 'Limit exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['offset']), 'Offset exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['orderby']), 'Ordering exposed');
$assert(isset($metadata['attributes']['collection']['settings']['innerContent']['items']['template']), 'Template exposed');

if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress Divi Directory collection assertions' . PHP_EOL;
