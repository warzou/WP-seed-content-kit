<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');

$GLOBALS['dpt_assertions'] = 0;
$GLOBALS['dpt_failures'] = array();
$GLOBALS['dpt_posts'] = array();
$GLOBALS['dpt_meta'] = array();
$GLOBALS['dpt_options'] = array();
$GLOBALS['dpt_shortcodes'] = array();
$GLOBALS['dpt_template_module'] = array();

class WP_Post
{
    public $ID;
    public $post_type = 'seed_directory';
    public $post_status = 'publish';
    public $post_password = '';
    public $post_title = '';
    public $post_excerpt = 'Fictional public profile.';
    public $post_content = '';
    public $post_date = '2026-07-26 00:00:00';
    public $menu_order = 0;

    public function __construct($id, $title, $status = 'publish')
    {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_status = $status;
        $this->menu_order = $id;
    }
}

function dpt_assert($condition, $label)
{
    $GLOBALS['dpt_assertions']++;
    if (!$condition) {
        $GLOBALS['dpt_failures'][] = $label;
    }
}

function dpt_same($expected, $actual, $label)
{
    dpt_assert($expected === $actual, $label . ' (got ' . var_export($actual, true) . ')');
}

function __($text, $domain = null) { return $text; }
function esc_html__($text, $domain = null) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function strip_shortcodes($value) { return preg_replace('/\[[^\]]+\]/', '', (string) $value); }
function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $value)); }
function sanitize_title($value) { return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $value)), '-'); }
function sanitize_email($value) { return filter_var((string) $value, FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function esc_url_raw($value, $protocols = null) { return false !== filter_var($value, FILTER_VALIDATE_URL) ? (string) $value : ''; }
function wp_parse_url($value) { return parse_url($value); }
function absint($value) { return abs((int) $value); }
function remove_accents($value) { return strtr($value, array('é' => 'e', 'É' => 'E')); }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
function apply_filters($hook, $value) { return $value; }
function add_shortcode($tag, $callback) { $GLOBALS['dpt_shortcodes'][$tag] = $callback; }
function shortcode_atts($defaults, $atts, $tag = '') { return array_merge($defaults, is_array($atts) ? $atts : array()); }
function wp_seed_content_kit_is_module_active($module) { return 'directory' === $module; }
function current_user_can($capability, $post_id = 0) { return false; }
function get_post($post_id) { return isset($GLOBALS['dpt_posts'][$post_id]) ? $GLOBALS['dpt_posts'][$post_id] : null; }
function get_post_thumbnail_id($post_id) { return 0; }
function wp_get_attachment_image_src($attachment_id, $size) { return false; }
function wp_seed_content_sanitize_iso_date($value) { return preg_match('/^\d{4}-\d{2}-\d{2}$/D', (string) $value) ? (string) $value : ''; }
function wp_seed_content_kit_register_template_module($module, array $definition)
{
    $GLOBALS['dpt_template_module'] = array($module, $definition);
    return true;
}

function get_post_meta($post_id, $key, $single = false)
{
    return isset($GLOBALS['dpt_meta'][$post_id][$key]) ? $GLOBALS['dpt_meta'][$post_id][$key] : '';
}

function metadata_exists($type, $post_id, $key)
{
    return isset($GLOBALS['dpt_meta'][$post_id]) && array_key_exists($key, $GLOBALS['dpt_meta'][$post_id]);
}

function add_post_meta($post_id, $key, $value, $unique = false)
{
    if ($unique && metadata_exists('post', $post_id, $key)) {
        return false;
    }
    $GLOBALS['dpt_meta'][$post_id][$key] = $value;
    return true;
}

function get_option($key, $default = false)
{
    return array_key_exists($key, $GLOBALS['dpt_options']) ? $GLOBALS['dpt_options'][$key] : $default;
}

function update_option($key, $value, $autoload = null)
{
    $GLOBALS['dpt_options'][$key] = $value;
    return true;
}

function get_posts($query)
{
    $posts = array_values($GLOBALS['dpt_posts']);
    $posts = array_values(array_filter($posts, function ($post) use ($query) {
        if ('publish' === $query['post_status'] && 'publish' !== $post->post_status) {
            return false;
        }
        if (!empty($query['post__in']) && !in_array($post->ID, $query['post__in'], true)) {
            return false;
        }
        if (!empty($query['post__not_in']) && in_array($post->ID, $query['post__not_in'], true)) {
            return false;
        }
        return true;
    }));
    if (isset($query['fields']) && 'ids' === $query['fields']) {
        return array_map(function ($post) { return $post->ID; }, $posts);
    }
    return $posts;
}

function wp_seed_content_directory_is_publicly_eligible($post_id)
{
    $post = get_post($post_id);
    return $post instanceof WP_Post
        && 'publish' === $post->post_status
        && '' === $post->post_password
        && '1' === get_post_meta($post_id, '_seed_directory_publication_authorized', true)
        && '1' === get_post_meta($post_id, '_seed_directory_publicly_listed', true);
}

require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/fields.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/data.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/collections.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/templates.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/collection-renderer.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/shortcode.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/profile-upgrade.php';

$profiles = array(
    11 => array('Alice', array('praticien'), false, 'practicing', 'publish'),
    12 => array('Bruno', array('intervenant'), false, 'practicing', 'publish'),
    13 => array('Céline', array('praticien', 'intervenant'), false, 'practicing', 'publish'),
    14 => array('David', array('praticien'), true, 'practicing', 'publish'),
    15 => array('Emma', array('intervenant'), true, 'practicing', 'publish'),
    16 => array('Historique', null, false, 'seeking_models', 'publish'),
    17 => array('Brouillon', array('praticien'), true, 'practicing', 'draft'),
);
foreach ($profiles as $id => $profile) {
    $GLOBALS['dpt_posts'][$id] = new WP_Post($id, $profile[0], $profile[4]);
    $GLOBALS['dpt_meta'][$id] = array(
        '_seed_directory_status' => $profile[3],
        '_seed_directory_country' => 'FR',
        '_seed_directory_publication_authorized' => '1',
        '_seed_directory_publicly_listed' => '1',
    );
    if (null !== $profile[1]) {
        $GLOBALS['dpt_meta'][$id]['_seed_directory_profile_types'] = $profile[1];
    }
    if ($profile[2]) {
        $GLOBALS['dpt_meta'][$id]['_seed_directory_seeking_models'] = '1';
    }
}

$matrix = array(
    'all' => array(array(), array(11, 12, 13, 14, 15, 16)),
    'praticiens' => array(array('profile_type' => 'praticien'), array(11, 13, 14)),
    'intervenants' => array(array('profile_type' => 'intervenant'), array(12, 13, 15)),
    'recherche' => array(array('seeking_models' => '1'), array(14, 15)),
    'praticiens_recherche' => array(array('profile_type' => 'praticien', 'seeking_models' => '1'), array(14)),
    'intervenants_recherche' => array(array('profile_type' => 'intervenant', 'seeking_models' => '1'), array(15)),
    'types_or' => array(array('profile_types' => array('praticien', 'intervenant'), 'profile_type_operator' => 'or'), array(11, 12, 13, 14, 15)),
    'types_and' => array(array('profile_types' => array('praticien', 'intervenant'), 'profile_type_operator' => 'and'), array(13)),
);
foreach ($matrix as $name => $case) {
    dpt_same($case[1], wp_seed_content_directory_get_entries($case[0]), 'Collection matrix: ' . $name);
}

dpt_same(array(11, 12, 13, 16), wp_seed_content_directory_get_entries(array('seeking_models' => '0')), 'Inactive seeking filter');
dpt_same(array(12, 13), wp_seed_content_directory_get_entries(array('profile_type' => 'intervenant', 'exclude_ids' => array(15))), 'Explicit exclusions');
dpt_same(array(12, 13), wp_seed_content_directory_get_entries(array('offset' => 1, 'limit' => 2)), 'Offset and limit after stable sort');
dpt_same(array(), wp_seed_content_directory_get_entries(array('profile_type' => 'unknown')), 'Invalid profile type is fail closed');
dpt_same(array(), wp_seed_content_directory_get_entries(array('seeking_models' => 'later')), 'Invalid temporary status is fail closed');
dpt_same(array(), wp_seed_content_directory_get_entries(array('profile_types' => array('praticien'), 'profile_type_operator' => 'xor')), 'Invalid operator is fail closed');
dpt_same(array(11, 13, 14), wp_seed_content_directory_get_entries(wp_seed_content_directory_get_predefined_collections()['praticiens']['args']), 'Practitioner preset');
dpt_same(6, count(wp_seed_content_directory_get_predefined_collections()), 'Six non-persistent presets');

$celine = wp_seed_content_directory_get_public_data(13);
dpt_same(array('praticien', 'intervenant'), $celine['profile_types'], 'Multi-type public slugs');
dpt_same(array('Praticien', 'Intervenant'), $celine['profile_type_labels'], 'Multi-type public labels');
dpt_same('Praticien, Intervenant', $celine['profile_types_label'], 'Multi-type public label string');
dpt_same(false, $celine['seeking_models'], 'Inactive status is boolean false');
$david = wp_seed_content_directory_get_public_data(14);
dpt_same(true, $david['seeking_models'], 'Active status is boolean true');
dpt_same('Recherche de modèles', $david['seeking_models_label'], 'Active status has public label');
$legacy = wp_seed_content_directory_get_public_data(16);
dpt_same(array(), $legacy['profile_types'], 'Legacy profile has clean empty types');
dpt_same('', $legacy['profile_types_label'], 'Legacy profile has clean empty type label');
dpt_same(false, wp_seed_content_directory_get_public_data(17), 'Draft has no public data');
$GLOBALS['dpt_posts'][16]->post_password = 'protected';
dpt_same(false, wp_seed_content_directory_get_public_data(16), 'Protected profile has no public data');
$GLOBALS['dpt_posts'][16]->post_password = '';

wp_seed_content_directory_register_template_module();
dpt_same(21, count($GLOBALS['dpt_template_module'][1]['placeholders']), 'Twenty-one public Directory placeholders');
dpt_same($celine['summary'], $celine['bio'], 'Historical bio remains a strict summary alias');
dpt_same('', $celine['full_presentation'], 'Missing long presentation is a clean empty value');
$celine_context = wp_seed_content_directory_get_template_context($celine);
dpt_same('Praticien, Intervenant', $celine_context['directory.profile_types'], 'Template gets human profile list');
dpt_same('praticien,intervenant', $celine_context['directory.profile_type_slugs'], 'Template gets normalized slugs');
dpt_same('', $celine_context['directory.seeking_models'], 'Template inactive label is clean empty');
$david_context = wp_seed_content_directory_get_template_context($david);
dpt_same('1', $david_context['directory.seeking_models_active'], 'Template active boolean is typed text');

dpt_same($GLOBALS['dpt_shortcodes']['seed_directory'], $GLOBALS['dpt_shortcodes']['wp_seed_directory'], 'Historical shortcode alias preserved');
$shortcode = wp_seed_content_directory_normalize_shortcode_atts(array(
    'profile_type' => 'praticien',
    'seeking_models' => '1',
    'exclude_ids' => '11,13',
    'offset' => '1',
));
dpt_same('praticien', $shortcode['collection']['profile_type'], 'Shortcode sends canonical profile filter');
dpt_same('1', $shortcode['collection']['seeking_models'], 'Shortcode sends canonical seeking filter');
dpt_same(array(11, 13), $shortcode['collection']['exclude_ids'], 'Shortcode parses exclusions');
dpt_same(null, wp_seed_content_directory_normalize_shortcode_atts(array('profile_type' => 'unknown')), 'Shortcode rejects invalid type');
dpt_same(null, wp_seed_content_directory_normalize_shortcode_atts(array('seeking_models' => 'later')), 'Shortcode rejects invalid seeking status');
dpt_same(false, class_exists('ET_Builder_Module_Seed_Directory'), 'No dedicated Divi or Loop Builder module added');

$upgrade = wp_seed_content_directory_upgrade_profile_facets();
dpt_same(array('status' => 'migrated', 'updated' => 1), $upgrade, 'Additive migration maps one legacy seeking status');
dpt_same('1', get_post_meta(16, '_seed_directory_seeking_models', true), 'Legacy seeking status copied to orthogonal boolean');
dpt_same(false, metadata_exists('post', 16, '_seed_directory_profile_types'), 'Migration never assigns practitioner automatically');
dpt_same(array('status' => 'unchanged', 'updated' => 0), wp_seed_content_directory_upgrade_profile_facets(), 'Additive migration is idempotent');
$additional_profiles = array(
    18 => array('Profil 08', array('praticien'), false, 'publish', true),
    19 => array('Profil 09', array('intervenant'), false, 'publish', true),
    20 => array('Profil 10', array('praticien', 'intervenant'), false, 'publish', true),
    21 => array('Profil 11', null, false, 'publish', true),
    22 => array('Profil 12', array('praticien'), true, 'publish', true),
    23 => array('Profil 13', array('intervenant'), true, 'publish', true),
    24 => array('Profil 14 non liste', array('praticien', 'intervenant'), false, 'publish', false),
    25 => array('Profil 15', array('praticien'), false, 'publish', true),
    26 => array('Profil 16 brouillon', array('intervenant'), false, 'draft', true),
    27 => array('Profil 17 prive', array('praticien'), false, 'private', true),
    28 => array('Profil 18', array('praticien'), false, 'publish', true),
    29 => array('Profil 19', array('intervenant'), false, 'publish', true),
    30 => array('Profil 20', null, false, 'publish', true),
);
foreach ($additional_profiles as $id => $profile) {
    $GLOBALS['dpt_posts'][$id] = new WP_Post($id, $profile[0], $profile[3]);
    $GLOBALS['dpt_posts'][$id]->post_excerpt = 'Resume court ' . $id;
    $GLOBALS['dpt_posts'][$id]->post_content = '<p>Presentation complete ' . $id . '</p>';
    $GLOBALS['dpt_meta'][$id] = array(
        '_seed_directory_status' => 'practicing',
        '_seed_directory_country' => 'FR',
        '_seed_directory_publication_authorized' => '1',
    );
    if (null !== $profile[1]) {
        $GLOBALS['dpt_meta'][$id]['_seed_directory_profile_types'] = $profile[1];
    }
    if ($profile[2]) {
        $GLOBALS['dpt_meta'][$id]['_seed_directory_seeking_models'] = '1';
    }
    if ($profile[4]) {
        $GLOBALS['dpt_meta'][$id]['_seed_directory_publicly_listed'] = '1';
    }
}

dpt_same(20, count($GLOBALS['dpt_posts']), 'Twenty fictional real-site-compatible profiles prepared');
dpt_same(16, count(wp_seed_content_directory_get_entries()), 'Only sixteen published and explicitly listed profiles are public');
dpt_same(array(), wp_seed_content_directory_get_entries(array('ids' => array(24))), 'Explicit IDs cannot include an unlisted profile');
dpt_same(false, wp_seed_content_directory_get_public_data(24), 'Unlisted profile has no public Data API projection');
$full = wp_seed_content_directory_get_public_data(20);
dpt_same('Resume court 20', $full['summary'], 'Summary remains sourced from post_excerpt');
dpt_same('Resume court 20', $full['bio'], 'Bio remains the strict summary alias');
dpt_same('<p>Presentation complete 20</p>', $full['full_presentation'], 'Full presentation remains independently sourced from post_content');
$full_context = wp_seed_content_directory_get_template_context($full);
dpt_same($full['summary'], $full_context['directory.summary'], 'Template summary is available');
dpt_same($full['full_presentation'], $full_context['directory.full_presentation'], 'Template full presentation is available');

if (!empty($GLOBALS['dpt_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['dpt_failures']) . ' / ' . $GLOBALS['dpt_assertions'] . PHP_EOL);
    foreach ($GLOBALS['dpt_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['dpt_assertions'] . ' Directory profile types assertions' . PHP_EOL;
