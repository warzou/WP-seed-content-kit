<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_meta_definitions()
{
    return array(
        '_seed_directory_status' => array('type' => 'status'),
        '_seed_directory_profile_types' => array('type' => 'profile_types'),
        '_seed_directory_seeking_models' => array('type' => 'boolean'),
        '_seed_directory_publicly_listed' => array('type' => 'boolean'),
        '_seed_directory_city' => array('type' => 'text'),
        '_seed_directory_postal_code' => array('type' => 'postal_code'),
        '_seed_directory_department' => array('type' => 'department'),
        '_seed_directory_country' => array('type' => 'country', 'default' => 'FR'),
        '_seed_directory_featured' => array('type' => 'boolean'),
        '_seed_directory_profession' => array('type' => 'text'),
        '_seed_directory_phone' => array('type' => 'phone'),
        '_seed_directory_phone_visible' => array('type' => 'boolean'),
        '_seed_directory_email' => array('type' => 'email'),
        '_seed_directory_email_visible' => array('type' => 'boolean'),
        '_seed_directory_website' => array('type' => 'url'),
        '_seed_directory_website_visible' => array('type' => 'boolean'),
        '_seed_directory_facebook' => array('type' => 'facebook'),
        '_seed_directory_facebook_visible' => array('type' => 'boolean'),
        '_seed_directory_instagram' => array('type' => 'instagram'),
        '_seed_directory_instagram_visible' => array('type' => 'boolean'),
        '_seed_directory_publication_authorized' => array('type' => 'boolean'),
        '_seed_directory_internal_note' => array('type' => 'textarea'),
        '_seed_directory_last_verified' => array('type' => 'date'),
    );
}

function wp_seed_content_directory_get_profile_types()
{
    return function_exists('wp_seed_content_directory_classification_options')
        ? wp_seed_content_directory_classification_options('profile_type', true)
        : array('praticien' => __('Praticien', 'wp-seed-content-kit'), 'intervenant' => __('Intervenant', 'wp-seed-content-kit'));
}

function wp_seed_content_directory_normalize_profile_types($value)
{
    if (is_string($value)) {
        $value = '' === trim($value) ? array() : explode(',', $value);
    }
    if (!is_array($value)) {
        return array();
    }

    $requested = array();
    foreach ($value as $profile_type) {
        if (!is_scalar($profile_type)) {
            continue;
        }
        $profile_type = sanitize_key(trim((string) $profile_type));
        if ('' !== $profile_type) {
            $requested[$profile_type] = true;
        }
    }

    if (function_exists('wp_seed_content_directory_normalize_profile_slugs')) {
        return wp_seed_content_directory_normalize_profile_slugs(array_keys($requested), true);
    }
    return array_values(array_intersect(array_keys(wp_seed_content_directory_get_profile_types()), array_keys($requested)));
}

function wp_seed_content_directory_get_profile_type_labels($profile_types)
{
    $labels = array();
    $registered = function_exists('wp_seed_content_directory_classification_options')
        ? wp_seed_content_directory_classification_options('profile_type', false)
        : wp_seed_content_directory_get_profile_types();
    foreach (wp_seed_content_directory_normalize_profile_types($profile_types) as $profile_type) {
        if (isset($registered[$profile_type])) {
            $labels[] = $registered[$profile_type];
        }
    }

    return $labels;
}

function wp_seed_content_directory_get_statuses()
{
    return function_exists('wp_seed_content_directory_classification_options')
        ? wp_seed_content_directory_classification_options('status', true)
        : array('en_exercice' => __('En exercice', 'wp-seed-content-kit'), 'recherche_modeles' => __('En recherche de modèles', 'wp-seed-content-kit'));
}

