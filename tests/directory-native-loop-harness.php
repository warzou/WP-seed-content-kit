<?php

declare(strict_types=1);

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent {
    class DynamicContentElements { public static function get_wrapper_element(array $args): string { return (string) ($args['value'] ?? ''); } }
    abstract class DynamicContentOptionBase { public function load() {} }
    interface DynamicContentOptionInterface {}
}

namespace {
define('ABSPATH', __DIR__ . '/');
$GLOBALS['directory_loop_assertions'] = 0;
$GLOBALS['directory_loop_failures'] = array();
$GLOBALS['directory_loop_contact_registry'] = array(
    'phone' => array('label' => 'Téléphone', 'active' => true),
    'email' => array('label' => 'E-mail', 'active' => true),
);
class WP_Error {}
class WP_Post {
    public $ID; public $post_type; public $post_status = 'publish'; public $post_password = '';
    public function __construct($id, $type = 'seed_directory') { $this->ID = (int) $id; $this->post_type = $type; }
}
function __($value, $domain = null) { return $value; }
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {}
function apply_filters($hook, $value) { return $value; }
function absint($value) { return abs((int) $value); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function get_the_ID() { return 0; }
function get_post($id) { return in_array((int) $id, array(701, 702, 703), true) ? new WP_Post($id) : new WP_Post($id, 'page'); }
function wp_seed_content_directory_is_publicly_eligible($id) { return in_array((int) $id, array(701, 702, 703), true); }
function wp_seed_content_directory_contact_type_registry($active_only = false) {
    if (!$active_only) { return $GLOBALS['directory_loop_contact_registry']; }
    return array_filter($GLOBALS['directory_loop_contact_registry'], function ($definition) { return !empty($definition['active']); });
}
function wp_seed_content_directory_individual_contact_provider_definitions() {
    $labels = array('phone' => 'Téléphone', 'email' => 'E-mail', 'website' => 'Site', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp', 'address' => 'Adresse');
    $definitions = array();
    foreach ($labels as $slug => $label) {
        $has_href = 'address' !== $slug;
        $definitions[$slug] = array(
            'slug' => $slug,
            'label' => $label,
            'display_field_id' => 'directory.' . $slug,
            'display_provider_id' => 'loop_wpsck_directory_' . $slug,
            'href_field_id' => $has_href ? 'directory.' . $slug . '_href' : '',
            'href_provider_id' => $has_href ? 'loop_wpsck_directory_' . $slug . '_href' : '',
            'has_href' => $has_href,
        );
    }
    return $definitions;
}
function wp_seed_content_get_directory_data($id, $args = array()) {
    if (!wp_seed_content_directory_is_publicly_eligible($id)) { return array(); }
    return array(
        'id' => (int) $id, 'name' => 'Nom ' . $id, 'professional_label' => 703 === (int) $id ? '' : 'Métier ' . $id, 'summary' => 'Résumé ' . $id,
        'presentation' => 'Présentation ' . $id, 'full_presentation' => 'Présentation ' . $id,
        'presentation_intro' => 'Introduction ' . $id, 'presentation_more' => 'Suite ' . $id,
        'has_more' => true, 'status_label' => 'En exercice',
        'profile_types_label' => 'Intervenant', 'seeking_models_label' => '',
        'location_label' => 'Paris', 'phone' => '+33100000' . $id, 'email' => 'p' . $id . '@example.test',
        'website' => 'https://example.test/' . $id, 'facebook' => '', 'instagram' => '',
        'linkedin' => 'https://linkedin.com/in/' . $id, 'whatsapp' => 'https://wa.me/' . $id,
        'address' => 'Adresse ' . $id,
        'phone_href' => 'tel:+33100000' . $id, 'email_href' => 'mailto:p' . $id . '@example.test',
        'website_href' => 'https://example.test/' . $id, 'facebook_href' => '', 'instagram_href' => '',
        'linkedin_href' => 'https://linkedin.com/in/' . $id, 'whatsapp_href' => 'https://wa.me/' . $id,
        'anchor' => 'annuaire-' . $id,
        'photo' => array('id' => 800 + (int) $id, 'url' => 'https://example.test/' . $id . '.jpg', 'alt' => 'Portrait'),
    );
}
function directory_loop_same($expected, $actual, $label) { $GLOBALS['directory_loop_assertions']++; if ($expected !== $actual) { $GLOBALS['directory_loop_failures'][] = $label; } }

$root = dirname(__DIR__);
directory_loop_same(false, file_exists($root . '/plugin/includes/integrations/divi/class-dynamic-content-directory-contacts.php'), 'Composite Directory provider file stays removed');
directory_loop_same(false, false !== strpos(file_get_contents($root . '/plugin/includes/integrations/divi/dynamic-content.php'), 'WP_Seed_Content_Divi_Dynamic_Content_Directory_Contacts'), 'Composite Directory provider stays unregistered');
require_once $root . '/plugin/includes/core/dynamic-data.php';
require_once $root . '/plugin/includes/integrations/divi/loop-context.php';
require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-directory-base.php';
require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-directory-fields.php';

$classes = array(
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Visual', 'WP_Seed_Content_Divi_Dynamic_Content_Directory_Name',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Professional_Label',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Summary', 'WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation_Intro', 'WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation_More',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Status', 'WP_Seed_Content_Divi_Dynamic_Content_Directory_Profile_Types',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Seeking_Models', 'WP_Seed_Content_Divi_Dynamic_Content_Directory_Location',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Id',
    'WP_Seed_Content_Divi_Dynamic_Content_Directory_Anchor',
);
$contact_providers = array();
foreach (wp_seed_content_directory_individual_contact_provider_definitions() as $definition) {
    $contact_providers[] = new WP_Seed_Content_Divi_Dynamic_Content_Directory_Contact_Field($definition, false);
    if ($definition['has_href']) {
        $contact_providers[] = new WP_Seed_Content_Divi_Dynamic_Content_Directory_Contact_Field($definition, true);
    }
}
$names = array();
foreach (array_merge(array_map(function ($class) { return new $class(); }, $classes), $contact_providers) as $provider) {
    $names[] = $provider->get_name();
    directory_loop_same(0, strpos($provider->get_name(), 'loop_'), $provider->get_name() . ' prefix');
    $option = $provider->register_option_callback(array(), 0, 'content')[$provider->get_name()];
    directory_loop_same('WPSCK — Annuaire', $option['group'], $provider->get_name() . ' group');
    $values = array();
    foreach (array(701, 702, 703) as $id) {
        $value = $provider->render_callback('', array('name' => $provider->get_name(), 'loop_id' => $id, 'post_id' => 900001));
        directory_loop_same(false, false !== strpos($value, '$variable('), $provider->get_name() . ' no raw token');
        $values[] = $value;
    }
    if (!in_array($provider->get_name(), array('loop_wpsck_directory_status', 'loop_wpsck_directory_profile_types', 'loop_wpsck_directory_seeking_models', 'loop_wpsck_directory_location', 'loop_wpsck_directory_facebook', 'loop_wpsck_directory_instagram', 'loop_wpsck_directory_facebook_href', 'loop_wpsck_directory_instagram_href'), true)) {
        directory_loop_same(3, count(array_unique($values)), $provider->get_name() . ' clone values distinct');
    }
    $token = '$variable({"type":"content","value":{"name":"' . $provider->get_name() . '","settings":[]}})$';
    directory_loop_same($token, $provider->render_callback($token, array('name' => $provider->get_name(), 'post_id' => 900001)), $provider->get_name() . ' deferred');
    directory_loop_same(
        $provider->render_callback('', array('name' => $provider->get_name(), 'loop_id' => 701)),
        $provider->render_callback('', array('name' => $provider->get_name(), 'loop_object' => new WP_Post(701))),
        $provider->get_name() . ' loop object'
    );
}
directory_loop_same(28, count(array_unique($names)), 'Exactly twenty-eight individual providers');
$definitions = wp_seed_content_directory_individual_contact_provider_definitions();
$website_provider = new WP_Seed_Content_Divi_Dynamic_Content_Directory_Contact_Field($definitions['website'], false);
$website_option = $website_provider->register_option_callback(array(), 0, 'content')[$website_provider->get_name()];
directory_loop_same('text', $website_option['type'], 'Website display value remains available in Text modules');
$phone_href_provider = new WP_Seed_Content_Divi_Dynamic_Content_Directory_Contact_Field($definitions['phone'], true);
$phone_href_option = $phone_href_provider->register_option_callback(array(), 0, 'content')[$phone_href_provider->get_name()];
directory_loop_same('url', $phone_href_option['type'], 'Phone href is available in URL fields');
directory_loop_same('tel:+33100000701', $phone_href_provider->render_callback('', array('name' => $phone_href_provider->get_name(), 'loop_id' => 701)), 'Phone href uses canonical link builder projection');
$professional_provider = new WP_Seed_Content_Divi_Dynamic_Content_Directory_Professional_Label();
directory_loop_same('', $professional_provider->render_callback('', array('name' => $professional_provider->get_name(), 'loop_id' => 703)), 'Empty professional label is safe');

$sources = wp_seed_content_divi_loop_dynamic_data_sources()['seed_directory'];
directory_loop_same(28, count($sources), 'Twenty-eight QueryResults aliases');
foreach ($sources as $alias => $definition) {
    directory_loop_same(false, 0 === strpos($alias, 'loop_'), $alias . ' alias unprefixed');
    directory_loop_same(true, in_array('loop_' . $alias, $names, true), $alias . ' provider pair');
}
directory_loop_same(701, wp_seed_content_divi_get_dynamic_content_context(array('loop_id' => 701), 'seed_directory')['current_post_id'], 'loop_id context');
directory_loop_same(702, wp_seed_content_divi_get_dynamic_content_context(array('loop_object' => new WP_Post(702)), 'seed_directory')['current_post_id'], 'loop_object context');

if ($GLOBALS['directory_loop_failures']) { fwrite(STDERR, 'FAIL ' . count($GLOBALS['directory_loop_failures']) . '/' . $GLOBALS['directory_loop_assertions'] . ': ' . implode(', ', $GLOBALS['directory_loop_failures']) . PHP_EOL); exit(1); }
echo 'PASS ' . $GLOBALS['directory_loop_assertions'] . ' Directory Native Loop assertions' . PHP_EOL;
}
