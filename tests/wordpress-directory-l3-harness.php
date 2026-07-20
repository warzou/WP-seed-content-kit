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
$created_posts = array();
$created_users = array();
$created_files = array();
$previous_modules = get_option('wp_seed_content_kit_modules', null);
$previous_user_id = get_current_user_id();

function seed_l3_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_l3_wp_same($expected, $actual, $label)
{
    seed_l3_wp_assert($expected === $actual, $label);
}

function seed_l3_wp_create_entry($overrides = array())
{
    global $created_posts;
    $defaults = array(
        'post_type' => 'seed_directory',
        'post_status' => 'draft',
        'post_title' => 'SEED L3 FICTIVE ENTRY',
        'post_excerpt' => 'Présentation fictive.',
        'meta_input' => array(
            '_seed_directory_status' => 'practicing',
            '_seed_directory_country' => 'FR',
            '_seed_directory_publication_authorized' => '1',
        ),
    );
    $args = array_merge($defaults, $overrides);
    if (isset($overrides['meta_input'])) {
        $args['meta_input'] = array_merge($defaults['meta_input'], $overrides['meta_input']);
    }
    $post_id = wp_insert_post($args, true);
    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }
    $created_posts[] = (int) $post_id;
    return (int) $post_id;
}

try {
    require_once $plugin_file;
    require_once ABSPATH . 'wp-admin/includes/user.php';
    require_once ABSPATH . 'wp-admin/includes/post.php';
    require_once ABSPATH . 'wp-admin/includes/template.php';

    $administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($administrators)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }

    wp_set_current_user((int) $administrators[0]);
    wp_seed_content_kit_activate();
    wp_set_current_user(0);
    wp_set_current_user((int) $administrators[0]);
    do_action('init');

    seed_l3_wp_same('0.6.0-dev', WP_SEED_CONTENT_KIT_VERSION, 'Plugin version');
    seed_l3_wp_assert(post_type_exists('seed_directory'), 'Directory CPT registered');
    seed_l3_wp_same(false, get_post_type_object('seed_directory')->show_in_rest, 'Directory remains outside REST');
    seed_l3_wp_assert(post_type_supports('seed_directory', 'revisions'), 'Directory supports native revisions');
    seed_l3_wp_same(19, count(wp_seed_content_directory_get_meta_definitions()), 'Exact business meta count');

    $draft_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 MINIMAL DRAFT',
        'post_status' => 'draft',
        'meta_input' => array(
            '_seed_directory_status' => '',
            '_seed_directory_publication_authorized' => '',
        ),
    ));
    seed_l3_wp_same('draft', get_post_status($draft_id), 'Minimal draft remains saveable');

    $no_auth_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 WITHOUT AUTHORIZATION',
        'post_status' => 'publish',
        'meta_input' => array('_seed_directory_publication_authorized' => ''),
    ));
    seed_l3_wp_same('draft', get_post_status($no_auth_id), 'Publication without authorization rejected');
    seed_l3_wp_same(false, wp_seed_content_directory_is_publicly_eligible($no_auth_id), 'Unauthorized entry ineligible');

    $valid_id = seed_l3_wp_create_entry(array('post_title' => 'SEED L3 VALID PUBLISHED', 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($valid_id), 'Authorized valid entry published');
    seed_l3_wp_same(true, wp_seed_content_directory_is_publicly_eligible($valid_id), 'Authorized valid entry eligible');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_public_contacts($valid_id), 'No contact required');

    $missing_name_id = seed_l3_wp_create_entry(array('post_title' => '', 'post_status' => 'publish'));
    seed_l3_wp_same('draft', get_post_status($missing_name_id), 'Missing name blocks publication');

    $invalid_status_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 INVALID STATUS',
        'post_status' => 'publish',
        'meta_input' => array('_seed_directory_status' => 'invalid'),
    ));
    seed_l3_wp_same('draft', get_post_status($invalid_status_id), 'Invalid business status blocks publication');
    seed_l3_wp_same('', get_post_meta($invalid_status_id, '_seed_directory_status', true), 'Invalid status not stored');

    $invalid_country_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 INVALID COUNTRY',
        'post_status' => 'publish',
        'meta_input' => array('_seed_directory_country' => 'ZZ'),
    ));
    seed_l3_wp_same('draft', get_post_status($invalid_country_id), 'Invalid country blocks publication');
    seed_l3_wp_same('', get_post_meta($invalid_country_id, '_seed_directory_country', true), 'Invalid country not stored');

    $uploads = wp_upload_dir();
    $photo_path = trailingslashit($uploads['path']) . 'seed-l3-fictive.gif';
    file_put_contents($photo_path, base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='));
    $created_files[] = $photo_path;
    $attachment_id = wp_insert_attachment(array(
        'post_title' => 'SEED L3 FICTIVE PHOTO',
        'post_status' => 'inherit',
        'post_mime_type' => 'image/gif',
        'guid' => trailingslashit($uploads['url']) . 'seed-l3-fictive.gif',
    ), $photo_path, 0, true);
    if (is_wp_error($attachment_id)) {
        throw new RuntimeException($attachment_id->get_error_message());
    }
    $created_posts[] = (int) $attachment_id;

    $photo_no_alt_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 PHOTO WITHOUT ALT',
        'post_status' => 'publish',
        'meta_input' => array('_thumbnail_id' => $attachment_id),
    ));
    seed_l3_wp_same('draft', get_post_status($photo_no_alt_id), 'Photo without alt blocks publication');

    update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Portrait fictif accessible');
    $photo_alt_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 PHOTO WITH ALT',
        'post_status' => 'publish',
        'meta_input' => array('_thumbnail_id' => $attachment_id),
    ));
    seed_l3_wp_same('publish', get_post_status($photo_alt_id), 'Photo with alt permits publication');
    seed_l3_wp_same(true, wp_seed_content_directory_is_publicly_eligible($photo_alt_id), 'Photo entry eligible');

    update_post_meta($valid_id, '_seed_directory_phone', '+33 (0)1 23 45 67 89');
    update_post_meta($valid_id, '_seed_directory_phone_visible', '1');
    update_post_meta($valid_id, '_seed_directory_email', 'private@example.test');
    update_post_meta($valid_id, '_seed_directory_email_visible', '');
    update_post_meta($valid_id, '_seed_directory_website', 'invalid');
    update_post_meta($valid_id, '_seed_directory_website_visible', '1');
    $public_contacts = wp_seed_content_directory_get_public_contacts($valid_id);
    seed_l3_wp_same(array('phone' => '+33 (0)1 23 45 67 89'), $public_contacts, 'Visible valid contact only: ' . serialize($public_contacts));
    seed_l3_wp_assert(false === strpos(serialize(wp_seed_content_directory_get_public_contacts($valid_id)), 'private@example.test'), 'Masked contact absent from public data');
    seed_l3_wp_assert(false === strpos(serialize(wp_seed_content_directory_get_public_contacts($valid_id)), 'invalid'), 'Invalid visible contact absent from public data');

    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        '_seed_directory_status' => 'practicing',
        '_seed_directory_country' => 'FR',
        '_seed_directory_phone' => '+33 (0)1 23 45 67 89',
        '_seed_directory_phone_visible' => '1',
        '_seed_directory_email' => 'private@example.test',
    );
    seed_l3_wp_assert(current_user_can('edit_seed_directory_entry', $valid_id), 'Administrator has object edit capability');
    wp_seed_content_directory_save_meta($valid_id, get_post($valid_id));
    seed_l3_wp_same('draft', get_post_status($valid_id), 'Unchecking authorization demotes published entry; got ' . get_post_status($valid_id));
    seed_l3_wp_same(false, wp_seed_content_directory_is_publicly_eligible($valid_id), 'Authorization withdrawal immediately ineligible');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_public_contacts($valid_id), 'Authorization withdrawal exposes no contact');
    $_POST = array();

    $internal_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 INTERNAL PUBLISH',
        'post_status' => 'publish',
        'meta_input' => array(
            '_seed_directory_status' => 'seeking_models',
            '_seed_directory_country' => 'fr',
            '_seed_directory_publication_authorized' => true,
            '_seed_directory_postal_code' => '00120',
            '_seed_directory_department' => '2a',
        ),
    ));
    seed_l3_wp_same('publish', get_post_status($internal_id), 'Internal valid publication succeeds');
    seed_l3_wp_same('FR', get_post_meta($internal_id, '_seed_directory_country', true), 'Internal country sanitized');
    seed_l3_wp_same('00120', get_post_meta($internal_id, '_seed_directory_postal_code', true), 'Postal leading zeros preserved');
    seed_l3_wp_same('2A', get_post_meta($internal_id, '_seed_directory_department', true), 'Corsican department preserved');

    $future_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 FUTURE VALID',
        'post_status' => 'future',
        'post_date' => gmdate('Y-m-d H:i:s', time() + DAY_IN_SECONDS),
        'post_date_gmt' => gmdate('Y-m-d H:i:s', time() + DAY_IN_SECONDS),
    ));
    seed_l3_wp_same('future', get_post_status($future_id), 'Valid scheduled publication retained');

    $subscriber_id = wp_create_user('seed_l3_reader_' . wp_generate_password(8, false), wp_generate_password(24), '');
    if (is_wp_error($subscriber_id)) {
        throw new RuntimeException($subscriber_id->get_error_message());
    }
    $created_users[] = (int) $subscriber_id;
    (new WP_User($subscriber_id))->set_role('subscriber');
    wp_set_current_user($subscriber_id);
    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        '_seed_directory_internal_note' => 'SHOULD NOT BE SAVED',
    );
    wp_seed_content_directory_save_meta($internal_id, get_post($internal_id));
    seed_l3_wp_same('', get_post_meta($internal_id, '_seed_directory_internal_note', true), 'User without capability cannot save');
    seed_l3_wp_assert(is_wp_error(wp_seed_content_directory_get_admin_data($internal_id)), 'Unauthorized admin data denied');
    $_POST = array();

    wp_set_current_user((int) $administrators[0]);
    update_post_meta($internal_id, '_seed_directory_internal_note', 'Note strictement interne');
    update_post_meta($internal_id, '_seed_directory_email', 'internal@example.test');
    $admin_data = wp_seed_content_directory_get_admin_data($internal_id);
    seed_l3_wp_same('Note strictement interne', $admin_data['internal_note'], 'Authorized admin receives internal note');
    seed_l3_wp_same('internal@example.test', $admin_data['email'], 'Authorized admin receives private contact');

    $columns = apply_filters('manage_seed_directory_posts_columns', array('cb' => 'Select', 'title' => 'Title', 'date' => 'Date'));
    seed_l3_wp_same(array('cb', 'directory_photo', 'title', 'directory_status', 'directory_location', 'directory_authorized', 'directory_order', 'date'), array_keys($columns), 'Exact admin columns');
    ob_start();
    foreach (array('directory_photo', 'directory_status', 'directory_location', 'directory_authorized', 'directory_order') as $column) {
        do_action('manage_seed_directory_posts_custom_column', $column, $internal_id);
    }
    $column_html = ob_get_clean();
    seed_l3_wp_assert(false === strpos($column_html, 'internal@example.test'), 'Private email absent from columns');
    seed_l3_wp_assert(false === strpos($column_html, 'Note strictement interne'), 'Internal note absent from columns');

    do_action('add_meta_boxes_seed_directory', get_post($internal_id));
    global $wp_meta_boxes;
    $directory_boxes = is_array($wp_meta_boxes) ? $wp_meta_boxes : array();
    $expected_boxes = array(
        'wp_seed_content_directory_identity',
        'wp_seed_content_directory_situation',
        'wp_seed_content_directory_contacts',
        'wp_seed_content_directory_publication',
    );
    $found_boxes = array();
    $serialized_boxes = serialize($directory_boxes);
    foreach ($expected_boxes as $box_id) {
        if (false !== strpos($serialized_boxes, $box_id)) {
            $found_boxes[$box_id] = true;
        }
    }
    seed_l3_wp_same($expected_boxes, array_values(array_intersect($expected_boxes, array_keys($found_boxes))), 'Exactly four Directory custom panels: ' . serialize(array_keys($found_boxes)));

    wp_update_post(array('ID' => $internal_id, 'post_excerpt' => 'Présentation modifiée pour la révision.'));
    $revisions = wp_get_post_revisions($internal_id);
    $revision_id = $revisions ? (int) key($revisions) : 0;
    seed_l3_wp_assert($revision_id > 0, 'Native revision created');
    seed_l3_wp_same('Note strictement interne', get_post_meta($internal_id, '_seed_directory_internal_note', true), 'Private meta preserved after revision');
    wp_restore_post_revision($revision_id);
    seed_l3_wp_same('Note strictement interne', get_post_meta($internal_id, '_seed_directory_internal_note', true), 'Private meta preserved after revision restore');
    seed_l3_wp_same(true, wp_seed_content_directory_is_publicly_eligible($internal_id), 'Revision restore does not bypass eligibility');

    require_once $root . '/plugin/includes/admin/modules-page.php';
    $stored = wp_seed_content_kit_get_module_options();
    $stored['directory'] = false;
    update_option('wp_seed_content_kit_modules', $stored);
    wp_seed_content_kit_refresh_module_rewrite_rules($stored);
    seed_l3_wp_same(false, post_type_exists('seed_directory'), 'Directory CPT removed on module deactivation');
    seed_l3_wp_assert(null !== get_post($internal_id), 'Data retained while module disabled');
    $stored['directory'] = true;
    update_option('wp_seed_content_kit_modules', $stored);
    wp_seed_content_kit_refresh_module_rewrite_rules($stored);
    seed_l3_wp_assert(post_type_exists('seed_directory'), 'Directory CPT restored on reactivation');
    seed_l3_wp_same('Note strictement interne', get_post_meta($internal_id, '_seed_directory_internal_note', true), 'Private data retained after reactivation');

    seed_l3_wp_assert(post_type_exists('seed_quote'), 'Citations unchanged');
    seed_l3_wp_assert(post_type_exists('seed_testimonial'), 'Testimonials unchanged');
    seed_l3_wp_same('1.0', wp_seed_content_kit_get_contract_version(), 'Template Extension contract unchanged');
    seed_l3_wp_same(false, shortcode_exists('seed_directory'), 'No Directory shortcode');
    seed_l3_wp_same(false, shortcode_exists('wp_seed_directory'), 'No Directory compatibility alias');
    $routes = rest_get_server()->get_routes();
    $directory_routes = array_filter(array_keys($routes), function ($route) {
        return false !== strpos($route, 'seed_directory');
    });
    seed_l3_wp_same(array(), array_values($directory_routes), 'No Directory REST route');
} catch (Throwable $error) {
    $failures[] = $error->getMessage();
} catch (Exception $error) {
    $failures[] = $error->getMessage();
}

$_POST = array();
wp_set_current_user((int) $previous_user_id);
foreach (array_reverse($created_posts) as $post_id) {
    wp_delete_post($post_id, true);
}
foreach ($created_users as $user_id) {
    wp_delete_user($user_id);
}
foreach ($created_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
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

echo 'PASS ' . $assertions . ' WordPress Annuaire L3 assertions' . PHP_EOL;