function wp_seed_content_directory_get_contact_definitions()
{
    return array(
        'phone' => array(
            'key' => '_seed_directory_phone',
            'visible_key' => '_seed_directory_phone_visible',
            'label' => __('Téléphone', 'wp-seed-content-kit'),
            'type' => 'tel',
            'visibility_label' => __('Afficher ce numéro dans l’annuaire', 'wp-seed-content-kit'),
            'error' => 'invalid_public_phone',
        ),
        'email' => array(
            'key' => '_seed_directory_email',
            'visible_key' => '_seed_directory_email_visible',
            'label' => __('Adresse e-mail', 'wp-seed-content-kit'),
            'type' => 'email',
            'visibility_label' => __('Afficher cette adresse e-mail dans l’annuaire', 'wp-seed-content-kit'),
            'error' => 'invalid_public_email',
        ),
        'website' => array(
            'key' => '_seed_directory_website',
            'visible_key' => '_seed_directory_website_visible',
            'label' => __('Site internet', 'wp-seed-content-kit'),
            'type' => 'url',
            'visibility_label' => __('Afficher ce site dans l’annuaire', 'wp-seed-content-kit'),
            'error' => 'invalid_public_website',
        ),
        'facebook' => array(
            'key' => '_seed_directory_facebook',
            'visible_key' => '_seed_directory_facebook_visible',
            'label' => __('Lien Facebook', 'wp-seed-content-kit'),
            'type' => 'url',
            'visibility_label' => __('Afficher ce lien Facebook dans l’annuaire', 'wp-seed-content-kit'),
            'error' => 'invalid_public_facebook',
        ),
        'instagram' => array(
            'key' => '_seed_directory_instagram',
            'visible_key' => '_seed_directory_instagram_visible',
            'label' => __('Lien Instagram', 'wp-seed-content-kit'),
            'type' => 'url',
            'visibility_label' => __('Afficher ce lien Instagram dans l’annuaire', 'wp-seed-content-kit'),
            'error' => 'invalid_public_instagram',
        ),
    );
}
function wp_seed_content_directory_get_country_codes()
{
    static $codes = null;
    if (null === $codes) {
        $codes = array_flip(explode(' ', 'AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO DZ EC EE EG EH ER ES ET FI FJ FK FM FO FR GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY HK HM HN HR HT HU ID IE IL IM IN IO IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL NO NP NR NU NZ OM PA PE PF PG PH PK PL PM PN PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN VU WF WS YE YT ZA ZM ZW'));
    }

    return $codes;
}

function wp_seed_content_directory_sanitize_http_url($value, $allowed_host = '')
{
    if (!is_scalar($value)) {
        return '';
    }
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }
    $raw_parts = wp_parse_url($value);
    if (!$raw_parts || empty($raw_parts['scheme']) || empty($raw_parts['host']) || !in_array(strtolower($raw_parts['scheme']), array('http', 'https'), true)) {
        return '';
    }
    $url = esc_url_raw($value, array('http', 'https'));
    $parts = $url ? wp_parse_url($url) : false;
    if (!$parts || empty($parts['host'])) {
        return '';
    }
    if ('' !== $allowed_host) {
        $host = strtolower(rtrim($parts['host'], '.'));
        if ($host !== $allowed_host && substr($host, -(strlen($allowed_host) + 1)) !== '.' . $allowed_host) {
            return '';
        }
    }
    return $url;
}

function wp_seed_content_directory_sanitize_phone($value)
{
    if (!is_scalar($value)) {
        return '';
    }
    $value = sanitize_text_field((string) $value);
    if ('' === $value || preg_match('/[^0-9+().\-\s]/u', $value)) {
        return '';
    }
    $value = preg_replace('/\s+/u', ' ', trim($value));
    return strlen($value) <= 40 && preg_match('/\d/', $value) ? $value : '';
}

function wp_seed_content_directory_sanitize_private_contact($value, $maximum = 2048)
{
    if (!is_scalar($value)) {
        return '';
    }
    $value = sanitize_text_field((string) $value);
    return strlen($value) <= $maximum ? $value : substr($value, 0, $maximum);
}

