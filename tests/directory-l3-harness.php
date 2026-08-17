<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');

$GLOBALS['seed_l3_assertions'] = 0;
$GLOBALS['seed_l3_failures'] = array();
$GLOBALS['seed_l3_posts'] = array();
$GLOBALS['seed_l3_meta'] = array();
$GLOBALS['seed_l3_images'] = array();
$GLOBALS['seed_l3_urls'] = array();
$GLOBALS['seed_l3_caps'] = true;
$GLOBALS['seed_l3_hooks'] = array('actions' => array(), 'filters' => array());
$GLOBALS['seed_l3_meta_boxes'] = array();
$GLOBALS['seed_l3_registered_meta'] = array();
$GLOBALS['seed_l3_rest_fields'] = array();
$GLOBALS['seed_l3_options'] = array(
    'wp_seed_content_directory_contact_type_settings' => array(
        array(
            'slug' => 'website',
            'label' => 'Site internet',
            'behavior' => 'url',
            'active' => true,
            'order' => 30,
            'individual_provider' => true,
        ),
    ),
);

class WP_Error
{
    public $code;
    public function __construct($code)
    {
        $this->code = $code;
    }
}
class WP_Query
{
    public $is_admin = false;
    public function is_main_query()
    {
        return true;
    }
    public function get($key)
    {
        return '';
    }
    public function set($key, $value)
    {
    }
}

function seed_l3_assert($condition, $label)
{
    $GLOBALS['seed_l3_assertions']++;
    if (!$condition) {
        $GLOBALS['seed_l3_failures'][] = $label;
    }
}
function seed_l3_same($expected, $actual, $label)
{
    seed_l3_assert($expected === $actual, $label);
}
function __($text, $domain = null)
{
    return $text;
}
function sanitize_text_field($value)
{
    return trim(strip_tags((string) $value));
}
function sanitize_textarea_field($value)
{
    return trim(strip_tags((string) $value));
}
function sanitize_key($value)
{
    return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value));
}
function sanitize_email($value)
{
    return filter_var((string) $value, FILTER_SANITIZE_EMAIL);
}
function is_email($value)
{
    return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
}
function esc_url_raw($value, $protocols = null)
{
    return false !== filter_var($value, FILTER_VALIDATE_URL) ? (string) $value : '';
}
function wp_parse_url($value)
{
    return parse_url($value);
}
function checkdate_stub($month, $day, $year)
{
    return checkdate($month, $day, $year);
}
function absint($value)
{
    return abs((int) $value);
}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['seed_l3_hooks']['actions'][$hook][] = array($callback, $priority, $accepted_args);
}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    $GLOBALS['seed_l3_hooks']['filters'][$hook][] = array($callback, $priority, $accepted_args);
}
function add_shortcode($tag, $callback)
{
    $GLOBALS['seed_l3_hooks']['shortcodes'][$tag] = $callback;
}
function get_post($post_id)
{
    return isset($GLOBALS['seed_l3_posts'][$post_id]) ? $GLOBALS['seed_l3_posts'][$post_id] : null;
}
function get_option($key, $default = false)
{
    return array_key_exists($key, $GLOBALS['seed_l3_options']) ? $GLOBALS['seed_l3_options'][$key] : $default;
}
function get_post_meta($post_id, $key, $single = false)
{
    return isset($GLOBALS['seed_l3_meta'][$post_id][$key]) ? $GLOBALS['seed_l3_meta'][$post_id][$key] : '';
}
function metadata_exists($type, $post_id, $key)
{
    return isset($GLOBALS['seed_l3_meta'][$post_id]) && array_key_exists($key, $GLOBALS['seed_l3_meta'][$post_id]);
}
function get_post_thumbnail_id($post_id)
{
    return isset($GLOBALS['seed_l3_meta'][$post_id]['_thumbnail_id']) ? (int) $GLOBALS['seed_l3_meta'][$post_id]['_thumbnail_id'] : 0;
}
function wp_get_attachment_url($attachment_id)
{
    return isset($GLOBALS['seed_l3_urls'][$attachment_id]) ? $GLOBALS['seed_l3_urls'][$attachment_id] : false;
}
function wp_attachment_is_image($attachment_id)
{
    return !empty($GLOBALS['seed_l3_images'][$attachment_id]);
}
function current_user_can($capability, $post_id = 0)
{
    return $GLOBALS['seed_l3_caps'];
}
function get_current_user_id()
{
    return 1;
}
function set_transient($key, $value, $expiration)
{
}
function wp_update_post($data)
{
    return isset($data['ID']) ? $data['ID'] : 0;
}
function update_post_meta($post_id, $key, $value)
{
    $GLOBALS['seed_l3_meta'][$post_id][$key] = $value;
    return true;
}
function delete_post_meta($post_id, $key)
{
    unset($GLOBALS['seed_l3_meta'][$post_id][$key]);
    return true;
}
function wp_is_post_revision($post_id)
{
    return false;
}
function wp_verify_nonce($nonce, $action)
{
    return 'valid' === $nonce;
}
function wp_unslash($value)
{
    return $value;
}
function get_role($role)
{
    return null;
}
function add_meta_box($id, $title, $callback, $screen, $context, $priority)
{
    $GLOBALS['seed_l3_meta_boxes'][$id] = array($title, $callback, $screen, $context, $priority);
}
function remove_meta_box($id, $screen, $context)
{
}
function register_post_meta($post_type, $key, $args)
{
    $GLOBALS['seed_l3_registered_meta'][$key] = $args;
}
function register_rest_field($post_type, $field, $args)
{
    $GLOBALS['seed_l3_rest_fields'][$post_type][$field] = $args;
}
function register_post_type($post_type, $args)
{
    return (object) $args;
}
function wp_seed_content_kit_register_manual_order_for_post_type($post_type)
{
}
function wp_seed_content_kit_get_post_type_menu_parent($post_type)
{
    return 'wp-seed-content-kit';
}
function selected($selected, $current, $echo = true)
{
    return $selected === $current ? 'selected="selected"' : '';
}
function checked($checked, $current, $echo = true)
{
    return $checked === $current ? 'checked="checked"' : '';
}
function get_post_field($field, $post_id)
{
    $post = get_post($post_id);
    return $post && isset($post->$field) ? $post->$field : '';
}
function wp_seed_content_sanitize_iso_date($value)
{
    if (!is_scalar($value) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', (string) $value, $matches)) {
        return '';
    }
    return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? (string) $value : '';
}

