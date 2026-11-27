<?php

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
$GLOBALS['contacts_meta'] = array();
$GLOBALS['contacts_options'] = array();
$GLOBALS['contacts_eligible'] = array(101 => true, 102 => true, 103 => true, 104 => true, 105 => true, 106 => true, 107 => true);
$GLOBALS['contacts_assertions'] = 0;
$GLOBALS['contacts_failures'] = array();

class WP_Post { public $ID; public $post_type = 'seed_directory'; public function __construct($id) { $this->ID = (int) $id; } }
function __($value, $domain = null) { return $value; }
function _n($single, $plural, $number, $domain = null) { return 1 === (int) $number ? $single : $plural; }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {}
function apply_filters($hook, $value) { return $value; }
function get_option($key, $default = false) { return array_key_exists($key, $GLOBALS['contacts_options']) ? $GLOBALS['contacts_options'][$key] : $default; }
function update_option($key, $value) { $GLOBALS['contacts_options'][$key] = $value; return true; }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_email($value) { return filter_var((string) $value, FILTER_SANITIZE_EMAIL); }
function is_email($value) { return false !== filter_var($value, FILTER_VALIDATE_EMAIL); }
function absint($value) { return abs((int) $value); }
function wp_parse_url($value) { return parse_url((string) $value); }
function esc_url_raw($value, $protocols = null) { return filter_var((string) $value, FILTER_VALIDATE_URL) ? (string) $value : ''; }
function esc_url($value, $protocols = null) { return esc_url_raw($value, $protocols); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_js($value) { return addslashes((string) $value); }
function esc_html_e($value, $domain = null) { echo esc_html($value); }
function esc_attr_e($value, $domain = null) { echo esc_attr($value); }
function selected($actual, $expected, $echo = true) { $value = $actual === $expected ? 'selected="selected"' : ''; if ($echo) echo $value; return $value; }
function checked($actual, $expected = true, $echo = true) { $value = $actual === $expected ? 'checked="checked"' : ''; if ($echo) echo $value; return $value; }
function wp_json_encode($value) { return json_encode($value); }
function wp_enqueue_media() {}
function wp_unslash($value) { return $value; }
function sanitize_html_class($value) { return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $value); }
function wp_get_attachment_url($id) { return 77 === (int) $id ? 'https://example.test/icon.png' : ''; }
function wp_attachment_is_image($id) { return 77 === (int) $id; }
function current_user_can($capability, $post_id = 0) { return true; }
function wp_verify_nonce($nonce, $action) { return true; }
function register_post_meta($post_type, $key, $args) { $GLOBALS['registered_contacts_meta'] = array($post_type, $key, $args); }
function metadata_exists($type, $id, $key) { return isset($GLOBALS['contacts_meta'][(int) $id]) && array_key_exists($key, $GLOBALS['contacts_meta'][(int) $id]); }
function get_post_meta($id, $key, $single = false) { return metadata_exists('post', $id, $key) ? $GLOBALS['contacts_meta'][(int) $id][$key] : ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['contacts_meta'][(int) $id][$key] = $value; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['contacts_meta'][(int) $id][$key]); return true; }
function get_post($id) { return in_array((int) $id, array(101, 102, 103, 104, 105, 106, 107), true) ? new WP_Post($id) : null; }
function get_posts($args = array()) { $ids = array_keys($GLOBALS['contacts_meta']); sort($ids, SORT_NUMERIC); return $ids; }
function wp_seed_content_directory_is_publicly_eligible($id) { return !empty($GLOBALS['contacts_eligible'][(int) $id]); }
function _wp_seed_content_collections_normalize_ids($ids) { return array_values(array_unique(array_map('absint', is_array($ids) ? $ids : array()))); }
function contacts_same($expected, $actual, $label) { $GLOBALS['contacts_assertions']++; if ($expected !== $actual) { $GLOBALS['contacts_failures'][] = $label . ' expected=' . serialize($expected) . ' actual=' . serialize($actual); } }
function contacts_assert($condition, $label) { contacts_same(true, (bool) $condition, $label); }

$root = dirname(__DIR__);
contacts_same(false, file_exists($root . '/plugin/includes/modules/directory/contact-display-profiles.php'), 'Composite contact display profiles stay removed');
require_once $root . '/plugin/includes/modules/directory/fields.php';
require_once $root . '/plugin/includes/modules/directory/contact-registry.php';
require_once $root . '/plugin/includes/modules/directory/contacts.php';
require_once $root . '/plugin/includes/modules/directory/contact-migration.php';
require_once $root . '/plugin/includes/integrations/divi/directory-has-more-condition.php';
contacts_same(false, function_exists('wp_seed_content_directory_render_public_contacts'), 'Composite contact HTML renderer stays removed');

