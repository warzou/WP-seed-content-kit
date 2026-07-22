<?php

$wp_load = getenv('WP_SEED_WORDPRESS_LOAD');
if (!defined('ABSPATH')) {
    if (!is_string($wp_load) || '' === $wp_load || !is_file($wp_load)) {
        fwrite(STDERR, "Set WP_SEED_WORDPRESS_LOAD to an isolated WordPress wp-load.php.\n");
        exit(2);
    }
    require $wp_load;
}

$root = dirname(__DIR__);
$plugin_file = $root . '/plugin/wp-seed-content-kit.php';
$fixture_file = __DIR__ . '/fixtures/directory-l4.json';
$assertions = 0;
$failures = array();
$created_posts = array();
$previous_modules = get_option('wp_seed_content_kit_modules', null);
$previous_user_id = get_current_user_id();
$private_sentinel = 'PRIVATE-SENTINEL-06@example.test';
$performance = array();

function seed_l4_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_l4_wp_same($expected, $actual, $label)
{
    seed_l4_wp_assert($expected === $actual, $label . ' (got ' . var_export($actual, true) . ')');
}

function seed_l4_wp_not_contains($needle, $haystack, $label)
{
    seed_l4_wp_assert(false === strpos((string) $haystack, (string) $needle), $label);
}

function seed_l4_wp_create_post($args)
{
    global $created_posts;
    $post_id = wp_insert_post($args, true);
    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }
    $created_posts[] = (int) $post_id;
    return (int) $post_id;
}

function seed_l4_wp_create_entry($entry)
{
    $meta = array(
        '_seed_directory_status' => $entry['status'],
        '_seed_directory_city' => $entry['city'],
        '_seed_directory_postal_code' => $entry['postal_code'],
        '_seed_directory_department' => $entry['department'],
        '_seed_directory_country' => $entry['country'],
        '_seed_directory_featured' => !empty($entry['featured']) ? '1' : '',
        '_seed_directory_publication_authorized' => '1',
    );
    foreach (array('phone', 'email', 'website', 'facebook', 'instagram') as $contact) {
        if (isset($entry[$contact])) {
            $meta['_seed_directory_' . $contact] = $entry[$contact];
            $meta['_seed_directory_' . $contact . '_visible'] = !empty($entry[$contact . '_visible']) ? '1' : '';
        }
    }
    return seed_l4_wp_create_post(array(
        'post_type' => 'seed_directory',
        'post_status' => $entry['post_status'],
        'post_title' => $entry['name'],
        'post_excerpt' => 'Presentation strictement fictive pour la recette L4.',
        'menu_order' => (int) $entry['order'],
        'meta_input' => $meta,
    ));
}

function seed_l4_wp_create_template($slug, $module, $status, $content, $source = 'native', $layout_id = 0)
{
    $id = seed_l4_wp_create_post(array(
        'post_type' => 'seed_template',
        'post_status' => $status,
        'post_title' => $slug,
        'post_name' => $slug,
        'post_content' => $content,
    ));
    update_post_meta($id, '_wp_seed_content_template_module', $module);
    update_post_meta($id, '_wp_seed_content_template_source', $source);
    if ($layout_id > 0) {
        update_post_meta($id, '_wp_seed_content_divi_layout_id', $layout_id);
    }
    return $id;
}

function seed_l4_wp_measure($callback)
{
    global $wpdb;
    $queries = (int) $wpdb->num_queries;
    $start = microtime(true);
    $value = call_user_func($callback);
    return array('seconds' => microtime(true) - $start, 'queries' => (int) $wpdb->num_queries - $queries, 'value' => $value);
}

