<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_migration_context()
{
    return 'ck_a6_fictional_directory_migration';
}

function wp_seed_content_directory_migration_canonicalize($value)
{
    if (!is_array($value)) {
        return $value;
    }
    $keys = array_keys($value);
    $is_list = empty($keys) || $keys === range(0, count($value) - 1);
    if (!$is_list) {
        ksort($value, SORT_STRING);
    }
    foreach ($value as $key => $item) {
        $value[$key] = wp_seed_content_directory_migration_canonicalize($item);
    }
    return $value;
}

function wp_seed_content_directory_migration_canonical_json($value)
{
    return (string) json_encode(wp_seed_content_directory_migration_canonicalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function wp_seed_content_directory_migration_source_hash($item)
{
    if (is_array($item)) {
        unset($item['source_hash']);
    }
    return hash('sha256', wp_seed_content_directory_migration_canonical_json($item));
}

function wp_seed_content_directory_migration_manifest_hash($manifest)
{
    return hash('sha256', wp_seed_content_directory_migration_canonical_json($manifest));
}

function wp_seed_content_directory_migration_entry_keys()
{
    return array(
        'source_id', 'source_hash', 'display_name', 'professional_status', 'city', 'postal_code',
        'department', 'country', 'short_bio', 'phone', 'phone_public', 'email', 'email_public',
        'website', 'website_public', 'facebook', 'facebook_public', 'instagram', 'instagram_public',
        'featured', 'display_order', 'publication_authorized', 'internal_note', 'last_verified_date',
        'media_source_id', 'target_status',
    );
}

function wp_seed_content_directory_migration_media_keys()
{
    return array('media_source_id', 'source_hash', 'filename', 'mime_type', 'alt', 'payload', 'width', 'height');
}

function wp_seed_content_directory_migration_exact_keys($value, $expected)
{
    if (!is_array($value)) {
        return false;
    }
    $actual = array_keys($value);
    sort($actual, SORT_STRING);
    sort($expected, SORT_STRING);
    return $actual === $expected;
}

function wp_seed_content_directory_migration_error(&$errors, $path, $code)
{
    $errors[] = array('path' => $path, 'code' => $code);
}

function wp_seed_content_directory_validate_migration_manifest($manifest)
{
    $errors = array();
    $root_keys = array('schema_version', 'batch_id', 'source_system', 'source_snapshot', 'generated_at', 'entries', 'media');
    if (!wp_seed_content_directory_migration_exact_keys($manifest, $root_keys)) {
        return array('valid' => false, 'errors' => array(array('path' => '$', 'code' => 'invalid_root_schema')), 'manifest' => array());
    }
    if ('1.0.0' !== $manifest['schema_version']) {
        wp_seed_content_directory_migration_error($errors, 'schema_version', 'unsupported_schema_version');
    }
    foreach (array('batch_id', 'source_system', 'source_snapshot', 'generated_at') as $field) {
        if (!is_string($manifest[$field]) || '' === trim($manifest[$field])) {
            wp_seed_content_directory_migration_error($errors, $field, 'required_string');
        }
    }
    if (!is_string($manifest['batch_id']) || 1 !== preg_match('/^[a-z0-9][a-z0-9._:-]{2,100}$/D', $manifest['batch_id'])) {
        wp_seed_content_directory_migration_error($errors, 'batch_id', 'invalid_batch_id');
    }
    if ('native-directory-demo' !== $manifest['source_system']) {
        wp_seed_content_directory_migration_error($errors, 'source_system', 'unsupported_source_system');
    }
    if (!is_array($manifest['entries']) || !is_array($manifest['media'])) {
        wp_seed_content_directory_migration_error($errors, '$', 'entries_and_media_must_be_arrays');
        return array('valid' => false, 'errors' => $errors, 'manifest' => array());
    }

    $media_ids = array();
    foreach ($manifest['media'] as $index => $medium) {
        $path = 'media[' . $index . ']';
        if (!wp_seed_content_directory_migration_exact_keys($medium, wp_seed_content_directory_migration_media_keys())) {
            wp_seed_content_directory_migration_error($errors, $path, 'invalid_media_schema');
            continue;
        }
        $media_id = $medium['media_source_id'];
        if (!is_string($media_id) || 1 !== preg_match('/^media-[0-9]{3}$/D', $media_id) || isset($media_ids[$media_id])) {
            wp_seed_content_directory_migration_error($errors, $path . '.media_source_id', 'invalid_or_duplicate_media_source_id');
        } else {
            $media_ids[$media_id] = true;
        }
        if (!is_string($medium['source_hash']) || !hash_equals(wp_seed_content_directory_migration_source_hash($medium), $medium['source_hash'])) {
            wp_seed_content_directory_migration_error($errors, $path . '.source_hash', 'source_hash_mismatch');
        }
        if (!is_string($medium['filename']) || 1 !== preg_match('/^[a-z0-9][a-z0-9._-]*\.(png|jpe?g)$/D', $medium['filename'])) {
            wp_seed_content_directory_migration_error($errors, $path . '.filename', 'invalid_filename');
        }
        if (!in_array($medium['mime_type'], array('image/png', 'image/jpeg'), true)) {
            wp_seed_content_directory_migration_error($errors, $path . '.mime_type', 'invalid_mime_type');
        }
        if (!is_string($medium['alt']) || '' === trim(strip_tags($medium['alt']))) {
            wp_seed_content_directory_migration_error($errors, $path . '.alt', 'missing_alt');
        }
        $decoded = is_string($medium['payload']) ? base64_decode($medium['payload'], true) : false;
        if (false === $decoded || '' === $decoded || strlen($decoded) > 1048576) {
            wp_seed_content_directory_migration_error($errors, $path . '.payload', 'invalid_payload');
        }
        if (!is_int($medium['width']) || !is_int($medium['height']) || $medium['width'] <= 0 || $medium['height'] <= $medium['width']) {
            wp_seed_content_directory_migration_error($errors, $path, 'invalid_portrait_dimensions');
        }
    }

    $source_ids = array();
    foreach ($manifest['entries'] as $index => $entry) {
        $path = 'entries[' . $index . ']';
        if (!wp_seed_content_directory_migration_exact_keys($entry, wp_seed_content_directory_migration_entry_keys())) {
            wp_seed_content_directory_migration_error($errors, $path, 'invalid_entry_schema');
            continue;
        }
        $source_id = $entry['source_id'];
        if (!is_string($source_id) || 1 !== preg_match('/^entry-[0-9]{3}$/D', $source_id) || isset($source_ids[$source_id])) {
            wp_seed_content_directory_migration_error($errors, $path . '.source_id', 'invalid_or_duplicate_source_id');
        } else {
            $source_ids[$source_id] = true;
        }
        if (!is_string($entry['source_hash']) || !hash_equals(wp_seed_content_directory_migration_source_hash($entry), $entry['source_hash'])) {
            wp_seed_content_directory_migration_error($errors, $path . '.source_hash', 'source_hash_mismatch');
        }
        if (!is_string($entry['display_name']) || 0 !== strpos($entry['display_name'], 'SEED CONTENT KIT TEST - ANNUAIRE - ')) {
            wp_seed_content_directory_migration_error($errors, $path . '.display_name', 'invalid_test_name');
        }
        if (!in_array($entry['professional_status'], array('practicing', 'seeking_models'), true)) {
            wp_seed_content_directory_migration_error($errors, $path . '.professional_status', 'invalid_status');
        }
        if (!is_string($entry['country']) || !isset(wp_seed_content_directory_get_country_codes()[strtoupper($entry['country'])])) {
            wp_seed_content_directory_migration_error($errors, $path . '.country', 'invalid_country');
        }
        if (!in_array($entry['target_status'], array('publish', 'draft'), true)) {
            wp_seed_content_directory_migration_error($errors, $path . '.target_status', 'invalid_target_status');
        }
        foreach (array('phone_public', 'email_public', 'website_public', 'facebook_public', 'instagram_public', 'featured', 'publication_authorized') as $boolean) {
            if (!is_bool($entry[$boolean])) {
                wp_seed_content_directory_migration_error($errors, $path . '.' . $boolean, 'invalid_boolean');
            }
        }
        foreach (array('display_name', 'city', 'postal_code', 'department', 'country', 'short_bio', 'phone', 'email', 'website', 'facebook', 'instagram', 'internal_note', 'last_verified_date', 'media_source_id') as $string) {
            if (!is_string($entry[$string])) {
                wp_seed_content_directory_migration_error($errors, $path . '.' . $string, 'invalid_type');
            }
        }
        if (!is_int($entry['display_order']) || $entry['display_order'] < 0) {
            wp_seed_content_directory_migration_error($errors, $path . '.display_order', 'invalid_display_order');
        }
        if (!isset($media_ids[$entry['media_source_id']])) {
            wp_seed_content_directory_migration_error($errors, $path . '.media_source_id', 'unknown_media_source_id');
        }
        $contacts = array('phone' => '_seed_directory_phone', 'email' => '_seed_directory_email', 'website' => '_seed_directory_website', 'facebook' => '_seed_directory_facebook', 'instagram' => '_seed_directory_instagram');
        foreach ($contacts as $field => $meta_key) {
            if ($entry[$field . '_public'] && '' === wp_seed_content_directory_normalize_contact_value($meta_key, $entry[$field])) {
                wp_seed_content_directory_migration_error($errors, $path . '.' . $field, 'invalid_public_contact');
            }
        }
        if ('publish' === $entry['target_status'] && !$entry['publication_authorized']) {
            wp_seed_content_directory_migration_error($errors, $path . '.publication_authorized', 'publish_without_authorization');
        }
        if ('' !== $entry['last_verified_date'] && '' === wp_seed_content_sanitize_iso_date($entry['last_verified_date'])) {
            wp_seed_content_directory_migration_error($errors, $path . '.last_verified_date', 'invalid_date');
        }
    }
    return array('valid' => empty($errors), 'errors' => $errors, 'manifest' => empty($errors) ? $manifest : array());
}

function wp_seed_content_directory_load_migration_manifest($path)
{
    if (!is_string($path) || !is_file($path) || !is_readable($path)) {
        return array('valid' => false, 'errors' => array(array('path' => '$', 'code' => 'manifest_unreadable')), 'manifest' => array());
    }
    $manifest = json_decode((string) file_get_contents($path), true);
    if (!is_array($manifest) || JSON_ERROR_NONE !== json_last_error()) {
        return array('valid' => false, 'errors' => array(array('path' => '$', 'code' => 'invalid_json')), 'manifest' => array());
    }
    return wp_seed_content_directory_validate_migration_manifest($manifest);
}

function wp_seed_content_directory_migration_option_name($batch_id)
{
    return 'wp_seed_content_directory_migration_' . substr(hash('sha256', (string) $batch_id), 0, 40);
}

function wp_seed_content_directory_migration_is_authorized($args)
{
    return is_array($args) && isset($args['context']) && wp_seed_content_directory_migration_context() === $args['context'] && current_user_can('manage_wp_seed_imports');
}

function wp_seed_content_directory_migration_report($status = 'unchanged')
{
    return array(
        'status' => $status, 'created_entries' => 0, 'updated_entries' => 0, 'unchanged_entries' => 0,
        'created_media' => 0, 'updated_media' => 0, 'unchanged_media' => 0, 'missing_from_source' => array(),
        'deleted_entries' => 0, 'restored_entries' => 0, 'deleted_media' => 0, 'preserved_media' => 0,
        'restored_media' => 0, 'errors' => array(),
        'performance' => array('media_seconds' => 0.0, 'media_queries' => 0, 'entries_seconds' => 0.0, 'entries_queries' => 0),
    );
}

function wp_seed_content_directory_migration_entry_reference($source_id)
{
    return 'native-directory-demo:' . $source_id;
}
function wp_seed_content_directory_migration_find_post_by_meta($post_type, $meta_key, $meta_value)
{
    $ids = get_posts(array(
        'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_key' => $meta_key, 'meta_value' => $meta_value, 'suppress_filters' => true,
    ));
    return empty($ids) ? 0 : (int) $ids[0];
}

function wp_seed_content_directory_migration_find_entry($reference)
{
    return wp_seed_content_directory_migration_find_post_by_meta('seed_directory', '_wp_seed_content_directory_migration_source_reference', $reference);
}

function wp_seed_content_directory_migration_find_medium($source_id)
{
    return wp_seed_content_directory_migration_find_post_by_meta('attachment', '_wp_seed_content_directory_migration_media_source_id', $source_id);
}

function wp_seed_content_directory_migration_entry_fields($entry, $attachment_id)
{
    $meta = array(
        '_seed_directory_status' => $entry['professional_status'], '_seed_directory_city' => $entry['city'],
        '_seed_directory_postal_code' => $entry['postal_code'], '_seed_directory_department' => $entry['department'],
        '_seed_directory_country' => strtoupper($entry['country']), '_seed_directory_featured' => $entry['featured'] ? '1' : '',
        '_seed_directory_phone' => $entry['phone'], '_seed_directory_phone_visible' => $entry['phone_public'] ? '1' : '',
        '_seed_directory_email' => $entry['email'], '_seed_directory_email_visible' => $entry['email_public'] ? '1' : '',
        '_seed_directory_website' => $entry['website'], '_seed_directory_website_visible' => $entry['website_public'] ? '1' : '',
        '_seed_directory_facebook' => $entry['facebook'], '_seed_directory_facebook_visible' => $entry['facebook_public'] ? '1' : '',
        '_seed_directory_instagram' => $entry['instagram'], '_seed_directory_instagram_visible' => $entry['instagram_public'] ? '1' : '',
        '_seed_directory_publication_authorized' => $entry['publication_authorized'] ? '1' : '',
        '_seed_directory_internal_note' => $entry['internal_note'], '_seed_directory_last_verified' => $entry['last_verified_date'],
    );
    return array(
        'post' => array(
            'post_type' => 'seed_directory', 'post_title' => sanitize_text_field($entry['display_name']),
            'post_excerpt' => sanitize_textarea_field($entry['short_bio']), 'post_status' => $entry['target_status'],
            'menu_order' => max(0, (int) $entry['display_order']), '_thumbnail_id' => (int) $attachment_id,
            'meta_input' => $meta,
        ),
        'meta' => $meta,
    );
}

function wp_seed_content_directory_migration_snapshot_entry($post_id)
{
    $post = get_post($post_id, ARRAY_A);
    $meta = array();
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        $meta[$key] = get_post_meta($post_id, $key, true);
    }
    return array(
        'post' => $post, 'meta' => $meta, 'thumbnail_id' => (int) get_post_thumbnail_id($post_id),
        'revision_ids' => array_map('intval', array_keys(wp_get_post_revisions($post_id))),
        'migration_meta' => array(
            '_wp_seed_content_directory_migration_source_reference' => get_post_meta($post_id, '_wp_seed_content_directory_migration_source_reference', true),
            '_wp_seed_content_directory_migration_source_hash' => get_post_meta($post_id, '_wp_seed_content_directory_migration_source_hash', true),
            '_wp_seed_content_directory_migration_batch_id' => get_post_meta($post_id, '_wp_seed_content_directory_migration_batch_id', true),
        ),
    );
}

function wp_seed_content_directory_migration_restore_entry($post_id, $snapshot)
{
    if (empty($snapshot['post']) || !is_array($snapshot['post'])) {
        return false;
    }
    $post = $snapshot['post'];
    $post['ID'] = (int) $post_id;
    $result = wp_update_post(wp_slash($post), true);
    if (is_wp_error($result)) {
        return false;
    }
    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        $value = isset($snapshot['meta'][$key]) ? $snapshot['meta'][$key] : '';
        if ('' === $value) {
            delete_post_meta($post_id, $key);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }
    foreach (array('_wp_seed_content_directory_migration_source_reference', '_wp_seed_content_directory_migration_source_hash', '_wp_seed_content_directory_migration_batch_id') as $migration_key) {
        $migration_value = isset($snapshot['migration_meta'][$migration_key]) ? $snapshot['migration_meta'][$migration_key] : '';
        if ('' === $migration_value) { delete_post_meta($post_id, $migration_key); } else { update_post_meta($post_id, $migration_key, $migration_value); }
    }
    if (!empty($snapshot['thumbnail_id'])) {
        set_post_thumbnail($post_id, (int) $snapshot['thumbnail_id']);
    } else {
        delete_post_thumbnail($post_id);
    }
    $kept_revision_ids = isset($snapshot['revision_ids']) && is_array($snapshot['revision_ids'])
        ? array_map('intval', $snapshot['revision_ids'])
        : array();
    foreach (array_keys(wp_get_post_revisions($post_id)) as $revision_id) {
        if (!in_array((int) $revision_id, $kept_revision_ids, true)) {
            wp_delete_post((int) $revision_id, true);
        }
    }
    return true;
}

function wp_seed_content_directory_migration_snapshot_medium($attachment_id)
{
    $file = get_attached_file($attachment_id);
    return array(
        'file' => $file, 'payload' => $file && is_file($file) ? base64_encode((string) file_get_contents($file)) : '',
        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        'metadata' => wp_get_attachment_metadata($attachment_id),
        'migration_meta' => array(
            '_wp_seed_content_directory_migration_media_source_id' => get_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_source_id', true),
            '_wp_seed_content_directory_migration_media_hash' => get_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_hash', true),
            '_wp_seed_content_directory_migration_batch_id' => get_post_meta($attachment_id, '_wp_seed_content_directory_migration_batch_id', true),
        ),
    );
}

function wp_seed_content_directory_migration_write_medium($attachment_id, $medium)
{
    $payload = base64_decode($medium['payload'], true);
    if (false === $payload) {
        return false;
    }
    if ($attachment_id > 0) {
        $file = get_attached_file($attachment_id);
        if (!$file || false === file_put_contents($file, $payload)) {
            return false;
        }
        wp_update_post(array('ID' => $attachment_id, 'post_title' => sanitize_text_field(pathinfo($medium['filename'], PATHINFO_FILENAME)), 'post_mime_type' => $medium['mime_type']));
    } else {
        $upload = wp_upload_bits($medium['filename'], null, $payload);
        if (!empty($upload['error'])) {
            return false;
        }
        $attachment_id = wp_insert_attachment(array(
            'post_title' => sanitize_text_field(pathinfo($medium['filename'], PATHINFO_FILENAME)),
            'post_mime_type' => $medium['mime_type'], 'post_status' => 'inherit',
        ), $upload['file']);
        if (!$attachment_id || is_wp_error($attachment_id)) {
            return false;
        }
    }
    update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($medium['alt']));
    wp_update_attachment_metadata($attachment_id, array(
        'width' => $medium['width'], 'height' => $medium['height'],
        'file' => basename((string) get_attached_file($attachment_id)), 'sizes' => array(),
    ));
    return (int) $attachment_id;
}

