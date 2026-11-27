<?php

if (!defined('ABSPATH')) { exit; }

function wp_seed_content_formation_register_program_sections_box()
{
    add_meta_box('wpsck-formation-sections', __('Sections du parcours', 'wp-seed-content-kit'), 'wp_seed_content_formation_render_program_sections_box', 'seed_program', 'normal', 'default');
}
add_action('add_meta_boxes_seed_program', 'wp_seed_content_formation_register_program_sections_box');

function wp_seed_content_formation_get_sections($program_id, $post_status = array('publish', 'draft', 'pending', 'private'))
{
    return get_posts(array(
        'post_type' => 'seed_section', 'post_status' => $post_status, 'posts_per_page' => -1,
        'meta_key' => '_seed_formation_program_id', 'meta_value' => absint($program_id),
        'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'), 'suppress_filters' => true,
    ));
}

function wp_seed_content_formation_section_usage_count($section_id)
{
    return wp_seed_content_formation_reference_count($section_id);
}

function wp_seed_content_formation_render_section_row($section)
{
    $section_id = $section instanceof WP_Post ? (int) $section->ID : 0;
    $usage_count = wp_seed_content_formation_section_usage_count($section_id);
    $remove_label = $usage_count
        ? sprintf(_n('Utilisée par %d programmation', 'Utilisée par %d programmations', $usage_count, 'wp-seed-content-kit'), $usage_count)
        : __('Supprimer', 'wp-seed-content-kit');
    echo '<div class="wpsck-formation-section-row">';
    echo '<input type="hidden" name="wpsck_formation_sections[id][]" value="' . esc_attr($section_id) . '">';
    echo '<input type="hidden" class="wpsck-formation-section-order" name="wpsck_formation_sections[order][]" value="' . esc_attr($section instanceof WP_Post ? $section->menu_order : 0) . '">';
    echo '<label><span class="screen-reader-text">' . esc_html__('Nom de la section', 'wp-seed-content-kit') . '</span><input class="widefat wpsck-formation-section-name" name="wpsck_formation_sections[label][]" value="' . esc_attr($section instanceof WP_Post ? $section->post_title : '') . '" placeholder="' . esc_attr__('Nom de la section', 'wp-seed-content-kit') . '"></label>';
    echo '<div class="wpsck-formation-section-actions">';
    echo '<button type="button" class="button wpsck-formation-section-up">' . esc_html__('Monter', 'wp-seed-content-kit') . '</button>';
    echo '<button type="button" class="button wpsck-formation-section-down">' . esc_html__('Descendre', 'wp-seed-content-kit') . '</button>';
    echo '<button type="button" class="button-link-delete wpsck-formation-section-remove"' . ($usage_count ? ' disabled aria-disabled="true"' : '') . '>' . esc_html($remove_label) . '</button>';
    echo '</div></div>';
}

function wp_seed_content_formation_render_program_sections_box($post)
{
    wp_nonce_field('wp_seed_content_formation_save_sections', 'wp_seed_content_formation_sections_nonce');
    echo '<div class="wpsck-formation-sections">';
    echo '<p class="description">' . esc_html__('Nommez les niveaux dans leur ordre d’affichage. Une section déjà utilisée par le calendrier ne peut pas être supprimée.', 'wp-seed-content-kit') . '</p>';
    echo '<div class="wpsck-formation-sections__list">';
    foreach (wp_seed_content_formation_get_sections($post->ID) as $section) wp_seed_content_formation_render_section_row($section);
    echo '</div><p><button type="button" class="button wpsck-formation-section-add">' . esc_html__('Ajouter une section', 'wp-seed-content-kit') . '</button></p>';
    echo '<template class="wpsck-formation-section-template">';
    wp_seed_content_formation_render_section_row(null);
    echo '</template></div>';
}

