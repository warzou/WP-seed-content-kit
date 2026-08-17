<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_value_for_validation($post_id, $key, $overrides)
{
    if (array_key_exists($key, $overrides)) {
        return $overrides[$key];
    }
    return wp_seed_content_directory_get_meta_value($post_id, $key);
}

function wp_seed_content_directory_validation_error_detail($code, $field = '', $value = '', $row_id = '', $type = '')
{
    $code = sanitize_key((string) $code);
    $field = sanitize_key((string) $field);
    $row_id = sanitize_key((string) $row_id);
    $type = sanitize_key((string) $type);
    $fingerprint = hash('sha256', is_scalar($value) ? (string) $value : serialize($value));
    $signature = hash('sha256', implode('|', array($code, $field, $row_id, $type, $fingerprint)));

    return array(
        'code' => $code,
        'field' => $field,
        'row_id' => $row_id,
        'type' => $type,
        'fingerprint' => $fingerprint,
        'signature' => $signature,
    );
}

function wp_seed_content_directory_get_publication_error_details($post_or_id, $overrides = array())
{
    $post = is_object($post_or_id) ? $post_or_id : get_post((int) $post_or_id);
    if (!$post || 'seed_directory' !== $post->post_type) {
        return array(wp_seed_content_directory_validation_error_detail('invalid_object'));
    }

    $post_id = (int) $post->ID;
    $title = array_key_exists('post_title', $overrides) ? sanitize_text_field($overrides['post_title']) : sanitize_text_field($post->post_title);
    $details = array();
    if ('' === $title) {
        $details[] = wp_seed_content_directory_validation_error_detail('missing_name', 'post_title', $title);
    }

    $statuses = function_exists('wp_seed_content_directory_classification_options')
        ? wp_seed_content_directory_classification_options('status', false)
        : wp_seed_content_directory_get_statuses();
    $status = wp_seed_content_directory_get_value_for_validation($post_id, '_seed_directory_status', $overrides);
    if (!isset($statuses[$status])) {
        $details[] = wp_seed_content_directory_validation_error_detail('invalid_status', '_seed_directory_status', $status);
    }

    $countries = wp_seed_content_directory_get_country_codes();
    $country = wp_seed_content_directory_get_value_for_validation($post_id, '_seed_directory_country', $overrides);
    if (!isset($countries[$country])) {
        $details[] = wp_seed_content_directory_validation_error_detail('invalid_country', '_seed_directory_country', $country);
    }

    if ('1' !== wp_seed_content_directory_get_value_for_validation($post_id, '_seed_directory_publication_authorized', $overrides)) {
        $details[] = wp_seed_content_directory_validation_error_detail('missing_authorization', '_seed_directory_publication_authorized', 'missing');
    }

    $contacts_key = wp_seed_content_directory_contacts_meta_key();
    if (array_key_exists($contacts_key, $overrides) || metadata_exists('post', $post_id, $contacts_key)) {
        $contacts = array_key_exists($contacts_key, $overrides)
            ? wp_seed_content_directory_sanitize_contacts($overrides[$contacts_key])
            : wp_seed_content_directory_get_contacts($post_id);
        foreach ($contacts as $contact) {
            $valid_contact = 'full_link_v1' === $contact['format']
                ? wp_seed_content_directory_is_full_contact_link($contact['type'], $contact['value'])
                : '' !== wp_seed_content_directory_sanitize_contact_value($contact['type'], $contact['value'], true);
            if (!empty($contact['public']) && !$valid_contact) {
                $details[] = wp_seed_content_directory_validation_error_detail(
                    'invalid_public_contact',
                    $contacts_key,
                    $contact['value'],
                    isset($contact['row_id']) ? $contact['row_id'] : '',
                    isset($contact['type']) ? $contact['type'] : ''
                );
            }
        }
    } else {
        foreach (wp_seed_content_directory_get_contact_definitions() as $contact) {
            $visible = wp_seed_content_directory_get_value_for_validation($post_id, $contact['visible_key'], $overrides);
            if ('1' !== $visible) {
                continue;
            }
            $value = wp_seed_content_directory_get_value_for_validation($post_id, $contact['key'], $overrides);
            if ('' === wp_seed_content_directory_normalize_contact_value($contact['key'], $value)) {
                $details[] = wp_seed_content_directory_validation_error_detail($contact['error'], $contact['key'], $value);
            }
        }
    }

    $thumbnail_id = array_key_exists('_thumbnail_id', $overrides)
        ? max(0, (int) $overrides['_thumbnail_id'])
        : ($post_id > 0 ? (int) get_post_thumbnail_id($post_id) : 0);
    if ($thumbnail_id > 0) {
        $url = wp_get_attachment_url($thumbnail_id);
        if (!wp_attachment_is_image($thumbnail_id) || '' === wp_seed_content_directory_sanitize_http_url($url)) {
            $details[] = wp_seed_content_directory_validation_error_detail('invalid_photo', '_thumbnail_id', $thumbnail_id . '|' . (string) $url);
        } else {
            $alt = array_key_exists('_wp_attachment_image_alt', $overrides)
                ? sanitize_text_field($overrides['_wp_attachment_image_alt'])
                : sanitize_text_field(get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
            if ('' === $alt) {
                $details[] = wp_seed_content_directory_validation_error_detail('missing_photo_alt', '_wp_attachment_image_alt', $thumbnail_id . '|' . $alt);
            }
        }
    }

    $unique = array();
    foreach ($details as $detail) {
        $unique[$detail['signature']] = $detail;
    }
    return array_values($unique);
}

function wp_seed_content_directory_get_publication_errors($post_or_id, $overrides = array())
{
    $errors = array();
    foreach (wp_seed_content_directory_get_publication_error_details($post_or_id, $overrides) as $detail) {
        $errors[] = $detail['code'];
    }
    return array_values(array_unique($errors));
}

function wp_seed_content_directory_normalize_publication_errors($errors)
{
    $normalized = array();
    foreach ((array) $errors as $error) {
        $error = sanitize_key((string) $error);
        if ('' !== $error) {
            $normalized[$error] = true;
        }
    }
    return array_keys($normalized);
}

function wp_seed_content_directory_compare_publication_errors($before, $after)
{
    $before = wp_seed_content_directory_normalize_publication_errors($before);
    $after = wp_seed_content_directory_normalize_publication_errors($after);
    return array(
        'before' => $before,
        'after' => $after,
        'preexisting' => array_values(array_intersect($before, $after)),
        'new' => array_values(array_diff($after, $before)),
        'resolved' => array_values(array_diff($before, $after)),
    );
}

function wp_seed_content_directory_validation_warning_meta_key()
{
    return '_wp_seed_content_directory_validation_warning';
}

function wp_seed_content_directory_get_validation_warning($post_id)
{
    return wp_seed_content_directory_normalize_publication_errors(
        get_post_meta((int) $post_id, wp_seed_content_directory_validation_warning_meta_key(), true)
    );
}

function wp_seed_content_directory_sync_validation_warning($post_id, $errors)
{
    $post_id = (int) $post_id;
    $errors = wp_seed_content_directory_normalize_publication_errors($errors);
    if ($post_id <= 0 || empty($errors)) {
        if ($post_id > 0) {
            delete_post_meta($post_id, wp_seed_content_directory_validation_warning_meta_key());
        }
        return;
    }
    update_post_meta($post_id, wp_seed_content_directory_validation_warning_meta_key(), $errors);
}

function wp_seed_content_directory_pending_validation_meta_key()
{
    return '_wp_seed_content_directory_pending_validation';
}

function wp_seed_content_directory_warning_first_error_codes()
{
    return array(
        'missing_name',
        'invalid_status',
        'invalid_country',
        'invalid_photo',
        'missing_photo_alt',
        'invalid_public_phone',
        'invalid_public_email',
        'invalid_public_website',
        'invalid_public_facebook',
        'invalid_public_instagram',
        'invalid_public_contact',
    );
}

function wp_seed_content_directory_strict_immediate_error_codes()
{
    return array('missing_authorization', 'invalid_object');
}

function wp_seed_content_directory_normalize_validation_details($details)
{
    $normalized = array();
    foreach ((array) $details as $detail) {
        if (!is_array($detail) || empty($detail['code']) || empty($detail['signature'])) {
            continue;
        }
        $code = sanitize_key((string) $detail['code']);
        $signature = preg_replace('/[^a-f0-9]/', '', strtolower((string) $detail['signature']));
        if ('' === $code || 64 !== strlen($signature)) {
            continue;
        }
        $normalized[$signature] = array(
            'code' => $code,
            'field' => isset($detail['field']) ? sanitize_key((string) $detail['field']) : '',
            'row_id' => isset($detail['row_id']) ? sanitize_key((string) $detail['row_id']) : '',
            'type' => isset($detail['type']) ? sanitize_key((string) $detail['type']) : '',
            'fingerprint' => isset($detail['fingerprint']) ? preg_replace('/[^a-f0-9]/', '', strtolower((string) $detail['fingerprint'])) : '',
            'signature' => $signature,
        );
    }
    return array_values($normalized);
}

function wp_seed_content_directory_get_pending_validation($post_id)
{
    $state = get_post_meta((int) $post_id, wp_seed_content_directory_pending_validation_meta_key(), true);
    if (!is_array($state) || empty($state['status'])) {
        return array();
    }
    $status = sanitize_key((string) $state['status']);
    if (!in_array($status, array('pending', 'drafted', 'blocked'), true)) {
        return array();
    }
    return array(
        'version' => 1,
        'status' => $status,
        'errors' => wp_seed_content_directory_normalize_validation_details(isset($state['errors']) ? $state['errors'] : array()),
        'timestamp' => isset($state['timestamp']) ? max(0, (int) $state['timestamp']) : 0,
    );
}

function wp_seed_content_directory_sync_pending_validation($post_id, $status = '', $details = array())
{
    $post_id = (int) $post_id;
    $details = wp_seed_content_directory_normalize_validation_details($details);
    if ($post_id <= 0 || !in_array($status, array('pending', 'drafted', 'blocked'), true) || empty($details)) {
        if ($post_id > 0) {
            delete_post_meta($post_id, wp_seed_content_directory_pending_validation_meta_key());
        }
        return;
    }
    update_post_meta($post_id, wp_seed_content_directory_pending_validation_meta_key(), array(
        'version' => 1,
        'status' => $status,
        'errors' => $details,
        'timestamp' => time(),
    ));
}

function wp_seed_content_directory_validation_detail_signatures($details)
{
    $signatures = array();
    foreach (wp_seed_content_directory_normalize_validation_details($details) as $detail) {
        $signatures[$detail['signature']] = true;
    }
    return array_keys($signatures);
}

function wp_seed_content_directory_filter_validation_details_by_codes($details, $codes)
{
    $allowed = array_fill_keys((array) $codes, true);
    return array_values(array_filter(wp_seed_content_directory_normalize_validation_details($details), function ($detail) use ($allowed) {
        return isset($allowed[$detail['code']]);
    }));
}

function wp_seed_content_directory_compare_publication_error_details($before, $after)
{
    $before = wp_seed_content_directory_normalize_validation_details($before);
    $after = wp_seed_content_directory_normalize_validation_details($after);
    $before_signatures = array_fill_keys(wp_seed_content_directory_validation_detail_signatures($before), true);
    $after_signatures = array_fill_keys(wp_seed_content_directory_validation_detail_signatures($after), true);
    return array(
        'before' => $before,
        'after' => $after,
        'preexisting' => array_values(array_filter($after, function ($detail) use ($before_signatures) { return isset($before_signatures[$detail['signature']]); })),
        'new' => array_values(array_filter($after, function ($detail) use ($before_signatures) { return !isset($before_signatures[$detail['signature']]); })),
        'resolved' => array_values(array_filter($before, function ($detail) use ($after_signatures) { return !isset($after_signatures[$detail['signature']]); })),
    );
}

function wp_seed_content_directory_validation_request_context($post_id, $context = null, $consume = false)
{
    static $contexts = array();
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return null;
    }
    if (is_array($context)) {
        if (!isset($contexts[$post_id])) {
            $contexts[$post_id] = array();
        }
        $contexts[$post_id][] = $context;
        return $context;
    }
    if (empty($contexts[$post_id])) {
        return null;
    }
    if (!$consume) {
        return end($contexts[$post_id]);
    }
    $current = array_pop($contexts[$post_id]);
    if (empty($contexts[$post_id])) {
        unset($contexts[$post_id]);
    }
    return $current;
}

