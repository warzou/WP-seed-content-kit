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
        '_seed_directory_publicly_listed' => '1',
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
    require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
    require_once ABSPATH . 'wp-admin/includes/screen.php';

    $administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($administrators)) {
        throw new RuntimeException('The isolated WordPress has no administrator.');
    }

    wp_set_current_user((int) $administrators[0]);
    wp_seed_content_kit_activate();
    wp_set_current_user(0);
    wp_set_current_user((int) $administrators[0]);
    do_action('init');

    $plugin_headers = get_file_data(WP_SEED_CONTENT_KIT_FILE, array('Version' => 'Version'), 'plugin');
    seed_l3_wp_same($plugin_headers['Version'], WP_SEED_CONTENT_KIT_VERSION, 'Plugin version matches header');
    seed_l3_wp_assert(post_type_exists('seed_directory'), 'Directory CPT registered');
    seed_l3_wp_same(true, get_post_type_object('seed_directory')->show_in_rest, 'Directory editor REST enabled');
    seed_l3_wp_assert(post_type_supports('seed_directory', 'revisions'), 'Directory supports native revisions');
    seed_l3_wp_assert(post_type_supports('seed_directory', 'editor'), 'Directory supports native full presentation editor');
    seed_l3_wp_same(23, count(wp_seed_content_directory_get_meta_definitions()), 'Exact current business meta count');

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

    $grandfathered_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 GRANDFATHERED PUBLISHED',
        'post_status' => 'publish',
        'meta_input' => array('_thumbnail_id' => $attachment_id),
    ));
    delete_post_meta($attachment_id, '_wp_attachment_image_alt');
    wp_update_post(array('ID' => $grandfathered_id, 'meta_input' => array('_seed_directory_phone' => '+33 1 98 76 54 32')));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Published entry with preexisting missing alt survives phone edit');
    seed_l3_wp_same(array('missing_photo_alt'), wp_seed_content_directory_get_validation_warning($grandfathered_id), 'Phone edit stores persistent warning');
    wp_update_post(array('ID' => $grandfathered_id, 'post_excerpt' => 'Résumé grandfathered modifié.'));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Published entry with preexisting missing alt survives summary edit');
    wp_update_post(array('ID' => $grandfathered_id, 'meta_input' => array('seed_directory_professional_label' => 'Praticienne')));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Published entry with preexisting missing alt survives professional label edit');

    do_action('rest_api_init');
    $request = new WP_REST_Request('PUT', '/wp/v2/seed_directory/' . $grandfathered_id);
    $request->set_param('excerpt', 'Résumé modifié via REST.');
    $response = rest_do_request($request);
    seed_l3_wp_same(200, $response->get_status(), 'Gutenberg REST update succeeds for grandfathered published entry');
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Gutenberg REST update preserves published status');
    seed_l3_wp_same(array('missing_photo_alt'), wp_seed_content_directory_get_validation_warning($grandfathered_id), 'REST flow preserves persistent warning');

    update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Portrait fictif accessible');
    wp_update_post(array('ID' => $grandfathered_id, 'post_excerpt' => 'Résumé après correction.'));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Fixing missing alt keeps entry published');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_validation_warning($grandfathered_id), 'Fixing missing alt clears persistent warning');

    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        '_seed_directory_photo_alt' => '',
        '_seed_directory_publication_authorized' => '1',
    );
    wp_update_post(array('ID' => $grandfathered_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'First new missing alt save keeps valid published entry online');
    seed_l3_wp_same('pending', wp_seed_content_directory_get_pending_validation($grandfathered_id)['status'], 'First new missing alt save stores private pending state');
    $state_request = new WP_REST_Request('GET', '/wp/v2/seed_directory/' . $grandfathered_id);
    $state_request->set_param('context', 'edit');
    $state_response = rest_do_request($state_request);
    $state_data = $state_response->get_data();
    seed_l3_wp_same('pending', $state_data['wpsck_directory_validation']['state'], 'REST edit response exposes persistent Gutenberg warning state');
    seed_l3_wp_assert(false !== strpos($state_data['wpsck_directory_validation']['summary'], 'reste publi'), 'REST warning explains temporary publication grace');

    $_POST['_seed_directory_photo_alt'] = 'Portrait corrigé avant seconde sauvegarde';
    wp_update_post(array('ID' => $grandfathered_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Correction between saves preserves publication');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_pending_validation($grandfathered_id), 'Correction clears pending marker');

    $_POST['_seed_directory_photo_alt'] = '';
    wp_update_post(array('ID' => $grandfathered_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Later new missing alt starts a fresh warning cycle');
    wp_update_post(array('ID' => $grandfathered_id, 'post_status' => 'publish'));
    seed_l3_wp_same('draft', get_post_status($grandfathered_id), 'Second unchanged missing alt save moves entry to draft');
    seed_l3_wp_same('drafted', wp_seed_content_directory_get_pending_validation($grandfathered_id)['status'], 'Draft reason persists for Gutenberg');

    $_POST['_seed_directory_photo_alt'] = 'Portrait corrigé pour republication';
    wp_update_post(array('ID' => $grandfathered_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($grandfathered_id), 'Draft republishes after correction');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_pending_validation($grandfathered_id), 'Successful republish clears draft validation state');
    $_POST = array();

    update_post_meta($valid_id, '_seed_directory_phone', '+33 (0)1 23 45 67 89');
    update_post_meta($valid_id, '_seed_directory_phone_visible', '1');
    update_post_meta($valid_id, '_seed_directory_email', 'private@example.test');
    update_post_meta($valid_id, '_seed_directory_email_visible', '');
    update_post_meta($valid_id, '_seed_directory_website', 'invalid');
    update_post_meta($valid_id, '_seed_directory_website_visible', '1');
    seed_l3_wp_assert(in_array('invalid_public_website', wp_seed_content_directory_get_publication_errors($valid_id), true), 'Invalid visible website blocks publication eligibility');
    seed_l3_wp_same(false, wp_seed_content_directory_is_publicly_eligible($valid_id), 'Invalid visible website makes entry ineligible');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_public_contacts($valid_id), 'Ineligible entry exposes no contacts');
    update_post_meta($valid_id, '_seed_directory_website_visible', '');
    $public_contacts = wp_seed_content_directory_get_public_contacts($valid_id);
    seed_l3_wp_same(array('phone' => '+33 (0)1 23 45 67 89'), $public_contacts, 'Only valid visible contact returned: ' . serialize($public_contacts));
    seed_l3_wp_assert(false === strpos(serialize($public_contacts), 'private@example.test'), 'Masked contact absent from public data');
    seed_l3_wp_assert(false === strpos(serialize($public_contacts), 'invalid'), 'Private invalid contact absent from public data');

    $invalid_contacts = array(
        '_seed_directory_phone' => array('letters only', 'invalid_public_phone'),
        '_seed_directory_email' => array('invalid', 'invalid_public_email'),
        '_seed_directory_website' => array('ftp://example.test', 'invalid_public_website'),
        '_seed_directory_facebook' => array('https://example.test/facebook', 'invalid_public_facebook'),
        '_seed_directory_instagram' => array('https://example.test/instagram', 'invalid_public_instagram'),
    );
    foreach ($invalid_contacts as $meta_key => $case) {
        $contact_id = seed_l3_wp_create_entry(array(
            'post_title' => 'SEED L3 INVALID PUBLIC CONTACT ' . $meta_key,
            'post_status' => 'publish',
            'meta_input' => array($meta_key => $case[0], $meta_key . '_visible' => '1'),
        ));
        seed_l3_wp_same('draft', get_post_status($contact_id), 'Invalid public contact blocks publication: ' . $meta_key);
        seed_l3_wp_assert(in_array($case[1], wp_seed_content_directory_get_publication_errors($contact_id), true), 'Precise contact error returned: ' . $meta_key);
        seed_l3_wp_same($case[0], get_post_meta($contact_id, $meta_key, true), 'Invalid contact value retained privately in draft: ' . $meta_key);
        seed_l3_wp_same(array(), wp_seed_content_directory_get_public_contacts($contact_id), 'Invalid contact never exposed: ' . $meta_key);
    }

    $repeatable_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 TWO STEP CONTACT',
        'post_status' => 'publish',
    ));
    $invalid_repeatable = array(array(
        'row_id' => 'website-primary',
        'type' => 'website',
        'label' => 'Site principal',
        'value' => 'htps://example.test',
        'public' => '1',
        'order' => 10,
        'format' => 'full_link_v1',
    ));
    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        'wp_seed_content_directory_contacts_present' => '1',
        'seed_directory_contacts' => $invalid_repeatable,
        '_seed_directory_publication_authorized' => '1',
    );
    wp_update_post(array('ID' => $repeatable_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($repeatable_id), 'Invalid repeatable website save #1 remains published');
    $repeatable_pending = wp_seed_content_directory_get_pending_validation($repeatable_id);
    seed_l3_wp_same('pending', $repeatable_pending['status'], 'Invalid repeatable website stores pending state');
    seed_l3_wp_same('website', $repeatable_pending['errors'][0]['type'], 'Repeatable warning identifies website type');
    seed_l3_wp_same('website-primary', $repeatable_pending['errors'][0]['row_id'], 'Repeatable warning identifies exact row');
    seed_l3_wp_assert(false === strpos(serialize($repeatable_pending), 'htps://example.test'), 'Pending state does not copy invalid private value');

    $invalid_repeatable[0]['value'] = 'still-invalid';
    $_POST['seed_directory_contacts'] = $invalid_repeatable;
    wp_update_post(array('ID' => $repeatable_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($repeatable_id), 'Different invalid website starts a fresh warning cycle');
    wp_update_post(array('ID' => $repeatable_id, 'post_status' => 'publish'));
    seed_l3_wp_same('draft', get_post_status($repeatable_id), 'Same changed invalid website save #2 becomes draft');

    $invalid_repeatable[0]['value'] = 'https://example.test';
    $_POST['seed_directory_contacts'] = $invalid_repeatable;
    wp_update_post(array('ID' => $repeatable_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($repeatable_id), 'Corrected repeatable website republishes draft');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_pending_validation($repeatable_id), 'Corrected repeatable website clears pending state');
    $_POST = array();

    $split_id = seed_l3_wp_create_entry(array(
        'post_title' => 'SEED L3 GUTENBERG SPLIT SAVE',
        'post_status' => 'publish',
    ));
    $split_contacts = array(array(
        'row_id' => 'website-split',
        'type' => 'website',
        'label' => 'Site internet',
        'value' => 'www.example.test',
        'public' => '1',
        'order' => 10,
        'format' => 'full_link_v1',
    ));
    $split_meta_post = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        'wp_seed_content_directory_contacts_present' => '1',
        'seed_directory_contacts' => $split_contacts,
        '_seed_directory_publication_authorized' => '1',
    );
    $_POST = array();
    $split_rest_first = new WP_REST_Request('PUT', '/wp/v2/seed_directory/' . $split_id);
    $split_rest_first->set_param('status', 'publish');
    $split_rest_first_response = rest_do_request($split_rest_first);
    seed_l3_wp_same(200, $split_rest_first_response->get_status(), 'Gutenberg REST save #1 succeeds before separate metabox update');
    seed_l3_wp_same('publish', $split_rest_first_response->get_data()['status'], 'Gutenberg REST save #1 returns published status');
    $_POST = $split_meta_post;
    wp_seed_content_directory_save_meta($split_id, get_post($split_id));
    wp_seed_content_directory_after_insert_post($split_id, get_post($split_id), true, get_post($split_id));
    seed_l3_wp_same('publish', get_post_status($split_id), 'Separate Gutenberg metabox save #1 keeps DB status published');
    seed_l3_wp_same('pending', wp_seed_content_directory_get_pending_validation($split_id)['status'], 'Separate Gutenberg metabox save #1 stores pending warning');
    $_POST = array();
    $split_get_first = new WP_REST_Request('GET', '/wp/v2/seed_directory/' . $split_id);
    $split_get_first->set_param('context', 'edit');
    $split_get_first_response = rest_do_request($split_get_first);
    $split_get_first_data = $split_get_first_response->get_data();
    seed_l3_wp_same('publish', $split_get_first_data['status'], 'Post-save REST refresh returns published status after warning-first save');
    seed_l3_wp_same('pending', $split_get_first_data['wpsck_directory_validation']['state'], 'Post-save REST refresh returns warning state');
    seed_l3_wp_assert(false !== strpos(implode(' ', $split_get_first_data['wpsck_directory_validation']['messages']), 'Site'), 'Post-save REST warning identifies website field');

    $split_rest_second = new WP_REST_Request('PUT', '/wp/v2/seed_directory/' . $split_id);
    $split_rest_second->set_param('status', 'publish');
    $split_rest_second_response = rest_do_request($split_rest_second);
    $split_rest_second_data = $split_rest_second_response->get_data();
    seed_l3_wp_same(200, $split_rest_second_response->get_status(), 'Gutenberg REST save #2 returns a controlled response');
    seed_l3_wp_same('draft', $split_rest_second_data['status'], 'Gutenberg REST save #2 returns draft status for unchanged invalid value');
    seed_l3_wp_same('drafted', $split_rest_second_data['wpsck_directory_validation']['state'], 'Gutenberg REST save #2 returns explicit depublication reason');
    seed_l3_wp_same('draft', get_post_status($split_id), 'Gutenberg REST save #2 drafts unchanged invalid entry in DB');

    $split_rest_blocked = new WP_REST_Request('PUT', '/wp/v2/seed_directory/' . $split_id);
    $split_rest_blocked->set_param('status', 'publish');
    $split_rest_blocked_response = rest_do_request($split_rest_blocked);
    $split_rest_blocked_data = $split_rest_blocked_response->get_data();
    seed_l3_wp_same('draft', $split_rest_blocked_data['status'], 'Invalid draft remains draft when Gutenberg requests publication');
    seed_l3_wp_same('blocked', $split_rest_blocked_data['wpsck_directory_validation']['state'], 'Invalid draft publication returns explicit blocking state');
    seed_l3_wp_assert(false !== strpos(implode(' ', $split_rest_blocked_data['wpsck_directory_validation']['messages']), 'Site'), 'Publication block identifies website field');

    $split_contacts[0]['value'] = 'https://example.test';
    $split_meta_post['seed_directory_contacts'] = $split_contacts;
    $_POST = $split_meta_post;
    wp_seed_content_directory_save_meta($split_id, get_post($split_id));
    wp_seed_content_directory_after_insert_post($split_id, get_post($split_id), true, get_post($split_id));
    seed_l3_wp_same('draft', get_post_status($split_id), 'Corrected metabox save remains draft until the captured publish intent is finalized');
    seed_l3_wp_same(array(), wp_seed_content_directory_get_pending_validation($split_id), 'Corrected metabox save clears the pending or blocked marker immediately');
    $split_get_corrected = new WP_REST_Request('GET', '/wp/v2/seed_directory/' . $split_id);
    $split_get_corrected->set_param('context', 'edit');
    $split_get_corrected_response = rest_do_request($split_get_corrected);
    $split_get_corrected_data = $split_get_corrected_response->get_data();
    seed_l3_wp_same('draft', $split_get_corrected_data['status'], 'Post-metabox REST refresh sees the corrected draft before finalization');
    seed_l3_wp_same('none', $split_get_corrected_data['wpsck_directory_validation']['state'], 'Post-metabox REST refresh is clean and permits same-click finalization');
    $split_rest_fixed = new WP_REST_Request('PUT', '/wp/v2/seed_directory/' . $split_id);
    $split_rest_fixed->set_param('status', 'publish');
    $split_rest_fixed_response = rest_do_request($split_rest_fixed);
    $split_rest_fixed_data = $split_rest_fixed_response->get_data();
    seed_l3_wp_same('publish', $split_rest_fixed_data['status'], 'Same-click Core Data finalization publishes the corrected fixture');
    seed_l3_wp_same('none', $split_rest_fixed_data['wpsck_directory_validation']['state'], 'Same-click publication response remains validation-clean');
    seed_l3_wp_same('publish', get_post_status($split_id), 'Same-click publication finalization persists published status in DB');
    $_POST = array();

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

    $editor_id = wp_create_user('seed_l3_editor_' . wp_generate_password(8, false), wp_generate_password(24), '');
    if (is_wp_error($editor_id)) {
        throw new RuntimeException($editor_id->get_error_message());
    }
    $created_users[] = (int) $editor_id;
    (new WP_User($editor_id))->set_role('editor');
    wp_set_current_user($editor_id);
    seed_l3_wp_assert(current_user_can('edit_seed_directory_entries'), 'Editor can access Directory entries');
    seed_l3_wp_assert(current_user_can('publish_seed_directory_entries'), 'Editor can publish Directory entries');
    seed_l3_wp_assert(current_user_can('edit_seed_directory_entry', $internal_id), 'Editor can edit another author Directory entry');
    seed_l3_wp_same(false, current_user_can('manage_options'), 'Editor cannot access global configuration');
    $editor_entry_id = seed_l3_wp_create_entry(array('post_title' => 'SEED L3 EDITOR ENTRY', 'post_status' => 'draft'));
    seed_l3_wp_same('draft', get_post_status($editor_entry_id), 'Editor creates incomplete or complete draft');
    wp_update_post(array('ID' => $editor_entry_id, 'post_status' => 'publish'));
    seed_l3_wp_same('publish', get_post_status($editor_entry_id), 'Editor publishes valid entry');
    wp_update_post(array('ID' => $internal_id, 'post_title' => 'SEED L3 EDITED BY EDITOR'));
    seed_l3_wp_same('SEED L3 EDITED BY EDITOR', get_post($internal_id)->post_title, 'Editor updates entry created by another user');
    wp_update_post(array('ID' => $editor_entry_id, 'post_status' => 'draft'));
    seed_l3_wp_same('draft', get_post_status($editor_entry_id), 'Editor unpublishes entry');
    wp_trash_post($editor_entry_id);
    seed_l3_wp_same('trash', get_post_status($editor_entry_id), 'Editor trashes entry');
    wp_untrash_post($editor_entry_id);
    seed_l3_wp_same('draft', get_post_status($editor_entry_id), 'Editor restores entry');
    wp_set_current_user((int) $administrators[0]);
    seed_l3_wp_assert(current_user_can('manage_options'), 'Administrator keeps global configuration access');
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

    ob_start();
    wp_seed_content_directory_admin_filters('seed_directory');
    $filter_html = ob_get_clean();
    seed_l3_wp_assert(false !== strpos($filter_html, 'directory-professional-status-filter'), 'Professional status admin filter rendered');
    $columns = apply_filters('manage_seed_directory_posts_columns', array('cb' => 'Select', 'title' => 'Title', 'date' => 'Date'));
    seed_l3_wp_same(array('cb', 'directory_photo', 'title', 'directory_status', 'directory_city', 'directory_department', 'directory_authorized', 'directory_public_contacts', 'directory_wp_state', 'date'), array_keys($columns), 'Exact admin columns');
    ob_start();
    foreach (array('directory_photo', 'directory_status', 'directory_city', 'directory_department', 'directory_authorized', 'directory_public_contacts', 'directory_wp_state') as $column) {
        do_action('manage_seed_directory_posts_custom_column', $column, $internal_id);
    }
    $column_html = ob_get_clean();
    seed_l3_wp_assert(false === strpos($column_html, 'internal@example.test'), 'Private email absent from columns');
    seed_l3_wp_assert(false === strpos($column_html, 'Note strictement interne'), 'Internal note absent from columns');

    set_current_screen('seed_directory');
    do_action('add_meta_boxes_seed_directory', get_post($internal_id));
    global $wp_meta_boxes;
    $directory_boxes = is_array($wp_meta_boxes) ? $wp_meta_boxes : array();
    $expected_boxes = array(
        'wp_seed_content_directory_identity',
        'wp_seed_content_directory_profile',
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
    seed_l3_wp_same($expected_boxes, array_values(array_intersect($expected_boxes, array_keys($found_boxes))), 'Exactly five Directory custom panels: ' . serialize(array_keys($found_boxes)));

    ob_start();
    wp_seed_content_directory_admin_styles();
    $directory_admin_css = ob_get_clean();
    seed_l3_wp_assert(false !== strpos($directory_admin_css, '#wp_seed_content_directory_situation .regular-text,#wp_seed_content_directory_contacts .regular-text{display:block;width:100%;box-sizing:border-box}'), 'Directory edit screen receives scoped fluid input CSS');
    seed_l3_wp_assert(false !== strpos($directory_admin_css, '@media(max-width:782px){#wp_seed_content_directory_situation .regular-text,#wp_seed_content_directory_contacts .regular-text{max-width:100%}'), 'Directory edit screen receives the mobile width override');
    seed_l3_wp_assert(false === strpos($directory_admin_css, '<style>.regular-text{'), 'Directory edit CSS has no global regular-text override');
    set_current_screen('seed_quote');
    ob_start();
    wp_seed_content_directory_admin_styles();
    seed_l3_wp_same('', ob_get_clean(), 'Directory edit CSS is absent from Citation screens');
    set_current_screen('seed_directory');

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
    seed_l3_wp_same(true, shortcode_exists('seed_directory'), 'L4 canonical Directory shortcode registered');
    seed_l3_wp_same(true, shortcode_exists('wp_seed_directory'), 'L4 Directory compatibility alias registered');
    $routes = rest_get_server()->get_routes();
    $directory_routes = array_filter(array_keys($routes), function ($route) {
        return false !== strpos($route, 'seed_directory');
    });
    seed_l3_wp_assert(in_array('/wp/v2/seed_directory', array_values($directory_routes), true), 'Core Directory REST route registered');
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
