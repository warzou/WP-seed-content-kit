<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_add_meta_boxes()
{
    remove_meta_box('postexcerpt', 'seed_directory', 'normal');

    add_meta_box('wp_seed_content_directory_identity', __('Identité et présentation', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_identity_box', 'seed_directory', 'normal', 'high');
    add_meta_box('wp_seed_content_directory_situation', __('Situation', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_situation_box', 'seed_directory', 'normal', 'default');
    add_meta_box('wp_seed_content_directory_contacts', __('Coordonnées et visibilité', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_contacts_box', 'seed_directory', 'normal', 'default');
    add_meta_box('wp_seed_content_directory_publication', __('Publication et suivi', 'wp-seed-content-kit'), 'wp_seed_content_directory_render_publication_box', 'seed_directory', 'normal', 'default');
}
add_action('add_meta_boxes_seed_directory', 'wp_seed_content_directory_add_meta_boxes');

function wp_seed_content_directory_render_identity_box($post)
{
    wp_nonce_field('wp_seed_content_directory_save', 'wp_seed_content_directory_nonce');
    $thumbnail_id = (int) get_post_thumbnail_id($post->ID);
    $alt = $thumbnail_id ? get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) : '';
    ?>
    <p class="description"><?php esc_html_e('Le nom affiché se renseigne dans le champ de titre. La photo se choisit dans le panneau Photo.', 'wp-seed-content-kit'); ?></p>
    <p>
        <label for="wp_seed_content_directory_excerpt"><strong><?php esc_html_e('Présentation courte', 'wp-seed-content-kit'); ?></strong></label><br>
        <textarea id="wp_seed_content_directory_excerpt" name="wp_seed_content_directory_excerpt" rows="5" class="widefat"><?php echo esc_textarea($post->post_excerpt); ?></textarea>
    </p>
    <?php if ($thumbnail_id) : ?>
        <p>
            <label for="wp_seed_content_directory_photo_alt"><strong><?php esc_html_e('Texte alternatif de la photo', 'wp-seed-content-kit'); ?></strong></label><br>
            <input id="wp_seed_content_directory_photo_alt" name="_seed_directory_photo_alt" type="text" value="<?php echo esc_attr($alt); ?>" class="widefat">
        </p>
        <p class="description"><?php esc_html_e('Décrivez brièvement la personne ou la photo. Ce texte est obligatoire avant publication.', 'wp-seed-content-kit'); ?></p>
    <?php else : ?>
        <p class="description"><?php esc_html_e('La photo est facultative. Lorsqu’elle est ajoutée, son texte alternatif devient obligatoire avant publication.', 'wp-seed-content-kit'); ?></p>
    <?php endif;
}

function wp_seed_content_directory_render_situation_box($post)
{
    $status = wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_status');
    ?>
    <p><label for="seed-directory-status"><strong><?php esc_html_e('Statut', 'wp-seed-content-kit'); ?></strong></label><br>
        <select id="seed-directory-status" name="_seed_directory_status">
            <option value=""><?php esc_html_e('Sélectionner', 'wp-seed-content-kit'); ?></option>
            <?php foreach (wp_seed_content_directory_get_statuses() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select></p>
    <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_city', __('Ville', 'wp-seed-content-kit')); ?>
    <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_postal_code', __('Code postal', 'wp-seed-content-kit'), 'text', 16); ?>
    <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_department', __('Département', 'wp-seed-content-kit'), 'text', 12); ?>
    <?php wp_seed_content_directory_render_text_input($post->ID, '_seed_directory_country', __('Pays (code à deux lettres)', 'wp-seed-content-kit'), 'text', 2); ?>
    <p><label for="seed-directory-order"><strong><?php esc_html_e('Ordre', 'wp-seed-content-kit'); ?></strong></label><br>
        <input id="seed-directory-order" name="wp_seed_content_directory_order" type="number" min="0" step="1" value="<?php echo esc_attr(max(0, (int) $post->menu_order)); ?>"></p>
    <?php wp_seed_content_directory_render_checkbox($post->ID, '_seed_directory_featured', __('Mettre cette fiche en avant', 'wp-seed-content-kit')); ?>
    <?php
}

function wp_seed_content_directory_render_contacts_box($post)
{
    $contacts = array(
        '_seed_directory_phone' => array(__('Téléphone', 'wp-seed-content-kit'), 'tel'),
        '_seed_directory_email' => array(__('E-mail', 'wp-seed-content-kit'), 'email'),
        '_seed_directory_website' => array(__('Site internet', 'wp-seed-content-kit'), 'url'),
        '_seed_directory_facebook' => array(__('Facebook', 'wp-seed-content-kit'), 'url'),
        '_seed_directory_instagram' => array(__('Instagram', 'wp-seed-content-kit'), 'url'),
    );
    echo '<table class="form-table"><tbody>';
    foreach ($contacts as $key => $config) {
        echo '<tr><th scope="row"><label for="' . esc_attr($key) . '">' . esc_html($config[0]) . '</label></th><td>';
        echo '<input id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="' . esc_attr($config[1]) . '" value="' . esc_attr(wp_seed_content_directory_get_meta_value($post->ID, $key)) . '" class="regular-text"> ';
        echo '<label><input name="' . esc_attr($key . '_visible') . '" type="checkbox" value="1" ' . checked('1', get_post_meta($post->ID, $key . '_visible', true), false) . '> ' . esc_html__('Afficher publiquement', 'wp-seed-content-kit') . '</label>';
        echo '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p class="description">' . esc_html__('Une coordonnée n’est rendue disponible que si elle est valide et si sa case est cochée.', 'wp-seed-content-kit') . '</p>';
}

function wp_seed_content_directory_render_publication_box($post)
{
    wp_seed_content_directory_render_checkbox($post->ID, '_seed_directory_publication_authorized', __('Autorisation de publication obtenue', 'wp-seed-content-kit'));
    echo '<p class="description">' . esc_html__('Confirme que la personne a autorisé la publication de sa fiche, de sa photo et des coordonnées rendues visibles.', 'wp-seed-content-kit') . '</p>';
    ?>
    <p><label for="seed-directory-note"><strong><?php esc_html_e('Note interne', 'wp-seed-content-kit'); ?></strong></label><br>
        <textarea id="seed-directory-note" name="_seed_directory_internal_note" rows="4" class="widefat"><?php echo esc_textarea(wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_internal_note')); ?></textarea></p>
    <p><label for="seed-directory-last-verified"><strong><?php esc_html_e('Dernière vérification', 'wp-seed-content-kit'); ?></strong></label><br>
        <input id="seed-directory-last-verified" name="_seed_directory_last_verified" type="date" value="<?php echo esc_attr(wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_last_verified')); ?>"></p>
    <?php
}

function wp_seed_content_directory_render_text_input($post_id, $key, $label, $type = 'text', $maxlength = 0)
{
    echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
    echo '<input id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="' . esc_attr($type) . '" value="' . esc_attr(wp_seed_content_directory_get_meta_value($post_id, $key)) . '" class="regular-text"' . ($maxlength ? ' maxlength="' . (int) $maxlength . '"' : '') . '></p>';
}

function wp_seed_content_directory_render_checkbox($post_id, $key, $label)
{
    echo '<p><label><input name="' . esc_attr($key) . '" type="checkbox" value="1" ' . checked('1', get_post_meta($post_id, $key, true), false) . '> ' . esc_html($label) . '</label></p>';
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
    $order = isset($_POST['wp_seed_content_directory_order']) ? max(0, absint(wp_unslash($_POST['wp_seed_content_directory_order']))) : max(0, (int) $post->menu_order);
    if ($excerpt !== (string) $post->post_excerpt || $order !== (int) $post->menu_order) {
        $saving = true;
        wp_update_post(array('ID' => (int) $post_id, 'post_excerpt' => $excerpt, 'menu_order' => $order));
        $saving = false;
    }

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
        'directory_location' => __('Ville / département', 'wp-seed-content-kit'),
        'directory_authorized' => __('Autorisation', 'wp-seed-content-kit'),
        'directory_order' => __('Ordre', 'wp-seed-content-kit'),
        'date' => isset($columns['date']) ? $columns['date'] : __('Date', 'wp-seed-content-kit'),
    );
}
add_filter('manage_seed_directory_posts_columns', 'wp_seed_content_directory_columns', 30);

function wp_seed_content_directory_render_column($column, $post_id)
{
    if ('directory_photo' === $column) {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        echo $thumbnail_id ? wp_get_attachment_image($thumbnail_id, array(48, 48), false, array('alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true))) : '&mdash;';
    } elseif ('directory_status' === $column) {
        $statuses = wp_seed_content_directory_get_statuses();
        $status = wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_status');
        echo isset($statuses[$status]) ? esc_html($statuses[$status]) : '&mdash;';
    } elseif ('directory_location' === $column) {
        $parts = array_filter(array(wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_city'), wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_department')));
        echo $parts ? esc_html(implode(' / ', $parts)) : '&mdash;';
    } elseif ('directory_authorized' === $column) {
        echo '1' === get_post_meta($post_id, '_seed_directory_publication_authorized', true) ? esc_html__('Oui', 'wp-seed-content-kit') : esc_html__('Non', 'wp-seed-content-kit');
    } elseif ('directory_order' === $column) {
        echo (int) get_post_field('menu_order', $post_id);
    }
}
add_action('manage_seed_directory_posts_custom_column', 'wp_seed_content_directory_render_column', 30, 2);

function wp_seed_content_directory_sortable_columns($columns)
{
    $columns['title'] = 'title';
    $columns['date'] = 'date';
    $columns['directory_order'] = 'menu_order';
    $columns['directory_status'] = 'directory_status';
    return $columns;
}
add_filter('manage_edit-seed_directory_sortable_columns', 'wp_seed_content_directory_sortable_columns');

function wp_seed_content_directory_apply_admin_sorting($query)
{
    if (!$query instanceof WP_Query || !$query->is_admin || !$query->is_main_query() || 'seed_directory' !== $query->get('post_type')) {
        return;
    }
    if ('directory_status' === $query->get('orderby')) {
        $query->set('meta_key', '_seed_directory_status');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'wp_seed_content_directory_apply_admin_sorting', 20);

function wp_seed_content_directory_admin_notices()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || 'seed_directory' !== $screen->post_type) {
        return;
    }
    $post_id = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;
    $key = 'wp_seed_content_directory_notice_' . get_current_user_id() . '_' . $post_id;
    $errors = get_transient($key);
    if (!$errors) {
        return;
    }
    delete_transient($key);
    $labels = wp_seed_content_directory_get_error_labels();
    $messages = array();
    foreach ((array) $errors as $error) {
        if (isset($labels[$error])) {
            $messages[] = $labels[$error];
        }
    }
    if ($messages) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(sprintf(__('Publication impossible : %s.', 'wp-seed-content-kit'), implode(', ', $messages))) . '</p></div>';
    }
}
add_action('admin_notices', 'wp_seed_content_directory_admin_notices');