function wp_seed_content_directory_is_publicly_eligible($post_id)
{
    $post = get_post((int) $post_id);
    if (!$post || 'seed_directory' !== $post->post_type || 'publish' !== $post->post_status || '' !== (string) $post->post_password) {
        return false;
    }
    if ('1' !== get_post_meta((int) $post_id, '_seed_directory_publicly_listed', true)) {
        return false;
    }
    return array() === wp_seed_content_directory_get_publication_errors($post);
}

function wp_seed_content_directory_collect_publication_overrides($postarr)
{
    $overrides = array();
    $meta_input = isset($postarr['meta_input']) && is_array($postarr['meta_input']) ? $postarr['meta_input'] : array();
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        if (array_key_exists($key, $meta_input)) {
            $overrides[$key] = wp_seed_content_directory_sanitize_meta_value($key, $meta_input[$key]);
        }
    }

    $has_form = isset($_POST['wp_seed_content_directory_nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_directory_nonce'])), 'wp_seed_content_directory_save');
    if ($has_form) {
        $profile_panel_present = isset($_POST['wp_seed_content_directory_profile_present']);
        $publication_panel_present = isset($_POST['wp_seed_content_directory_publication_present']);
        foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
            if (isset($_POST['wp_seed_content_directory_contacts_present']) && in_array($key, wp_seed_content_directory_legacy_contact_meta_keys(), true)) {
                continue;
            }
            if (in_array($key, array('_seed_directory_profile_types', '_seed_directory_seeking_models'), true) && !$profile_panel_present) {
                continue;
            }
            if ('_seed_directory_publicly_listed' === $key && !$publication_panel_present) {
                continue;
            }
            if ('profile_types' === $definition['type']) {
                $overrides[$key] = isset($_POST[$key])
                    ? wp_seed_content_directory_sanitize_meta_value($key, wp_unslash($_POST[$key]))
                    : array();
            } elseif ('boolean' === $definition['type']) {
                $overrides[$key] = isset($_POST[$key]) ? '1' : '';
            } elseif (array_key_exists($key, $_POST)) {
                $overrides[$key] = wp_seed_content_directory_sanitize_meta_value($key, wp_unslash($_POST[$key]));
            }
        }
        if (isset($_POST['wp_seed_content_directory_contacts_present'])) {
            $overrides[wp_seed_content_directory_contacts_meta_key()] = wp_seed_content_directory_sanitize_contacts(
                isset($_POST['seed_directory_contacts']) ? wp_unslash($_POST['seed_directory_contacts']) : array()
            );
        }
    }

    if (array_key_exists('_thumbnail_id', $postarr)) {
        $overrides['_thumbnail_id'] = max(0, (int) $postarr['_thumbnail_id']);
    } elseif (isset($_POST['_thumbnail_id'])) {
        $overrides['_thumbnail_id'] = max(0, (int) wp_unslash($_POST['_thumbnail_id']));
    }
    if (isset($_POST['_seed_directory_photo_alt'])) {
        $overrides['_wp_attachment_image_alt'] = sanitize_text_field(wp_unslash($_POST['_seed_directory_photo_alt']));
    }

    return $overrides;
}