function wp_seed_content_formation_save_program_sections($post_id, $post)
{
    if (!$post instanceof WP_Post || 'seed_program' !== $post->post_type || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
    if (!isset($_POST['wp_seed_content_formation_sections_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_formation_sections_nonce'])), 'wp_seed_content_formation_save_sections')) return;
    if (!current_user_can('edit_seed_program', $post_id) || !current_user_can('edit_seed_sections')) return;
    $rows = isset($_POST['wpsck_formation_sections']) && is_array($_POST['wpsck_formation_sections']) ? wp_unslash($_POST['wpsck_formation_sections']) : array();
    $ids = isset($rows['id']) ? (array) $rows['id'] : array();
    $labels = isset($rows['label']) ? (array) $rows['label'] : array();
    $orders = isset($rows['order']) ? (array) $rows['order'] : array();
    $created = 0; $updated = 0; $deleted = 0; $blocked = 0;
    foreach ($labels as $index => $raw_label) {
        $label = sanitize_text_field($raw_label);
        if ('' === $label) continue;
        $section_id = isset($ids[$index]) ? absint($ids[$index]) : 0;
        $existing = $section_id ? get_post($section_id) : null;
        if ($section_id && (!$existing instanceof WP_Post || 'seed_section' !== $existing->post_type || 'trash' === $existing->post_status || absint(get_post_meta($section_id, '_seed_formation_program_id', true)) !== absint($post_id) || !current_user_can('edit_seed_section', $section_id))) continue;
        if (!$section_id && !current_user_can('publish_seed_sections')) continue;
        $payload = array(
            'post_type' => 'seed_section', 'post_title' => $label, 'post_status' => 'publish',
            'menu_order' => isset($orders[$index]) ? (int) $orders[$index] : (($index + 1) * 10),
        );
        if ($section_id) $payload['ID'] = $section_id;
        $saved_id = wp_insert_post(wp_slash($payload), true);
        if (!is_wp_error($saved_id)) {
            update_post_meta($saved_id, '_seed_formation_program_id', absint($post_id));
            $section_id ? $updated++ : $created++;
        }
    }
    $removed_ids = isset($_POST['wpsck_formation_removed_sections']) ? wp_seed_content_formation_sanitize_id_list(wp_unslash($_POST['wpsck_formation_removed_sections'])) : array();
    foreach ($removed_ids as $section_id) {
        $section = get_post($section_id);
        if (!$section instanceof WP_Post || 'seed_section' !== $section->post_type || absint(get_post_meta($section_id, '_seed_formation_program_id', true)) !== absint($post_id)) continue;
        if (!current_user_can('delete_seed_section', $section_id) || wp_seed_content_formation_section_usage_count($section_id)) { $blocked++; continue; }
        if (wp_trash_post($section_id)) $deleted++;
    }
    set_transient('wpsck_formation_sections_notice_' . get_current_user_id(), compact('created', 'updated', 'deleted', 'blocked'), 60);
}
add_action('save_post_seed_program', 'wp_seed_content_formation_save_program_sections', 10, 2);

function wp_seed_content_formation_render_sections_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || 'seed_program' !== $screen->post_type) return;
    $key = 'wpsck_formation_sections_notice_' . get_current_user_id();
    $notice = get_transient($key);
    if (!is_array($notice)) return;
    delete_transient($key);
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(
        __('Sections enregistrées : %1$d créée(s), %2$d mise(s) à jour, %3$d supprimée(s).', 'wp-seed-content-kit'),
        absint($notice['created']), absint($notice['updated']), absint($notice['deleted'])
    )) . '</p></div>';
    if (!empty($notice['blocked'])) echo '<div class="notice notice-warning"><p>' . esc_html__('Une section utilisée par le calendrier n’a pas été supprimée.', 'wp-seed-content-kit') . '</p></div>';
}
add_action('admin_notices', 'wp_seed_content_formation_render_sections_notice');

function wp_seed_content_formation_register_calendar_page()
{
    add_submenu_page('wp-seed-content-kit', __('Calendrier annuel', 'wp-seed-content-kit'), __('Calendrier annuel', 'wp-seed-content-kit'), 'edit_seed_schedules', 'wpsck-formation-calendar', 'wp_seed_content_formation_render_calendar_page');
}
add_action('admin_menu', 'wp_seed_content_formation_register_calendar_page', 30);

