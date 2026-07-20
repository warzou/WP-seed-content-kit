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

function wp_seed_content_directory_get_publication_errors($post_or_id, $overrides = array())
{
    $post = is_object($post_or_id) ? $post_or_id : get_post((int) $post_or_id);
    if (!$post || 'seed_directory' !== $post->post_type) {
        return array('invalid_object');
    }

    $post_id = (int) $post->ID;
    $title = array_key_exists('post_title', $overrides) ? sanitize_text_field($overrides['post_title']) : sanitize_text_field($post->post_title);
    $errors = array();
    if ('' === $title) {
        $errors[] = 'missing_name';
    }

    $statuses = wp_seed_content_directory_get_statuses();
    $status = wp_seed_content_directory_get_value_for_validation($post_id, '_seed_directory_status', $overrides);
    if (!isset($statuses[$status])) {
        $errors[] = 'invalid_status';
    }

    $countries = wp_seed_content_directory_get_country_codes();
    $country = wp_seed_content_directory_get_value_for_validation($post_id, '_seed_directory_country', $overrides);
    if (!isset($countries[$country])) {
        $errors[] = 'invalid_country';
    }

    if ('1' !== wp_seed_content_directory_get_value_for_validation($post_id, '_seed_directory_publication_authorized', $overrides)) {
        $errors[] = 'missing_authorization';
    }

    $thumbnail_id = array_key_exists('_thumbnail_id', $overrides) ? absint($overrides['_thumbnail_id']) : (int) get_post_thumbnail_id($post_id);
    if ($thumbnail_id > 0) {
        $url = wp_get_attachment_url($thumbnail_id);
        if (!wp_attachment_is_image($thumbnail_id) || '' === wp_seed_content_directory_sanitize_http_url($url)) {
            $errors[] = 'invalid_photo';
        } else {
            $alt = array_key_exists('_wp_attachment_image_alt', $overrides)
                ? sanitize_text_field($overrides['_wp_attachment_image_alt'])
                : sanitize_text_field(get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
            if ('' === $alt) {
                $errors[] = 'missing_photo_alt';
            }
        }
    }

    return array_values(array_unique($errors));
}

function wp_seed_content_directory_is_publicly_eligible($post_id)
{
    $post = get_post((int) $post_id);
    if (!$post || 'seed_directory' !== $post->post_type || 'publish' !== $post->post_status || '' !== (string) $post->post_password) {
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
        foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
            if ('boolean' === $definition['type']) {
                $overrides[$key] = isset($_POST[$key]) ? '1' : '';
            } elseif (array_key_exists($key, $_POST)) {
                $overrides[$key] = wp_seed_content_directory_sanitize_meta_value($key, wp_unslash($_POST[$key]));
            }
        }
    }

    if (array_key_exists('_thumbnail_id', $postarr)) {
        $overrides['_thumbnail_id'] = absint($postarr['_thumbnail_id']);
    } elseif (isset($_POST['_thumbnail_id'])) {
        $overrides['_thumbnail_id'] = absint(wp_unslash($_POST['_thumbnail_id']));
    }
    if (isset($_POST['_seed_directory_photo_alt'])) {
        $overrides['_wp_attachment_image_alt'] = sanitize_text_field(wp_unslash($_POST['_seed_directory_photo_alt']));
    }

    return $overrides;
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

    $overrides = wp_seed_content_directory_collect_publication_overrides($postarr);
    $overrides['post_title'] = isset($data['post_title']) ? $data['post_title'] : '';
    $errors = wp_seed_content_directory_get_publication_errors($post, $overrides);
    if (!empty($errors)) {
        $data['post_status'] = 'draft';
        wp_seed_content_directory_store_publication_notice($post_id, $errors);
    }

    return $data;
}
add_filter('wp_insert_post_data', 'wp_seed_content_directory_filter_insert_post_data', 20, 2);

function wp_seed_content_directory_store_publication_notice($post_id, $errors)
{
    $user_id = get_current_user_id();
    if (!$user_id || empty($errors)) {
        return;
    }
    set_transient('wp_seed_content_directory_notice_' . $user_id . '_' . absint($post_id), array_values(array_unique($errors)), 60);
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

    $errors = wp_seed_content_directory_get_publication_errors($post);
    if (empty($errors)) {
        return;
    }

    $demoting = true;
    wp_update_post(array('ID' => (int) $post->ID, 'post_status' => 'draft'));
    $demoting = false;
    wp_seed_content_directory_store_publication_notice((int) $post->ID, $errors);
}

function wp_seed_content_directory_after_insert_post($post_id, $post)
{
    wp_seed_content_directory_enforce_publication($post_id, $post);
}
add_action('wp_after_insert_post', 'wp_seed_content_directory_after_insert_post', 100, 2);

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
        'missing_authorization' => __('autorisation de publication absente', 'wp-seed-content-kit'),
        'missing_name' => __('nom absent', 'wp-seed-content-kit'),
        'invalid_status' => __('statut invalide', 'wp-seed-content-kit'),
        'invalid_country' => __('pays invalide', 'wp-seed-content-kit'),
        'invalid_photo' => __('photo invalide', 'wp-seed-content-kit'),
        'missing_photo_alt' => __('texte alternatif de la photo absent', 'wp-seed-content-kit'),
    );
}
