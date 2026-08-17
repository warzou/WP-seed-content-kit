<?php

$wp_load = getenv('WP_SEED_WORDPRESS_LOAD');
if (!defined('ABSPATH')) {
    if (!is_string($wp_load) || '' === $wp_load || !is_file($wp_load)) {
        fwrite(STDERR, "Set WP_SEED_WORDPRESS_LOAD to an isolated WordPress wp-load.php.\n");
        exit(2);
    }
    require $wp_load;
}
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__);
$plugin_file = $root . '/plugin/wp-seed-content-kit.php';
$assertions = 0;
$failures = array();
$created_posts = array();
$created_users = array();
$matrix_output = array();
$previous_modules = get_option('wp_seed_content_kit_modules', null);
$previous_schema = get_option('wp_seed_content_directory_profile_facets_schema', null);
$previous_user_id = get_current_user_id();

function dpt_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function dpt_wp_same($expected, $actual, $label)
{
    dpt_wp_assert($expected === $actual, $label . ' (got ' . var_export($actual, true) . ')');
}

function dpt_wp_create_entry($name, $profile_types, $seeking, $status = 'publish', $password = '')
{
    global $created_posts;
    $meta = array(
        '_seed_directory_status' => 'practicing',
        '_seed_directory_country' => 'FR',
        '_seed_directory_publication_authorized' => '1',
        '_seed_directory_publicly_listed' => '1',
    );
    if (null !== $profile_types) {
        $meta['_seed_directory_profile_types'] = $profile_types;
    }
    if ($seeking) {
        $meta['_seed_directory_seeking_models'] = '1';
    }
    $post_id = wp_insert_post(array(
        'post_type' => 'seed_directory',
        'post_status' => $status,
        'post_password' => $password,
        'post_title' => 'SEED DPT ' . $name,
        'post_excerpt' => 'Profil strictement fictif pour la recette 0.8.0.',
        'meta_input' => $meta,
    ), true);
    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }
    $created_posts[] = (int) $post_id;
    return (int) $post_id;
}

function dpt_wp_matrix($name, $args, $expected)
{
    global $matrix_output;
    $actual = wp_seed_content_directory_get_entries($args);
    dpt_wp_same($expected, $actual, 'Collection matrix: ' . $name);
    $matrix_output[] = $name . ' | ' . implode(',', $expected) . ' | ' . implode(',', $actual);
}

