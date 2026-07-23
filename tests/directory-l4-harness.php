<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');
define('WP_SEED_CONTENT_KIT_URL', 'https://example.test/plugin/');
define('WP_SEED_CONTENT_KIT_VERSION', '0.6.0-dev');

$GLOBALS['seed_l4_assertions'] = 0;
$GLOBALS['seed_l4_failures'] = array();
$GLOBALS['seed_l4_posts'] = array();
$GLOBALS['seed_l4_meta'] = array();
$GLOBALS['seed_l4_eligible'] = array();
$GLOBALS['seed_l4_shortcodes'] = array();
$GLOBALS['seed_l4_styles'] = array();
$GLOBALS['seed_l4_enqueued'] = array();
$GLOBALS['seed_l4_template_module'] = array();
$GLOBALS['seed_l4_template_mode'] = 'success';

class WP_Post
{
    public $ID;
    public $post_type = 'seed_directory';
    public $post_status = 'publish';
    public $post_password = '';
    public $post_title = '';
    public $post_excerpt = '';
    public $post_date = '2026-01-01 00:00:00';
    public $menu_order = 0;

    public function __construct($id, $title, $status, $order)
    {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_status = $status;
        $this->menu_order = $order;
    }
}
class WP_Error
{
}

function seed_l4_assert($condition, $label)
{
    $GLOBALS['seed_l4_assertions']++;
    if (!$condition) {
        $GLOBALS['seed_l4_failures'][] = $label;
    }
}
function seed_l4_same($expected, $actual, $label)
{
    seed_l4_assert($expected === $actual, $label);
}
function __($text, $domain = null)
{
    return $text;
}
function esc_html__($text, $domain = null)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
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
function remove_accents($value)
{
    return strtr($value, array('É' => 'E', 'é' => 'e', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o'));
}
function sanitize_title($value)
{
    return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $value)), '-');
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
function absint($value)
{
    return abs((int) $value);
}
function esc_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function esc_attr($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function esc_url($value)
{
    return esc_attr($value);
}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
}
function apply_filters($hook, $value)
{
    return $value;
}
function add_shortcode($tag, $callback)
{
    $GLOBALS['seed_l4_shortcodes'][$tag] = $callback;
}
function shortcode_atts($defaults, $atts, $tag = '')
{
    return array_merge($defaults, is_array($atts) ? $atts : array());
}
function wp_seed_content_kit_is_module_active($module)
{
    return !empty($GLOBALS['seed_l4_module_active']);
}
function get_post($post_id)
{
    return isset($GLOBALS['seed_l4_posts'][$post_id]) ? $GLOBALS['seed_l4_posts'][$post_id] : null;
}
function get_posts($query)
{
    $posts = array_values($GLOBALS['seed_l4_posts']);
    if (!empty($query['post__in'])) {
        $ids = array_flip($query['post__in']);
        $posts = array_values(array_filter($posts, function ($post) use ($ids) {
            return isset($ids[$post->ID]);
        }));
    }
    return array_values(array_filter($posts, function ($post) {
        return 'publish' === $post->post_status;
    }));
}
function get_post_meta($post_id, $key, $single = false)
{
    return isset($GLOBALS['seed_l4_meta'][$post_id][$key]) ? $GLOBALS['seed_l4_meta'][$post_id][$key] : '';
}
function get_post_thumbnail_id($post_id)
{
    return isset($GLOBALS['seed_l4_meta'][$post_id]['_thumbnail_id']) ? $GLOBALS['seed_l4_meta'][$post_id]['_thumbnail_id'] : 0;
}
function wp_get_attachment_image_src($attachment_id, $size)
{
    return array('https://example.test/photo.jpg', 640, 480);
}
function update_meta_cache($type, $ids)
{
}
function wp_seed_content_directory_is_publicly_eligible($post_id)
{
    return !empty($GLOBALS['seed_l4_eligible'][$post_id]);
}
function current_user_can($capability, $post_id = 0)
{
    return false;
}
function wp_register_style($handle, $src, $dependencies, $version)
{
    $GLOBALS['seed_l4_styles'][$handle] = compact('src', 'dependencies', 'version');
}
function wp_style_is($handle, $state)
{
    return isset($GLOBALS['seed_l4_styles'][$handle]);
}
function wp_enqueue_style($handle)
{
    $GLOBALS['seed_l4_enqueued'][$handle] = true;
}
function wp_seed_content_kit_register_template_module($module, array $definition)
{
    $GLOBALS['seed_l4_template_module'] = array($module, $definition);
    return true;
}
function wp_seed_content_kit_render_template($slug, $module, array $context = array(), array $args = array())
{
    if ('success' === $GLOBALS['seed_l4_template_mode']) {
        return new WP_Seed_Content_Kit_Render_Result(true, '<div class="template-card">' . esc_html($context['directory.name']) . '</div>', 'success', 5, array());
    }
    return new WP_Seed_Content_Kit_Render_Result(false, '', $GLOBALS['seed_l4_template_mode'], 0, array());
}
function wp_seed_content_sanitize_iso_date($value)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/D', (string) $value) ? (string) $value : '';
}