function wp_seed_content_directory_collect_submitted_values()
{
    $values = array();
    $profile_panel_present = isset($_POST['wp_seed_content_directory_profile_present']);
    $publication_panel_present = isset($_POST['wp_seed_content_directory_publication_present']);
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        if (isset($_POST['wp_seed_content_directory_contacts_present']) && in_array($key, wp_seed_content_directory_legacy_contact_meta_keys(), true)) {
            continue;
        }
        if (in_array($key, array('_seed_directory_profile_types', '_seed_directory_seeking_models'), true) && !$profile_panel_present) {
            continue;
        }
        if ('_seed_directory_publicly_listed' === $key && !$publication_panel_present) {
            continue;
        }
        if ('profile_types' === $definition['type']) {
            $values[$key] = isset($_POST[$key])
                ? wp_seed_content_directory_sanitize_meta_value($key, wp_unslash($_POST[$key]))
                : array();
        } elseif ('boolean' === $definition['type']) {
            $values[$key] = isset($_POST[$key]) ? '1' : '';
        } elseif (array_key_exists($key, $_POST)) {
            $values[$key] = wp_seed_content_directory_sanitize_meta_value($key, wp_unslash($_POST[$key]));
        }
    }
    if (isset($_POST['wp_seed_content_directory_contacts_present'])) {
        $values[wp_seed_content_directory_contacts_meta_key()] = wp_seed_content_directory_sanitize_contacts(
            isset($_POST['seed_directory_contacts']) ? wp_unslash($_POST['seed_directory_contacts']) : array()
        );
    }
    if (isset($_POST['_seed_directory_photo_alt'])) {
        $values['_seed_directory_photo_alt'] = sanitize_text_field(wp_unslash($_POST['_seed_directory_photo_alt']));
    }
    return $values;
}