require WP_SEED_CONTENT_KIT_DIR . 'includes/core/capabilities.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/bootstrap.php';

$expected_keys = array(
    '_seed_directory_status',
    '_seed_directory_profile_types',
    '_seed_directory_seeking_models',
    '_seed_directory_publicly_listed',
    '_seed_directory_city',
    '_seed_directory_postal_code',
    '_seed_directory_department',
    '_seed_directory_country',
    '_seed_directory_featured',
    '_seed_directory_profession',
    '_seed_directory_phone',
    '_seed_directory_phone_visible',
    '_seed_directory_email',
    '_seed_directory_email_visible',
    '_seed_directory_website',
    '_seed_directory_website_visible',
    '_seed_directory_facebook',
    '_seed_directory_facebook_visible',
    '_seed_directory_instagram',
    '_seed_directory_instagram_visible',
    '_seed_directory_publication_authorized',
    '_seed_directory_internal_note',
    '_seed_directory_last_verified',
);
seed_l3_same($expected_keys, array_keys(wp_seed_content_directory_get_meta_definitions()), 'Exact canonical meta definitions');
seed_l3_same(23, count(wp_seed_content_directory_get_meta_definitions()), 'Exact Directory private and legacy meta count');
seed_l3_same(array('en_exercice', 'recherche_modeles'), array_keys(wp_seed_content_directory_get_statuses()), 'Exact statuses');