function wp_seed_content_directory_migration_upsert_medium($medium, &$registry, &$report)
{
    $source_id = $medium['media_source_id'];
    $attachment_id = wp_seed_content_directory_migration_find_medium($source_id);
    $hash = $medium['source_hash'];
    if ($attachment_id && hash_equals((string) get_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_hash', true), $hash)) {
        $report['unchanged_media']++;
        return $attachment_id;
    }
    if ($attachment_id) {
        if (!isset($registry['updated_media'][$attachment_id]) && !in_array($attachment_id, $registry['created_media'], true)) {
            $registry['updated_media'][$attachment_id] = wp_seed_content_directory_migration_snapshot_medium($attachment_id);
        }
        if (!wp_seed_content_directory_migration_write_medium($attachment_id, $medium)) {
            return 0;
        }
        $report['updated_media']++;
    } else {
        $attachment_id = wp_seed_content_directory_migration_write_medium(0, $medium);
        if (!$attachment_id) {
            return 0;
        }
        $registry['created_media'][] = $attachment_id;
        $report['created_media']++;
    }
    update_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_source_id', $source_id);
    update_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_hash', $hash);
    update_post_meta($attachment_id, '_wp_seed_content_directory_migration_batch_id', $registry['batch_id']);
    return $attachment_id;
}
function wp_seed_content_directory_import_manifest($manifest, $args = array())
{
    $report = wp_seed_content_directory_migration_report('failed');
    if (!wp_seed_content_directory_migration_is_authorized($args)) {
        $report['errors'][] = array('path' => '$', 'code' => 'forbidden');
        return $report;
    }
    $validation = wp_seed_content_directory_validate_migration_manifest($manifest);
    if (!$validation['valid']) {
        $report['errors'] = $validation['errors'];
        return $report;
    }

    $batch_id = $manifest['batch_id'];
    $option_name = wp_seed_content_directory_migration_option_name($batch_id);
    $registry = get_option($option_name, array());
    if (!is_array($registry) || empty($registry)) {
        $registry = array(
            'batch_id' => $batch_id, 'status' => 'running', 'source_snapshot' => $manifest['source_snapshot'],
            'manifest_hash' => '', 'created_entries' => array(), 'created_media' => array(),
            'updated_entries' => array(), 'updated_media' => array(), 'missing_from_source' => array(),
            'started_at' => gmdate('c'), 'completed_at' => '', 'result' => array(),
        );
    }

    global $wpdb;
    $media_ids = array();
    $media_started = microtime(true);
    $media_queries_before = isset($wpdb->num_queries) ? (int) $wpdb->num_queries : 0;
    foreach ($manifest['media'] as $medium) {
        $attachment_id = wp_seed_content_directory_migration_upsert_medium($medium, $registry, $report);
        if (!$attachment_id) {
            $report['errors'][] = array('path' => 'media.' . $medium['media_source_id'], 'code' => 'media_write_failed');
            return $report;
        }
        $media_ids[$medium['media_source_id']] = $attachment_id;
    }
    $report['performance']['media_seconds'] = microtime(true) - $media_started;
    $report['performance']['media_queries'] = (isset($wpdb->num_queries) ? (int) $wpdb->num_queries : 0) - $media_queries_before;

    $entries_started = microtime(true);
    $entries_queries_before = isset($wpdb->num_queries) ? (int) $wpdb->num_queries : 0;
    $manifest_references = array();
    foreach ($manifest['entries'] as $entry) {
        $reference = wp_seed_content_directory_migration_entry_reference($entry['source_id']);
        $manifest_references[$reference] = true;
        $post_id = wp_seed_content_directory_migration_find_entry($reference);
        $fields = wp_seed_content_directory_migration_entry_fields($entry, $media_ids[$entry['media_source_id']]);
        $current_hash = $post_id ? (string) get_post_meta($post_id, '_wp_seed_content_directory_migration_source_hash', true) : '';
        $current_thumbnail = $post_id ? (int) get_post_thumbnail_id($post_id) : 0;
        if ($post_id && hash_equals($current_hash, $entry['source_hash']) && $current_thumbnail === (int) $media_ids[$entry['media_source_id']]) {
            $report['unchanged_entries']++;
            continue;
        }
        if ($post_id) {
            if (!isset($registry['updated_entries'][$post_id]) && !in_array($post_id, $registry['created_entries'], true)) {
                $registry['updated_entries'][$post_id] = wp_seed_content_directory_migration_snapshot_entry($post_id);
            }
            $fields['post']['ID'] = $post_id;
            $result = wp_update_post(wp_slash($fields['post']), true);
            if (is_wp_error($result)) {
                $report['errors'][] = array('path' => 'entries.' . $entry['source_id'], 'code' => 'entry_write_failed');
                return $report;
            }
            $report['updated_entries']++;
        } else {
            $post_id = wp_insert_post(wp_slash($fields['post']), true);
            if (!$post_id || is_wp_error($post_id)) {
                $report['errors'][] = array('path' => 'entries.' . $entry['source_id'], 'code' => 'entry_write_failed');
                return $report;
            }
            $registry['created_entries'][] = (int) $post_id;
            $report['created_entries']++;
        }
        foreach ($fields['meta'] as $key => $value) {
            if ('' === $value) {
                delete_post_meta($post_id, $key);
            } else {
                update_post_meta($post_id, $key, wp_seed_content_directory_sanitize_meta_value($key, $value));
            }
        }
        set_post_thumbnail($post_id, (int) $media_ids[$entry['media_source_id']]);
        update_post_meta($post_id, '_wp_seed_content_directory_migration_source_reference', $reference);
        update_post_meta($post_id, '_wp_seed_content_directory_migration_source_hash', $entry['source_hash']);
        update_post_meta($post_id, '_wp_seed_content_directory_migration_batch_id', $batch_id);
    }
    $report['performance']['entries_seconds'] = microtime(true) - $entries_started;
    $report['performance']['entries_queries'] = (isset($wpdb->num_queries) ? (int) $wpdb->num_queries : 0) - $entries_queries_before;

    $known_ids = get_posts(array(
        'post_type' => 'seed_directory', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids',
        'meta_key' => '_wp_seed_content_directory_migration_batch_id', 'meta_value' => $batch_id, 'suppress_filters' => true,
    ));
    foreach ($known_ids as $post_id) {
        $reference = (string) get_post_meta($post_id, '_wp_seed_content_directory_migration_source_reference', true);
        if ('' !== $reference && !isset($manifest_references[$reference])) {
            $report['missing_from_source'][] = substr($reference, strlen('native-directory-demo:'));
        }
    }
    sort($report['missing_from_source'], SORT_STRING);
    $report['status'] = $report['created_entries'] || $report['updated_entries'] || $report['created_media'] || $report['updated_media'] ? 'imported' : 'unchanged';
    $registry['status'] = 'completed';
    $registry['source_snapshot'] = $manifest['source_snapshot'];
    $registry['manifest_hash'] = wp_seed_content_directory_migration_manifest_hash($manifest);
    $registry['missing_from_source'] = $report['missing_from_source'];
    $registry['completed_at'] = gmdate('c');
    $registry['result'] = $report;
    update_option($option_name, $registry, false);
    return $report;
}

function wp_seed_content_directory_migration_medium_is_reused($attachment_id, $batch_entry_ids)
{
    $uses = get_posts(array(
        'post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids',
        'meta_key' => '_thumbnail_id', 'meta_value' => (int) $attachment_id, 'suppress_filters' => true,
    ));
    return !empty(array_diff(array_map('intval', $uses), array_map('intval', $batch_entry_ids)));
}

function wp_seed_content_directory_rollback_migration_batch($batch_id, $args = array())
{
    $report = wp_seed_content_directory_migration_report('failed');
    if (!wp_seed_content_directory_migration_is_authorized($args)) {
        $report['errors'][] = array('path' => '$', 'code' => 'forbidden');
        return $report;
    }
    $option_name = wp_seed_content_directory_migration_option_name($batch_id);
    $registry = get_option($option_name, null);
    if (!is_array($registry) || empty($registry)) {
        $report['status'] = 'unchanged';
        return $report;
    }

    $created_entries = array_map('intval', isset($registry['created_entries']) ? $registry['created_entries'] : array());
    foreach ($created_entries as $post_id) {
        if (get_post($post_id) && wp_delete_post($post_id, true)) {
            $report['deleted_entries']++;
        }
    }
    foreach ((array) $registry['updated_entries'] as $post_id => $snapshot) {
        if (wp_seed_content_directory_migration_restore_entry((int) $post_id, $snapshot)) {
            $report['restored_entries']++;
        }
    }
    foreach ((array) $registry['updated_media'] as $attachment_id => $snapshot) {
        $attachment_id = (int) $attachment_id;
        $payload = isset($snapshot['payload']) ? base64_decode($snapshot['payload'], true) : false;
        $file = isset($snapshot['file']) ? $snapshot['file'] : '';
        if (false !== $payload && $file && false !== file_put_contents($file, $payload)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $snapshot['alt']);
            wp_update_attachment_metadata($attachment_id, $snapshot['metadata']);
            foreach (array('_wp_seed_content_directory_migration_media_source_id', '_wp_seed_content_directory_migration_media_hash', '_wp_seed_content_directory_migration_batch_id') as $migration_key) {
                $migration_value = isset($snapshot['migration_meta'][$migration_key]) ? $snapshot['migration_meta'][$migration_key] : '';
                if ('' === $migration_value) { delete_post_meta($attachment_id, $migration_key); } else { update_post_meta($attachment_id, $migration_key, $migration_value); }
            }
            $report['restored_media']++;
        }
    }
    foreach ((array) $registry['created_media'] as $attachment_id) {
        $attachment_id = (int) $attachment_id;
        if (wp_seed_content_directory_migration_medium_is_reused($attachment_id, $created_entries)) {
            delete_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_source_id');
            delete_post_meta($attachment_id, '_wp_seed_content_directory_migration_media_hash');
            delete_post_meta($attachment_id, '_wp_seed_content_directory_migration_batch_id');
            $report['preserved_media']++;
        } elseif (get_post($attachment_id) && wp_delete_attachment($attachment_id, true)) {
            $report['deleted_media']++;
        }
    }
    delete_option($option_name);
    $report['status'] = 'rolled_back';
    return $report;
}