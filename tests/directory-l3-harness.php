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
function get_post($post_id)
{
    return isset($GLOBALS['seed_l3_posts'][$post_id]) ? $GLOBALS['seed_l3_posts'][$post_id] : null;
}
function get_post_meta($post_id, $key, $single = false)
{
    return isset($GLOBALS['seed_l3_meta'][$post_id][$key]) ? $GLOBALS['seed_l3_meta'][$post_id][$key] : '';
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

require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/bootstrap.php';

$expected_keys = array(
    '_seed_directory_status',
    '_seed_directory_city',
    '_seed_directory_postal_code',
    '_seed_directory_department',
    '_seed_directory_country',
    '_seed_directory_featured',
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
seed_l3_same(19, count(wp_seed_content_directory_get_meta_definitions()), 'No extra business meta');
seed_l3_same(array('practicing', 'seeking_models'), array_keys(wp_seed_content_directory_get_statuses()), 'Exact statuses');

$cases = array(
    array('_seed_directory_status', 'practicing', 'practicing'),
    array('_seed_directory_status', 'other', ''),
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
    array('_seed_directory_phone', '<b>secret</b>', ''),
    array('_seed_directory_email', 'person@example.test', 'person@example.test'),
    array('_seed_directory_email', 'invalid', ''),
    array('_seed_directory_website', 'https://example.test/path', 'https://example.test/path'),
    array('_seed_directory_website', 'ftp://example.test', ''),
    array('_seed_directory_facebook', 'https://www.facebook.com/example', 'https://www.facebook.com/example'),
    array('_seed_directory_facebook', 'https://evil.test/facebook.com', ''),
    array('_seed_directory_instagram', 'https://instagram.com/example', 'https://instagram.com/example'),
    array('_seed_directory_instagram', 'https://example.test/instagram', ''),
    array('_seed_directory_last_verified', '2026-02-28', '2026-02-28'),
    array('_seed_directory_last_verified', '2026-02-30', ''),
    array('_seed_directory_internal_note', '<b>Interne</b>', 'Interne'),
);
foreach ($cases as $case) {
    seed_l3_same($case[2], wp_seed_content_directory_sanitize_meta_value($case[0], $case[1]), 'Sanitize ' . $case[0] . ' ' . $case[1]);
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
    '_seed_directory_phone' => '+33 1 23 45 67 89',
    '_seed_directory_phone_visible' => '1',
    '_seed_directory_email' => 'private@example.test',
    '_seed_directory_email_visible' => '',
    '_seed_directory_website' => 'not-a-url',
    '_seed_directory_website_visible' => '1',
    '_seed_directory_internal_note' => 'Strictement interne',
);
seed_l3_same(array(), wp_seed_content_directory_get_publication_errors($post_id), 'Valid entry has no publication errors');
seed_l3_same(true, wp_seed_content_directory_is_publicly_eligible($post_id), 'Valid published entry eligible');
seed_l3_same(array('phone' => '+33 1 23 45 67 89'), wp_seed_content_directory_get_public_contacts($post_id), 'Only valid visible contact returned');

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

$GLOBALS['seed_l3_caps'] = true;
$admin_data = wp_seed_content_directory_get_admin_data($post_id);
seed_l3_same('Strictement interne', $admin_data['internal_note'], 'Authorized admin receives internal note');
seed_l3_same('private@example.test', $admin_data['email'], 'Authorized admin receives private contact');
$GLOBALS['seed_l3_caps'] = false;
seed_l3_assert(wp_seed_content_directory_get_admin_data($post_id) instanceof WP_Error, 'Unauthorized admin data denied');
$GLOBALS['seed_l3_caps'] = true;

$columns = wp_seed_content_directory_columns(array('cb' => 'Select', 'title' => 'Title', 'date' => 'Date'));
seed_l3_same(array('cb', 'directory_photo', 'title', 'directory_status', 'directory_location', 'directory_authorized', 'directory_order', 'date'), array_keys($columns), 'Exact admin columns');
wp_seed_content_directory_add_meta_boxes();
seed_l3_same(4, count($GLOBALS['seed_l3_meta_boxes']), 'Exactly four custom panels');
wp_seed_content_directory_register_post_type();
seed_l3_same(19, count($GLOBALS['seed_l3_registered_meta']), 'All canonical meta registered');
foreach ($GLOBALS['seed_l3_registered_meta'] as $registered) {
    seed_l3_same(false, $registered['show_in_rest'], 'Registered meta remains private');
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
seed_l3_assert(false !== strpos($admin_source, 'Autorisation de publication obtenue'), 'Exact authorization label');
seed_l3_assert(false !== strpos($admin_source, 'Confirme que la personne a autorisé la publication'), 'Authorization help text');
$bootstrap_source = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/bootstrap.php');
seed_l3_assert(false === strpos($bootstrap_source, 'add_shortcode'), 'No Directory shortcode');
seed_l3_assert(false === strpos($bootstrap_source, 'register_rest_route'), 'No Directory REST route');
seed_l3_assert(false === function_exists('wp_seed_content_directory_render'), 'No complete Directory renderer');
seed_l3_assert(false === function_exists('wp_seed_content_directory_get_entries'), 'No public Directory Collection');

if (!empty($GLOBALS['seed_l3_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_l3_failures']) . ' / ' . $GLOBALS['seed_l3_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_l3_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_l3_assertions'] . ' Annuaire L3 assertions' . PHP_EOL;