function wp_seed_content_directory_build_validation_context($post_id, $post, $requested_status, $details_before, $details_after)
{
    $post_id = (int) $post_id;
    $comparison = wp_seed_content_directory_compare_publication_error_details($details_before, $details_after);
    $strict = wp_seed_content_directory_filter_validation_details_by_codes(
        $details_after,
        wp_seed_content_directory_strict_immediate_error_codes()
    );
    $is_published_update = $post_id > 0
        && is_object($post)
        && 'publish' === $post->post_status
        && 'publish' === $requested_status;
    $pending = $post_id > 0 ? wp_seed_content_directory_get_pending_validation($post_id) : array();
    $pending_signatures = !empty($pending['errors'])
        ? array_fill_keys(wp_seed_content_directory_validation_detail_signatures($pending['errors']), true)
        : array();
    $persistent_pending = array_values(array_filter($details_after, function ($detail) use ($pending_signatures) {
        return isset($pending_signatures[$detail['signature']]);
    }));

    if (!empty($strict)) {
        $action = 'strict';
    } elseif (empty($details_after)) {
        $action = 'valid';
    } elseif (!$is_published_update) {
        $action = 'blocked';
    } elseif (!empty($persistent_pending)) {
        $action = 'drafted';
    } elseif (!empty($comparison['new'])) {
        $action = 'warning_first';
    } else {
        $action = 'grandfathered';
    }

    return array(
        'action' => $action,
        'old_status' => is_object($post) && isset($post->post_status) ? $post->post_status : '',
        'requested_status' => (string) $requested_status,
        'comparison' => $comparison,
        'details' => wp_seed_content_directory_normalize_validation_details($details_after),
    );
}