$registry = wp_seed_content_directory_contact_type_registry();
contacts_same(array('phone', 'email'), array_keys($registry), 'Phone and email are the only default types');
contacts_same(array('phone', 'email'), array_keys(wp_seed_content_directory_contact_type_registry(true)), 'Default types are active');
contacts_same('phone', $registry['phone']['behavior'], 'Phone behavior protected');
contacts_same('email', $registry['email']['behavior'], 'Email behavior protected');
$individual = wp_seed_content_directory_individual_contact_provider_definitions();
contacts_same(array('phone', 'email'), array_keys($individual), 'Phone and email are the only protected individual provider contracts');
contacts_same(array('phone', 'email'), array_keys(wp_seed_content_directory_system_contact_types()), 'Phone and email are the only system-protected contact types');
$stable_conditions = array(
    'phone' => 'wpsckDirectoryPhonePresent',
    'email' => 'wpsckDirectoryEmailPresent',
    'website' => 'wpsckDirectoryWebsitePresent',
    'facebook' => 'wpsckDirectoryFacebookPresent',
    'instagram' => 'wpsckDirectoryInstagramPresent',
    'linkedin' => 'wpsckDirectoryLinkedinPresent',
    'whatsapp' => 'wpsckDirectoryWhatsappPresent',
    'address' => 'wpsckDirectoryAddressPresent',
);
foreach ($individual as $slug => $definition) {
    contacts_same('loop_wpsck_directory_' . $slug, $definition['display_provider_id'], $slug . ' display provider ID stable');
    contacts_same($stable_conditions[$slug], $definition['condition_name'], $slug . ' condition ID stable');
    contacts_same('address' !== $slug, $definition['has_href'], $slug . ' href capability follows its registered behavior');
    contacts_same('address' === $slug ? '' : 'loop_wpsck_directory_' . $slug . '_href', $definition['href_provider_id'], $slug . ' href provider contract stable');
}
contacts_same(false, isset($individual['other']), 'Repeatable other type has no ambiguous individual provider');