require WP_SEED_CONTENT_KIT_DIR . 'includes/core/template-render-result.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/fields.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/data.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/collections.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/assets.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/templates.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/render.php';
require WP_SEED_CONTENT_KIT_DIR . 'includes/modules/directory/shortcode.php';

$GLOBALS['seed_l4_module_active'] = true;
for ($i = 1; $i <= 4; $i++) {
    $status = $i <= 2 ? 'practicing' : 'seeking_models';
    $post_status = 4 === $i ? 'draft' : 'publish';
    $GLOBALS['seed_l4_posts'][$i] = new WP_Post($i, 'Fiche ' . $i, $post_status, $i);
    $GLOBALS['seed_l4_eligible'][$i] = 'publish' === $post_status;
    $GLOBALS['seed_l4_meta'][$i] = array(
        '_seed_directory_status' => $status,
        '_seed_directory_country' => 'FR',
        '_seed_directory_department' => 1 === $i ? '75' : '69',
        '_seed_directory_city' => 1 === $i ? 'Paris' : 'Lyon',
        '_seed_directory_postal_code' => 1 === $i ? '75001' : '69001',
        '_seed_directory_publication_authorized' => '1',
        '_seed_directory_featured' => 1 === $i ? '1' : '',
    );
}
$GLOBALS['seed_l4_meta'][1]['_seed_directory_phone'] = '+33 1 00 00 00 01';
$GLOBALS['seed_l4_meta'][1]['_seed_directory_phone_visible'] = '1';
$GLOBALS['seed_l4_meta'][1]['_seed_directory_email'] = 'PRIVATE-L4@example.test';
$GLOBALS['seed_l4_meta'][1]['_seed_directory_email_visible'] = '';
$GLOBALS['seed_l4_meta'][1]['_seed_directory_internal_note'] = 'PRIVATE-L4-NOTE';

$data = wp_seed_content_directory_get_public_data(1);
seed_l4_same(array('id', 'name', 'photo', 'bio', 'status', 'status_label', 'location', 'featured', 'display_order', 'contacts'), array_keys($data), 'Fixed public schema');
seed_l4_same(array('phone' => '+33 1 00 00 00 01'), $data['contacts'], 'Only visible contact returned');
seed_l4_assert(false === strpos(serialize($data), 'PRIVATE-L4'), 'Public data excludes private sentinels');
seed_l4_same(false, wp_seed_content_directory_get_public_data(4), 'Ineligible entry returns false');
seed_l4_same(array('city', 'postal_code', 'department', 'country'), array_keys($data['location']), 'Location schema');
$card_without_photo = wp_seed_content_directory_render_native_card($data);
seed_l4_assert(false === strpos($card_without_photo, 'wp-seed-directory-card__media'), 'Card without photo omits media wrapper');
seed_l4_assert(false === strpos($card_without_photo, 'photo-placeholder'), 'Card without photo omits placeholder');
$data_with_photo = $data;
$data_with_photo['photo'] = array('url' => 'https://example.test/photo.jpg', 'alt' => 'Portrait fictif', 'width' => 640, 'height' => 480);
$card_with_photo = wp_seed_content_directory_render_native_card($data_with_photo);
seed_l4_assert(false !== strpos($card_with_photo, 'wp-seed-directory-card__media'), 'Card with photo renders media wrapper');
seed_l4_assert(false !== strpos($card_with_photo, 'alt="Portrait fictif"'), 'Card with photo preserves alt text');