function wp_seed_content_directory_prepare_meta_save_validation($post_id, $post)
{
    $context = wp_seed_content_directory_validation_request_context((int) $post_id);
    if ($context || !is_object($post) || !in_array($post->post_status, array('publish', 'future', 'draft'), true)) {
        return $context;
    }

    $details_before = wp_seed_content_directory_get_publication_error_details($post);
    $overrides = wp_seed_content_directory_collect_publication_overrides(array('ID' => (int) $post_id));
    $overrides['post_title'] = $post->post_title;
    $details_after = wp_seed_content_directory_get_publication_error_details($post, $overrides);
    $context = wp_seed_content_directory_build_validation_context(
        (int) $post_id,
        $post,
        $post->post_status,
        $details_before,
        $details_after
    );
    wp_seed_content_directory_validation_request_context((int) $post_id, $context);
    return $context;
}

function wp_seed_content_directory_filter_insert_post_data($data, $postarr)
{
    if (!isset($data['post_type']) || 'seed_directory' !== $data['post_type']) {
        return $data;
    }

    if (isset($data['post_title'])) {
        $data['post_title'] = sanitize_text_field($data['post_title']);
    }
    if (isset($data['post_excerpt'])) {
        $data['post_excerpt'] = sanitize_textarea_field($data['post_excerpt']);
    }
    if (isset($data['menu_order'])) {
        $data['menu_order'] = max(0, (int) $data['menu_order']);
    }

    if (!isset($data['post_status']) || !in_array($data['post_status'], array('publish', 'future'), true)) {
        return $data;
    }

    $post_id = isset($postarr['ID']) ? absint($postarr['ID']) : 0;
    $post = $post_id ? get_post($post_id) : null;
    if (!$post) {
        $post = (object) array(
            'ID' => 0,
            'post_type' => 'seed_directory',
            'post_title' => isset($data['post_title']) ? $data['post_title'] : '',
            'post_status' => $data['post_status'],
            'post_password' => isset($data['post_password']) ? $data['post_password'] : '',
        );
    }

    $details_before = $post_id && $post
        ? wp_seed_content_directory_get_publication_error_details($post)
        : array();
    $overrides = wp_seed_content_directory_collect_publication_overrides($postarr);
    $overrides['post_title'] = isset($data['post_title']) ? $data['post_title'] : '';
    $details_after = wp_seed_content_directory_get_publication_error_details($post, $overrides);
    $context = wp_seed_content_directory_build_validation_context(
        $post_id,
        $post,
        $data['post_status'],
        $details_before,
        $details_after
    );
    $action = $context['action'];
    if (in_array($action, array('strict', 'blocked', 'drafted'), true)) {
        $data['post_status'] = 'draft';
    }

    if ($post_id > 0) {
        wp_seed_content_directory_validation_request_context($post_id, $context);
    }

    if (in_array($action, array('strict', 'blocked', 'drafted'), true)) {
        $reason = 'drafted' === $action ? 'persistent_error' : ('blocked' === $action ? 'publication_blocked' : 'strict');
        wp_seed_content_directory_store_publication_notice(
            $post_id,
            wp_seed_content_directory_get_error_codes_from_details($details_after),
            wp_seed_content_directory_collect_submitted_values(),
            $reason
        );
    }

    return $data;
}
add_filter('wp_insert_post_data', 'wp_seed_content_directory_filter_insert_post_data', 20, 2);