$configured = wp_seed_content_directory_sanitize_contact_type_settings(array(
    array('slug' => 'phone', 'label' => 'Téléphone direct', 'behavior' => 'url', 'active' => 1, 'order' => 10),
    array('slug' => 'email', 'label' => 'E-mail pro', 'behavior' => 'text', 'active' => 1, 'order' => 20),
    array('slug' => 'website', 'label' => 'Site internet', 'behavior' => 'url', 'active' => 1, 'individual_provider' => 1, 'order' => 30),
    array('slug' => 'facebook', 'label' => 'Facebook', 'behavior' => 'url', 'active' => 1, 'individual_provider' => 1, 'order' => 35),
    array('slug' => 'appointment', 'label' => 'Prendre rendez-vous', 'behavior' => 'url', 'active' => 1, 'individual_provider' => 1, 'order' => 40),
    array('slug' => 'instagram', 'label' => 'Instagram', 'behavior' => 'instagram', 'active' => 1, 'individual_provider' => 1, 'order' => 50),
    array('slug' => 'booking', 'label' => 'Réservation', 'behavior' => 'url', 'active' => 1, 'order' => 60),
    array('slug' => 'youtube', 'label' => 'YouTube', 'behavior' => 'url', 'active' => 1, 'individual_provider' => 1, 'order' => 70),
    array('slug' => 'speciality-note', 'label' => 'Note de spécialité', 'behavior' => 'text', 'active' => 1, 'individual_provider' => 1, 'order' => 80),
    array('slug' => 'other', 'label' => 'Autre', 'behavior' => 'url_or_text', 'active' => 1, 'individual_provider' => 1, 'order' => 90),
));
$GLOBALS['contacts_options'][wp_seed_content_directory_contact_type_option_key()] = array_values($configured);
$registry = wp_seed_content_directory_contact_type_registry();
contacts_same(array('phone', 'email', 'website', 'facebook', 'appointment', 'instagram', 'booking', 'youtube', 'speciality-note', 'other'), array_keys($registry), 'Configured registry order preserved');
contacts_same('email', $registry['email']['behavior'], 'System email behavior cannot be changed');
contacts_same('phone', $registry['phone']['behavior'], 'System phone behavior cannot be changed');
contacts_same('url', $registry['instagram']['behavior'], 'Legacy Instagram behavior normalizes to generic web link');
contacts_same(false, $registry['facebook']['system'], 'Facebook is configurable rather than system-protected');
contacts_same(false, $registry['instagram']['system'], 'Instagram is configurable rather than system-protected');
contacts_same(false, $registry['website']['system'], 'Website is configurable rather than system-protected');
contacts_same(true, $registry['website']['individual_provider'], 'Website keeps its individual provider opt-in');
contacts_same(true, $registry['facebook']['individual_provider'], 'Facebook keeps its individual provider opt-in');
contacts_same(true, $registry['instagram']['individual_provider'], 'Instagram keeps its individual provider opt-in');
contacts_same('url', $registry['appointment']['behavior'], 'Custom appointment behavior retained');
contacts_same('url', $registry['booking']['behavior'], 'Custom type uses a safe registered behavior');
contacts_same('https://example.test/profile', wp_seed_content_directory_sanitize_contact_value('facebook', 'https://example.test/profile', true), 'Facebook type accepts any valid HTTP(S) host');
contacts_same('https://facebook.com/example', wp_seed_content_directory_sanitize_contact_value('facebook', 'https://facebook.com/example', true), 'Facebook behavior accepts its canonical domain');
contacts_same('https://example.test/profile', wp_seed_content_directory_sanitize_contact_value('instagram', 'https://example.test/profile', true), 'Instagram type accepts any valid HTTP(S) host');
contacts_same('https://instagram.com/example', wp_seed_content_directory_sanitize_contact_value('instagram', 'https://instagram.com/example', true), 'Instagram behavior accepts its canonical domain');
contacts_same('https://example.test/profile', wp_seed_content_directory_sanitize_contact_value('website', 'https://example.test/profile', true), 'Generic web-link behavior accepts any valid HTTP(S) host');
contacts_same('', wp_seed_content_directory_sanitize_contact_value('facebook', 'javascript:alert(1)', true), 'Facebook type rejects javascript scheme');
contacts_same('', wp_seed_content_directory_sanitize_contact_value('instagram', 'data:text/plain,unsafe', true), 'Instagram type rejects data scheme');
contacts_same('', wp_seed_content_directory_sanitize_contact_value('facebook', 'not-a-url', true), 'Facebook type rejects invalid URL');
contacts_same(false, isset(wp_seed_content_directory_contact_behavior_registry()['facebook']), 'Dedicated Facebook behavior is no longer exposed');
contacts_same(false, isset(wp_seed_content_directory_contact_behavior_registry()['instagram']), 'Dedicated Instagram behavior is no longer exposed');
contacts_same('https://example.test/profile', wp_seed_content_directory_contact_href('facebook', 'https://example.test/profile'), 'Facebook href provider keeps a generic valid web link');
contacts_same('Profil social', wp_seed_content_directory_contact_display_value('instagram', 'https://example.test/profile', 'Profil social'), 'Instagram display provider keeps the optional label contract');
contacts_same(false, isset($registry['appointment']['icon_id']), 'Contact type registry stores no presentation icon');
contacts_same(true, isset(wp_seed_content_directory_individual_contact_provider_definitions()['appointment']), 'Opted-in custom type gets individual providers');
contacts_same(false, isset(wp_seed_content_directory_individual_contact_provider_definitions()['booking']), 'Custom type without opt-in remains composite only');
$individual = wp_seed_content_directory_individual_contact_provider_definitions();
contacts_same('loop_wpsck_directory_website', $individual['website']['display_provider_id'], 'Website display provider ID remains published');
contacts_same('loop_wpsck_directory_website_href', $individual['website']['href_provider_id'], 'Website href provider ID remains published');
contacts_same('wpsckDirectoryWebsitePresent', $individual['website']['condition_name'], 'Website condition ID remains published');
contacts_same(false, $individual['website']['system'], 'Website provider contract is independent from system protection');
contacts_same('loop_wpsck_directory_facebook', $individual['facebook']['display_provider_id'], 'Facebook display provider ID remains published');
contacts_same('loop_wpsck_directory_facebook_href', $individual['facebook']['href_provider_id'], 'Facebook href provider ID remains published');
contacts_same('wpsckDirectoryFacebookPresent', $individual['facebook']['condition_name'], 'Facebook condition ID remains published');
contacts_same('loop_wpsck_directory_instagram', $individual['instagram']['display_provider_id'], 'Instagram display provider ID remains published');
contacts_same('loop_wpsck_directory_instagram_href', $individual['instagram']['href_provider_id'], 'Instagram href provider ID remains published');
contacts_same('wpsckDirectoryInstagramPresent', $individual['instagram']['condition_name'], 'Instagram condition ID remains published');
contacts_same(false, $individual['facebook']['system'], 'Facebook provider contract is independent from system protection');
contacts_same(false, $individual['instagram']['system'], 'Instagram provider contract is independent from system protection');
contacts_same('loop_wpsck_directory_youtube', $individual['youtube']['display_provider_id'], 'Future linked type gets a canonical display provider automatically');
contacts_same('loop_wpsck_directory_youtube_href', $individual['youtube']['href_provider_id'], 'Future linked type gets a canonical href provider automatically');
contacts_same('wpsckDirectoryContactYoutubePresent', $individual['youtube']['condition_name'], 'Future linked type gets a stable presence condition automatically');
contacts_same('WPSCK — Annuaire — YouTube renseigné', $individual['youtube']['condition_label'], 'Future linked type gets its condition label automatically');
contacts_same('loop_wpsck_directory_speciality-note', $individual['speciality-note']['display_provider_id'], 'Future display-only type gets a canonical display provider automatically');
contacts_same(false, $individual['speciality-note']['has_href'], 'Future display-only type does not get an href provider');
contacts_same('', $individual['speciality-note']['href_provider_id'], 'Future display-only type has no href provider ID');
contacts_same('wpsckDirectoryContactSpecialityNotePresent', $individual['speciality-note']['condition_name'], 'Future display-only type gets a stable presence condition automatically');
contacts_same(false, isset($individual['other']), 'Other remains composite-only even when its stored opt-in is set');
$GLOBALS['contacts_meta'][108] = array('seed_directory_contacts' => array(
    array('row_id' => 'private-booking', 'type' => 'booking', 'value' => 'https://booking.test/private', 'public' => 0),
    array('row_id' => 'invalid-booking', 'type' => 'booking', 'value' => 'not-a-link', 'public' => 1),
    array('row_id' => 'facebook-1', 'type' => 'facebook', 'value' => 'https://facebook.com/one', 'public' => 1),
    array('row_id' => 'facebook-2', 'type' => 'facebook', 'value' => 'https://facebook.com/two', 'public' => 0),
    array('row_id' => 'facebook-3', 'type' => 'facebook', 'value' => 'https://facebook.com/three', 'public' => 1),
    array('row_id' => 'instagram-1', 'type' => 'instagram', 'value' => 'https://instagram.com/one', 'public' => 1),
    array('row_id' => 'instagram-2', 'type' => 'instagram', 'value' => 'https://instagram.com/two', 'public' => 1),
    array('row_id' => 'website-1', 'type' => 'website', 'value' => 'https://example.test/1', 'public' => 1),
    array('row_id' => 'website-2', 'type' => 'website', 'value' => 'https://example.test/2', 'public' => 1),
    array('row_id' => 'website-3', 'type' => 'website', 'value' => 'https://example.test/3', 'public' => 1),
    array('row_id' => 'website-4', 'type' => 'website', 'value' => 'https://example.test/4', 'public' => 1),
    array('row_id' => 'website-5', 'type' => 'website', 'value' => 'https://example.test/5', 'public' => 1),
    array('row_id' => 'website-6', 'type' => 'website', 'value' => 'https://example.test/6', 'public' => 1),
    array('row_id' => 'website-7', 'type' => 'website', 'value' => 'https://example.test/7', 'public' => 0),
    array('row_id' => 'website-8', 'type' => 'website', 'value' => 'https://example.test/8', 'public' => 1),
));
contacts_same(array('booking' => 2), wp_seed_content_directory_contact_type_usage_counts(array('booking')), 'Usage count includes private and invalid canonical rows');
contacts_same(array('facebook' => 3, 'instagram' => 2), wp_seed_content_directory_contact_type_usage_counts(array('facebook', 'instagram')), 'Social usage counts include every canonical row');
contacts_same(array('website' => 8), wp_seed_content_directory_contact_type_usage_counts(array('website')), 'Website usage count includes every canonical row');
$meta_before_removal_tests = serialize($GLOBALS['contacts_meta']);
$unused_removal = wp_seed_content_directory_apply_contact_type_removals(
    array_values(array_filter($configured, function ($row) { return 'appointment' !== $row['slug']; })),
    $configured,
    array('appointment'),
    array('appointment' => 0)
);
contacts_same(array('appointment'), $unused_removal['removed'], 'Unused custom type can be removed explicitly');
contacts_same(false, isset($unused_removal['settings']['appointment']), 'Removed custom type leaves registry settings');
$GLOBALS['contacts_options'][wp_seed_content_directory_contact_type_option_key()] = array_values($unused_removal['settings']);
contacts_same(false, isset(wp_seed_content_directory_individual_contact_provider_definitions()['appointment']), 'Removed custom provider disappears');
contacts_same(false, isset(wp_seed_content_divi_directory_contact_condition_definitions()['wpsckDirectoryContactAppointmentPresent']), 'Removed custom condition disappears');
$GLOBALS['contacts_options'][wp_seed_content_directory_contact_type_option_key()] = array_values($configured);

