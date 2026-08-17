<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_contacts_meta_key()
{
    return 'seed_directory_contacts';
}

function wp_seed_content_directory_legacy_contact_meta_keys()
{
    return array(
        '_seed_directory_phone', '_seed_directory_phone_visible',
        '_seed_directory_email', '_seed_directory_email_visible',
        '_seed_directory_website', '_seed_directory_website_visible',
        '_seed_directory_facebook', '_seed_directory_facebook_visible',
        '_seed_directory_instagram', '_seed_directory_instagram_visible',
    );
}

function wp_seed_content_directory_normalize_contact_types($value)
{
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        $value = is_array($decoded) ? $decoded : preg_split('/[|,]+/', $value);
    }
    if (!is_array($value)) {
        return array();
    }

    $registry = wp_seed_content_directory_contact_type_registry();
    $types = array();
    foreach ($value as $type) {
        $type = sanitize_key((string) $type);
        if (isset($registry[$type])) {
            $types[$type] = $type;
        }
    }
    return array_values($types);
}

function wp_seed_content_directory_sanitize_contact_value($type, $value, $for_public = false)
{
    $definition = wp_seed_content_directory_get_contact_type($type);
    if (!$definition || !is_scalar($value)) {
        return '';
    }
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }

    if (!$for_public) {
        return wp_seed_content_directory_sanitize_private_contact($value, 2048);
    }

    switch ($definition['value_type']) {
        case 'phone':
            if (0 === stripos($value, 'tel:')) {
                $value = substr($value, 4);
            }
            return wp_seed_content_directory_sanitize_phone($value);
        case 'email':
            if (0 === stripos($value, 'mailto:')) {
                $value = substr($value, 7);
            }
            $email = sanitize_email($value);
            return $email && is_email($email) ? $email : '';
        case 'linkedin':
            return wp_seed_content_directory_sanitize_http_url($value, 'linkedin.com');
        case 'url':
            return wp_seed_content_directory_sanitize_http_url($value);
        case 'whatsapp':
            if (0 === strpos($value, 'http://') || 0 === strpos($value, 'https://')) {
                return wp_seed_content_directory_sanitize_http_url($value, 'wa.me');
            }
            return wp_seed_content_directory_sanitize_phone($value);
        case 'url_or_text':
        case 'text':
        default:
            return sanitize_text_field($value);
    }
}

function wp_seed_content_directory_is_full_contact_link($type, $value)
{
    $definition = wp_seed_content_directory_get_contact_type($type);
    $value = is_scalar($value) ? trim((string) $value) : '';
    if (!$definition || '' === $value) {
        return false;
    }

    switch ($definition['href_type']) {
        case 'tel':
            return 0 === stripos($value, 'tel:')
                && '' !== wp_seed_content_directory_sanitize_contact_value($type, $value, true);
        case 'mailto':
            return 0 === stripos($value, 'mailto:')
                && '' !== wp_seed_content_directory_sanitize_contact_value($type, $value, true);
        case 'url':
        case 'url_if_valid':
            return '' !== wp_seed_content_directory_sanitize_contact_value($type, $value, true)
                && (0 === strpos($value, 'https://') || 0 === strpos($value, 'http://'));
        case 'whatsapp':
            return (0 === strpos($value, 'https://') || 0 === strpos($value, 'http://'))
                && '' !== wp_seed_content_directory_sanitize_contact_value($type, $value, true);
        default:
            return '' !== wp_seed_content_directory_sanitize_contact_value($type, $value, true);
    }
}

function wp_seed_content_directory_contact_href($type, $value)
{
    $definition = wp_seed_content_directory_get_contact_type($type);
    $value = wp_seed_content_directory_sanitize_contact_value($type, $value, true);
    if (!$definition || '' === $value) {
        return '';
    }

    switch ($definition['href_type']) {
        case 'tel':
            $number = preg_replace('/[^0-9+]/', '', $value);
            return $number ? 'tel:' . $number : '';
        case 'mailto':
            return 'mailto:' . $value;
        case 'url':
            return $value;
        case 'whatsapp':
            if (0 === strpos($value, 'https://') || 0 === strpos($value, 'http://')) {
                return $value;
            }
            $number = preg_replace('/\D+/', '', $value);
            return $number ? 'https://wa.me/' . $number : '';
        case 'url_if_valid':
            return wp_seed_content_directory_sanitize_http_url($value);
        default:
            return '';
    }
}

function wp_seed_content_directory_contact_display_value($type, $value, $label = '')
{
    $label = sanitize_text_field($label);
    if ('' !== $label) {
        return $label;
    }
    return wp_seed_content_directory_sanitize_contact_value($type, $value, true);
}