function wp_seed_content_directory_get_error_codes_from_details($details)
{
    $errors = array();
    foreach (wp_seed_content_directory_normalize_validation_details($details) as $detail) {
        $errors[$detail['code']] = true;
    }
    return array_keys($errors);
}

function wp_seed_content_directory_store_publication_notice($post_id, $errors, $values = array(), $reason = 'publication_blocked')
{
    $user_id = get_current_user_id();
    if (!$user_id || empty($errors)) {
        return;
    }
    $payload = array(
        'errors' => array_values(array_unique($errors)),
        'values' => is_array($values) ? $values : array(),
        'reason' => sanitize_key((string) $reason),
    );
    set_transient('wp_seed_content_directory_notice_' . $user_id . '_' . absint($post_id), $payload, 60);
    $GLOBALS['wp_seed_content_directory_pending_notice'] = $payload;
}

function wp_seed_content_directory_reassign_pending_notice($post_id)
{
    if (empty($GLOBALS['wp_seed_content_directory_pending_notice']) || !is_array($GLOBALS['wp_seed_content_directory_pending_notice'])) {
        return;
    }
    $payload = $GLOBALS['wp_seed_content_directory_pending_notice'];
    wp_seed_content_directory_store_publication_notice(
        $post_id,
        $payload['errors'],
        $payload['values'],
        isset($payload['reason']) ? $payload['reason'] : 'publication_blocked'
    );
    unset($GLOBALS['wp_seed_content_directory_pending_notice']);
}

function wp_seed_content_directory_get_publication_notice($post_id)
{
    static $notices = array();
    $post_id = absint($post_id);
    if (array_key_exists($post_id, $notices)) {
        return $notices[$post_id];
    }
    $key = 'wp_seed_content_directory_notice_' . get_current_user_id() . '_' . $post_id;
    $payload = get_transient($key);
    delete_transient($key);
    if (is_array($payload) && isset($payload['errors'])) {
        $notices[$post_id] = array(
            'errors' => array_values(array_unique((array) $payload['errors'])),
            'values' => isset($payload['values']) && is_array($payload['values']) ? $payload['values'] : array(),
            'reason' => isset($payload['reason']) ? sanitize_key((string) $payload['reason']) : 'publication_blocked',
        );
    } else {
        $notices[$post_id] = array('errors' => is_array($payload) ? $payload : array(), 'values' => array(), 'reason' => 'publication_blocked');
    }
    return $notices[$post_id];
}

function wp_seed_content_directory_enforce_publication($post_id, $post = null)
{
    static $demoting = false;
    if ($demoting) {
        return;
    }

    $post = $post && is_object($post) ? $post : get_post((int) $post_id);
    if (!$post || 'seed_directory' !== $post->post_type || !in_array($post->post_status, array('publish', 'future'), true)) {
        return;
    }

    $details = wp_seed_content_directory_get_publication_error_details($post);
    $errors = wp_seed_content_directory_get_error_codes_from_details($details);
    $context = wp_seed_content_directory_validation_request_context((int) $post->ID);
    if ($context && isset($context['action']) && in_array($context['action'], array('warning_first', 'grandfathered'), true) && 'publish' === $post->post_status) {
        return;
    }
    $pending = wp_seed_content_directory_get_pending_validation((int) $post->ID);
    $context_action = $context && isset($context['action']) ? $context['action'] : '';
    if (
        'publish' === $post->post_status
        && isset($pending['status'])
        && 'pending' === $pending['status']
        && !in_array($context_action, array('strict', 'blocked', 'drafted'), true)
    ) {
        $pending_signatures = array_fill_keys(wp_seed_content_directory_validation_detail_signatures($pending['errors']), true);
        foreach (wp_seed_content_directory_validation_detail_signatures($details) as $signature) {
            if (isset($pending_signatures[$signature])) {
                return;
            }
        }
    }
    if (empty($errors)) {
        wp_seed_content_directory_sync_validation_warning((int) $post->ID, array());
        wp_seed_content_directory_sync_pending_validation((int) $post->ID);
        return;
    }

    $demoting = true;
    wp_update_post(array('ID' => (int) $post->ID, 'post_status' => 'draft'));
    $demoting = false;
    $draft_state = 'drafted' === $context_action ? 'drafted' : 'blocked';
    wp_seed_content_directory_sync_pending_validation((int) $post->ID, $draft_state, $details);
    wp_seed_content_directory_store_publication_notice(
        (int) $post->ID,
        $errors,
        array(),
        'drafted' === $draft_state ? 'persistent_error' : 'publication_blocked'
    );
}