function wp_seed_content_directory_normalize_contact_value($key, $value)
{
    $definitions = wp_seed_content_directory_get_meta_definitions();
    if (!isset($definitions[$key])) {
        return '';
    }
    $type = $definitions[$key]['type'];
    if ('phone' === $type) {
        return wp_seed_content_directory_sanitize_phone($value);
    }
    if ('email' === $type) {
        $value = sanitize_email($value);
        return $value && is_email($value) ? $value : '';
    }
    if ('url' === $type) {
        return wp_seed_content_directory_sanitize_http_url($value);
    }
    return '';
}
function wp_seed_content_directory_sanitize_meta_value($key, $value)
{
    $definitions = wp_seed_content_directory_get_meta_definitions();
    if (!isset($definitions[$key])) {
        return '';
    }
    $type = $definitions[$key]['type'];
    if ('boolean' === $type) {
        return !empty($value) ? '1' : '';
    }
    if ('profile_types' === $type) {
        return wp_seed_content_directory_normalize_profile_types($value);
    }
    if ('textarea' === $type) {
        return sanitize_textarea_field($value);
    }
    if ('status' === $type) {
        if (function_exists('wp_seed_content_directory_normalize_status_slug')) {
            return wp_seed_content_directory_normalize_status_slug($value, true);
        }
        $legacy = array('practicing' => 'en_exercice', 'seeking_models' => 'recherche_modeles');
        $value = sanitize_key($value);
        return isset($legacy[$value]) ? $legacy[$value] : (isset(wp_seed_content_directory_get_statuses()[$value]) ? $value : '');
    }
    if ('postal_code' === $type || 'department' === $type) {
        $value = strtoupper(sanitize_text_field($value));
        $maximum = 'postal_code' === $type ? 16 : 12;
        return '' !== $value && strlen($value) <= $maximum && preg_match('/^[A-Z0-9 -]+$/D', $value) ? $value : '';
    }
    if ('country' === $type) {
        $value = strtoupper(sanitize_text_field($value));
        $countries = wp_seed_content_directory_get_country_codes();
        return isset($countries[$value]) ? $value : '';
    }
    if (in_array($type, array('phone', 'email', 'url', 'facebook', 'instagram'), true)) {
        return wp_seed_content_directory_sanitize_private_contact($value, 'phone' === $type ? 40 : 2048);
    }
    if ('date' === $type) {
        return wp_seed_content_sanitize_iso_date($value);
    }
    return sanitize_text_field($value);
}

function wp_seed_content_directory_get_meta_value($post_id, $key)
{
    $definitions = wp_seed_content_directory_get_meta_definitions();
    if (!isset($definitions[$key])) {
        return '';
    }
    if ('_seed_directory_status' === $key && function_exists('wp_seed_content_directory_resolve_status')) {
        return wp_seed_content_directory_resolve_status($post_id);
    }
    if ('_seed_directory_profile_types' === $key && function_exists('wp_seed_content_directory_resolve_profile_types')) {
        return wp_seed_content_directory_resolve_profile_types($post_id);
    }
    if ('_seed_directory_seeking_models' === $key && function_exists('wp_seed_content_directory_resolve_status')) {
        return 'recherche_modeles' === wp_seed_content_directory_resolve_status($post_id) ? '1' : '';
    }
    $public_key = function_exists('wp_seed_content_directory_builder_meta_public_key')
        ? wp_seed_content_directory_builder_meta_public_key($key)
        : '';
    if ('' !== $public_key) {
        $value = wp_seed_content_directory_get_builder_meta($post_id, $public_key);
        if ('boolean' === $definitions[$key]['type']) {
            return $value ? '1' : '';
        }
        if ('status' === $definitions[$key]['type']) {
            return wp_seed_content_directory_sanitize_meta_value($key, $value);
        }
        return $value;
    }

    $value = get_post_meta($post_id, $key, true);
    if ('status' === $definitions[$key]['type']) {
        return wp_seed_content_directory_sanitize_meta_value($key, $value);
    }
    if ('profile_types' === $definitions[$key]['type']) {
        return wp_seed_content_directory_normalize_profile_types($value);
    }
    if ('' === $value && isset($definitions[$key]['default'])) {
        return $definitions[$key]['default'];
    }
    return $value;
}
function wp_seed_content_directory_sanitize_registered_meta($value, $meta_key)
{
    return wp_seed_content_directory_sanitize_meta_value($meta_key, $value);
}

function wp_seed_content_directory_register_meta_fields()
{
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        $registered_type = 'profile_types' === $definition['type'] ? 'array' : 'string';
        register_post_meta('seed_directory', $key, array(
            'type' => $registered_type,
            'single' => true,
            'show_in_rest' => false,
            'sanitize_callback' => 'wp_seed_content_directory_sanitize_registered_meta',
            'auth_callback' => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_seed_directory_entry', $post_id);
            },
        ));
    }
}