$used_removal = wp_seed_content_directory_apply_contact_type_removals(
    array_values(array_filter($configured, function ($row) { return 'booking' !== $row['slug']; })),
    $configured,
    array('booking'),
    array('booking' => 2)
);
contacts_same('used', $used_removal['blocked']['booking'], 'Used custom type removal is blocked');
contacts_same(true, isset($used_removal['settings']['booking']), 'Blocked used type remains registered');

$used_social_removal = wp_seed_content_directory_apply_contact_type_removals(
    array_values(array_filter($configured, function ($row) { return !in_array($row['slug'], array('facebook', 'instagram'), true); })),
    $configured,
    array('facebook', 'instagram'),
    array('facebook' => 3, 'instagram' => 2)
);
contacts_same('used', $used_social_removal['blocked']['facebook'], 'Used Facebook type removal is blocked');
contacts_same('used', $used_social_removal['blocked']['instagram'], 'Used Instagram type removal is blocked');
contacts_same(true, isset($used_social_removal['settings']['facebook']), 'Blocked Facebook type remains registered');
contacts_same(true, isset($used_social_removal['settings']['instagram']), 'Blocked Instagram type remains registered');
$unused_facebook_removal = wp_seed_content_directory_apply_contact_type_removals(
    array_values(array_filter($configured, function ($row) { return 'facebook' !== $row['slug']; })),
    $configured,
    array('facebook'),
    array('facebook' => 0)
);
contacts_same(array('facebook'), $unused_facebook_removal['removed'], 'Unused Facebook type follows normal configurable deletion');
contacts_same(false, isset($unused_facebook_removal['settings']['facebook']), 'Unused Facebook deletion removes its registry row');
$used_website_removal = wp_seed_content_directory_apply_contact_type_removals(
    array_values(array_filter($configured, function ($row) { return 'website' !== $row['slug']; })),
    $configured,
    array('website'),
    array('website' => 8)
);
contacts_same('used', $used_website_removal['blocked']['website'], 'Used Website type removal is blocked');
contacts_same(true, isset($used_website_removal['settings']['website']), 'Blocked Website type remains registered');

