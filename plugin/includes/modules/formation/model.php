<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_formation_statuses()
{
    return array(
        'planned' => __('Prévu', 'wp-seed-content-kit'),
        'postponed' => __('Reporté', 'wp-seed-content-kit'),
        'cancelled' => __('Annulé', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_formation_schedule_meta_definitions()
{
    return array(
        '_seed_formation_program_id' => array('type' => 'integer', 'default' => 0),
        '_seed_formation_theme_id' => array('type' => 'integer', 'default' => 0),
        '_seed_formation_section_id' => array('type' => 'integer', 'default' => 0),
        '_seed_formation_calendar_year' => array('type' => 'integer', 'default' => 0),
        '_seed_formation_start_date' => array('type' => 'string', 'default' => ''),
        '_seed_formation_end_date' => array('type' => 'string', 'default' => ''),
        '_seed_formation_status' => array('type' => 'string', 'default' => 'planned'),
        '_seed_formation_original_start_date' => array('type' => 'string', 'default' => ''),
        '_seed_formation_original_end_date' => array('type' => 'string', 'default' => ''),
        '_seed_formation_annual_display_title' => array('type' => 'string', 'default' => ''),
        '_seed_formation_directory_person_ids' => array('type' => 'array', 'default' => array()),
        '_seed_formation_theme_title_snapshot' => array('type' => 'string', 'default' => ''),
        '_seed_formation_section_label_snapshot' => array('type' => 'string', 'default' => ''),
        '_seed_formation_section_order_snapshot' => array('type' => 'integer', 'default' => 0),
    );
}

function wp_seed_content_formation_section_program_meta_key()
{
    return '_seed_formation_program_id';
}

function wp_seed_content_formation_sanitize_iso_date($value)
{
    $value = trim((string) $value);
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $matches)) {
        return '';
    }
    return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $value : '';
}

function wp_seed_content_formation_sanitize_id_list($value)
{
    $result = array();
    foreach ((array) $value as $id) {
        $id = absint($id);
        if ($id && !in_array($id, $result, true)) {
            $result[] = $id;
        }
    }
    return $result;
}

function wp_seed_content_formation_relevant_calendar_year($years, $current_year)
{
    $available = array_values(array_unique(array_filter(array_map('absint', (array) $years))));
    sort($available, SORT_NUMERIC);
    $current_year = absint($current_year);
    foreach ($available as $year) {
        if ($year >= $current_year) return $year;
    }
    return $available ? end($available) : $current_year;
}

function wp_seed_content_formation_register_meta()
{
    register_post_meta('seed_section', wp_seed_content_formation_section_program_meta_key(), array(
        'type' => 'integer', 'single' => true, 'default' => 0, 'show_in_rest' => false,
        'sanitize_callback' => 'absint', 'auth_callback' => function () { return current_user_can('edit_seed_sections'); },
    ));
    foreach (wp_seed_content_formation_schedule_meta_definitions() as $key => $definition) {
        $sanitize = 'sanitize_text_field';
        if ('integer' === $definition['type']) {
            $sanitize = 'absint';
        } elseif ('array' === $definition['type']) {
            $sanitize = 'wp_seed_content_formation_sanitize_id_list';
        } elseif (false !== strpos($key, '_date')) {
            $sanitize = 'wp_seed_content_formation_sanitize_iso_date';
        }
        register_post_meta('seed_schedule', $key, array(
            'type' => $definition['type'], 'single' => true, 'default' => $definition['default'],
            'show_in_rest' => false, 'sanitize_callback' => $sanitize,
            'auth_callback' => function () { return current_user_can('edit_seed_schedules'); },
        ));
    }
}
add_action('init', 'wp_seed_content_formation_register_meta');

function wp_seed_content_formation_valid_relation($post_id, $post_type)
{
    $post = $post_id ? get_post(absint($post_id)) : null;
    return $post instanceof WP_Post && $post_type === $post->post_type && 'trash' !== $post->post_status;
}

function wp_seed_content_formation_validate_schedule_values($values, $schedule_id = 0)
{
    $errors = array();
    $program_id = isset($values['program_id']) ? absint($values['program_id']) : 0;
    $theme_id = isset($values['theme_id']) ? absint($values['theme_id']) : 0;
    $section_id = isset($values['section_id']) ? absint($values['section_id']) : 0;
    $start = wp_seed_content_formation_sanitize_iso_date(isset($values['start_date']) ? $values['start_date'] : '');
    $end = wp_seed_content_formation_sanitize_iso_date(isset($values['end_date']) ? $values['end_date'] : '');
    $year = isset($values['calendar_year']) ? absint($values['calendar_year']) : 0;
    $status = isset($values['status']) ? sanitize_key($values['status']) : 'planned';

    if (!wp_seed_content_formation_valid_relation($program_id, 'seed_program')) $errors[] = 'invalid_program';
    if (!wp_seed_content_formation_valid_relation($theme_id, 'seed_theme')) $errors[] = 'invalid_theme';
    if (!wp_seed_content_formation_valid_relation($section_id, 'seed_section')) $errors[] = 'invalid_section';
    if ($section_id && $program_id && absint(get_post_meta($section_id, wp_seed_content_formation_section_program_meta_key(), true)) !== $program_id) $errors[] = 'section_program_mismatch';
    if ('' === $start || '' === $end) $errors[] = 'invalid_dates';
    if ('' !== $start && '' !== $end && $start > $end) $errors[] = 'date_order';
    if ('' !== $start && (int) substr($start, 0, 4) !== $year) $errors[] = 'calendar_year_mismatch';
    if (!isset(wp_seed_content_formation_statuses()[$status])) $errors[] = 'invalid_status';

    foreach (wp_seed_content_formation_sanitize_id_list(isset($values['person_ids']) ? $values['person_ids'] : array()) as $person_id) {
        if (!wp_seed_content_formation_valid_relation($person_id, 'seed_directory')) {
            $errors[] = 'invalid_person';
            break;
        }
    }
    if (!$errors && wp_seed_content_formation_schedule_exists($program_id, $year, $theme_id, $section_id, $start, $end, $schedule_id)) {
        $errors[] = 'duplicate_schedule';
    }
    return array_values(array_unique($errors));
}

function wp_seed_content_formation_schedule_exists($program_id, $year, $theme_id, $section_id, $start, $end, $exclude_id = 0)
{
    $ids = get_posts(array(
        'post_type' => 'seed_schedule', 'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
        'posts_per_page' => 1, 'fields' => 'ids', 'post__not_in' => $exclude_id ? array(absint($exclude_id)) : array(),
        'meta_query' => array(
            array('key' => '_seed_formation_program_id', 'value' => absint($program_id), 'compare' => '='),
            array('key' => '_seed_formation_calendar_year', 'value' => absint($year), 'compare' => '='),
            array('key' => '_seed_formation_theme_id', 'value' => absint($theme_id), 'compare' => '='),
            array('key' => '_seed_formation_section_id', 'value' => absint($section_id), 'compare' => '='),
            array('key' => '_seed_formation_start_date', 'value' => wp_seed_content_formation_sanitize_iso_date($start), 'compare' => '='),
            array('key' => '_seed_formation_end_date', 'value' => wp_seed_content_formation_sanitize_iso_date($end), 'compare' => '='),
        ),
        'no_found_rows' => true, 'suppress_filters' => true, 'cache_results' => false,
    ));
    return !empty($ids);
}

function wp_seed_content_formation_capture_schedule_history($new_status, $old_status, $post)
{
    if (!$post instanceof WP_Post || 'seed_schedule' !== $post->post_type || 'publish' !== $new_status || 'publish' === $old_status) {
        return;
    }
    $theme = get_post(absint(get_post_meta($post->ID, '_seed_formation_theme_id', true)));
    $section = get_post(absint(get_post_meta($post->ID, '_seed_formation_section_id', true)));
    if ($theme instanceof WP_Post && '' === (string) get_post_meta($post->ID, '_seed_formation_theme_title_snapshot', true)) {
        update_post_meta($post->ID, '_seed_formation_theme_title_snapshot', sanitize_text_field($theme->post_title));
    }
    if ($section instanceof WP_Post) {
        if ('' === (string) get_post_meta($post->ID, '_seed_formation_section_label_snapshot', true)) {
            update_post_meta($post->ID, '_seed_formation_section_label_snapshot', sanitize_text_field($section->post_title));
        }
        if (!metadata_exists('post', $post->ID, '_seed_formation_section_order_snapshot')) {
            update_post_meta($post->ID, '_seed_formation_section_order_snapshot', (int) $section->menu_order);
        }
    }
}
add_action('transition_post_status', 'wp_seed_content_formation_capture_schedule_history', 10, 3);

function wp_seed_content_formation_reference_count($post_id)
{
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) return 0;
    $keys = array('seed_program' => '_seed_formation_program_id', 'seed_theme' => '_seed_formation_theme_id', 'seed_section' => '_seed_formation_section_id');
    if (!isset($keys[$post->post_type])) return 0;
    return count(get_posts(array(
        'post_type' => 'seed_program' === $post->post_type ? array('seed_section', 'seed_schedule') : 'seed_schedule',
        'post_status' => array('publish', 'draft', 'pending', 'private', 'future', 'trash'),
        'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => $keys[$post->post_type],
        'meta_value' => absint($post_id), 'suppress_filters' => true,
    )));
}