$cases = array(
    array('_seed_directory_status', 'practicing', 'en_exercice'),
    array('_seed_directory_status', 'other', ''),
    array('_seed_directory_profile_types', array('intervenant', 'invalid', 'praticien', 'intervenant'), array('praticien', 'intervenant')),
    array('_seed_directory_seeking_models', 1, '1'),
    array('_seed_directory_seeking_models', 0, ''),
    array('_seed_directory_publicly_listed', 1, '1'),
    array('_seed_directory_publicly_listed', 0, ''),
    array('_seed_directory_city', ' <b>Paris</b> ', 'Paris'),
    array('_seed_directory_postal_code', '00120', '00120'),
    array('_seed_directory_postal_code', 'AB-01 2', 'AB-01 2'),
    array('_seed_directory_postal_code', '12/34', ''),
    array('_seed_directory_department', '2a', '2A'),
    array('_seed_directory_department', '02', '02'),
    array('_seed_directory_country', 'fr', 'FR'),
    array('_seed_directory_country', 'ZZ', ''),
    array('_seed_directory_featured', 1, '1'),
    array('_seed_directory_featured', 0, ''),
    array('_seed_directory_phone', '+33 (0)1 23 45 67 89', '+33 (0)1 23 45 67 89'),
    array('_seed_directory_phone', '<b>secret</b>', 'secret'),
    array('_seed_directory_email', 'person@example.test', 'person@example.test'),
    array('_seed_directory_email', 'invalid', 'invalid'),
    array('_seed_directory_website', 'https://example.test/path', 'https://example.test/path'),
    array('_seed_directory_website', 'ftp://example.test', 'ftp://example.test'),
    array('_seed_directory_facebook', 'https://www.facebook.com/example', 'https://www.facebook.com/example'),
    array('_seed_directory_facebook', 'https://evil.test/facebook.com', 'https://evil.test/facebook.com'),
    array('_seed_directory_instagram', 'https://instagram.com/example', 'https://instagram.com/example'),
    array('_seed_directory_instagram', 'https://example.test/instagram', 'https://example.test/instagram'),
    array('_seed_directory_last_verified', '2026-02-28', '2026-02-28'),
    array('_seed_directory_last_verified', '2026-02-30', ''),
    array('_seed_directory_internal_note', '<b>Interne</b>', 'Interne'),
);
foreach ($cases as $case) {
    $case_label = is_array($case[1]) ? implode(',', $case[1]) : $case[1];
    seed_l3_same($case[2], wp_seed_content_directory_sanitize_meta_value($case[0], $case[1]), 'Sanitize ' . $case[0] . ' ' . $case_label);
}
seed_l3_same('', wp_seed_content_directory_sanitize_meta_value('_seed_directory_unknown', 'value'), 'Unknown meta rejected');
$filtered_draft = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'draft',
    'post_title' => ' <b>Nom</b> ',
    'post_excerpt' => ' <em>Présentation</em> ',
    'menu_order' => -8,
), array());
seed_l3_same('Nom', $filtered_draft['post_title'], 'Native title sanitized');
seed_l3_same('Présentation', $filtered_draft['post_excerpt'], 'Native excerpt sanitized');
seed_l3_same(0, $filtered_draft['menu_order'], 'Native order clamped to zero');
$_POST = array('wp_seed_content_directory_nonce' => 'valid', '_thumbnail_id' => '-1');
$thumbnail_overrides = wp_seed_content_directory_collect_publication_overrides(array());
seed_l3_same(0, $thumbnail_overrides['_thumbnail_id'], 'No-photo sentinel remains zero');
$_POST = array();

$post_id = 42;
$GLOBALS['seed_l3_posts'][$post_id] = (object) array(
    'ID' => $post_id,
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_password' => '',
    'post_title' => 'Fiche fictive',
    'post_excerpt' => 'Présentation',
    'menu_order' => 3,
);
$GLOBALS['seed_l3_meta'][$post_id] = array(
    '_seed_directory_status' => 'practicing',
    '_seed_directory_country' => 'FR',
    '_seed_directory_publication_authorized' => '1',
    '_seed_directory_publicly_listed' => '1',
    '_seed_directory_phone' => '+33 1 23 45 67 89',
    '_seed_directory_phone_visible' => '1',
    '_seed_directory_email' => 'private@example.test',
    '_seed_directory_email_visible' => '',
    '_seed_directory_website' => 'not-a-url',
    '_seed_directory_website_visible' => '',
    '_seed_directory_internal_note' => 'Strictement interne',
);
seed_l3_same(array(), wp_seed_content_directory_get_publication_errors($post_id), 'Valid entry has no publication errors');
seed_l3_same(true, wp_seed_content_directory_is_publicly_eligible($post_id), 'Valid published entry eligible');
seed_l3_same(array('phone' => '+33 1 23 45 67 89'), wp_seed_content_directory_get_public_contacts($post_id), 'Only valid visible contact returned');
$GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publicly_listed'] = '';
seed_l3_same(false, wp_seed_content_directory_is_publicly_eligible($post_id), 'Missing public listing flag closes eligibility');
$GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publicly_listed'] = '0';
seed_l3_same(false, wp_seed_content_directory_is_publicly_eligible($post_id), 'Non-canonical public listing flag closes eligibility');
$GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publicly_listed'] = '1';

$invalid_public_contacts = array(
    '_seed_directory_phone' => array('letters only', 'invalid_public_phone'),
    '_seed_directory_email' => array('invalid', 'invalid_public_email'),
    '_seed_directory_website' => array('ftp://example.test', 'invalid_public_website'),
    '_seed_directory_facebook' => array('https://example.test/facebook', 'invalid_public_facebook'),
    '_seed_directory_instagram' => array('https://example.test/instagram', 'invalid_public_instagram'),
);
foreach ($invalid_public_contacts as $key => $case) {
    $original_value = isset($GLOBALS['seed_l3_meta'][$post_id][$key]) ? $GLOBALS['seed_l3_meta'][$post_id][$key] : '';
    $original_visibility = isset($GLOBALS['seed_l3_meta'][$post_id][$key . '_visible']) ? $GLOBALS['seed_l3_meta'][$post_id][$key . '_visible'] : '';
    $GLOBALS['seed_l3_meta'][$post_id][$key] = $case[0];
    $GLOBALS['seed_l3_meta'][$post_id][$key . '_visible'] = '1';
    seed_l3_assert(in_array($case[1], wp_seed_content_directory_get_publication_errors($post_id), true), 'Invalid visible contact blocks publication: ' . $key);
    seed_l3_same(false, wp_seed_content_directory_is_publicly_eligible($post_id), 'Invalid visible contact makes entry ineligible: ' . $key);
    seed_l3_same(array(), wp_seed_content_directory_get_public_contacts($post_id), 'Invalid visible contact exposes nothing: ' . $key);
    $GLOBALS['seed_l3_meta'][$post_id][$key] = $original_value;
    $GLOBALS['seed_l3_meta'][$post_id][$key . '_visible'] = $original_visibility;
}
seed_l3_same('invalid', wp_seed_content_directory_sanitize_meta_value('_seed_directory_email', 'invalid'), 'Invalid private contact is retained safely');
seed_l3_same('', wp_seed_content_directory_normalize_contact_value('_seed_directory_email', 'invalid'), 'Invalid private contact is never normalized for public output');

$GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publication_authorized'] = '';
seed_l3_same(false, wp_seed_content_directory_is_publicly_eligible($post_id), 'Authorization required');
seed_l3_same(array(), wp_seed_content_directory_get_public_contacts($post_id), 'Ineligible entry exposes no contact');
$GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publication_authorized'] = '1';

$GLOBALS['seed_l3_posts'][$post_id]->post_password = 'protected';
seed_l3_same(false, wp_seed_content_directory_is_publicly_eligible($post_id), 'Password protected entry ineligible');
$GLOBALS['seed_l3_posts'][$post_id]->post_password = '';

$GLOBALS['seed_l3_meta'][$post_id]['_thumbnail_id'] = 90;
$GLOBALS['seed_l3_images'][90] = true;
$GLOBALS['seed_l3_urls'][90] = 'https://example.test/photo.jpg';
seed_l3_assert(in_array('missing_photo_alt', wp_seed_content_directory_get_publication_errors($post_id), true), 'Photo alt required');
$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = 'Portrait fictif';
seed_l3_same(array(), wp_seed_content_directory_get_publication_errors($post_id), 'Valid image and alt accepted');
$GLOBALS['seed_l3_images'][90] = false;
seed_l3_assert(in_array('invalid_photo', wp_seed_content_directory_get_publication_errors($post_id), true), 'Non-image rejected');
$GLOBALS['seed_l3_images'][90] = true;

$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'draft';
unset($GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt']);
$filtered_publish = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('draft', $filtered_publish['post_status'], 'Draft publication with missing photo alt remains blocked');
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
wp_seed_content_directory_sync_pending_validation($post_id);

$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'publish';
$grandfathered_edits = array(
    'phone' => array('meta_input' => array('_seed_directory_phone' => '+33 1 99 88 77 66')),
    'summary' => array(),
    'professional label' => array('meta_input' => array('seed_directory_professional_label' => 'Praticienne')),
);
foreach ($grandfathered_edits as $edit_label => $postarr) {
    $postarr['ID'] = $post_id;
    $filtered_update = wp_seed_content_directory_filter_insert_post_data(array(
        'post_type' => 'seed_directory',
        'post_status' => 'publish',
        'post_title' => 'Fiche fictive',
        'post_excerpt' => 'Résumé modifié',
    ), $postarr);
    seed_l3_same('publish', $filtered_update['post_status'], 'Published entry keeps status for grandfathered ' . $edit_label . ' edit');
    wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
    seed_l3_same(array('missing_photo_alt'), wp_seed_content_directory_get_validation_warning($post_id), 'Persistent warning stored after ' . $edit_label . ' edit');
}

$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = 'Portrait fictif';
wp_seed_content_directory_sync_validation_warning($post_id, array());
wp_seed_content_directory_sync_pending_validation($post_id);
$_POST = array(
    'wp_seed_content_directory_nonce' => 'valid',
    '_seed_directory_photo_alt' => '',
    '_seed_directory_publication_authorized' => '1',
);
$filtered_new_error = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_new_error['post_status'], 'First new missing photo alt save keeps valid published entry online');
$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = '';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same('pending', wp_seed_content_directory_get_pending_validation($post_id)['status'], 'First correctable error stores a private pending marker');
seed_l3_same('pending', wp_seed_content_directory_get_validation_editor_state($post_id)['state'], 'Persistent editor state exposes first warning');
seed_l3_assert(false === strpos(serialize(wp_seed_content_directory_get_pending_validation($post_id)), 'Portrait'), 'Pending marker stores no submitted value');

$_POST = array('wp_seed_content_directory_nonce' => 'valid', '_seed_directory_photo_alt' => 'Portrait corrigé', '_seed_directory_publication_authorized' => '1');
$filtered_corrected = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_corrected['post_status'], 'Correction between saves preserves publication');
$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = 'Portrait corrigé';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same(array(), wp_seed_content_directory_get_pending_validation($post_id), 'Correction clears pending marker');