$disabled_used = $configured;
$disabled_used['booking']['active'] = false;
$disabled_result = wp_seed_content_directory_apply_contact_type_removals(array_values($disabled_used), $configured, array(), array());
contacts_same(false, $disabled_result['settings']['booking']['active'], 'Used custom type can be disabled without deletion');
$disabled_social = $configured;
$disabled_social['facebook']['active'] = false;
$disabled_social['instagram']['active'] = false;
$disabled_social_result = wp_seed_content_directory_apply_contact_type_removals(array_values($disabled_social), $configured, array(), array());
contacts_same(false, $disabled_social_result['settings']['facebook']['active'], 'Facebook can be disabled without deletion');
contacts_same(false, $disabled_social_result['settings']['instagram']['active'], 'Instagram can be disabled without deletion');
$disabled_website = $configured;
$disabled_website['website']['active'] = false;
$disabled_website_result = wp_seed_content_directory_apply_contact_type_removals(array_values($disabled_website), $configured, array(), array());
contacts_same(false, $disabled_website_result['settings']['website']['active'], 'Website can be disabled without deletion');

$system_removal = wp_seed_content_directory_apply_contact_type_removals(
    array_values(array_filter($configured, function ($row) { return 'phone' !== $row['slug']; })),
    $configured,
    array('phone'),
    array('phone' => 0)
);
contacts_same('system', $system_removal['blocked']['phone'], 'System phone removal is refused');
contacts_same(true, isset($system_removal['settings']['phone']), 'System phone remains registered after crafted removal');
contacts_same('system', wp_seed_content_directory_apply_contact_type_removals(array_values($configured), $configured, array('email'), array('email' => 0))['blocked']['email'], 'System email removal is refused');
contacts_same('unknown', wp_seed_content_directory_apply_contact_type_removals(array_values($configured), $configured, array('unknown'), array())['blocked']['unknown'], 'Unknown removal slug is refused');
contacts_same($meta_before_removal_tests, serialize($GLOBALS['contacts_meta']), 'Registry removal decisions never alter contact rows');
$condition_definitions = wp_seed_content_divi_directory_contact_condition_definitions();
contacts_same('loop_wpsck_directory_youtube', $condition_definitions['wpsckDirectoryContactYoutubePresent']['provider'], 'Future linked condition uses its canonical loop provider');
contacts_same('loop_wpsck_directory_speciality-note', $condition_definitions['wpsckDirectoryContactSpecialityNotePresent']['provider'], 'Future display-only condition uses its canonical loop provider');
$preserved = wp_seed_content_directory_sanitize_contact_type_settings(array(
    array('slug' => 'phone', 'label' => 'Téléphone', 'behavior' => 'phone', 'active' => 1, 'order' => 10),
    array('slug' => 'email', 'label' => 'E-mail', 'behavior' => 'email', 'active' => 1, 'order' => 20),
), $configured);
contacts_same(true, isset($preserved['appointment']), 'Omitted existing type remains registered');
contacts_same(false, $preserved['appointment']['active'], 'Omitted existing type becomes inactive');
contacts_same('appointment', $preserved['appointment']['slug'], 'Inactive type stable slug preserved');
$safe_custom = wp_seed_content_directory_sanitize_contact_type_settings(array(
    array('slug' => 'Custom Booking<script>', 'label' => 'Réserver', 'behavior' => 'javascript', 'active' => 1, 'order' => 10),
));
contacts_same(true, isset($safe_custom['custombookingscript']), 'Custom slug sanitized');
contacts_same('url_or_text', $safe_custom['custombookingscript']['behavior'], 'Unknown behavior falls back safely');