try {
    require_once $plugin_file;
    $admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($admins)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }
    wp_set_current_user((int) $admins[0]);
    wp_seed_content_kit_activate();
    do_action('init');

    $modules = wp_seed_content_kit_get_module_options();
    $modules['directory'] = true;
    update_option('wp_seed_content_kit_modules', $modules);

    $ids = array();
    $ids['alice'] = dpt_wp_create_entry('Alice', array('praticien'), false);
    $ids['bruno'] = dpt_wp_create_entry('Bruno', array('intervenant'), false);
    $ids['celine'] = dpt_wp_create_entry('Celine', array('praticien', 'intervenant'), false);
    $ids['david'] = dpt_wp_create_entry('David', array('praticien'), true);
    $ids['emma'] = dpt_wp_create_entry('Emma', array('intervenant'), true);
    $ids['legacy'] = dpt_wp_create_entry('Historique', null, false);
    $ids['draft'] = dpt_wp_create_entry('Brouillon', array('praticien'), true, 'draft');
    $ids['unlisted'] = dpt_wp_create_entry('Non liste', array('praticien'), false);
    delete_post_meta($ids['unlisted'], '_seed_directory_publicly_listed');
    $ids['private'] = dpt_wp_create_entry('Prive', array('praticien'), false, 'private');
    $ids['protected'] = dpt_wp_create_entry('Protege', array('praticien'), false, 'publish', 'secret');

    dpt_wp_matrix('Tous les profils', array(), array($ids['alice'], $ids['bruno'], $ids['celine'], $ids['david'], $ids['emma'], $ids['legacy']));
    dpt_wp_matrix('Praticiens', array('profile_type' => 'praticien'), array($ids['alice'], $ids['celine'], $ids['david']));
    dpt_wp_matrix('Intervenants', array('profile_type' => 'intervenant'), array($ids['bruno'], $ids['celine'], $ids['emma']));
    dpt_wp_matrix('Recherche de modeles', array('seeking_models' => '1'), array($ids['david'], $ids['emma']));
    dpt_wp_matrix('Praticiens + recherche', array('profile_type' => 'praticien', 'seeking_models' => '1'), array($ids['david']));
    dpt_wp_matrix('Intervenants + recherche', array('profile_type' => 'intervenant', 'seeking_models' => '1'), array($ids['emma']));
    dpt_wp_matrix('Types OR', array('profile_types' => array('praticien', 'intervenant'), 'profile_type_operator' => 'or'), array($ids['alice'], $ids['bruno'], $ids['celine'], $ids['david'], $ids['emma']));
    dpt_wp_matrix('Types AND', array('profile_types' => array('praticien', 'intervenant'), 'profile_type_operator' => 'and'), array($ids['celine']));
    dpt_wp_matrix('Recherche inactive', array('seeking_models' => '0'), array($ids['alice'], $ids['bruno'], $ids['celine'], $ids['legacy']));

    dpt_wp_same(array(), wp_seed_content_directory_get_entries(array('profile_type' => 'invalid')), 'Invalid profile type fails closed');
    dpt_wp_same(array(), wp_seed_content_directory_get_entries(array('seeking_models' => 'later')), 'Invalid seeking status fails closed');
    dpt_wp_same(array($ids['bruno'], $ids['celine']), wp_seed_content_directory_get_entries(array(
        'profile_type' => 'intervenant',
        'exclude_ids' => array($ids['emma']),
    )), 'Explicit exclusion');
    dpt_wp_same(array($ids['bruno'], $ids['celine']), wp_seed_content_directory_get_entries(array('offset' => 1, 'limit' => 2)), 'Pagination after stable sort');
    dpt_wp_same(array($ids['bruno']), wp_seed_content_directory_get_entries(array('ids' => array($ids['alice'], $ids['bruno']), 'exclude_ids' => array($ids['alice']))), 'RC2 regression: intersecting exclusion wins over explicit IDs');
    dpt_wp_same(array(), wp_seed_content_directory_get_entries(array('ids' => array($ids['alice']), 'exclude_ids' => array($ids['alice']))), 'All explicit IDs excluded remains empty');
    dpt_wp_same(array($ids['alice'], $ids['bruno']), wp_seed_content_directory_get_entries(array('ids' => array($ids['alice'], $ids['alice'], $ids['bruno']), 'exclude_ids' => array($ids['emma'], $ids['emma']))), 'ID lists are deduplicated independently');
    dpt_wp_same(array(), wp_seed_content_directory_get_entries(array('ids' => array('1'), 'exclude_ids' => array())), 'Invalid explicit ID types fail closed');
    dpt_wp_same(array(), wp_seed_content_directory_get_entries(array('ids' => array($ids['unlisted'], $ids['alice']), 'exclude_ids' => array($ids['alice']))), 'Unlisted profile is not widened into an intersected selection');
    dpt_wp_same(array(), wp_seed_content_directory_get_entries(array('ids' => array($ids['draft'], $ids['private'], $ids['protected'], $ids['alice']), 'exclude_ids' => array($ids['alice']))), 'Draft private and protected profiles remain excluded');
    dpt_wp_same(array($ids['celine']), wp_seed_content_directory_get_entries(array('ids' => array($ids['alice'], $ids['celine'], $ids['bruno']), 'exclude_ids' => array($ids['alice']), 'profile_types' => array('praticien', 'intervenant'), 'profile_type_operator' => 'and')), 'AND type filter applies after ID intersection');
    dpt_wp_same(array($ids['emma']), wp_seed_content_directory_get_entries(array('ids' => array($ids['david'], $ids['emma']), 'exclude_ids' => array($ids['david']), 'seeking_models' => '1')), 'Seeking filter applies after ID intersection');
    dpt_wp_same(array($ids['celine']), wp_seed_content_directory_get_entries(array('ids' => array($ids['alice'], $ids['bruno'], $ids['celine']), 'exclude_ids' => array($ids['alice']), 'offset' => 1, 'limit' => 1)), 'Offset and limit apply after exclusion');
    dpt_wp_same(array($ids['celine'], $ids['bruno']), wp_seed_content_directory_get_entries(array('ids' => array($ids['alice'], $ids['bruno'], $ids['celine']), 'exclude_ids' => array($ids['alice']), 'orderby' => 'id', 'order' => 'desc')), 'Canonical order applies after exclusion');
    $canonical_html = do_shortcode('[seed_directory ids="' . $ids['alice'] . ',' . $ids['bruno'] . '" exclude_ids="' . $ids['alice'] . '"]');
    $alias_html = do_shortcode('[wp_seed_directory ids="' . $ids['alice'] . ',' . $ids['bruno'] . '" exclude_ids="' . $ids['alice'] . '"]');
    dpt_wp_same($canonical_html, $alias_html, 'Canonical shortcode and alias share the corrected contract');
    dpt_wp_assert(false === strpos($canonical_html, 'SEED DPT Alice') && false !== strpos($canonical_html, 'SEED DPT Bruno'), 'Shortcode renderer excludes the intersecting explicit ID');
    $legacy_data = wp_seed_content_directory_get_public_data($ids['legacy']);
    dpt_wp_same(array(), $legacy_data['profile_types'], 'Legacy public profile remains visible with empty type list');
    dpt_wp_same('', $legacy_data['profile_types_label'], 'Legacy public type label is clean empty');
    dpt_wp_same(false, wp_seed_content_directory_get_public_data($ids['draft']), 'Draft has no public data');
    wp_update_post(array('ID' => $ids['legacy'], 'post_password' => 'protected'));
    dpt_wp_same(false, wp_seed_content_directory_get_public_data($ids['legacy']), 'Protected profile has no public data');
    wp_update_post(array('ID' => $ids['legacy'], 'post_password' => ''));

    $celine_data = wp_seed_content_directory_get_public_data($ids['celine']);
    dpt_wp_same(array('Praticien', 'Intervenant'), $celine_data['profile_type_labels'], 'Public Data API exposes human multi-type labels');
    $celine_context = wp_seed_content_directory_get_template_context($celine_data);
    dpt_wp_same('Praticien, Intervenant', $celine_context['directory.profile_types'], 'Template receives human multi-type label');
    dpt_wp_same('praticien,intervenant', $celine_context['directory.profile_type_slugs'], 'Template receives clean slugs');
    $david_context = wp_seed_content_directory_get_template_context(wp_seed_content_directory_get_public_data($ids['david']));
    dpt_wp_same('1', $david_context['directory.seeking_models_active'], 'Template receives active status');
    dpt_wp_same('', $celine_context['directory.seeking_models_active'], 'Template receives clean empty inactive status');

    $layout_id = wp_insert_post(array(
        'post_type' => 'et_pb_layout',
        'post_status' => 'publish',
        'post_title' => 'SEED DPT DIVI LAYOUT',
        'post_content' => '<div class="seed-dpt-layout">{{directory.name}}|{{directory.profile_types}}|{{directory.seeking_models}}</div>',
    ));
    $created_posts[] = (int) $layout_id;
    $template_id = wp_insert_post(array(
        'post_type' => 'seed_template',
        'post_status' => 'publish',
        'post_title' => 'SEED DPT DIVI TEMPLATE',
        'post_name' => 'seed-dpt-divi-template',
        'post_content' => 'Native fallback',
        'meta_input' => array(
            '_wp_seed_content_template_module' => 'directory',
            '_wp_seed_content_template_source' => 'divi_layout',
            '_wp_seed_content_divi_layout_id' => (int) $layout_id,
        ),
    ));
    $created_posts[] = (int) $template_id;
    $layout_before = get_post_field('post_content', $layout_id, 'raw');
    $layout_meta_before = get_post_meta($layout_id);
    $divi_multi = do_shortcode('[seed_directory profile_types="praticien,intervenant" profile_type_operator="and" template="seed-dpt-divi-template"]');
    dpt_wp_same(1, substr_count($divi_multi, 'seed-dpt-layout'), 'Divi Layout Template renders one multi-type profile');
    dpt_wp_assert(false !== strpos($divi_multi, 'Praticien, Intervenant'), 'Divi Layout Template receives human multi-type label');
    $divi_seeking = do_shortcode('[seed_directory profile_type="intervenant" seeking_models="1" template="seed-dpt-divi-template"]');
    dpt_wp_assert(false !== strpos($divi_seeking, 'Recherche de modèles'), 'Divi Layout Template receives active seeking label');
    $divi_empty = do_shortcode('[seed_directory ids="' . $ids['legacy'] . '" template="seed-dpt-divi-template"]');
    dpt_wp_assert(false === strpos($divi_empty, 'Praticien') && false === strpos($divi_empty, 'Intervenant'), 'Divi Layout Template keeps legacy types empty');
    dpt_wp_same($layout_before, get_post_field('post_content', $layout_id, 'raw'), 'Divi Layout content remains unchanged');
    dpt_wp_same($layout_meta_before, get_post_meta($layout_id), 'Divi Layout metadata remains unchanged');
    dpt_wp_same(1, substr_count(do_shortcode('[seed_directory profile_type="praticien" limit="1" template="missing-dpt-template"]'), '<article class="wp-seed-directory-card">'), 'Missing Divi Template falls back locally');

    $canonical = do_shortcode('[seed_directory profile_type="praticien" seeking_models="1"]');
    dpt_wp_same(1, substr_count($canonical, '<article class="wp-seed-directory-card">'), 'Combined shortcode renders one public profile');
    dpt_wp_same($canonical, do_shortcode('[wp_seed_directory profile_type="praticien" seeking_models="1"]'), 'Historical shortcode alias remains identical');
    dpt_wp_same('', do_shortcode('[seed_directory profile_type="invalid"]'), 'Invalid shortcode filter is fail closed');
    dpt_wp_same(false, class_exists('ET_Builder_Module_Seed_Directory'), 'No dedicated Divi or Loop Builder module');

    $editor_id = wp_create_user('seed_dpt_editor_' . wp_generate_password(8, false), wp_generate_password(24, true, true), '');
    if (is_wp_error($editor_id)) {
        throw new RuntimeException($editor_id->get_error_message());
    }
    $created_users[] = (int) $editor_id;
    (new WP_User($editor_id))->set_role('editor');
    wp_set_current_user((int) $editor_id);
    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        'wp_seed_content_directory_profile_present' => '1',
        '_seed_directory_profile_types' => array('intervenant', 'praticien', 'invalid'),
        '_seed_directory_seeking_models' => '1',
    );
    wp_seed_content_directory_save_meta($ids['alice'], get_post($ids['alice']));
    dpt_wp_same(array('praticien', 'intervenant'), get_post_meta($ids['alice'], '_seed_directory_profile_types', true), 'Authorized Editor saves normalized multiple types');
    dpt_wp_same('1', get_post_meta($ids['alice'], '_seed_directory_seeking_models', true), 'Authorized Editor saves temporary status');

    $_POST = array('wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'));
    wp_seed_content_directory_save_meta($ids['alice'], get_post($ids['alice']));
    dpt_wp_same(array('praticien', 'intervenant'), get_post_meta($ids['alice'], '_seed_directory_profile_types', true), 'Partial save without panel preserves profile types');
    dpt_wp_same('1', get_post_meta($ids['alice'], '_seed_directory_seeking_models', true), 'Partial save without panel preserves temporary status');

    $subscriber_id = wp_create_user('seed_dpt_reader_' . wp_generate_password(8, false), wp_generate_password(24, true, true), '');
    if (is_wp_error($subscriber_id)) {
        throw new RuntimeException($subscriber_id->get_error_message());
    }
    $created_users[] = (int) $subscriber_id;
    (new WP_User($subscriber_id))->set_role('subscriber');
    wp_set_current_user((int) $subscriber_id);
    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        'wp_seed_content_directory_profile_present' => '1',
        '_seed_directory_profile_types' => array('intervenant'),
    );
    wp_seed_content_directory_save_meta($ids['alice'], get_post($ids['alice']));
    dpt_wp_same(array('praticien', 'intervenant'), get_post_meta($ids['alice'], '_seed_directory_profile_types', true), 'Unauthorized user cannot alter profile types');

    wp_set_current_user((int) $editor_id);
    $_POST = array(
        'wp_seed_content_directory_nonce' => 'invalid',
        'wp_seed_content_directory_profile_present' => '1',
        '_seed_directory_profile_types' => array('intervenant'),
    );
    wp_seed_content_directory_save_meta($ids['alice'], get_post($ids['alice']));
    dpt_wp_same(array('praticien', 'intervenant'), get_post_meta($ids['alice'], '_seed_directory_profile_types', true), 'Invalid nonce cannot alter profile types');
    dpt_wp_assert(false !== strpos(file_get_contents($root . '/plugin/includes/modules/directory/admin.php'), 'DOING_AUTOSAVE'), 'Autosave guard remains explicit');
    $_POST = array();

    update_post_meta($ids['legacy'], '_seed_directory_status', 'seeking_models');
    delete_post_meta($ids['legacy'], '_seed_directory_seeking_models');
    delete_option('wp_seed_content_directory_profile_facets_schema');
    $upgrade = wp_seed_content_directory_upgrade_profile_facets();
    dpt_wp_same('migrated', $upgrade['status'], 'Additive profile migration runs once');
    dpt_wp_same('1', get_post_meta($ids['legacy'], '_seed_directory_seeking_models', true), 'Legacy seeking status copied to temporary boolean');
    dpt_wp_same('', get_post_meta($ids['legacy'], '_seed_directory_profile_types', true), 'Migration does not auto-assign practitioner');
    dpt_wp_same(array('status' => 'unchanged', 'updated' => 0), wp_seed_content_directory_upgrade_profile_facets(), 'Profile migration is idempotent');

    dpt_wp_same(25, count(wp_seed_content_kit_get_registered_template_placeholders('directory')), 'Twenty-five Directory template placeholders registered');
    dpt_wp_same(6, count(wp_seed_content_directory_get_predefined_collections()), 'Six non-persistent predefined Collections');
} catch (Throwable $error) {
    $failures[] = 'Unhandled exception: ' . $error->getMessage();
} finally {
    $_POST = array();
    wp_set_current_user((int) $admins[0]);
    foreach (array_reverse($created_posts) as $post_id) {
        wp_delete_post($post_id, true);
    }
    foreach ($created_users as $user_id) {
        wp_delete_user($user_id);
    }
    if (null === $previous_modules) {
        delete_option('wp_seed_content_kit_modules');
    } else {
        update_option('wp_seed_content_kit_modules', $previous_modules);
    }
    if (null === $previous_schema) {
        delete_option('wp_seed_content_directory_profile_facets_schema');
    } else {
        update_option('wp_seed_content_directory_profile_facets_schema', $previous_schema);
    }
    wp_set_current_user($previous_user_id);
}

if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

foreach ($matrix_output as $row) {
    echo 'MATRIX ' . $row . PHP_EOL;
}
echo 'PASS ' . $assertions . ' WordPress Directory profile types assertions' . PHP_EOL;