function wp_seed_content_directory_after_insert_post($post_id, $post, $update = false, $post_before = null)
{
    if (!is_object($post) || 'seed_directory' !== $post->post_type) {
        return;
    }
    $context = wp_seed_content_directory_validation_request_context((int) $post_id, null, true);
    if ($context && !empty($context['action'])) {
        $action = $context['action'];
        $details = isset($context['details']) ? $context['details'] : array();
        $errors = wp_seed_content_directory_get_error_codes_from_details($details);
        if ('warning_first' === $action && 'publish' === $post->post_status) {
            $new_details = isset($context['comparison']['new']) ? $context['comparison']['new'] : $details;
            wp_seed_content_directory_sync_pending_validation((int) $post_id, 'pending', $new_details);
            wp_seed_content_directory_sync_validation_warning((int) $post_id, $errors);
            return;
        }
        if ('grandfathered' === $action && 'publish' === $post->post_status) {
            wp_seed_content_directory_sync_pending_validation((int) $post_id);
            wp_seed_content_directory_sync_validation_warning((int) $post_id, $errors);
            return;
        }
        if ('valid' === $action) {
            wp_seed_content_directory_sync_pending_validation((int) $post_id);
            wp_seed_content_directory_sync_validation_warning((int) $post_id, array());
            return;
        }
        if (in_array($action, array('strict', 'blocked', 'drafted'), true)) {
            wp_seed_content_directory_sync_pending_validation(
                (int) $post_id,
                'drafted' === $action ? 'drafted' : 'blocked',
                $details
            );
            wp_seed_content_directory_sync_validation_warning((int) $post_id, array());
            return;
        }
    }

    if ('publish' !== $post->post_status && 'future' !== $post->post_status) {
        if (empty(wp_seed_content_directory_get_publication_error_details($post))) {
            wp_seed_content_directory_sync_pending_validation((int) $post_id);
            wp_seed_content_directory_sync_validation_warning((int) $post_id, array());
        }
        return;
    }
    wp_seed_content_directory_enforce_publication($post_id, $post);
}
add_action('wp_after_insert_post', 'wp_seed_content_directory_after_insert_post', 100, 4);

function wp_seed_content_directory_guard_scheduled_transition($new_status, $old_status, $post)
{
    if ('publish' === $new_status && 'future' === $old_status && is_object($post) && 'seed_directory' === $post->post_type) {
        wp_seed_content_directory_enforce_publication((int) $post->ID, $post);
    }
}
add_action('transition_post_status', 'wp_seed_content_directory_guard_scheduled_transition', 100, 3);