function wp_seed_content_formation_guard_referenced_delete($result, $post)
{
    return $post instanceof WP_Post && wp_seed_content_formation_reference_count($post->ID) ? false : $result;
}
add_filter('pre_trash_post', 'wp_seed_content_formation_guard_referenced_delete', 10, 2);
add_filter('pre_delete_post', 'wp_seed_content_formation_guard_referenced_delete', 10, 2);

function wp_seed_content_formation_store_schedule($values, $schedule_id = 0)
{
    global $wpdb;
    $schedule_id = absint($schedule_id);
    if (!current_user_can('edit_seed_schedules') || (!$schedule_id && !current_user_can('publish_seed_schedules'))) {
        return new WP_Error('forbidden', __('Accès refusé.', 'wp-seed-content-kit'));
    }
    // Serialize all schedule writers on this site, including moves between years.
    $lock = 'wpsck_schedule_' . substr(hash('sha256', (defined('DB_NAME') ? DB_NAME : '') . ':' . $wpdb->prefix), 0, 40);
    if ('1' !== (string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $lock))) {
        return new WP_Error('schedule_busy', __('Une autre programmation est en cours de sauvegarde. Réessayez dans quelques instants.', 'wp-seed-content-kit'));
    }
    try {
        if ($schedule_id) clean_post_cache($schedule_id);
        $existing = $schedule_id ? get_post($schedule_id) : null;
        if ($schedule_id && (!$existing instanceof WP_Post || 'seed_schedule' !== $existing->post_type || 'trash' === $existing->post_status || !current_user_can('edit_seed_schedule', $schedule_id))) {
            return new WP_Error('invalid_schedule', __('Programmation invalide.', 'wp-seed-content-kit'));
        }
        $errors = wp_seed_content_formation_validate_schedule_values($values, $schedule_id);
        if ($errors) return new WP_Error('invalid_values', implode(',', $errors), $errors);
        if ($existing instanceof WP_Post && 'draft' === $existing->post_status && !current_user_can('publish_seed_schedules')) {
            return new WP_Error('forbidden', __('Accès refusé.', 'wp-seed-content-kit'));
        }
        $old_theme = $schedule_id ? absint(get_post_meta($schedule_id, '_seed_formation_theme_id', true)) : 0;
        $old_section = $schedule_id ? absint(get_post_meta($schedule_id, '_seed_formation_section_id', true)) : 0;
        $old_status = $schedule_id ? (string) get_post_meta($schedule_id, '_seed_formation_status', true) : 'planned';
        $old_start = $schedule_id ? (string) get_post_meta($schedule_id, '_seed_formation_start_date', true) : '';
        $old_end = $schedule_id ? (string) get_post_meta($schedule_id, '_seed_formation_end_date', true) : '';
        $payload = array('post_type' => 'seed_schedule', 'post_status' => $schedule_id ? $existing->post_status : 'draft',
            'post_title' => 'Programmation ' . absint($values['calendar_year']), 'menu_order' => isset($values['menu_order']) ? (int) $values['menu_order'] : 0);
        if ($schedule_id) $payload['ID'] = $schedule_id;
        $saved_id = wp_insert_post(wp_slash($payload), true);
        if (is_wp_error($saved_id)) return $saved_id;
        foreach (array('program_id', 'theme_id', 'section_id', 'calendar_year', 'start_date', 'end_date', 'status') as $field) {
            update_post_meta($saved_id, '_seed_formation_' . $field, $values[$field]);
        }
        update_post_meta($saved_id, '_seed_formation_directory_person_ids', wp_seed_content_formation_sanitize_id_list(isset($values['person_ids']) ? $values['person_ids'] : array()));
        update_post_meta($saved_id, '_seed_formation_annual_display_title', wp_slash(sanitize_text_field(isset($values['annual_display_title']) ? $values['annual_display_title'] : '')));
        if ($schedule_id) {
            wp_seed_content_formation_preserve_original_dates($saved_id, $old_status, $values['status'], $old_start, $old_end);
            // Only an explicit relation edit replaces this schedule's historical identity.
            if ($old_theme !== absint($values['theme_id'])) update_post_meta($saved_id, '_seed_formation_theme_title_snapshot', wp_slash(sanitize_text_field(get_the_title($values['theme_id']))));
            if ($old_section !== absint($values['section_id'])) {
                $section = get_post($values['section_id']);
                update_post_meta($saved_id, '_seed_formation_section_label_snapshot', wp_slash(sanitize_text_field($section->post_title)));
                update_post_meta($saved_id, '_seed_formation_section_order_snapshot', (int) $section->menu_order);
            }
        }
        if (!$schedule_id || 'draft' === $existing->post_status) {
            $published = wp_update_post(array('ID' => $saved_id, 'post_status' => 'publish'), true);
            if (is_wp_error($published)) return $published;
        }
        return $saved_id;
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function wp_seed_content_formation_preserve_original_dates($post_id, $previous_status, $next_status, $previous_start, $previous_end)
{
    if ('postponed' !== $next_status || 'postponed' === $previous_status) return;
    if ('' === (string) get_post_meta($post_id, '_seed_formation_original_start_date', true)) {
        update_post_meta($post_id, '_seed_formation_original_start_date', wp_seed_content_formation_sanitize_iso_date($previous_start));
    }
    if ('' === (string) get_post_meta($post_id, '_seed_formation_original_end_date', true)) {
        update_post_meta($post_id, '_seed_formation_original_end_date', wp_seed_content_formation_sanitize_iso_date($previous_end));
    }
}
