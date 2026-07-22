<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_add_meta_boxes()
{
    remove_meta_box('postexcerpt', 'seed_directory', 'normal');
    remove_meta_box('pageparentdiv', 'seed_directory', 'side');
    remove_meta_box('slugdiv', 'seed_directory', 'normal');

    add_meta_box('wp_seed_content_directory_identity', __('Identité', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_identity_box', 'seed_directory', 'normal', 'high');
    add_meta_box('wp_seed_content_directory_situation', __('Localisation, présentation et photo', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_situation_box', 'seed_directory', 'normal', 'high');
    add_meta_box('wp_seed_content_directory_contacts', __('Coordonnées', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_contacts_box', 'seed_directory', 'normal', 'default');
    add_meta_box('wp_seed_content_directory_publication', __('Autorisation et suivi', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_publication_box', 'seed_directory', 'normal', 'default');
}
add_action('add_meta_boxes_seed_directory', 'wp_seed_content_directory_add_meta_boxes');

function wp_seed_content_directory_get_admin_field_value($post_id, $key)
{
    $notice = wp_seed_content_directory_get_publication_notice($post_id);
    if (isset($notice['values']) && array_key_exists($key, $notice['values'])) {
        return $notice['values'][$key];
    }
    if ('_seed_directory_photo_alt' === $key) {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        return $thumbnail_id ? get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) : '';
    }
    return wp_seed_content_directory_get_meta_value($post_id, $key);
}

function wp_seed_content_directory_get_field_error($post_id, $field_id)
{
    $notice = wp_seed_content_directory_get_publication_notice($post_id);
    $labels = wp_seed_content_directory_get_error_labels();
    $fields = wp_seed_content_directory_get_error_field_ids();
    foreach ($notice['errors'] as $error) {
        if (isset($fields[$error]) && $field_id === $fields[$error] && isset($labels[$error])) {
            return $labels[$error];
        }
    }
    return '';
}

function wp_seed_content_directory_render_field_error($post_id, $field_id)
{
    $message = wp_seed_content_directory_get_field_error($post_id, $field_id);
    if ('' !== $message) {
        echo '<p id="' . esc_attr($field_id . '-error') . '" class="seed-directory-field-error"><span class="dashicons dashicons-warning" aria-hidden="true"></span> ' . esc_html($message) . '</p>';
    }
}

function wp_seed_content_directory_get_error_attributes($post_id, $field_id)
{
    return '' !== wp_seed_content_directory_get_field_error($post_id, $field_id)
        ? ' aria-invalid="true" aria-describedby="' . esc_attr($field_id . '-error') . '"'
        : '';
}

function wp_seed_content_directory_render_identity_box($post)
{
    wp_nonce_field('wp_seed_content_directory_save', 'wp_seed_content_directory_nonce');
    $status = wp_seed_content_directory_get_admin_field_value($post->ID, '_seed_directory_status');
    ?>
    <p class="description"><?php esc_html_e('Saisissez le nom de la personne dans le champ « Nom affiché » situé au-dessus.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_directory_render_field_error($post->ID, 'title'); ?>
    <p>
        <label for="seed-directory-status"><strong><?php esc_html_e('Statut', 'wp-seed-content-kit'); ?></strong></label><br>
        <select id="seed-directory-status" name="_seed_directory_status"<?php echo wp_seed_content_directory_get_error_attributes($post->ID, 'seed-directory-status'); ?>>
            <option value=""><?php esc_html_e('Choisir un statut', 'wp-seed-content-kit'); ?></option>
            <?php foreach (wp_seed_content_directory_get_statuses() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <?php wp_seed_content_directory_render_field_error($post->ID, 'seed-directory-status'); ?>
    </p>
    <?php wp_seed_content_directory_render_checkbox($post->ID, '_seed_directory_featured', __('Mettre cette personne en avant dans l’annuaire', 'wp-seed-content-kit')); ?>
    <?php
}

function wp_seed_content_directory_render_situation_box($post)
{
    ?>
    <fieldset class="seed-directory-field-group">
        <legend><strong><?php esc_html_e('Localisation', 'wp-seed-content-kit'); ?></strong></legend>
        <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_city', __('Ville', 'wp-seed-content-kit')); ?>
        <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_postal_code', __('Code postal', 'wp-seed-content-kit'), 'text', 16); ?>
        <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_department', __('Département', 'wp-seed-content-kit'), 'text', 12); ?>
        <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_country', __('Pays', 'wp-seed-content-kit'), 'text', 2, __('Par exemple : FR pour France.', 'wp-seed-content-kit')); ?>
    </fieldset>
    <fieldset class="seed-directory-field-group">
        <legend><strong><?php esc_html_e('Présentation', 'wp-seed-content-kit'); ?></strong></legend>
        <p>
            <label for="wp_seed_content_directory_excerpt"><strong><?php esc_html_e('Présentation courte', 'wp-seed-content-kit'); ?></strong></label><br>
            <textarea id="wp_seed_content_directory_excerpt" name="wp_seed_content_directory_excerpt" rows="5" class="widefat"><?php echo esc_textarea($post->post_excerpt); ?></textarea>
        </p>
        <p class="description"><?php esc_html_e('Présentez brièvement l’activité, les modalités, les spécialités ou le public accompagné lorsque ces informations sont utiles.', 'wp-seed-content-kit'); ?></p>
    </fieldset>
    <fieldset class="seed-directory-field-group">
        <legend><strong><?php esc_html_e('Photo', 'wp-seed-content-kit'); ?></strong></legend>
        <p class="description"><?php esc_html_e('La photo est facultative et se choisit dans le panneau « Photo ». Si vous en ajoutez une, renseignez son texte alternatif ci-dessous.', 'wp-seed-content-kit'); ?></p>
        <p>
            <label for="wp_seed_content_directory_photo_alt"><strong><?php esc_html_e('Texte alternatif de la photo', 'wp-seed-content-kit'); ?></strong></label><br>
            <input id="wp_seed_content_directory_photo_alt" name="_seed_directory_photo_alt" type="text" value="<?php echo esc_attr(wp_seed_content_directory_get_admin_field_value($post->ID, '_seed_directory_photo_alt')); ?>" class="widefat"<?php echo wp_seed_content_directory_get_error_attributes($post->ID, 'wp_seed_content_directory_photo_alt'); ?>>
            <?php wp_seed_content_directory_render_field_error($post->ID, 'wp_seed_content_directory_photo_alt'); ?>
        </p>
    </fieldset>
    <?php
}

function wp_seed_content_directory_render_contacts_box($post)
{
    echo '<p class="description">' . esc_html__('Les coordonnées restent privées tant que leur case d’affichage n’est pas cochée. Une coordonnée publique doit être renseignée et valide.', 'wp-seed-content-kit') . '</p>';
    foreach (wp_seed_content_directory_get_contact_definitions() as $contact) {
        $value = wp_seed_content_directory_get_admin_field_value($post->ID, $contact['key']);
        $visible = wp_seed_content_directory_get_admin_field_value($post->ID, $contact['visible_key']);
        echo '<fieldset class="seed-directory-contact">';
        echo '<legend><strong>' . esc_html($contact['label']) . '</strong></legend>';
        echo '<label class="screen-reader-text" for="' . esc_attr($contact['key']) . '">' . esc_html($contact['label']) . '</label>';
        echo '<input id="' . esc_attr($contact['key']) . '" name="' . esc_attr($contact['key']) . '" type="text" inputmode="' . esc_attr('email' === $contact['type'] ? 'email' : ('tel' === $contact['type'] ? 'tel' : 'url')) . '" value="' . esc_attr($value) . '" class="regular-text"' . wp_seed_content_directory_get_error_attributes($post->ID, $contact['key']) . '>';
        wp_seed_content_directory_render_field_error($post->ID, $contact['key']);
        echo '<label class="seed-directory-visibility"><input id="' . esc_attr($contact['visible_key']) . '" name="' . esc_attr($contact['visible_key']) . '" type="checkbox" value="1" ' . checked('1', $visible, false) . '> ' . esc_html($contact['visibility_label']) . '</label>';
        echo '</fieldset>';
    }
}

function wp_seed_content_directory_render_publication_box($post)
{
    wp_seed_content_directory_render_checkbox($post->ID, '_seed_directory_publication_authorized', __('La personne a autorisé la publication de ses informations', 'wp-seed-content-kit'));
    wp_seed_content_directory_render_field_error($post->ID, '_seed_directory_publication_authorized');
    echo '<p class="description">' . esc_html__('Cette autorisation est obligatoire pour publier la fiche. Elle ne rend aucune coordonnée publique automatiquement.', 'wp-seed-content-kit') . '</p>';
    ?>
    <fieldset class="seed-directory-field-group seed-directory-secondary">
        <legend><strong><?php esc_html_e('Suivi interne facultatif', 'wp-seed-content-kit'); ?></strong></legend>
        <p><label for="seed-directory-note"><strong><?php esc_html_e('Note interne', 'wp-seed-content-kit'); ?></strong></label><br>
            <textarea id="seed-directory-note" name="_seed_directory_internal_note" rows="4" class="widefat"><?php echo esc_textarea(wp_seed_content_directory_get_admin_field_value($post->ID, '_seed_directory_internal_note')); ?></textarea></p>
        <p><label for="seed-directory-last-verified"><strong><?php esc_html_e('Dernière vérification', 'wp-seed-content-kit'); ?></strong></label><br>
            <input id="seed-directory-last-verified" name="_seed_directory_last_verified" type="date" value="<?php echo esc_attr(wp_seed_content_directory_get_admin_field_value($post->ID, '_seed_directory_last_verified')); ?>"></p>
    </fieldset>
    <?php
}

function wp_seed_content_directory_render_text_input($post_id, $key, $label, $type = 'text', $maxlength = 0, $help = '')
{
    echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
    echo '<input id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="' . esc_attr($type) . '" value="' . esc_attr(wp_seed_content_directory_get_admin_field_value($post_id, $key)) . '" class="regular-text"' . ($maxlength ? ' maxlength="' . (int) $maxlength . '"' : '') . wp_seed_content_directory_get_error_attributes($post_id, $key) . '>';
    if ('' !== $help) {
        echo '<br><span class="description">' . esc_html($help) . '</span>';
    }
    wp_seed_content_directory_render_field_error($post_id, $key);
    echo '</p>';
}

function wp_seed_content_directory_render_checkbox($post_id, $key, $label)
{
    echo '<p><label><input id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="checkbox" value="1" ' . checked('1', wp_seed_content_directory_get_admin_field_value($post_id, $key), false) . wp_seed_content_directory_get_error_attributes($post_id, $key) . '> ' . esc_html($label) . '</label></p>';
}

function wp_seed_content_directory_save_meta($post_id, $post)
{
    static $saving = false;
    if ($saving || !$post || 'seed_directory' !== $post->post_type || !isset($_POST['wp_seed_content_directory_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_seed_content_directory_nonce'])), 'wp_seed_content_directory_save')) {
        return;
    }
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_seed_directory_entry', $post_id)) {
        return;
    }

    foreach (wp_seed_content_directory_get_meta_definitions() as $key => $definition) {
        if ('boolean' === $definition['type']) {
            $value = isset($_POST[$key]) ? '1' : '';
        } elseif (array_key_exists($key, $_POST)) {
            $value = wp_seed_content_directory_sanitize_meta_value($key, wp_unslash($_POST[$key]));
        } else {
            continue;
        }
        if ('' === $value) {
            delete_post_meta($post_id, $key);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }

    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    if ($thumbnail_id && isset($_POST['_seed_directory_photo_alt'])) {
        update_post_meta($thumbnail_id, '_wp_attachment_image_alt', sanitize_text_field(wp_unslash($_POST['_seed_directory_photo_alt'])));
    }

    $excerpt = isset($_POST['wp_seed_content_directory_excerpt']) ? sanitize_textarea_field(wp_unslash($_POST['wp_seed_content_directory_excerpt'])) : (string) $post->post_excerpt;
    if ($excerpt !== (string) $post->post_excerpt) {
        $saving = true;
        wp_update_post(array('ID' => (int) $post_id, 'post_excerpt' => $excerpt));
        $saving = false;
    }

    wp_seed_content_directory_reassign_pending_notice($post_id);
    wp_seed_content_directory_enforce_publication($post_id, get_post($post_id));
}
add_action('save_post_seed_directory', 'wp_seed_content_directory_save_meta', 20, 2);

function wp_seed_content_directory_columns($columns)
{
    return array(
        'cb' => isset($columns['cb']) ? $columns['cb'] : '<input type="checkbox">',
        'directory_photo' => __('Photo', 'wp-seed-content-kit'),
        'title' => __('Nom', 'wp-seed-content-kit'),
        'directory_status' => __('Statut', 'wp-seed-content-kit'),
        'directory_city' => __('Ville', 'wp-seed-content-kit'),
        'directory_department' => __('Département', 'wp-seed-content-kit'),
        'directory_authorized' => __('Publication autorisée', 'wp-seed-content-kit'),
        'directory_public_contacts' => __('Coordonnées publiques', 'wp-seed-content-kit'),
        'directory_wp_state' => __('État', 'wp-seed-content-kit'),
        'date' => __('Modification', 'wp-seed-content-kit'),
    );
}
add_filter('manage_seed_directory_posts_columns', 'wp_seed_content_directory_columns', 30);

function wp_seed_content_directory_render_column($column, $post_id)
{
    if ('directory_photo' === $column) {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        echo $thumbnail_id ? wp_get_attachment_image($thumbnail_id, array(48, 48), false, array('alt' => '')) : '&mdash;';
    } elseif ('directory_status' === $column) {
        $statuses = wp_seed_content_directory_get_statuses();
        $status = wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_status');
        echo isset($statuses[$status]) ? esc_html($statuses[$status]) : '&mdash;';
    } elseif ('directory_city' === $column || 'directory_department' === $column) {
        $key = 'directory_city' === $column ? '_seed_directory_city' : '_seed_directory_department';
        $value = wp_seed_content_directory_get_meta_value($post_id, $key);
        echo '' !== $value ? esc_html($value) : '&mdash;';
    } elseif ('directory_authorized' === $column) {
        echo '1' === get_post_meta($post_id, '_seed_directory_publication_authorized', true) ? esc_html__('Oui', 'wp-seed-content-kit') : esc_html__('Non', 'wp-seed-content-kit');
    } elseif ('directory_public_contacts' === $column) {
        $labels = array();
        foreach (wp_seed_content_directory_get_contact_definitions() as $contact) {
            if ('1' === get_post_meta($post_id, $contact['visible_key'], true) && '' !== wp_seed_content_directory_normalize_contact_value($contact['key'], get_post_meta($post_id, $contact['key'], true))) {
                $labels[] = $contact['label'];
            }
        }
        echo $labels ? esc_html(implode(', ', $labels)) : esc_html__('Aucune', 'wp-seed-content-kit');
    } elseif ('directory_wp_state' === $column) {
        $status = get_post_status_object(get_post_status($post_id));
        echo $status && isset($status->label) ? esc_html($status->label) : '&mdash;';
    }
}
add_action('manage_seed_directory_posts_custom_column', 'wp_seed_content_directory_render_column', 30, 2);

function wp_seed_content_directory_sortable_columns($columns)
{
    $columns['title'] = 'title';
    $columns['date'] = 'date';
    $columns['directory_status'] = 'directory_status';
    $columns['directory_city'] = 'directory_city';
    $columns['directory_department'] = 'directory_department';
    return $columns;
}
add_filter('manage_edit-seed_directory_sortable_columns', 'wp_seed_content_directory_sortable_columns');

function wp_seed_content_directory_apply_admin_sorting($query)
{
    if (!$query instanceof WP_Query || !$query->is_admin || !$query->is_main_query() || 'seed_directory' !== $query->get('post_type')) {
        return;
    }
    $sort_keys = array(
        'directory_status' => '_seed_directory_status',
        'directory_city' => '_seed_directory_city',
        'directory_department' => '_seed_directory_department',
    );
    $orderby = $query->get('orderby');
    if (is_string($orderby) && isset($sort_keys[$orderby])) {
        $query->set('meta_key', $sort_keys[$orderby]);
        $query->set('orderby', 'meta_value');
    }

    $meta_query = array();
    $professional_status = isset($_GET['directory_professional_status']) ? sanitize_key(wp_unslash($_GET['directory_professional_status'])) : '';
    if (isset(wp_seed_content_directory_get_statuses()[$professional_status])) {
        $meta_query[] = array('key' => '_seed_directory_status', 'value' => $professional_status);
    }

    if ($meta_query) {
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'wp_seed_content_directory_apply_admin_sorting', 20);

function wp_seed_content_directory_admin_filters($post_type)
{
    if ('seed_directory' !== $post_type) {
        return;
    }
    $selected_status = isset($_GET['directory_professional_status']) ? sanitize_key(wp_unslash($_GET['directory_professional_status'])) : '';
    echo '<label class="screen-reader-text" for="directory-professional-status-filter">' . esc_html__('Filtrer par statut', 'wp-seed-content-kit') . '</label>';
    echo '<select id="directory-professional-status-filter" name="directory_professional_status"><option value="">' . esc_html__('Tous les statuts', 'wp-seed-content-kit') . '</option>';
    foreach (wp_seed_content_directory_get_statuses() as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($selected_status, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'wp_seed_content_directory_admin_filters');

function wp_seed_content_directory_title_placeholder($placeholder, $post)
{
    return is_object($post) && isset($post->post_type) && 'seed_directory' === $post->post_type
        ? __('Nom affiché', 'wp-seed-content-kit')
        : $placeholder;
}
add_filter('enter_title_here', 'wp_seed_content_directory_title_placeholder', 10, 2);

function wp_seed_content_directory_admin_notices()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || 'seed_directory' !== $screen->post_type) {
        return;
    }
    $post_id = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;
    $notice = wp_seed_content_directory_get_publication_notice($post_id);
    if (empty($notice['errors'])) {
        return;
    }
    $labels = wp_seed_content_directory_get_error_labels();
    $fields = wp_seed_content_directory_get_error_field_ids();
    echo '<div class="notice notice-error" role="alert"><p><strong>' . esc_html__('La fiche a été enregistrée en brouillon. Corrigez les éléments suivants avant de la publier :', 'wp-seed-content-kit') . '</strong></p><ul>';
    foreach ($notice['errors'] as $error) {
        if (!isset($labels[$error])) {
            continue;
        }
        $message = esc_html($labels[$error]);
        echo isset($fields[$error]) ? '<li><a href="#' . esc_attr($fields[$error]) . '">' . $message . '</a></li>' : '<li>' . $message . '</li>';
    }
    echo '</ul></div>';
}
add_action('admin_notices', 'wp_seed_content_directory_admin_notices');

function wp_seed_content_directory_admin_styles()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || 'seed_directory' !== $screen->post_type) {
        return;
    }
    echo '<style>.seed-directory-field-group{min-width:0;max-width:100%;margin:0 0 20px;padding:0;border:0;box-sizing:border-box}.seed-directory-field-group legend{font-size:14px;margin-bottom:4px}.seed-directory-contact{min-width:0;max-width:100%;margin:14px 0;padding:12px;border-left:4px solid #c3c4c7;background:#f6f7f7;box-sizing:border-box}.seed-directory-contact legend{padding:0 4px}#wp_seed_content_directory_situation .regular-text,#wp_seed_content_directory_contacts .regular-text{display:block;width:100%;box-sizing:border-box}#wp_seed_content_directory_situation .regular-text{max-width:400px}#wp_seed_content_directory_contacts .regular-text{max-width:520px;margin:6px 0 10px}.seed-directory-visibility{display:inline-block;max-width:100%;overflow-wrap:anywhere}.seed-directory-secondary{margin-top:20px;padding-top:16px;border-top:1px solid #dcdcde}.seed-directory-field-error{color:#b32d2e;font-weight:600}.seed-directory-field-error .dashicons{font-size:18px;width:18px;height:18px}input[aria-invalid=true],select[aria-invalid=true],textarea[aria-invalid=true]{border-color:#d63638;box-shadow:0 0 0 1px #d63638}@media(max-width:782px){#wp_seed_content_directory_situation .regular-text,#wp_seed_content_directory_contacts .regular-text{max-width:100%}.column-directory_photo,.column-directory_department{display:none}}</style>';
}
add_action('admin_head-post.php', 'wp_seed_content_directory_admin_styles');
add_action('admin_head-post-new.php', 'wp_seed_content_directory_admin_styles');
add_action('admin_head-edit.php', 'wp_seed_content_directory_admin_styles');