seed_l4_same(array(1, 2, 3), wp_seed_content_directory_get_entries(), 'Collection excludes draft');
seed_l4_same(array(1, 2), wp_seed_content_directory_get_entries(array('status' => 'practicing')), 'Status filter');
seed_l4_same(array(3), wp_seed_content_directory_get_entries(array('status' => 'seeking_models')), 'Second status filter');
seed_l4_same(array(1), wp_seed_content_directory_get_entries(array('featured' => 'only')), 'Featured only');
seed_l4_same(array(2, 3), wp_seed_content_directory_get_entries(array('featured' => 'exclude')), 'Featured exclude');
seed_l4_same(array(1), wp_seed_content_directory_get_entries(array('department' => '75')), 'Department filter');
seed_l4_same(array(1, 2, 3), wp_seed_content_directory_get_entries(array('country' => 'FR')), 'Country filter');
seed_l4_same(array(2), wp_seed_content_directory_get_entries(array('ids' => array(2, 4))), 'Explicit IDs preserve eligibility');
seed_l4_same(array(), wp_seed_content_directory_get_entries(array('status' => 'invalid')), 'Invalid collection status empty');
seed_l4_same(array(3, 2, 1), wp_seed_content_directory_get_entries(array('order' => 'desc')), 'Descending stable order');
seed_l4_same(array(1, 2), wp_seed_content_directory_get_entries(array('limit' => 2)), 'Collection limit');

seed_l4_assert(isset($GLOBALS['seed_l4_shortcodes']['seed_directory']), 'Canonical shortcode registered');
seed_l4_assert(isset($GLOBALS['seed_l4_shortcodes']['wp_seed_directory']), 'Alias registered');
seed_l4_same($GLOBALS['seed_l4_shortcodes']['seed_directory'], $GLOBALS['seed_l4_shortcodes']['wp_seed_directory'], 'Alias shares callback');
seed_l4_same(null, wp_seed_content_directory_normalize_shortcode_atts(array('status' => 'bad')), 'Invalid shortcode status rejected');
seed_l4_assert(
    wp_seed_content_directory_compare_entries(
        (object) array('ID' => 1, 'post_title' => 'Élise', 'post_date' => '', 'menu_order' => 1),
        (object) array('ID' => 2, 'post_title' => 'Maël', 'post_date' => '', 'menu_order' => 1),
        'display_order',
        'asc'
    ) < 0,
    'Display order tie ignores accents and case'
);
seed_l4_same(null, wp_seed_content_directory_normalize_shortcode_atts(array('ids' => '1,bad')), 'Invalid shortcode IDs rejected');
seed_l4_assert(is_array(wp_seed_content_directory_normalize_shortcode_atts(array('template' => 'Carte Annuaire'))), 'Template slug sanitized');

$GLOBALS['seed_l4_enqueued'] = array();
$empty_html = wp_seed_content_directory_shortcode(array('country' => 'US'));
seed_l4_assert(false !== strpos($empty_html, 'Aucune fiche'), 'Empty state rendered');
seed_l4_assert(isset($GLOBALS['seed_l4_enqueued']['wp-seed-directory']), 'Empty state enqueues structural CSS');
seed_l4_assert(!isset($GLOBALS['seed_l4_enqueued']['wp-seed-directory-card']), 'Empty state does not enqueue native card CSS');