$_POST = array('wp_seed_content_directory_nonce' => 'valid', '_seed_directory_photo_alt' => '', '_seed_directory_publication_authorized' => '1');
$filtered_first_again = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_first_again['post_status'], 'A later new correctable error starts a fresh warning cycle');
$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = '';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
$filtered_second_same = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('draft', $filtered_second_same['post_status'], 'Second unchanged invalid save moves entry to draft');
$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'draft';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same('drafted', wp_seed_content_directory_get_validation_editor_state($post_id)['state'], 'Draft transition remains visible persistently in editor state');

$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'publish';
$_POST = array(
    'wp_seed_content_directory_nonce' => 'valid',
    'wp_seed_content_directory_publication_present' => '1',
    '_seed_directory_photo_alt' => 'Portrait fictif',
);
$filtered_revocation = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('draft', $filtered_revocation['post_status'], 'Authorization revocation keeps strict guard');
$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'draft';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);

$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'publish';
$GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publication_authorized'] = '1';
wp_seed_content_directory_sync_pending_validation($post_id);
unset($GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt']);
$_POST = array();
$filtered_grandfathered = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_grandfathered['post_status'], 'Preexisting photo alt error is grandfathered');
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
$_POST = array('wp_seed_content_directory_nonce' => 'valid', '_seed_directory_photo_alt' => 'Portrait corrigé', '_seed_directory_publication_authorized' => '1');
$filtered_fixed = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory',
    'post_status' => 'publish',
    'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_fixed['post_status'], 'Fixing preexisting error keeps published status');
$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = 'Portrait corrigé';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same(array(), wp_seed_content_directory_get_validation_warning($post_id), 'Persistent warning clears after error is fixed');
$_POST = array();

$contact_key = wp_seed_content_directory_contacts_meta_key();
$GLOBALS['seed_l3_meta'][$post_id][$contact_key] = array();
$GLOBALS['seed_l3_meta'][90]['_wp_attachment_image_alt'] = 'Portrait corrigé';
$invalid_contact = array(array('row_id' => 'site-main', 'type' => 'website', 'value' => 'not-a-url', 'public' => '1', 'order' => 10, 'format' => 'full_link_v1'));
$_POST = array(
    'wp_seed_content_directory_nonce' => 'valid',
    'wp_seed_content_directory_contacts_present' => '1',
    'seed_directory_contacts' => $invalid_contact,
    '_seed_directory_publication_authorized' => '1',
);
$filtered_contact_first = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_contact_first['post_status'], 'Invalid repeatable website gets warning-first treatment');
$GLOBALS['seed_l3_meta'][$post_id][$contact_key] = wp_seed_content_directory_sanitize_contacts($invalid_contact);
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
$pending_contact = wp_seed_content_directory_get_pending_validation($post_id);
seed_l3_same('website', $pending_contact['errors'][0]['type'], 'Pending repeatable contact identifies its type');
seed_l3_same('site-main', $pending_contact['errors'][0]['row_id'], 'Pending repeatable contact identifies its row');
seed_l3_assert(false === strpos(serialize($pending_contact), 'not-a-url'), 'Private marker fingerprints invalid website without copying it');

$changed_contact = array(array('row_id' => 'site-main', 'type' => 'website', 'value' => 'still-not-a-url', 'public' => '1', 'order' => 10, 'format' => 'full_link_v1'));
$_POST['seed_directory_contacts'] = $changed_contact;
$filtered_contact_changed = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_contact_changed['post_status'], 'Different invalid value starts a new correction attempt');
$GLOBALS['seed_l3_meta'][$post_id][$contact_key] = wp_seed_content_directory_sanitize_contacts($changed_contact);
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
$filtered_contact_same = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('draft', $filtered_contact_same['post_status'], 'Second save of the same invalid repeatable website drafts entry');
$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'draft';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
$filtered_contact_republish = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('draft', $filtered_contact_republish['post_status'], 'Draft cannot republish with invalid website');

