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
$created_user_id = 0;
$created_post_id = 0;
$previous_user_id = get_current_user_id();
$previous_modules = get_option('wp_seed_content_kit_modules', null);
$previous_roles = get_option('wp_seed_content_kit_module_roles', null);
$previous_schema = get_option('wp_seed_content_kit_capability_schema', null);

function seed_roles_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_roles_wp_menu_has($parent, $slug)
{
    global $submenu;
    foreach (isset($submenu[$parent]) ? $submenu[$parent] : array() as $item) {
        if (isset($item[2]) && $slug === $item[2]) {
            return true;
        }
    }
    return false;
}

try {
    require_once $plugin_file;
    require_once $root . '/plugin/includes/admin/modules-page.php';
    require_once $root . '/plugin/includes/admin/usage-page.php';
    require_once ABSPATH . 'wp-admin/includes/user.php';

    $administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($administrators)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }

    $created_user_id = wp_create_user('seed_roles_' . wp_generate_password(8, false), wp_generate_password(24), '');
    if (is_wp_error($created_user_id)) {
        throw new RuntimeException($created_user_id->get_error_message());
    }
    $editor = new WP_User($created_user_id);
    $editor->set_role('editor');

    wp_set_current_user((int) $administrators[0]);
    wp_seed_content_kit_activate();
    wp_set_current_user(0);
    wp_set_current_user((int) $administrators[0]);

    foreach (wp_seed_content_kit_get_advanced_capabilities() as $capability) {
        seed_roles_wp_assert(current_user_can($capability), 'Administrator has ' . $capability);
    }
    foreach (wp_seed_content_kit_get_content_capability_definitions() as $module => $definition) {
        foreach (wp_seed_content_kit_get_primitive_capabilities($module) as $capability) {
            seed_roles_wp_assert(current_user_can($capability), 'Administrator has ' . $capability);
        }
    }

    global $menu, $submenu;
    $menu = array();
    $submenu = array();
    wp_seed_content_kit_register_modules_page();
    seed_roles_wp_assert(seed_roles_wp_menu_has('wp-seed-content-kit', 'wp-seed-content-kit'), 'Administrator sees Configuration');
    seed_roles_wp_assert(seed_roles_wp_menu_has('wp-seed-content-kit', 'wp-seed-content-kit-usage'), 'Administrator sees Utilisation');
    foreach (array('seed_testimonial', 'seed_quote', 'seed_directory') as $post_type) {
        seed_roles_wp_assert(seed_roles_wp_menu_has('wp-seed-content-kit', 'edit.php?post_type=' . $post_type), 'Administrator sees ' . $post_type);
    }

    wp_set_current_user($created_user_id);
    foreach (wp_seed_content_kit_get_content_capability_definitions() as $module => $definition) {
        foreach (wp_seed_content_kit_get_primitive_capabilities($module) as $capability) {
            seed_roles_wp_assert(current_user_can($capability), 'Editor has ' . $capability);
        }
    }
    foreach (wp_seed_content_kit_get_advanced_capabilities() as $capability) {
        seed_roles_wp_assert(!current_user_can($capability), 'Editor lacks ' . $capability);
    }
    seed_roles_wp_assert(!current_user_can('manage_wp_seed_templates'), 'Editor cannot manage Templates');

    $menu = array();
    $submenu = array();
    wp_seed_content_kit_register_modules_page();
    seed_roles_wp_assert(!seed_roles_wp_menu_has('wp-seed-content-kit', 'wp-seed-content-kit'), 'Editor does not see Configuration');
    seed_roles_wp_assert(!seed_roles_wp_menu_has('wp-seed-content-kit', 'wp-seed-content-kit-usage'), 'Editor does not see Utilisation');
    foreach (array('seed_testimonial', 'seed_quote', 'seed_directory') as $post_type) {
        seed_roles_wp_assert(seed_roles_wp_menu_has('wp-seed-content-kit', 'edit.php?post_type=' . $post_type), 'Editor sees content list ' . $post_type);
    }

    $assignments = wp_seed_content_kit_get_default_module_role_assignments();
    $assignments['quotes'] = array('administrator');
    update_option('wp_seed_content_kit_module_roles', $assignments);
    wp_seed_content_kit_synchronize_role_capabilities($assignments);
    wp_set_current_user(0);
    wp_set_current_user($created_user_id);
    seed_roles_wp_assert(!current_user_can('edit_seed_quotes'), 'Editor loses Quotes when Configuration removes assignment');
    seed_roles_wp_assert(current_user_can('edit_seed_testimonials'), 'Editor keeps Testimonials assignment');
    seed_roles_wp_assert(current_user_can('edit_seed_directory_entries'), 'Editor keeps Directory assignment');

    wp_set_current_user((int) $administrators[0]);
    $created_post_id = wp_insert_post(array(
        'post_type' => 'seed_directory',
        'post_status' => 'draft',
        'post_title' => 'SEED CK-A2 TEST',
    ), true);
    if (is_wp_error($created_post_id)) {
        throw new RuntimeException($created_post_id->get_error_message());
    }

    $modules = wp_seed_content_kit_get_module_options();
    $modules['directory'] = false;
    update_option('wp_seed_content_kit_modules', $modules);
    wp_seed_content_kit_refresh_module_rewrite_rules($modules);
    seed_roles_wp_assert(!post_type_exists('seed_directory'), 'Disabled Directory menu and CPT are absent');
    seed_roles_wp_assert(null !== get_post($created_post_id), 'Disabled Directory preserves content');
    seed_roles_wp_assert(current_user_can('edit_seed_directory_entries'), 'Disabled Directory preserves configured capability');

    $modules['directory'] = true;
    update_option('wp_seed_content_kit_modules', $modules);
    wp_seed_content_kit_refresh_module_rewrite_rules($modules);
    seed_roles_wp_assert(post_type_exists('seed_directory'), 'Reactivated Directory restores CPT');
    seed_roles_wp_assert(null !== get_post($created_post_id), 'Reactivated Directory keeps content');
} catch (Throwable $error) {
    $failures[] = $error->getMessage();
} catch (Exception $error) {
    $failures[] = $error->getMessage();
}

wp_set_current_user((int) (isset($administrators[0]) ? $administrators[0] : 0));
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
if (null === $previous_roles) {
    delete_option('wp_seed_content_kit_module_roles');
} else {
    update_option('wp_seed_content_kit_module_roles', $previous_roles);
}
if (null === $previous_schema) {
    delete_option('wp_seed_content_kit_capability_schema');
} else {
    update_option('wp_seed_content_kit_capability_schema', $previous_schema);
}
wp_seed_content_kit_synchronize_role_capabilities();
wp_set_current_user($previous_user_id);

if (!empty($failures)) {
    $output = 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL;
    foreach ($failures as $failure) {
        $output .= '- ' . $failure . PHP_EOL;
    }
    if (defined('STDERR')) {
        fwrite(STDERR, $output);
    } else {
        echo $output;
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress CK-A2 role and capability assertions' . PHP_EOL;