function wp_seed_content_formation_get_program_calendar_years($program_id)
{
    if (!$program_id) return array();
    $schedule_ids = get_posts(array(
        'post_type' => 'seed_schedule', 'post_status' => array('publish', 'draft', 'pending', 'private'),
        'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_seed_formation_program_id',
        'meta_value' => absint($program_id), 'no_found_rows' => true, 'suppress_filters' => true,
    ));
    $years = array();
    foreach ($schedule_ids as $schedule_id) {
        $year = absint(get_post_meta($schedule_id, '_seed_formation_calendar_year', true));
        if ($year) $years[] = $year;
    }
    return array_values(array_unique($years));
}

function wp_seed_content_formation_render_calendar_notice()
{
    if (!empty($_GET['formation_updated'])) echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('La programmation a été enregistrée.', 'wp-seed-content-kit') . '</p></div>';
    if (!empty($_GET['formation_error'])) {
        $labels = array('duplicate_schedule' => __('Ce thème est déjà programmé à ces dates dans cette section du programme. Modifiez la programmation existante.', 'wp-seed-content-kit'), 'schedule_busy' => __('Une sauvegarde est déjà en cours. Réessayez dans quelques instants.', 'wp-seed-content-kit'), 'date_order' => __('La date de fin doit suivre la date de début.', 'wp-seed-content-kit'), 'calendar_year_mismatch' => __('L’année doit correspondre à la date de début.', 'wp-seed-content-kit'), 'invalid_dates' => __('Vérifiez les deux dates.', 'wp-seed-content-kit'), 'section_program_mismatch' => __('La section doit appartenir au programme choisi.', 'wp-seed-content-kit'));
        $messages = array();
        foreach (explode(',', sanitize_text_field(wp_unslash($_GET['formation_error']))) as $code) $messages[] = isset($labels[$code]) ? $labels[$code] : __('Vérifiez les champs obligatoires et vos droits de modification.', 'wp-seed-content-kit');
        echo '<div class="notice notice-error" role="alert"><p>' . esc_html(implode(' ', array_unique($messages))) . '</p></div>';
    }
}