$valid_contact = array(array('row_id' => 'site-main', 'type' => 'website', 'value' => 'https://example.test', 'public' => '1', 'order' => 10, 'format' => 'full_link_v1'));
$_POST['seed_directory_contacts'] = $valid_contact;
$filtered_contact_fixed_publish = wp_seed_content_directory_filter_insert_post_data(array(
    'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Fiche fictive',
), array('ID' => $post_id));
seed_l3_same('publish', $filtered_contact_fixed_publish['post_status'], 'Draft publishes after website correction');
$GLOBALS['seed_l3_meta'][$post_id][$contact_key] = wp_seed_content_directory_sanitize_contacts($valid_contact);
$GLOBALS['seed_l3_posts'][$post_id]->post_status = 'publish';
wp_seed_content_directory_after_insert_post($post_id, $GLOBALS['seed_l3_posts'][$post_id], true, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same(array(), wp_seed_content_directory_get_pending_validation($post_id), 'Successful republish clears pending validation state');
$_POST = array();

$format_cases = array(
    46 => array('email', 'broken-email'),
    47 => array('phone', 'letters-only'),
);
foreach ($format_cases as $case_id => $format_case) {
    $GLOBALS['seed_l3_posts'][$case_id] = (object) array(
        'ID' => $case_id, 'post_type' => 'seed_directory', 'post_status' => 'publish',
        'post_password' => '', 'post_title' => 'Format fixture', 'post_excerpt' => '', 'menu_order' => 0,
    );
    $GLOBALS['seed_l3_meta'][$case_id] = array(
        '_seed_directory_status' => 'practicing',
        '_seed_directory_country' => 'FR',
        '_seed_directory_publication_authorized' => '1',
        $contact_key => array(),
    );
    $format_contacts = array(array(
        'row_id' => 'row-' . $format_case[0], 'type' => $format_case[0],
        'value' => $format_case[1], 'public' => '1', 'order' => 10, 'format' => 'full_link_v1',
    ));
    $_POST = array(
        'wp_seed_content_directory_nonce' => 'valid',
        'wp_seed_content_directory_contacts_present' => '1',
        'seed_directory_contacts' => $format_contacts,
        '_seed_directory_publication_authorized' => '1',
    );
    $format_first = wp_seed_content_directory_filter_insert_post_data(array(
        'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Format fixture',
    ), array('ID' => $case_id));
    seed_l3_same('publish', $format_first['post_status'], 'Invalid ' . $format_case[0] . ' gets warning-first treatment');
    $GLOBALS['seed_l3_meta'][$case_id][$contact_key] = wp_seed_content_directory_sanitize_contacts($format_contacts);
    wp_seed_content_directory_after_insert_post($case_id, $GLOBALS['seed_l3_posts'][$case_id], true, $GLOBALS['seed_l3_posts'][$case_id]);
    $format_second = wp_seed_content_directory_filter_insert_post_data(array(
        'post_type' => 'seed_directory', 'post_status' => 'publish', 'post_title' => 'Format fixture',
    ), array('ID' => $case_id));
    seed_l3_same('draft', $format_second['post_status'], 'Unchanged invalid ' . $format_case[0] . ' drafts on second save');
}
$_POST = array();

$split_id = 48;
$GLOBALS['seed_l3_posts'][$split_id] = (object) array(
    'ID' => $split_id, 'post_type' => 'seed_directory', 'post_status' => 'publish',
    'post_password' => '', 'post_title' => 'Gutenberg split fixture', 'post_excerpt' => '', 'menu_order' => 0,
);
$GLOBALS['seed_l3_meta'][$split_id] = array(
    '_seed_directory_status' => 'practicing',
    '_seed_directory_country' => 'FR',
    '_seed_directory_publication_authorized' => '1',
    $contact_key => array(),
);
$split_contacts = array(array(
    'row_id' => 'website-split', 'type' => 'website', 'value' => 'www.example.test',
    'public' => '1', 'order' => 10, 'format' => 'full_link_v1',
));
$_POST = array(
    'wp_seed_content_directory_nonce' => 'valid',
    'wp_seed_content_directory_contacts_present' => '1',
    'seed_directory_contacts' => $split_contacts,
    '_seed_directory_publication_authorized' => '1',
);
$split_context = wp_seed_content_directory_prepare_meta_save_validation($split_id, $GLOBALS['seed_l3_posts'][$split_id]);
seed_l3_same('warning_first', $split_context['action'], 'Separate Gutenberg metabox save computes warning before contact write');
$GLOBALS['seed_l3_meta'][$split_id][$contact_key] = wp_seed_content_directory_sanitize_contacts($split_contacts);
wp_seed_content_directory_after_insert_post($split_id, $GLOBALS['seed_l3_posts'][$split_id], true, $GLOBALS['seed_l3_posts'][$split_id]);
seed_l3_same('pending', wp_seed_content_directory_get_pending_validation($split_id)['status'], 'Separate Gutenberg metabox save persists warning state');
$split_second = wp_seed_content_directory_prepare_meta_save_validation($split_id, $GLOBALS['seed_l3_posts'][$split_id]);
seed_l3_same('drafted', $split_second['action'], 'Second separate Gutenberg metabox save detects unchanged error');
wp_seed_content_directory_validation_request_context($split_id, null, true);
$_POST = array();

wp_seed_content_directory_register_validation_rest_field();
seed_l3_assert(isset($GLOBALS['seed_l3_rest_fields']['seed_directory']['wpsck_directory_validation']), 'Private validation state has an edit-context REST projection');
seed_l3_assert(!isset($GLOBALS['seed_l3_registered_meta'][wp_seed_content_directory_pending_validation_meta_key()]), 'Private pending marker is not registered as public post meta');
$editor_script = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'assets/js/directory-editor-validation.js');
seed_l3_assert(false !== strpos($editor_script, 'PluginPostStatusInfo'), 'Gutenberg displays persistent validation state in post status UI');
seed_l3_assert(false !== strpos($editor_script, 'isDismissible: false'), 'Gutenberg warning cannot disappear as a transient dismissal');
seed_l3_assert(false !== strpos($editor_script, 'wp.apiFetch'), 'Gutenberg refreshes validation state after the real save cycle');
seed_l3_assert(false !== strpos($editor_script, 'isSavingPost'), 'Gutenberg refresh waits for post save completion');
seed_l3_assert(false !== strpos($editor_script, "addFilter('editor.preSavePost'"), 'Gutenberg records the explicit publish intent before the REST save resets editor state');
seed_l3_assert(false !== strpos($editor_script, "addAction('editor.savePost'"), 'Gutenberg finalizes corrected publication after metabox saves');
seed_l3_assert(false !== strpos($editor_script, '}, 20);'), 'Corrected publication runs after the Core metabox save action');
seed_l3_assert(false !== strpos($editor_script, "'blocked' === validation.state || 'drafted' === validation.state"), 'Only a blocked WPSCK publication can enter corrected publish finalization');
seed_l3_assert(false !== strpos($editor_script, "'none' !== record.wpsck_directory_validation.state"), 'Gutenberg refuses final publication until the post-save validation state is clean');
seed_l3_assert(false !== strpos($editor_script, "dispatch('core').saveEntityRecord("), 'Corrected publish finalization uses the native WordPress Core Data store');
seed_l3_assert(false !== strpos($editor_script, "'postType',\n                'seed_directory'"), 'Corrected publish finalization targets the directory post type');
seed_l3_assert(false !== strpos(file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/admin.php'), "'wp-core-data'"), 'Gutenberg validation declares its Core Data dependency');
seed_l3_assert(false !== strpos(file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/admin.php'), "'wp-hooks'"), 'Gutenberg validation declares its Hooks dependency');

$GLOBALS['seed_l3_caps'] = true;
$admin_data = wp_seed_content_directory_get_admin_data($post_id);
seed_l3_same('Strictement interne', $admin_data['internal_note'], 'Authorized admin receives internal note');
seed_l3_same('private@example.test', $admin_data['email'], 'Authorized admin receives private contact');
$GLOBALS['seed_l3_caps'] = false;
seed_l3_assert(wp_seed_content_directory_get_admin_data($post_id) instanceof WP_Error, 'Unauthorized admin data denied');
$GLOBALS['seed_l3_caps'] = true;

$saved_status = $GLOBALS['seed_l3_posts'][$post_id]->post_status;
$_POST = array('wp_seed_content_directory_nonce' => 'valid');
wp_seed_content_directory_save_meta($post_id, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same('1', $GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publicly_listed'], 'Partial save preserves public listing flag');
$_POST = array('wp_seed_content_directory_nonce' => 'valid', 'wp_seed_content_directory_publication_present' => '1', '_seed_directory_publication_authorized' => '1');
wp_seed_content_directory_save_meta($post_id, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_assert(!isset($GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publicly_listed']), 'Unchecked public listing flag is deleted');
seed_l3_same($saved_status, $GLOBALS['seed_l3_posts'][$post_id]->post_status, 'Unchecking public listing does not change WordPress status');
$_POST['_seed_directory_publicly_listed'] = '1';
wp_seed_content_directory_save_meta($post_id, $GLOBALS['seed_l3_posts'][$post_id]);
seed_l3_same('1', $GLOBALS['seed_l3_meta'][$post_id]['_seed_directory_publicly_listed'], 'Checked public listing flag stores canonical one');
seed_l3_same($saved_status, $GLOBALS['seed_l3_posts'][$post_id]->post_status, 'Checking public listing does not change WordPress status');
$_POST = array();

$columns = wp_seed_content_directory_columns(array('cb' => 'Select', 'title' => 'Title', 'date' => 'Date'));
seed_l3_same(array('cb', 'directory_photo', 'title', 'directory_status', 'directory_city', 'directory_department', 'directory_authorized', 'directory_public_contacts', 'directory_wp_state', 'date'), array_keys($columns), 'Exact admin columns');
wp_seed_content_directory_add_meta_boxes();
seed_l3_same(5, count($GLOBALS['seed_l3_meta_boxes']), 'Exactly five custom panels');
wp_seed_content_directory_register_post_type();
seed_l3_same(33, count($GLOBALS['seed_l3_registered_meta']), 'Private workflow, repeatable contacts and portable public meta registered');
foreach ($GLOBALS['seed_l3_registered_meta'] as $key => $registered) {
    $expected_rest = '_' !== substr($key, 0, 1) && 'seed_directory_contacts' !== $key;
    seed_l3_same($expected_rest, (bool) $registered['show_in_rest'], $key . ' REST visibility');
}
seed_l3_assert(isset($GLOBALS['seed_l3_hooks']['filters']['wp_insert_post_data']), 'Pre-write publication guard registered');
seed_l3_assert(isset($GLOBALS['seed_l3_hooks']['actions']['wp_after_insert_post']), 'Post-write publication guard registered');
seed_l3_assert(isset($GLOBALS['seed_l3_hooks']['actions']['transition_post_status']), 'Scheduled publication guard registered');
seed_l3_assert(isset($GLOBALS['seed_l3_hooks']['actions']['save_post_seed_directory']), 'Scoped save hook registered');
seed_l3_assert(isset($GLOBALS['seed_l3_hooks']['filters']['manage_seed_directory_posts_columns']), 'Columns hook registered');

$admin_source = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/admin.php');
seed_l3_assert(false !== strpos($admin_source, 'wp_verify_nonce'), 'Save requires nonce');
seed_l3_assert(false !== strpos($admin_source, 'DOING_AUTOSAVE'), 'Save ignores autosave');
seed_l3_assert(false !== strpos($admin_source, 'wp_is_post_revision'), 'Save ignores revision rows');
seed_l3_assert(false !== strpos($admin_source, "current_user_can('edit_seed_directory_entry'"), 'Save requires object capability');
seed_l3_assert(false !== strpos($admin_source, "Classement"), 'Classification panel title is explicit');
seed_l3_assert(false !== strpos($admin_source, "_seed_directory_profile_types[]"), 'Profile panel supports multiple types');
seed_l3_assert(false === strpos($admin_source, "Recherche actuellement des modèles"), 'Classification panel hides legacy seeking control');
seed_l3_assert(false !== strpos($admin_source, "wp_seed_content_directory_profile_present"), 'Profile panel has a partial-save marker');
seed_l3_assert(false !== strpos($admin_source, 'if ($profile_panel_present)'), 'Partial saves protect canonical classification fields');
seed_l3_assert(false !== strpos($admin_source, "current_user_can('edit_seed_directory_entry'"), 'Editor capability protects profile fields');
seed_l3_assert(false !== strpos($admin_source, 'La personne a autorisé la publication de ses informations'), 'Exact authorization label');
seed_l3_assert(false !== strpos($admin_source, 'Cette autorisation est obligatoire pour publier la fiche'), 'Authorization help text');
seed_l3_assert(false !== strpos($admin_source, 'wp_seed_content_directory_get_validation_editor_state'), 'Persistent two-step and grandfathered state is rendered in the editor');
seed_l3_assert(false !== strpos($admin_source, '#wp_seed_content_directory_situation .regular-text,#wp_seed_content_directory_contacts .regular-text{display:block;width:100%;box-sizing:border-box}'), 'Directory text inputs use scoped fluid sizing');
seed_l3_assert(false !== strpos($admin_source, '#wp_seed_content_directory_situation .regular-text{max-width:400px}'), 'Situation fields preserve the desktop width cap');
seed_l3_assert(false !== strpos($admin_source, '@media(max-width:782px){#wp_seed_content_directory_situation .regular-text,#wp_seed_content_directory_contacts .regular-text{max-width:100%}'), 'Directory text inputs become fluid at the WordPress mobile breakpoint');
seed_l3_assert(false === strpos($admin_source, '<style>.regular-text{'), 'Directory admin styles do not override regular-text globally');
$bootstrap_source = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/bootstrap.php');
seed_l3_assert(false !== strpos($bootstrap_source, "require_once __DIR__ . '/shortcode.php'"), 'L4 Directory shortcode loaded');
seed_l3_assert(false === strpos($bootstrap_source, 'register_rest_route'), 'No Directory REST route');
seed_l3_assert(false === function_exists('wp_seed_content_directory_render'), 'No complete Directory renderer');
seed_l3_assert(true === function_exists('wp_seed_content_directory_get_public_data'), 'L4 public Directory Data API available');
seed_l3_assert(true === function_exists('wp_seed_content_directory_get_entries'), 'L4 public Directory Collection available');

if (!empty($GLOBALS['seed_l3_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_l3_failures']) . ' / ' . $GLOBALS['seed_l3_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_l3_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_l3_assertions'] . ' Annuaire L3 assertions' . PHP_EOL;