function wp_seed_content_directory_sanitize_contacts($contacts)
{
    if (!is_array($contacts)) {
        return array();
    }
    $normalized = array();
    $used_ids = array();
    foreach (array_values($contacts) as $index => $contact) {
        if (!is_array($contact)) {
            continue;
        }
        $type = isset($contact['type']) ? sanitize_key($contact['type']) : '';
        if (!wp_seed_content_directory_get_contact_type($type)) {
            continue;
        }
        $value = wp_seed_content_directory_sanitize_contact_value($type, isset($contact['value']) ? $contact['value'] : '', false);
        if ('' === $value) {
            continue;
        }
        $row_id = isset($contact['row_id']) ? sanitize_key($contact['row_id']) : '';
        if ('' === $row_id || isset($used_ids[$row_id])) {
            $row_id = 'contact-' . substr(hash('sha256', $type . '|' . $value . '|' . $index), 0, 16);
        }
        $used_ids[$row_id] = true;
        $normalized[] = array(
            'row_id' => $row_id,
            'type' => $type,
            'label' => isset($contact['label']) ? sanitize_text_field($contact['label']) : '',
            'value' => $value,
            'public' => !empty($contact['public']),
            'order' => isset($contact['order']) ? max(0, (int) $contact['order']) : (($index + 1) * 10),
            'format' => isset($contact['format']) && 'full_link_v1' === $contact['format'] ? 'full_link_v1' : 'legacy',
            '_position' => $index,
        );
    }
    usort($normalized, function ($left, $right) {
        if ($left['order'] === $right['order']) {
            return $left['_position'] - $right['_position'];
        }
        return $left['order'] - $right['order'];
    });
    foreach ($normalized as &$contact) {
        unset($contact['_position']);
    }
    unset($contact);
    return $normalized;
}

function wp_seed_content_directory_get_legacy_contacts($post_id)
{
    $map = array(
        'phone' => '_seed_directory_phone', 'email' => '_seed_directory_email',
        'website' => '_seed_directory_website', 'facebook' => '_seed_directory_facebook',
        'instagram' => '_seed_directory_instagram',
    );
    $contacts = array();
    foreach ($map as $type => $key) {
        $value = get_post_meta($post_id, $key, true);
        if ('' === (string) $value) {
            continue;
        }
        $definition = wp_seed_content_directory_get_contact_type($type);
        $contacts[] = array(
            'row_id' => 'legacy-' . $type,
            'type' => $type,
            'label' => '',
            'value' => $value,
            'public' => '1' === get_post_meta($post_id, $key . '_visible', true),
            'order' => isset($definition['default_order']) ? (int) $definition['default_order'] : 100,
            'format' => 'legacy',
        );
    }
    return wp_seed_content_directory_sanitize_contacts($contacts);
}

function wp_seed_content_directory_get_contacts($post_id)
{
    $key = wp_seed_content_directory_contacts_meta_key();
    if (metadata_exists('post', $post_id, $key)) {
        return wp_seed_content_directory_sanitize_contacts(get_post_meta($post_id, $key, true));
    }
    return wp_seed_content_directory_get_legacy_contacts($post_id);
}

function wp_seed_content_directory_get_public_contact_rows($post_id, $types = array())
{
    if (!wp_seed_content_directory_is_publicly_eligible($post_id)) {
        return array();
    }
    $types = wp_seed_content_directory_normalize_contact_types($types);
    $filter = !empty($types) ? array_fill_keys($types, true) : array();
    $public = array();
    foreach (wp_seed_content_directory_get_contacts($post_id) as $contact) {
        if (empty($contact['public']) || ($filter && !isset($filter[$contact['type']]))) {
            continue;
        }
        $definition = wp_seed_content_directory_get_contact_type($contact['type']);
        if (!$definition || empty($definition['active'])) {
            continue;
        }
        $href = wp_seed_content_directory_contact_href($contact['type'], $contact['value']);
        $is_valid = 'full_link_v1' !== $contact['format']
            || wp_seed_content_directory_is_full_contact_link($contact['type'], $contact['value']);
        $value = wp_seed_content_directory_contact_display_value($contact['type'], $contact['value'], $contact['label']);
        if (!$is_valid || '' === $value || (!empty($definition['href_type']) && '' === $href)) {
            continue;
        }
        $public[] = array(
            'row_id' => $contact['row_id'],
            'type' => $contact['type'],
            'label' => $definition['label'],
            'value' => $value,
            'href' => $href,
            'order' => (int) $contact['order'],
        );
    }
    return $public;
}

function wp_seed_content_directory_get_first_public_contact($post_id, $type)
{
    $contacts = wp_seed_content_directory_get_public_contact_rows($post_id, array($type));
    return isset($contacts[0]) ? $contacts[0] : null;
}

function wp_seed_content_directory_register_contacts_meta()
{
    register_post_meta('seed_directory', wp_seed_content_directory_contacts_meta_key(), array(
        'type' => 'array',
        'single' => true,
        'show_in_rest' => false,
        'sanitize_callback' => 'wp_seed_content_directory_sanitize_contacts',
        'auth_callback' => function ($allowed, $meta_key, $post_id) {
            return current_user_can('edit_seed_directory_entry', $post_id);
        },
    ));
}