ob_start();
wp_seed_content_directory_render_contact_type_settings();
$registry_html = ob_get_clean();
contacts_assert(false !== strpos($registry_html, 'data-wpsck-contact-type-add'), 'Registry UI exposes add action');
contacts_assert(false !== strpos($registry_html, 'data-wpsck-contact-type-up'), 'Registry UI exposes ordering actions');
contacts_assert(false !== strpos($registry_html, 'data-wpsck-contact-type-remove'), 'Registry UI exposes custom removal action');
contacts_assert(false !== strpos($registry_html, 'data-wpsck-contact-type-new="1"'), 'New rows expose immediate unsaved removal state');
contacts_assert(false !== strpos($registry_html, 'Type système'), 'Registry UI marks protected system types');
contacts_assert(false !== strpos($registry_html, 'wp_seed_content_directory_contact_type_remove[]'), 'Registry UI submits explicit removal intent');
ob_start();
wp_seed_content_directory_render_contact_type_settings_row($configured['phone'], 0, false, 0);
$system_row_html = ob_get_clean();
contacts_assert(false === strpos($system_row_html, 'data-wpsck-contact-type-remove'), 'System row exposes no destructive action');
ob_start();
wp_seed_content_directory_render_contact_type_settings_row($configured['booking'], 1, false, 2);
$used_row_html = ob_get_clean();
contacts_assert(false !== strpos($used_row_html, 'Utilisé par 2 coordonnées.'), 'Used custom row explains why deletion is blocked');
ob_start();
wp_seed_content_directory_render_contact_type_settings_row($configured['facebook'], 2, false, 3);
$facebook_row_html = ob_get_clean();
contacts_assert(false !== strpos($facebook_row_html, 'data-wpsck-contact-type-remove'), 'Facebook row exposes configurable removal action');
contacts_assert(false === strpos($facebook_row_html, 'Type système'), 'Facebook row has no system badge');
contacts_assert(false !== strpos($facebook_row_html, 'Utilisé par 3 coordonnées.'), 'Facebook row explains why deletion is blocked');
ob_start();
wp_seed_content_directory_render_contact_type_settings_row($configured['instagram'], 3, false, 2);
$instagram_row_html = ob_get_clean();
contacts_assert(false !== strpos($instagram_row_html, 'data-wpsck-contact-type-remove'), 'Instagram row exposes configurable removal action');
contacts_assert(false === strpos($instagram_row_html, 'Type système'), 'Instagram row has no system badge');
contacts_assert(false !== strpos($instagram_row_html, 'Utilisé par 2 coordonnées.'), 'Instagram row explains why deletion is blocked');
ob_start();
wp_seed_content_directory_render_contact_type_settings_row($configured['website'], 4, false, 8);
$website_row_html = ob_get_clean();
contacts_assert(false !== strpos($website_row_html, 'data-wpsck-contact-type-remove'), 'Website row exposes configurable removal action');
contacts_assert(false === strpos($website_row_html, 'Type système'), 'Website row has no system badge');
contacts_assert(false !== strpos($website_row_html, 'Utilisé par 8 coordonnées.'), 'Website row explains why deletion is blocked');
contacts_assert(false !== strpos($registry_html, '[active]'), 'Registry UI exposes active state');
contacts_assert(false !== strpos($registry_html, '[individual_provider]'), 'Registry UI exposes explicit individual provider opt-in');
contacts_assert(false === strpos($registry_html, '[show_icon]'), 'Registry UI exposes no presentation icon state');
contacts_assert(false === strpos($registry_html, '[icon_size]'), 'Registry UI exposes no presentation icon size');
$admin_source = file_get_contents($root . '/plugin/includes/modules/directory/admin.php');
contacts_assert(false !== strpos($admin_source, "__('Lien complet'"), 'Directory editor renames contact value to full link');
contacts_assert(false !== strpos($admin_source, '[format]'), 'Directory editor marks submissions with the full-link contract');
contacts_assert(false === strpos($admin_source, 'Icône personnalisée'), 'Directory editor removes custom contact icon UI');
contacts_assert(false === strpos($admin_source, 'data-seed-directory-icon-select'), 'Directory editor removes custom icon media behavior');
$registry_source = file_get_contents($root . '/plugin/includes/modules/directory/contact-registry.php');
contacts_assert(false !== strpos($registry_source, "current_user_can('manage_wp_seed_content_kit')"), 'Registry save keeps a backend capability guard');
contacts_assert(false !== strpos($registry_source, 'wp_verify_nonce'), 'Registry save keeps a backend nonce guard');
$validation_source = file_get_contents($root . '/plugin/includes/modules/directory/validation.php');
contacts_assert(false !== strpos($validation_source, "'full_link_v1' === \$contact['format']"), 'Publication validation enforces the submitted full-link contract');
contacts_assert(false !== strpos($registry_html, 'slugs enregistrés restent immuables'), 'Registry UI explains stable slugs');
$raw = array(
    array('row_id' => 'primary', 'type' => 'phone', 'label' => 'Cabinet', 'value' => 'tel:+33122334455', 'public' => 1, 'order' => 20, 'format' => 'full_link_v1'),
    array('row_id' => 'secondary', 'type' => 'phone', 'value' => '+33 6 11 22 33 44', 'public' => 1, 'order' => 10),
    array('row_id' => 'mail', 'type' => 'email', 'value' => 'mailto:private@example.test', 'public' => 0, 'order' => 30, 'format' => 'full_link_v1'),
    array('row_id' => 'bad', 'type' => 'email', 'value' => 'mailto:not-an-email', 'public' => 1, 'order' => 40, 'format' => 'full_link_v1'),
    array('row_id' => 'missing-scheme', 'type' => 'phone', 'value' => '+33 6 55 44 33 22', 'public' => 1, 'order' => 45, 'format' => 'full_link_v1'),
    array('row_id' => 'site', 'type' => 'website', 'value' => 'https://example.test', 'public' => 1, 'order' => 50, 'format' => 'full_link_v1'),
    array('row_id' => 'inactive-linkedin', 'type' => 'linkedin', 'value' => 'https://linkedin.com/in/example', 'public' => 1, 'order' => 60),
    array('row_id' => 'youtube-private', 'type' => 'youtube', 'value' => 'https://youtube.test/private', 'public' => 0, 'order' => 70),
    array('row_id' => 'youtube-invalid', 'type' => 'youtube', 'value' => 'not a url', 'public' => 1, 'order' => 80),
    array('row_id' => 'youtube-public', 'type' => 'youtube', 'value' => 'https://youtube.test/public', 'public' => 1, 'order' => 90),
    array('row_id' => 'speciality-public', 'type' => 'speciality-note', 'value' => 'Sur rendez-vous', 'public' => 1, 'order' => 100),
);
$normalized = wp_seed_content_directory_sanitize_contacts($raw);
contacts_same(array('secondary', 'primary', 'mail', 'bad', 'missing-scheme', 'site', 'youtube-private', 'youtube-invalid', 'youtube-public', 'speciality-public'), array_column($normalized, 'row_id'), 'Manual order with duplicate, private and invalid types');
update_post_meta(101, 'seed_directory_contacts', $normalized);
$public = wp_seed_content_directory_get_public_contact_rows(101);
contacts_same(array('secondary', 'primary', 'site', 'youtube-public', 'speciality-public'), array_column($public, 'row_id'), 'Private and invalid public rows excluded');
contacts_same(false, in_array('inactive-linkedin', array_column($public, 'row_id'), true), 'Inactive type data retained but not exposed');
contacts_same(false, in_array('missing-scheme', array_column($public, 'row_id'), true), 'New full-link row without its required scheme is not exposed');
contacts_same('tel:+33611223344', $public[0]['href'], 'Phone href normalized');
contacts_same('+33 6 11 22 33 44', $public[0]['value'], 'Legacy phone remains readable without a label');
contacts_same('Cabinet', $public[1]['value'], 'Custom label overrides linked display value');
contacts_same('tel:+33122334455', $public[1]['href'], 'Full phone link is returned without a duplicate scheme');
contacts_same('https://example.test', $public[2]['href'], 'Website href preserved');
contacts_same(false, isset($public[1]['icon']), 'Public contact projection contains no presentation icon');
contacts_same(array('site'), array_column(wp_seed_content_directory_get_public_contact_rows(101, array('website')), 'row_id'), 'Per-loop type filter');
contacts_same('secondary', wp_seed_content_directory_get_first_public_contact(101, 'phone')['row_id'], 'Individual provider uses first ordered row');
$youtube = wp_seed_content_directory_get_first_public_contact(101, 'youtube');
$speciality = wp_seed_content_directory_get_first_public_contact(101, 'speciality-note');
$condition_value_key = wp_seed_content_divi_directory_contact_condition_value_key();
contacts_same('youtube-public', $youtube['row_id'], 'Future linked provider receives only the first valid public row');
contacts_same('https://youtube.test/public', $youtube['href'], 'Future linked provider derives its canonical href');
contacts_same(true, wp_seed_content_divi_directory_evaluate_has_more_condition(null, $individual['youtube']['condition_name'], array($condition_value_key => $youtube['value']), 'youtube-public'), 'Future linked condition is true for a valid public value');
contacts_same(true, wp_seed_content_divi_directory_evaluate_has_more_condition(null, $individual['speciality-note']['condition_name'], array($condition_value_key => $speciality['value']), 'speciality-public'), 'Future display-only condition is true for a valid public value');
contacts_same(false, wp_seed_content_divi_directory_evaluate_has_more_condition(null, $individual['youtube']['condition_name'], array($condition_value_key => ''), 'youtube-empty'), 'Future linked condition is false when projection returns empty');
contacts_same(false, wp_seed_content_divi_directory_evaluate_has_more_condition(null, $individual['youtube']['condition_name'], array($condition_value_key => wp_seed_content_directory_sanitize_contact_value('youtube', 'not a url', true)), 'youtube-invalid'), 'Future linked condition is false for an invalid value');
contacts_same(null, wp_seed_content_directory_get_first_public_contact(101, 'booking'), 'Type without an eligible public row projects no individual value');
contacts_same('', wp_seed_content_directory_contact_href('phone', 'tel:tel:+33612345678'), 'Duplicate telephone scheme is rejected');
contacts_same('tel:+33612345678', wp_seed_content_directory_contact_href('phone', 'tel:+33612345678'), 'Full telephone link is stable');
contacts_same('mailto:contact@example.test', wp_seed_content_directory_contact_href('email', 'mailto:contact@example.test'), 'Full e-mail link is stable');
contacts_same('contact@example.test', wp_seed_content_directory_contact_display_value('email', 'mailto:contact@example.test'), 'E-mail display strips mailto without a label');
contacts_same('Me contacter', wp_seed_content_directory_contact_display_value('email', 'mailto:contact@example.test', 'Me contacter'), 'Optional label wins over full link display');
contacts_same(false, wp_seed_content_directory_is_full_contact_link('phone', '+33612345678'), 'Legacy phone is not a full-link editor value');
contacts_same(true, wp_seed_content_directory_is_full_contact_link('phone', 'tel:+33612345678'), 'Telephone full-link editor value validates');
contacts_same(false, wp_seed_content_directory_is_full_contact_link('email', 'contact@example.test'), 'Legacy e-mail is not a full-link editor value');
contacts_same(true, wp_seed_content_directory_is_full_contact_link('email', 'mailto:contact@example.test'), 'E-mail full-link editor value validates');
$presence_cases = array(
    103 => array('contact' => array('row_id' => 'private-only', 'type' => 'youtube', 'value' => 'https://youtube.test/private-only', 'public' => 0, 'order' => 10), 'expected' => false, 'label' => 'private'),
    104 => array('contact' => array('row_id' => 'invalid-only', 'type' => 'youtube', 'value' => 'not a url', 'public' => 1, 'order' => 10), 'expected' => false, 'label' => 'invalid'),
    105 => array('contact' => array('row_id' => 'empty-only', 'type' => 'youtube', 'value' => '', 'public' => 1, 'order' => 10), 'expected' => false, 'label' => 'empty'),
    106 => array('contact' => array('row_id' => 'public-only', 'type' => 'youtube', 'value' => 'https://youtube.test/public-only', 'public' => 1, 'order' => 10), 'expected' => true, 'label' => 'public valid'),
    107 => array('contact' => array('row_id' => 'label-only', 'type' => 'youtube', 'label' => 'Mon profil', 'value' => '', 'public' => 1, 'order' => 10, 'format' => 'full_link_v1'), 'expected' => false, 'label' => 'label only'),
);
foreach ($presence_cases as $post_id => $case) {
    update_post_meta($post_id, 'seed_directory_contacts', wp_seed_content_directory_sanitize_contacts(array($case['contact'])));
    $projected_contact = wp_seed_content_directory_get_first_public_contact($post_id, 'youtube');
    $projected_value = is_array($projected_contact) ? $projected_contact['value'] : '';
    contacts_same(
        $case['expected'],
        wp_seed_content_divi_directory_evaluate_has_more_condition(
            null,
            $individual['youtube']['condition_name'],
            array($condition_value_key => $projected_value),
            'youtube-' . $case['label']
        ),
        'Presence condition follows the ' . $case['label'] . ' public projection'
    );
}
$GLOBALS['contacts_eligible'][101] = false;
contacts_same(array(), wp_seed_content_directory_get_public_contact_rows(101), 'Ineligible entry exposes no contacts');
$GLOBALS['contacts_eligible'][101] = true;