try {
    require_once $plugin_file;
    $admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($admins)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }
    wp_set_current_user((int) $admins[0]);
    wp_seed_content_kit_activate();
    wp_set_current_user(0);
    wp_set_current_user((int) $admins[0]);
    do_action('init');

    seed_l4_wp_same('0.6.0-rc.2', WP_SEED_CONTENT_KIT_VERSION, 'Plugin version');
    seed_l4_wp_assert(post_type_exists('seed_directory'), 'Directory CPT registered');
    seed_l4_wp_same(false, get_post_type_object('seed_directory')->show_in_rest, 'Directory outside REST');
    seed_l4_wp_assert(shortcode_exists('seed_directory'), 'Canonical shortcode registered');
    seed_l4_wp_assert(shortcode_exists('wp_seed_directory'), 'Compatibility alias registered');
    seed_l4_wp_same($GLOBALS['shortcode_tags']['seed_directory'], $GLOBALS['shortcode_tags']['wp_seed_directory'], 'Both shortcodes share callback');
    seed_l4_wp_same(array(), wp_seed_content_directory_get_entries(), 'No entries initially');
    seed_l4_wp_assert(false !== strpos(do_shortcode('[seed_directory]'), 'Aucune fiche'), 'Empty state is controlled');
    seed_l4_wp_assert(wp_style_is('wp-seed-directory', 'enqueued'), 'Empty state enqueues structural CSS');
    seed_l4_wp_same(false, wp_style_is('wp-seed-directory-card', 'enqueued'), 'Empty state does not enqueue native card CSS');

    $fixture = json_decode(file_get_contents($fixture_file), true);
    if (!is_array($fixture) || empty($fixture['entries'])) {
        throw new RuntimeException('Invalid L4 fixture.');
    }
    seed_l4_wp_same(16, count($fixture['entries']), 'Fixture has sixteen entries');
    $entry_ids = array();
    foreach ($fixture['entries'] as $entry) {
        $entry_ids[] = seed_l4_wp_create_entry($entry);
    }

    $public_ids = wp_seed_content_directory_get_entries();
    seed_l4_wp_same(14, count($public_ids), 'Fourteen eligible entries');
    seed_l4_wp_same(9, count(wp_seed_content_directory_get_entries(array('status' => 'practicing'))), 'Nine published practicing entries');
    seed_l4_wp_same(5, count(wp_seed_content_directory_get_entries(array('status' => 'seeking_models'))), 'Five published seeking entries');
    seed_l4_wp_same(3, count(wp_seed_content_directory_get_entries(array('featured' => 'only'))), 'Three featured eligible entries');
    seed_l4_wp_same(1, count(wp_seed_content_directory_get_entries(array('department' => '2A'))), 'Department filter');
    seed_l4_wp_same(14, count(wp_seed_content_directory_get_entries(array('country' => 'FR'))), 'Country filter');
    seed_l4_wp_same(2, count(wp_seed_content_directory_get_entries(array('limit' => 2))), 'Limit filter');
    seed_l4_wp_same(array($public_ids[1], $public_ids[0]), wp_seed_content_directory_get_entries(array('ids' => array($public_ids[0], $public_ids[1]), 'order' => 'desc')), 'Explicit IDs preserve eligibility and ordering');
    seed_l4_wp_assert(
        wp_seed_content_directory_compare_entries(
            (object) array('ID' => 1, 'post_title' => 'Élise', 'post_date' => '', 'menu_order' => 1),
            (object) array('ID' => 2, 'post_title' => 'Maël', 'post_date' => '', 'menu_order' => 1),
            'display_order',
            'asc'
        ) < 0,
        'Display order tie ignores accents and case'
    );
    seed_l4_wp_same(array(), wp_seed_content_directory_get_entries(array('ids' => array($entry_ids[9], $entry_ids[15]))), 'Draft IDs cannot bypass eligibility');

    $first = wp_seed_content_directory_get_public_data($entry_ids[0]);
    seed_l4_wp_same(array('id', 'name', 'photo', 'bio', 'status', 'status_label', 'location', 'featured', 'display_order', 'contacts'), array_keys($first), 'Fixed public schema');
    seed_l4_wp_same(array('city', 'postal_code', 'department', 'country'), array_keys($first['location']), 'Fixed location schema');
    seed_l4_wp_same(array('phone'), array_keys($first['contacts']), 'Only visible contact in public API');
    seed_l4_wp_same(false, wp_seed_content_directory_get_public_data($entry_ids[9]), 'Draft has no public data');
    $private_data = wp_seed_content_directory_get_public_data($entry_ids[5]);
    seed_l4_wp_not_contains($private_sentinel, serialize($private_data), 'Masked sentinel absent from public data');
    seed_l4_wp_not_contains($private_sentinel, serialize(wp_seed_content_directory_get_template_context($private_data)), 'Masked sentinel absent from template context');

    $native = do_shortcode('[seed_directory]');
    seed_l4_wp_same(14, substr_count($native, '<article class="wp-seed-directory-card">'), 'Native shortcode renders fourteen cards');
    seed_l4_wp_assert(strpos($native, 'En exercice') < strpos($native, 'En recherche'), 'Groups keep fixed order');
    seed_l4_wp_same(2, substr_count($native, '<section class="wp-seed-directory__group">'), 'Two non-empty groups');
    seed_l4_wp_same(2, substr_count($native, '<ul class="wp-seed-directory__grid">'), 'No empty list');
    seed_l4_wp_not_contains($private_sentinel, $native, 'Masked sentinel absent from native HTML');
    seed_l4_wp_not_contains('data-', $native, 'No business data attributes');
    seed_l4_wp_assert(wp_style_is('wp-seed-directory', 'enqueued'), 'Structural CSS enqueued');
    seed_l4_wp_assert(wp_style_is('wp-seed-directory-card', 'enqueued'), 'Native card CSS enqueued');
    seed_l4_wp_same(do_shortcode('[seed_directory status="practicing"]'), do_shortcode('[wp_seed_directory status="practicing"]'), 'Alias output equals canonical output');
    seed_l4_wp_same(9, substr_count(do_shortcode('[seed_directory status="practicing"]'), '<article class="wp-seed-directory-card">'), 'Practicing group only');
    seed_l4_wp_same(5, substr_count(do_shortcode('[seed_directory status="seeking_models"]'), '<article class="wp-seed-directory-card">'), 'Seeking group only');
    seed_l4_wp_same('', do_shortcode('[seed_directory status="invalid"]'), 'Invalid status returns empty');
    seed_l4_wp_same('', do_shortcode('[seed_directory limit="-1"]'), 'Invalid limit returns empty');
    seed_l4_wp_same('', do_shortcode('[seed_directory ids="1,nope"]'), 'Invalid IDs return empty');

    $template_id = seed_l4_wp_create_template('annuaire-carte-l4', 'directory', 'publish', '<p class="seed-l4-template">{{directory.name}} | {{directory.city}} | {{directory.email}}</p>');
    $template_html = do_shortcode('[seed_directory template="annuaire-carte-l4"]');
    seed_l4_wp_same(14, substr_count($template_html, '<article class="wp-seed-directory-template-card">'), 'Published template renders fourteen cards');
    seed_l4_wp_not_contains($private_sentinel, $template_html, 'Masked sentinel absent from template HTML');
    seed_l4_wp_assert(false !== strpos($template_html, 'visible02@example.test'), 'Visible contact reaches template');
    seed_l4_wp_same(0, substr_count($template_html, '<article class="wp-seed-directory-card">'), 'No native fallback on valid template');

    seed_l4_wp_create_template('annuaire-draft-l4', 'directory', 'draft', '{{directory.name}}');
    seed_l4_wp_create_template('annuaire-wrong-l4', 'quotes', 'publish', '{{directory.name}}');
    seed_l4_wp_same(14, substr_count(do_shortcode('[seed_directory template="annuaire-missing-l4"]'), '<article class="wp-seed-directory-card">'), 'Missing template falls back natively');
    seed_l4_wp_same(14, substr_count(do_shortcode('[seed_directory template="annuaire-draft-l4"]'), '<article class="wp-seed-directory-card">'), 'Draft template falls back natively');
    seed_l4_wp_same(14, substr_count(do_shortcode('[seed_directory template="annuaire-wrong-l4"]'), '<article class="wp-seed-directory-card">'), 'Wrong module falls back natively');

    $error_id = $public_ids[0];
    $filter = function ($context, $data) use ($error_id) {
        if ((int) $data['id'] === $error_id) {
            $context['directory.name'] = array('invalid');
        }
        return $context;
    };
    add_filter('wp_seed_content_directory_template_context', $filter, 10, 2);
    $mixed = do_shortcode('[seed_directory template="annuaire-carte-l4"]');
    remove_filter('wp_seed_content_directory_template_context', $filter, 10);
    seed_l4_wp_same(1, substr_count($mixed, '<article class="wp-seed-directory-card">'), 'One invalid entry gets one native fallback');
    seed_l4_wp_same(13, substr_count($mixed, '<article class="wp-seed-directory-template-card">'), 'Other entries retain template rendering');
    seed_l4_wp_not_contains($private_sentinel, $mixed, 'Per-entry fallback remains private');

    $layout_id = seed_l4_wp_create_post(array('post_type' => 'et_pb_layout', 'post_status' => 'publish', 'post_title' => 'SEED L4 DIVI LAYOUT', 'post_content' => '<div class="seed-l4-divi">{{directory.name}}</div>'));
    seed_l4_wp_create_template('annuaire-divi-l4', 'directory', 'publish', 'native fallback', 'divi_layout', $layout_id);
    $divi_html = do_shortcode('[seed_directory template="annuaire-divi-l4" limit="1"]');
    seed_l4_wp_assert(false !== strpos($divi_html, 'seed-l4-divi'), 'Divi Library template renders');
    seed_l4_wp_same(1, substr_count($divi_html, '<article class="wp-seed-directory-template-card">'), 'Divi output remains card-local');
    seed_l4_wp_assert(false !== strpos(apply_filters('the_content', '<!-- wp:shortcode -->[seed_directory limit="1"]<!-- /wp:shortcode -->'), 'wp-seed-directory'), 'Gutenberg shortcode pipeline');
    seed_l4_wp_assert(false !== strpos(do_shortcode('<div class="et_pb_text">[seed_directory limit="1"]</div>'), 'wp-seed-directory'), 'Divi Text module pipeline');
    seed_l4_wp_assert(false !== strpos(do_shortcode('<div class="et_pb_code">[seed_directory limit="1"]</div>'), 'wp-seed-directory'), 'Divi Code module pipeline');
    seed_l4_wp_assert(false !== strpos(do_shortcode('[seed_directory limit="1"]'), 'wp-seed-directory'), 'Classic theme shortcode pipeline');
    seed_l4_wp_same(false, class_exists('ET_Builder_Module_Seed_Directory'), 'No dedicated Divi module');

    $stored_name = get_the_title($entry_ids[0]);
    $modules = wp_seed_content_kit_get_module_options();
    $modules['directory'] = false;
    update_option('wp_seed_content_kit_modules', $modules);
    wp_dequeue_style('wp-seed-directory');
    wp_dequeue_style('wp-seed-directory-card');
    seed_l4_wp_same('', do_shortcode('[seed_directory]'), 'Disabled module shortcode returns empty');
    seed_l4_wp_same(array(), wp_seed_content_directory_get_entries(), 'Disabled module collection empty');
    seed_l4_wp_same(false, wp_style_is('wp-seed-directory', 'enqueued'), 'Disabled module enqueues no structure CSS');
    seed_l4_wp_same($stored_name, get_the_title($entry_ids[0]), 'Disabled module preserves data');
    $modules['directory'] = true;
    update_option('wp_seed_content_kit_modules', $modules);
    seed_l4_wp_same(14, count(wp_seed_content_directory_get_entries()), 'Reactivation restores public collection');

    seed_l4_wp_assert(post_type_exists('seed_testimonial'), 'Testimonials post type unchanged');
    seed_l4_wp_assert(post_type_exists('seed_quote'), 'Quotes post type unchanged');
    seed_l4_wp_assert(shortcode_exists('seed_testimonials'), 'Testimonials shortcode unchanged');
    seed_l4_wp_assert(shortcode_exists('seed_quotes'), 'Quotes shortcode unchanged');
    seed_l4_wp_same('1.0', wp_seed_content_kit_get_contract_version(), 'Template Extension contract unchanged');
    seed_l4_wp_same(15, count(wp_seed_content_kit_get_registered_template_placeholders('directory')), 'Exactly fifteen Directory placeholders');

    wp_cache_flush();
    $performance['data_api'] = seed_l4_wp_measure(function () use ($public_ids) { return wp_seed_content_directory_get_public_data($public_ids[0]); });
    wp_cache_flush();
    $performance['collection'] = seed_l4_wp_measure(function () { return wp_seed_content_directory_get_entries(); });
    wp_cache_flush();
    $performance['native_shortcode'] = seed_l4_wp_measure(function () { return do_shortcode('[seed_directory]'); });
    wp_cache_flush();
    $performance['template_shortcode'] = seed_l4_wp_measure(function () { return do_shortcode('[seed_directory template="annuaire-carte-l4"]'); });
    seed_l4_wp_same(14, count($performance['collection']['value']), 'Measured collection has fourteen entries');
    seed_l4_wp_same(14, substr_count($performance['native_shortcode']['value'], '<article class="wp-seed-directory-card">'), 'Measured native shortcode has fourteen cards');
    seed_l4_wp_same(14, substr_count($performance['template_shortcode']['value'], '<article class="wp-seed-directory-template-card">'), 'Measured template shortcode has fourteen cards');
    $before = (int) $GLOBALS['wpdb']->num_queries;
    wp_seed_content_kit_get_public_template_by_slug('annuaire-performance-missing-l4');
    $first_resolution_queries = (int) $GLOBALS['wpdb']->num_queries - $before;
    $before = (int) $GLOBALS['wpdb']->num_queries;
    wp_seed_content_kit_get_public_template_by_slug('annuaire-performance-missing-l4');
    $second_resolution_queries = (int) $GLOBALS['wpdb']->num_queries - $before;
    seed_l4_wp_same(0, $second_resolution_queries, 'Repeated template resolution uses request cache');
    $performance['template_resolution'] = array('first_queries' => $first_resolution_queries, 'second_queries' => $second_resolution_queries);

    $sources = file_get_contents($root . '/plugin/includes/modules/directory/shortcode.php') . file_get_contents($root . '/plugin/includes/modules/directory/collections.php');
    seed_l4_wp_assert(false === strpos($sources, 'register_rest_route'), 'No Directory REST route');
    seed_l4_wp_assert(false === strpos($sources, 'wp_ajax_'), 'No Directory AJAX action');
    seed_l4_wp_assert(false === strpos($sources, '$_GET'), 'No public GET controls');
    seed_l4_wp_assert(false === strpos($sources, 'migration'), 'No runtime migration in public layer');
} catch (Throwable $error) {
    $failures[] = 'Harness exception: ' . $error->getMessage();
} finally {
    foreach (array_reverse($created_posts) as $post_id) {
        wp_delete_post($post_id, true);
    }
    if (null === $previous_modules) {
        delete_option('wp_seed_content_kit_modules');
    } else {
        update_option('wp_seed_content_kit_modules', $previous_modules);
    }
    wp_set_current_user($previous_user_id);
}

foreach ($performance as $name => $measurement) {
    if (isset($measurement['seconds'])) {
        echo 'PERF ' . $name . ' seconds=' . number_format($measurement['seconds'], 6, '.', '') . ' queries=' . $measurement['queries'] . PHP_EOL;
    } else {
        echo 'PERF ' . $name . ' first_queries=' . $measurement['first_queries'] . ' second_queries=' . $measurement['second_queries'] . PHP_EOL;
    }
}
if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}
echo 'PASS ' . $assertions . ' WordPress Annuaire L4 assertions' . PHP_EOL;