$html = wp_seed_content_directory_shortcode(array());
seed_l4_assert(false !== strpos($html, 'En exercice'), 'Practicing group rendered');
seed_l4_assert(false !== strpos($html, 'En recherche de modèles'), 'Seeking group rendered');
seed_l4_assert(false !== strpos($html, '<ul'), 'Semantic list rendered');
seed_l4_assert(false === strpos($html, '<ul class="wp-seed-directory__grid"></ul>'), 'No empty list');
seed_l4_assert(false === strpos($html, 'PRIVATE-L4'), 'Native HTML excludes private sentinels');
seed_l4_assert(isset($GLOBALS['seed_l4_enqueued']['wp-seed-directory']), 'Structural CSS enqueued');
seed_l4_assert(isset($GLOBALS['seed_l4_enqueued']['wp-seed-directory-card']), 'Native CSS enqueued');
$structure_css = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'assets/css/directory.css');
$card_css = file_get_contents(WP_SEED_CONTENT_KIT_DIR . 'assets/css/directory-card.css');
seed_l4_assert(false !== strpos($structure_css, '.wp-seed-directory .wp-seed-directory__grid'), 'Grid reset outranks theme list styles');
seed_l4_assert(false !== strpos($structure_css, '.wp-seed-directory .wp-seed-directory__item::marker'), 'List marker is explicitly neutralized');
seed_l4_assert(false === strpos($card_css, 'photo-placeholder'), 'Native CSS has no photo placeholder');

$GLOBALS['seed_l4_template_mode'] = 'success';
$template_html = wp_seed_content_directory_shortcode(array('template' => 'card'));
seed_l4_assert(false !== strpos($template_html, 'template-card'), 'Successful template rendered');
$GLOBALS['seed_l4_template_mode'] = 'template_not_found';
$fallback_html = wp_seed_content_directory_shortcode(array('template' => 'missing'));
seed_l4_assert(false !== strpos($fallback_html, 'wp-seed-directory-card'), 'Missing template falls back per card');
foreach (array('module_mismatch', 'invalid_context', 'empty_render', 'provider_error', 'recursion_detected', 'invalid_assets') as $failure) {
    $GLOBALS['seed_l4_template_mode'] = $failure;
    $fallback = wp_seed_content_directory_render_entry($data, 'card');
    seed_l4_same(true, $fallback['native'], $failure . ' native fallback');
    seed_l4_assert(false !== strpos($fallback['html'], 'wp-seed-directory-card'), $failure . ' fallback HTML');
}

wp_seed_content_directory_register_template_module();
seed_l4_same('directory', $GLOBALS['seed_l4_template_module'][0], 'Directory template module registered');
$definitions = $GLOBALS['seed_l4_template_module'][1]['placeholders'];
$expected_placeholders = array(
    'directory.name', 'directory.photo', 'directory.bio', 'directory.status', 'directory.status_label',
    'directory.city', 'directory.postal_code', 'directory.department', 'directory.country',
    'directory.phone', 'directory.email', 'directory.website', 'directory.facebook',
    'directory.instagram', 'directory.featured',
);
seed_l4_same($expected_placeholders, array_keys($definitions), 'Exact Directory placeholders');
$context = wp_seed_content_directory_get_template_context($data);
seed_l4_assert(false === strpos(serialize($context), 'PRIVATE-L4'), 'Template context excludes private values');
seed_l4_same('', $context['directory.email'], 'Hidden email typed empty');

$GLOBALS['seed_l4_module_active'] = false;
seed_l4_same(array(), wp_seed_content_directory_get_entries(), 'Disabled module collection empty');
seed_l4_same('', wp_seed_content_directory_shortcode(array()), 'Disabled module shortcode empty');
seed_l4_same(false, function_exists('wp_seed_content_directory_search'), 'No public search');
seed_l4_same(false, function_exists('wp_seed_content_directory_migrate'), 'No runtime migration');

if (!empty($GLOBALS['seed_l4_failures'])) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['seed_l4_failures']) . ' / ' . $GLOBALS['seed_l4_assertions'] . PHP_EOL);
    foreach ($GLOBALS['seed_l4_failures'] as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $GLOBALS['seed_l4_assertions'] . ' Annuaire L4 assertions' . PHP_EOL;
