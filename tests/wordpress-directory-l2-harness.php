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
$assertions = 0;
$failures = array();
$created_post_id = 0;
$created_user_id = 0;
$previous_modules = get_option('wp_seed_content_kit_modules', null);
$previous_user_id = get_current_user_id();

function seed_l2_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_l2_wp_same($expected, $actual, $label)
{
    seed_l2_wp_assert($expected === $actual, $label);
}

try {
    require_once $plugin_file;
    require_once ABSPATH . 'wp-admin/includes/user.php';

    $administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($administrators)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }

    wp_set_current_user((int) $administrators[0]);
    wp_seed_content_kit_activate();
    wp_set_current_user(0);
    wp_set_current_user((int) $administrators[0]);
    do_action('init');

    seed_l2_wp_same('0.6.0', WP_SEED_CONTENT_KIT_VERSION, 'Plugin version');
    seed_l2_wp_same(true, wp_seed_content_kit_is_module_active('directory'), 'Directory active after clean activation');
    seed_l2_wp_assert(post_type_exists('seed_directory'), 'Directory CPT registered');

    $object = get_post_type_object('seed_directory');
    seed_l2_wp_same(false, $object->public, 'CPT private');
    seed_l2_wp_same(false, $object->publicly_queryable, 'CPT not publicly queryable');
    seed_l2_wp_same(true, $object->exclude_from_search, 'CPT excluded from search');
    seed_l2_wp_same(false, $object->show_in_rest, 'CPT absent from REST');
    seed_l2_wp_same(false, $object->has_archive, 'CPT has no archive');
    seed_l2_wp_same(false, $object->rewrite, 'CPT has no rewrite');
    seed_l2_wp_same(false, $object->query_var, 'CPT has no query var');
    seed_l2_wp_same('wp-seed-content-kit', $object->show_in_menu, 'CPT menu parent');
    seed_l2_wp_assert(current_user_can('edit_seed_directory_entries'), 'Administrator can edit Directory');

    require_once $root . '/plugin/includes/admin/modules-page.php';
    global $menu, $submenu;
    $menu = array();
    $submenu = array();
    do_action('admin_menu');
    $directory_slug = 'edit.php?post_type=seed_directory';
    $admin_has_menu = false;
    foreach (isset($submenu['wp-seed-content-kit']) ? $submenu['wp-seed-content-kit'] : array() as $item) {
        if (isset($item[2]) && $directory_slug === $item[2]) {
            $admin_has_menu = true;
        }
    }
    seed_l2_wp_assert($admin_has_menu, 'Administrator sees Content Kit > Annuaire');

    $created_user_id = wp_create_user('seed_l2_reader_' . wp_generate_password(8, false), wp_generate_password(24), '');
    if (is_wp_error($created_user_id)) {
        throw new RuntimeException($created_user_id->get_error_message());
    }
    $reader = new WP_User($created_user_id);
    $reader->set_role('subscriber');
    wp_set_current_user($created_user_id);
    seed_l2_wp_same(false, current_user_can('edit_seed_directory_entries'), 'Subscriber has no Directory capability');
    seed_l2_wp_same(false, current_user_can('manage_options'), 'Subscriber cannot access Content Kit menu');

    wp_set_current_user((int) $administrators[0]);
    $created_post_id = wp_insert_post(array(
        'post_type' => 'seed_directory',
        'post_status' => 'draft',
        'post_title' => 'SEED L2 ISOLATED TEST',
    ), true);
    if (is_wp_error($created_post_id)) {
        throw new RuntimeException($created_post_id->get_error_message());
    }

    seed_l2_wp_assert($created_post_id > 0, 'Directory draft created');
    seed_l2_wp_same(false, get_post_type_archive_link('seed_directory'), 'No archive URL');
    seed_l2_wp_same(0, url_to_postid(home_url('/seed_directory/' . $created_post_id . '/')), 'No public single route');
    seed_l2_wp_same('', apply_filters('preview_post_link', home_url('/preview/'), get_post($created_post_id)), 'No preview link');
    $row_actions = apply_filters('post_row_actions', array(
        'edit' => 'Edit',
        'inline hide-if-no-js' => 'Quick Edit',
        'view' => 'View',
        'preview' => 'Preview',
        'trash' => 'Trash',
    ), get_post($created_post_id));
    seed_l2_wp_assert(!isset($row_actions['inline hide-if-no-js']), 'Quick Edit absent');
    seed_l2_wp_assert(!isset($row_actions['view']) && !isset($row_actions['preview']), 'Public row actions absent');
    $bulk_actions = apply_filters('bulk_actions-edit-seed_directory', array('edit' => 'Edit', 'publish' => 'Publish', 'trash' => 'Trash'));
    seed_l2_wp_assert(!isset($bulk_actions['edit']) && !isset($bulk_actions['publish']), 'Bulk edit and publish absent');
    seed_l2_wp_assert(isset($bulk_actions['trash']), 'Bulk trash retained');

    $routes = rest_get_server()->get_routes();
    $directory_routes = array_filter(array_keys($routes), function ($route) {
        return false !== strpos($route, 'seed_directory');
    });
    seed_l2_wp_same(array(), array_values($directory_routes), 'No Directory REST route');

    $search = new WP_Query(array(
        's' => 'SEED L2 ISOLATED TEST',
        'post_type' => 'any',
        'post_status' => 'publish',
        'fields' => 'ids',
    ));
    seed_l2_wp_assert(!in_array($created_post_id, $search->posts, true), 'Directory absent from public search');

    $stored = wp_seed_content_kit_get_module_options();
    $stored['directory'] = false;
    update_option('wp_seed_content_kit_modules', $stored);
    wp_seed_content_kit_refresh_module_rewrite_rules($stored);
    seed_l2_wp_same(false, post_type_exists('seed_directory'), 'CPT unregistered after module deactivation');
    seed_l2_wp_assert(null !== get_post($created_post_id), 'Draft preserved while module disabled');
    seed_l2_wp_assert(current_user_can('edit_seed_directory_entries'), 'Capabilities retained while module disabled');

    $stored['directory'] = true;
    update_option('wp_seed_content_kit_modules', $stored);
    wp_seed_content_kit_refresh_module_rewrite_rules($stored);
    seed_l2_wp_assert(post_type_exists('seed_directory'), 'CPT restored after module reactivation');
    seed_l2_wp_same('seed_directory', get_post($created_post_id)->post_type, 'Draft recovered after reactivation');

    seed_l2_wp_assert(post_type_exists('seed_quote'), 'Quotes remain registered');
    seed_l2_wp_assert(post_type_exists('seed_testimonial'), 'Testimonials remain registered');
    seed_l2_wp_same('1.0', wp_seed_content_kit_get_contract_version(), 'Template Extension contract unchanged');
    seed_l2_wp_same(true, shortcode_exists('seed_directory'), 'L4 canonical Directory shortcode registered');
    seed_l2_wp_same(true, shortcode_exists('wp_seed_directory'), 'L4 Directory compatibility alias registered');
} catch (Throwable $error) {
    $failures[] = $error->getMessage();
} catch (Exception $error) {
    $failures[] = $error->getMessage();
}

if ($created_post_id) {
    wp_delete_post($created_post_id, true);
}
if ($created_user_id) {
    wp_delete_user($created_user_id);
}
if (null === $previous_modules) {
    delete_option('wp_seed_content_kit_modules');
} else {
    update_option('wp_seed_content_kit_modules', $previous_modules);
}
wp_set_current_user($previous_user_id);

if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress Annuaire L2 assertions' . PHP_EOL;