$GLOBALS['contacts_meta'][102] = array(
    '_seed_directory_phone' => '+33 1 00 00 00 00', '_seed_directory_phone_visible' => '1',
    '_seed_directory_email' => 'legacy@example.test', '_seed_directory_email_visible' => '',
);
contacts_same(array('legacy-phone', 'legacy-email'), array_column(wp_seed_content_directory_get_contacts(102), 'row_id'), 'Legacy fallback synthesized');
$full_link_plan = wp_seed_content_plan_directory_contact_full_links(array(102));
contacts_same(2, $full_link_plan['rows'], 'Full-link dry-run audits legacy effective rows');
contacts_same(2, $full_link_plan['migratable'], 'Legacy phone and e-mail are deterministically migratable');
contacts_same(array('email' => 1, 'phone' => 1), $full_link_plan['by_type'], 'Full-link dry-run classifies migration without exposing values');
contacts_same(false, metadata_exists('post', 102, 'seed_directory_contacts'), 'Full-link dry-run performs no write');
update_post_meta(102, 'seed_directory_contacts', array());
contacts_same(array(), wp_seed_content_directory_get_contacts(102), 'Canonical empty wins over legacy');
delete_post_meta(102, 'seed_directory_contacts');
$migration = wp_seed_content_migrate_directory_contacts(array(102));
contacts_same(array(102), $migration['updated'], 'Explicit migration writes canonical contacts');
contacts_same(10, get_post_meta(102, 'seed_directory_contacts', true)[0]['order'], 'Legacy migration deterministic order');
contacts_same('+33 1 00 00 00 00', get_post_meta(102, '_seed_directory_phone', true), 'Legacy value preserved');
$rollback = wp_seed_content_rollback_directory_contacts($migration);
contacts_same(array(102), $rollback['restored'], 'Migration rollback applied');
contacts_same(false, metadata_exists('post', 102, 'seed_directory_contacts'), 'Rollback restores missing canonical state');

wp_seed_content_directory_register_contacts_meta();
contacts_same('seed_directory_contacts', $GLOBALS['registered_contacts_meta'][1], 'Canonical meta registered');
contacts_same(false, $GLOBALS['registered_contacts_meta'][2]['show_in_rest'], 'Raw canonical meta private in REST');

if ($GLOBALS['contacts_failures']) {
    fwrite(STDERR, 'FAIL ' . count($GLOBALS['contacts_failures']) . '/' . $GLOBALS['contacts_assertions'] . ': ' . implode(', ', $GLOBALS['contacts_failures']) . PHP_EOL);
    exit(1);
}
echo 'PASS ' . $GLOBALS['contacts_assertions'] . ' Directory repeatable contacts assertions' . PHP_EOL;