function wp_seed_content_formation_render_calendar_page()
{
    if (!current_user_can('edit_seed_schedules')) wp_die(esc_html__('Accès refusé.', 'wp-seed-content-kit'));
    $programs = get_posts(array('post_type' => 'seed_program', 'post_status' => array('publish', 'draft'), 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
    $program_id = isset($_GET['program_id']) ? absint($_GET['program_id']) : (1 === count($programs) ? (int) $programs[0]->ID : 0);
    $year = isset($_GET['year'])
        ? absint($_GET['year'])
        : wp_seed_content_formation_relevant_calendar_year(wp_seed_content_formation_get_program_calendar_years($program_id), (int) wp_date('Y'));
    echo '<div class="wrap wpsck-formation-admin"><h1>' . esc_html__('Calendrier annuel', 'wp-seed-content-kit') . '</h1>';
    wp_seed_content_formation_render_calendar_notice();
    echo '<form class="wpsck-formation-filters" method="get"><input type="hidden" name="page" value="wpsck-formation-calendar"><label for="wpsck-program-filter">' . esc_html__('Programme', 'wp-seed-content-kit') . '</label><select id="wpsck-program-filter" name="program_id"><option value="0">—</option>';
    foreach ($programs as $program) echo '<option value="' . esc_attr($program->ID) . '" data-calendar-year="' . esc_attr(wp_seed_content_formation_relevant_calendar_year(wp_seed_content_formation_get_program_calendar_years($program->ID), (int) wp_date('Y'))) . '" ' . selected($program_id, $program->ID, false) . '>' . esc_html($program->post_title) . '</option>';
    echo '</select><label for="wpsck-year-filter">' . esc_html__('Année', 'wp-seed-content-kit') . '</label><input id="wpsck-year-filter" type="number" min="2000" max="2200" name="year" value="' . esc_attr($year) . '">';
    submit_button(__('Afficher', 'wp-seed-content-kit'), 'secondary', '', false);
    echo '</form>';
    if (!$program_id) echo '<p>' . esc_html__('Sélectionnez un programme pour consulter son calendrier.', 'wp-seed-content-kit') . '</p>';
    if ($program_id) {
        $groups = wp_seed_content_formation_get_grouped_calendar(array('program_id' => $program_id, 'year' => $year, 'include_cancelled' => true));
        $schedule_count = 0;
        foreach ($groups as $group) $schedule_count += count($group['items']);
        wp_seed_content_formation_render_schedule_form($program_id, $year);
        echo '<div class="wpsck-formation-admin__calendar-heading"><h2>' . esc_html(sprintf(_n('%d programmation', '%d programmations', $schedule_count, 'wp-seed-content-kit'), $schedule_count)) . '</h2>';
        if ($groups) {
            echo '<nav class="wpsck-formation-admin__section-nav" aria-label="' . esc_attr__('Sections du calendrier', 'wp-seed-content-kit') . '">';
            foreach ($groups as $index => $group) echo '<a href="#wpsck-formation-calendar-section-' . esc_attr($index + 1) . '">' . esc_html($group['label']) . ' <span>(' . esc_html(count($group['items'])) . ')</span></a>';
            echo '</nav>';
        }
        echo '</div>';
        if (!$groups) echo '<p class="wpsck-formation-admin__empty">' . esc_html__('Aucune programmation pour ce programme et cette année.', 'wp-seed-content-kit') . '</p>';
        foreach ($groups as $index => $group) {
            echo '<section class="wpsck-formation-admin__section" id="wpsck-formation-calendar-section-' . esc_attr($index + 1) . '"><h3>' . esc_html($group['label']) . '</h3><ul class="wpsck-formation-admin__schedules">';
            foreach ($group['items'] as $item) wp_seed_content_formation_render_schedule_row($item, $program_id, $year);
            echo '</ul></section>';
        }
    }
    echo '</div>';
}

function wp_seed_content_formation_render_schedule_row($item, $program_id, $year)
{
    $edit_url = add_query_arg(array('page' => 'wpsck-formation-calendar', 'program_id' => $program_id, 'year' => $year, 'schedule_id' => $item['id']), admin_url('admin.php')) . '#wpsck-formation-schedule-form';
    $trash_url = wp_nonce_url(add_query_arg(array('action' => 'wpsck_formation_trash_schedule', 'schedule_id' => $item['id']), admin_url('admin-post.php')), 'wpsck_formation_trash_schedule_' . $item['id']);
    $people = $item['people'] ? implode(', ', wp_list_pluck($item['people'], 'name')) : '';
    echo '<li class="wpsck-formation-admin__schedule wpsck-formation-admin__schedule--' . esc_attr($item['status']) . '"><div class="wpsck-formation-admin__schedule-content">';
    echo '<strong>' . esc_html(wp_seed_content_formation_format_date_range($item['start_date'], $item['end_date'])) . '</strong><span>' . esc_html($item['display_title']) . '</span>';
    if ($people) echo '<span>' . esc_html($people) . '</span>';
    echo '</div><div class="wpsck-formation-admin__schedule-meta"><span class="wpsck-formation-admin__status">' . esc_html($item['status_label']) . '</span>';
    if ('postponed' === $item['status'] && $item['original_start_date'] && $item['original_end_date']) echo '<span>' . esc_html(sprintf(__('Dates originales : %s', 'wp-seed-content-kit'), wp_seed_content_formation_format_date_range($item['original_start_date'], $item['original_end_date']))) . '</span>';
    echo '<span><a class="button button-small" href="' . esc_url($edit_url) . '">' . esc_html__('Modifier', 'wp-seed-content-kit') . '</a> <a class="button-link-delete wpsck-formation-confirm-delete" href="' . esc_url($trash_url) . '">' . esc_html__('Supprimer', 'wp-seed-content-kit') . '</a></span></div></li>';
}

function wp_seed_content_formation_render_schedule_form($program_id, $year)
{
    $schedule_id = isset($_GET['schedule_id']) ? absint($_GET['schedule_id']) : 0;
    $item = $schedule_id ? wp_seed_content_formation_get_schedule_data($schedule_id, array('allow_unpublished' => true)) : array();
    if (!$item || $item['program_id'] !== absint($program_id) || !current_user_can('edit_seed_schedule', $schedule_id)) { $item = array(); $schedule_id = 0; }
    $pending = get_transient('wpsck_formation_form_' . get_current_user_id());
    if (is_array($pending) && $pending['program_id'] === absint($program_id) && $pending['schedule_id'] === $schedule_id && !empty($_GET['formation_error'])) {
        $item = array_merge(array('person_ids' => array(), 'annual_display_title' => '', 'original_start_date' => '', 'original_end_date' => ''), $pending);
        delete_transient('wpsck_formation_form_' . get_current_user_id());
    }
    $themes = get_posts(array('post_type' => 'seed_theme', 'post_status' => array('publish', 'draft'), 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
    $sections = wp_seed_content_formation_get_sections($program_id);
    $people = get_posts(array('post_type' => 'seed_directory', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
    $selected_people = $item ? $item['person_ids'] : array();
    $people_by_id = array();
    foreach ($people as $person) $people_by_id[$person->ID] = $person;
    $ordered_people = array();
    foreach ($selected_people as $person_id) {
        $person = isset($people_by_id[$person_id]) ? $people_by_id[$person_id] : get_post($person_id);
        if ($person instanceof WP_Post && 'seed_directory' === $person->post_type) $ordered_people[$person_id] = $person;
    }
    foreach ($people_by_id as $person_id => $person) if (!isset($ordered_people[$person_id])) $ordered_people[$person_id] = $person;
    echo '<section class="wpsck-formation-schedule-editor" id="wpsck-formation-schedule-form"><h2>' . esc_html($schedule_id ? __('Modifier la programmation', 'wp-seed-content-kit') : __('Ajouter une programmation', 'wp-seed-content-kit')) . '</h2>';
    echo '<p class="description">' . esc_html(sprintf(__('Calendrier %1$d · %2$s', 'wp-seed-content-kit'), $year, get_the_title($program_id))) . '</p>';
    echo '<form class="wpsck-formation-schedule-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="wpsck_formation_save_schedule"><input type="hidden" name="schedule_id" value="' . esc_attr($schedule_id) . '"><input type="hidden" name="program_id" value="' . esc_attr($program_id) . '"><input type="hidden" name="calendar_year" value="' . esc_attr($year) . '">';
    wp_nonce_field('wpsck_formation_save_schedule', 'wpsck_formation_nonce');
    echo '<div class="wpsck-formation-schedule-form__grid"><label>' . esc_html__('Thème', 'wp-seed-content-kit') . '<select name="theme_id" required><option value="">—</option>';
    foreach ($themes as $theme) echo '<option value="' . esc_attr($theme->ID) . '" ' . selected($item ? $item['theme_id'] : 0, $theme->ID, false) . '>' . esc_html($theme->post_title) . '</option>';
    echo '</select></label><label>' . esc_html__('Intitulé affiché cette année (facultatif)', 'wp-seed-content-kit') . '<input type="text" name="annual_display_title" value="' . esc_attr($item ? $item['annual_display_title'] : '') . '"><span class="description">' . esc_html__('Laissez vide pour reprendre le nom du thème.', 'wp-seed-content-kit') . '</span></label>';
    echo '<label>' . esc_html__('Section', 'wp-seed-content-kit') . '<select name="section_id" required><option value="">—</option>';
    foreach ($sections as $section) echo '<option value="' . esc_attr($section->ID) . '" ' . selected($item ? $item['section_id'] : 0, $section->ID, false) . '>' . esc_html($section->post_title) . '</option>';
    echo '</select></label><label>' . esc_html__('Date de début', 'wp-seed-content-kit') . '<input type="date" required name="start_date" value="' . esc_attr($item ? $item['start_date'] : '') . '"></label>';
    echo '<label>' . esc_html__('Date de fin', 'wp-seed-content-kit') . '<input type="date" required name="end_date" value="' . esc_attr($item ? $item['end_date'] : '') . '"></label>';
    echo '<fieldset class="wpsck-formation-people"><legend>' . esc_html__('Intervenants', 'wp-seed-content-kit') . '</legend><div class="wpsck-formation-people__list">';
    foreach ($ordered_people as $person) {
        $is_selected = in_array((int) $person->ID, $selected_people, true);
        echo '<div class="wpsck-formation-person"><label><input type="checkbox" name="person_ids[]" value="' . esc_attr($person->ID) . '" ' . checked($is_selected, true, false) . '> <span>' . esc_html($person->post_title) . '</span></label><span class="wpsck-formation-person-actions"' . ($is_selected ? '' : ' hidden') . '>';
        foreach (array('up' => __('Monter', 'wp-seed-content-kit'), 'down' => __('Descendre', 'wp-seed-content-kit')) as $direction => $label) echo '<button type="button" class="button button-small wpsck-formation-person-' . esc_attr($direction) . '" title="' . esc_attr($label . ' : ' . $person->post_title) . '" aria-label="' . esc_attr($label . ' : ' . $person->post_title) . '"><span class="dashicons dashicons-arrow-' . esc_attr($direction) . '-alt2" aria-hidden="true"></span></button>';
        echo '</span></div>';
    }
    echo '</div></fieldset><label>' . esc_html__('Statut', 'wp-seed-content-kit') . '<select name="formation_status">';
    foreach (wp_seed_content_formation_statuses() as $status => $label) echo '<option value="' . esc_attr($status) . '" ' . selected($item ? $item['status'] : 'planned', $status, false) . '>' . esc_html($label) . '</option>';
    echo '</select></label><label>' . esc_html__('Ordre dans la même section', 'wp-seed-content-kit') . '<input type="number" name="menu_order" value="' . esc_attr($item ? $item['display_order'] : 0) . '"><span class="description">' . esc_html__('À ordre égal, les programmations sont classées par date.', 'wp-seed-content-kit') . '</span></label></div>';
    if ($item && 'postponed' === $item['status'] && $item['original_start_date'] && $item['original_end_date']) echo '<p class="description"><strong>' . esc_html__('Dates originales :', 'wp-seed-content-kit') . '</strong> ' . esc_html(wp_seed_content_formation_format_date_range($item['original_start_date'], $item['original_end_date'])) . '</p>';
    submit_button($schedule_id ? __('Mettre à jour', 'wp-seed-content-kit') : __('Ajouter', 'wp-seed-content-kit'));
    if ($schedule_id) echo '<a class="button" href="' . esc_url(add_query_arg(array('page' => 'wpsck-formation-calendar', 'program_id' => $program_id, 'year' => $year), admin_url('admin.php'))) . '">' . esc_html__('Annuler', 'wp-seed-content-kit') . '</a>';
    echo '</form></section>';
}

function wp_seed_content_formation_save_schedule()
{
    if (!current_user_can('edit_seed_schedules')) wp_die(esc_html__('Accès refusé.', 'wp-seed-content-kit'));
    check_admin_referer('wpsck_formation_save_schedule', 'wpsck_formation_nonce');
    $schedule_id = isset($_POST['schedule_id']) ? absint($_POST['schedule_id']) : 0;
    $existing = $schedule_id ? get_post($schedule_id) : null;
    if ($schedule_id && (!$existing instanceof WP_Post || 'seed_schedule' !== $existing->post_type || !current_user_can('edit_seed_schedule', $schedule_id))) wp_die(esc_html__('Programmation invalide.', 'wp-seed-content-kit'));
    $values = array(
        'program_id' => isset($_POST['program_id']) ? absint($_POST['program_id']) : 0,
        'theme_id' => isset($_POST['theme_id']) ? absint($_POST['theme_id']) : 0,
        'section_id' => isset($_POST['section_id']) ? absint($_POST['section_id']) : 0,
        'calendar_year' => isset($_POST['calendar_year']) ? absint($_POST['calendar_year']) : 0,
        'start_date' => isset($_POST['start_date']) ? wp_unslash($_POST['start_date']) : '',
        'end_date' => isset($_POST['end_date']) ? wp_unslash($_POST['end_date']) : '',
        'status' => isset($_POST['formation_status']) ? sanitize_key(wp_unslash($_POST['formation_status'])) : 'planned',
        'person_ids' => isset($_POST['person_ids']) ? wp_seed_content_formation_sanitize_id_list(wp_unslash($_POST['person_ids'])) : array(),
        'annual_display_title' => isset($_POST['annual_display_title']) ? sanitize_text_field(wp_unslash($_POST['annual_display_title'])) : '',
        'menu_order' => isset($_POST['menu_order']) ? (int) $_POST['menu_order'] : 0,
    );
    $redirect = add_query_arg(array('page' => 'wpsck-formation-calendar', 'program_id' => $values['program_id'], 'year' => $values['calendar_year']), admin_url('admin.php'));
    $saved_id = wp_seed_content_formation_store_schedule($values, $schedule_id);
    if (is_wp_error($saved_id)) {
        set_transient('wpsck_formation_form_' . get_current_user_id(), array_merge($values, array('schedule_id' => $schedule_id, 'display_order' => $values['menu_order'])), 600);
        $codes = $saved_id->get_error_data();
        wp_safe_redirect(add_query_arg(array('schedule_id' => $schedule_id, 'formation_error' => is_array($codes) ? implode(',', $codes) : $saved_id->get_error_code()), $redirect));
        exit;
    }
    delete_transient('wpsck_formation_form_' . get_current_user_id());
    wp_safe_redirect(add_query_arg('formation_updated', 1, $redirect));
    exit;
}
add_action('admin_post_wpsck_formation_save_schedule', 'wp_seed_content_formation_save_schedule');

function wp_seed_content_formation_trash_schedule()
{
    $schedule_id = isset($_GET['schedule_id']) ? absint($_GET['schedule_id']) : 0;
    check_admin_referer('wpsck_formation_trash_schedule_' . $schedule_id);
    if (!$schedule_id || !current_user_can('delete_seed_schedule', $schedule_id)) wp_die(esc_html__('Accès refusé.', 'wp-seed-content-kit'));
    $post = get_post($schedule_id);
    if (!$post instanceof WP_Post || 'seed_schedule' !== $post->post_type) wp_die(esc_html__('Programmation invalide.', 'wp-seed-content-kit'));
    wp_trash_post($schedule_id);
    wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpsck-formation-calendar'));
    exit;
}
add_action('admin_post_wpsck_formation_trash_schedule', 'wp_seed_content_formation_trash_schedule');

function wp_seed_content_formation_enqueue_admin_assets()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $is_program = $screen && 'seed_program' === $screen->post_type;
    $is_calendar = isset($_GET['page']) && 'wpsck-formation-calendar' === sanitize_key(wp_unslash($_GET['page']));
    if (!$is_program && !$is_calendar) return;
    $asset_version = WP_SEED_CONTENT_KIT_VERSION . '-formation-v42';
    wp_enqueue_style('wpsck-formation-admin', WP_SEED_CONTENT_KIT_URL . 'assets/css/formation-admin.css', array(), $asset_version);
    wp_enqueue_script('wpsck-formation-admin', WP_SEED_CONTENT_KIT_URL . 'assets/js/formation-admin.js', array(), $asset_version, true);
    wp_localize_script('wpsck-formation-admin', 'wpsckFormationAdmin', array('confirmDelete' => __('Supprimer cette programmation ?', 'wp-seed-content-kit')));
}
add_action('admin_enqueue_scripts', 'wp_seed_content_formation_enqueue_admin_assets');
