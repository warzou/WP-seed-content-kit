<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_meta_definitions()
{
    return array(
        '_seed_directory_status' => array('type' => 'status'),
        '_seed_directory_city' => array('type' => 'text'),
        '_seed_directory_postal_code' => array('type' => 'postal_code'),
        '_seed_directory_department' => array('type' => 'department'),
        '_seed_directory_country' => array('type' => 'country', 'default' => 'FR'),
        '_seed_directory_featured' => array('type' => 'boolean'),
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

function wp_seed_content_directory_get_statuses()
{
    return array(
        'practicing' => __('En activité', 'wp-seed-content-kit'),
        'seeking_models' => __('Recherche de modèles', 'wp-seed-content-kit'),
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
    if ('textarea' === $type) {
        return sanitize_textarea_field($value);
    }
    if ('status' === $type) {
        $value = sanitize_key($value);
        $statuses = wp_seed_content_directory_get_statuses();
        return isset($statuses[$value]) ? $value : '';
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
    if ('facebook' === $type) {
        return wp_seed_content_directory_sanitize_http_url($value, 'facebook.com');
    }
    if ('instagram' === $type) {
        return wp_seed_content_directory_sanitize_http_url($value, 'instagram.com');
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
    $value = get_post_meta($post_id, $key, true);
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
        register_post_meta('seed_directory', $key, array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,
            'sanitize_callback' => 'wp_seed_content_directory_sanitize_registered_meta',
            'auth_callback' => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_seed_directory_entry', $post_id);
            },
        ));
    }
}