function wp_seed_content_directory_get_error_labels()
{
    return array(
        'missing_authorization' => __('Cochez l’autorisation de publication avant de publier cette fiche.', 'wp-seed-content-kit'),
        'missing_name' => __('Saisissez le nom affiché avant de publier cette fiche.', 'wp-seed-content-kit'),
        'invalid_status' => __('Choisissez le statut de la personne avant de publier cette fiche.', 'wp-seed-content-kit'),
        'invalid_country' => __('Indiquez un pays valide avant de publier cette fiche.', 'wp-seed-content-kit'),
        'invalid_photo' => __('Choisissez une photo valide avant de publier cette fiche.', 'wp-seed-content-kit'),
        'missing_photo_alt' => __('Ajoutez un texte alternatif à la photo avant de publier.', 'wp-seed-content-kit'),
        'invalid_public_phone' => __('Le numéro de téléphone public n’est pas valide.', 'wp-seed-content-kit'),
        'invalid_public_email' => __('L’adresse e-mail publique n’est pas valide.', 'wp-seed-content-kit'),
        'invalid_public_website' => __('L’adresse du site public n’est pas valide.', 'wp-seed-content-kit'),
        'invalid_public_facebook' => __('Le lien Facebook public n’est pas valide.', 'wp-seed-content-kit'),
        'invalid_public_instagram' => __('Le lien Instagram public n’est pas valide.', 'wp-seed-content-kit'),
        'invalid_public_contact' => __('Une coordonnée marquée publique n’est pas valide.', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_directory_get_validation_detail_label($detail)
{
    $labels = wp_seed_content_directory_get_error_labels();
    $code = isset($detail['code']) ? sanitize_key((string) $detail['code']) : '';
    $message = isset($labels[$code]) ? $labels[$code] : __('Cette fiche contient une erreur de publication.', 'wp-seed-content-kit');
    if ('invalid_public_contact' === $code && !empty($detail['type']) && function_exists('wp_seed_content_directory_get_contact_type')) {
        $type = wp_seed_content_directory_get_contact_type($detail['type']);
        if (is_array($type) && !empty($type['label'])) {
            $message = sprintf(__('La coordonnée publique « %s » n’est pas valide.', 'wp-seed-content-kit'), $type['label']);
        }
    }
    return $message;
}

function wp_seed_content_directory_get_validation_editor_state($post_id)
{
    $post_id = (int) $post_id;
    $pending = wp_seed_content_directory_get_pending_validation($post_id);
    $warning_codes = wp_seed_content_directory_get_validation_warning($post_id);
    $details = !empty($pending['errors']) ? $pending['errors'] : array();
    $state = !empty($pending['status']) ? $pending['status'] : (!empty($warning_codes) ? 'grandfathered' : 'none');
    $messages = array();
    if (!empty($details)) {
        foreach ($details as $detail) {
            $messages[] = wp_seed_content_directory_get_validation_detail_label($detail);
        }
    } else {
        $labels = wp_seed_content_directory_get_error_labels();
        foreach ($warning_codes as $code) {
            if (isset($labels[$code])) {
                $messages[] = $labels[$code];
            }
        }
    }

    if ('pending' === $state) {
        $summary = __('La fiche reste publiée pour vous permettre de corriger cette erreur. Si vous enregistrez à nouveau sans la corriger, elle sera placée en brouillon.', 'wp-seed-content-kit');
    } elseif ('drafted' === $state) {
        $summary = __('La fiche a été placée en brouillon car l’erreur signalée précédemment n’a pas été corrigée.', 'wp-seed-content-kit');
    } elseif ('blocked' === $state) {
        $summary = __('La publication est bloquée jusqu’à correction des erreurs ci-dessous.', 'wp-seed-content-kit');
    } elseif ('grandfathered' === $state) {
        $summary = __('Cette fiche reste publiée, mais elle contient une anomalie historique à corriger.', 'wp-seed-content-kit');
    } else {
        $summary = '';
    }

    return array(
        'state' => $state,
        'summary' => $summary,
        'messages' => array_values(array_unique($messages)),
    );
}

function wp_seed_content_directory_register_validation_rest_field()
{
    register_rest_field('seed_directory', 'wpsck_directory_validation', array(
        'get_callback' => function ($object) {
            $post_id = isset($object['id']) ? (int) $object['id'] : 0;
            if ($post_id <= 0 || !current_user_can('edit_seed_directory_entry', $post_id)) {
                return null;
            }
            return wp_seed_content_directory_get_validation_editor_state($post_id);
        },
        'schema' => array(
            'description' => __('État éditorial des validations Annuaire.', 'wp-seed-content-kit'),
            'type' => 'object',
            'context' => array('edit'),
            'readonly' => true,
        ),
    ));
}
add_action('rest_api_init', 'wp_seed_content_directory_register_validation_rest_field');

function wp_seed_content_directory_get_error_field_ids()
{
    return array(
        'missing_name' => 'title',
        'invalid_status' => 'seed-directory-status',
        'invalid_country' => '_seed_directory_country',
        'invalid_photo' => 'postimagediv',
        'missing_photo_alt' => 'wp_seed_content_directory_photo_alt',
        'missing_authorization' => '_seed_directory_publication_authorized',
        'invalid_public_phone' => '_seed_directory_phone',
        'invalid_public_email' => '_seed_directory_email',
        'invalid_public_website' => '_seed_directory_website',
        'invalid_public_facebook' => '_seed_directory_facebook',
        'invalid_public_instagram' => '_seed_directory_instagram',
        'invalid_public_contact' => 'wp-seed-content-directory-contacts-list',
    );
}
