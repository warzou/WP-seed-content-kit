<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_testimonial_editor_id()
{
    return 'wp_seed_content_testimonial_text';
}

function wp_seed_content_testimonial_editor_settings()
{
    return array(
        'textarea_name' => 'seed_testimonial_text',
        'textarea_rows' => 8,
        'media_buttons' => false,
        'teeny' => false,
        'tinymce' => true,
        'quicktags' => array(
            'buttons' => 'strong,em,link,block,ul,ol,li,more,close',
        ),
        'editor_class' => 'wp-seed-content-testimonial-editor',
    );
}

function wp_seed_content_testimonial_clean_mce_buttons($buttons, $editor_id = '')
{
    if (wp_seed_content_testimonial_editor_id() !== $editor_id || !is_array($buttons)) {
        return $buttons;
    }

    $blocked = array('et_learn_more', 'et_box', 'et_button', 'et_tabs', 'et_author');
    return array_values(array_diff($buttons, $blocked));
}
add_filter('mce_buttons', 'wp_seed_content_testimonial_clean_mce_buttons', PHP_INT_MAX, 2);

function wp_seed_content_testimonial_clean_mce_plugins($plugins, $editor_id = '')
{
    if (wp_seed_content_testimonial_editor_id() === $editor_id && is_array($plugins)) {
        unset($plugins['et_quicktags']);
    }

    return $plugins;
}
add_filter('mce_external_plugins', 'wp_seed_content_testimonial_clean_mce_plugins', PHP_INT_MAX, 2);

function wp_seed_content_add_testimonial_meta_boxes()
{
    remove_meta_box('postimagediv', 'seed_testimonial', 'side');
    remove_meta_box('postexcerpt', 'seed_testimonial', 'normal');
    remove_meta_box('postcustom', 'seed_testimonial', 'normal');

    add_meta_box(
        'wp_seed_content_testimonial_details',
        __('Détails du témoignage', 'wp-seed-content-kit'),
        'wp_seed_content_render_testimonial_meta_box',
        'seed_testimonial',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'wp_seed_content_add_testimonial_meta_boxes');

function wp_seed_content_enqueue_testimonial_admin_assets($hook_suffix)
{
    if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen || 'seed_testimonial' !== $screen->post_type) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    $editor_script = WP_SEED_CONTENT_KIT_DIR . 'assets/js/testimonial-editor.js';
    $editor_version = is_file($editor_script) ? hash_file('sha256', $editor_script) : false;
    wp_enqueue_script(
        'wp-seed-content-testimonial-editor',
        WP_SEED_CONTENT_KIT_URL . 'assets/js/testimonial-editor.js',
        array('quicktags'),
        $editor_version ? substr($editor_version, 0, 16) : WP_SEED_CONTENT_KIT_VERSION,
        true
    );

    wp_add_inline_script('jquery', "
        jQuery(function($) {
            var frame;
            var field = $('.seed-testimonial-photo-field');

            if (!field.length || typeof wp === 'undefined' || !wp.media) {
                return;
            }

            function setPhoto(id, url) {
                field.find('[data-seed-testimonial-thumbnail-id]').val(id);
                field.find('[data-seed-testimonial-photo-preview]').empty();

                if (url) {
                    $('<img>', {
                        src: url,
                        alt: '',
                        class: 'seed-testimonial-photo-field__image'
                    }).appendTo(field.find('[data-seed-testimonial-photo-preview]'));
                }

                field.find('[data-seed-testimonial-photo-empty]').toggle(!url);
                field.find('[data-seed-testimonial-photo-choose]').toggle(!url);
                field.find('[data-seed-testimonial-photo-replace]').toggle(!!url);
                field.find('[data-seed-testimonial-photo-remove]').toggle(!!url);
            }

            field.on('click', '[data-seed-testimonial-photo-choose], [data-seed-testimonial-photo-replace]', function(event) {
                event.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '" . esc_js(__('Choisir une photo du témoignage', 'wp-seed-content-kit')) . "',
                    button: {
                        text: '" . esc_js(__('Utiliser cette photo', 'wp-seed-content-kit')) . "'
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var sizes = attachment.sizes || {};
                    var preview = sizes.thumbnail || sizes.medium || sizes.full || attachment;

                    setPhoto(attachment.id, preview.url);
                });

                frame.open();
            });

            field.on('click', '[data-seed-testimonial-photo-remove]', function(event) {
                event.preventDefault();
                setPhoto(0, '');
            });
        });
    ");
}
add_action('admin_enqueue_scripts', 'wp_seed_content_enqueue_testimonial_admin_assets');

/**
 * Whether the testimonial publication checkbox is selected.
 *
 * New auto-drafts start enabled. Existing testimonials always reflect the
 * stored canonical value so legacy records are never authorized implicitly.
 *
 * @param WP_Post $post Testimonial being edited.
 *
 * @return bool
 */
function wp_seed_content_testimonial_publication_consent_is_checked($post)
{
    if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type) {
        return false;
    }

    if ('auto-draft' === $post->post_status) {
        return true;
    }

    return '1' === (string) wp_seed_content_get_meta(
        $post->ID,
        wp_seed_content_testimonial_publication_consent_meta_key()
    );
}

function wp_seed_content_render_testimonial_meta_box($post)
{
    wp_nonce_field('wp_seed_content_save_testimonial_meta', 'wp_seed_content_testimonial_nonce');
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    $thumbnail = $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'thumbnail', false, array('class' => 'seed-testimonial-photo-field__image')) : '';
    $stored_date = (string) wp_seed_content_get_meta($post->ID, '_seed_testimonial_date');
    $testimonial_date = wp_seed_content_sanitize_iso_date($stored_date);
    $has_invalid_stored_date = '' !== $stored_date && '' === $testimonial_date;
    $publication_authorized = wp_seed_content_testimonial_publication_consent_is_checked($post);
    ?>
    <div class="seed-testimonial-text-field">
        <label for="wp_seed_content_testimonial_text"><strong><?php esc_html_e('Témoignage', 'wp-seed-content-kit'); ?></strong></label><br>
        <?php
        wp_editor(
            wp_seed_content_get_testimonial_builder_meta($post->ID, 'seed_testimonial_text'),
            wp_seed_content_testimonial_editor_id(),
            wp_seed_content_testimonial_editor_settings()
        );
        ?>
        <p class="description"><?php esc_html_e('Utilisez le bouton More pour séparer l’introduction de la suite du témoignage.', 'wp-seed-content-kit'); ?></p>
    </div>
    <p>
        <label for="wp_seed_content_testimonial_summary"><strong><?php esc_html_e('Résumé court', 'wp-seed-content-kit'); ?></strong></label><br>
        <textarea id="wp_seed_content_testimonial_summary" name="wp_seed_content_testimonial_summary" rows="4" class="widefat"><?php echo esc_textarea((string) $post->post_excerpt); ?></textarea>
    </p>
    <p class="description">
        <?php esc_html_e('Résumé affiché dans les formats courts, par exemple le carrousel de la page d’accueil.', 'wp-seed-content-kit'); ?>
    </p>
    <p>
        <label for="wp_seed_content_testimonial_name"><strong><?php esc_html_e('Nom ou initiales', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="text" id="wp_seed_content_testimonial_name" name="seed_testimonial_name" value="<?php echo esc_attr(wp_seed_content_get_testimonial_builder_meta($post->ID, 'seed_testimonial_name')); ?>" class="widefat">
    </p>
    <div class="seed-testimonial-photo-field">
        <p><strong><?php esc_html_e('Photo du témoignage', 'wp-seed-content-kit'); ?></strong></p>
        <input type="hidden" name="wp_seed_content_testimonial_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>" data-seed-testimonial-thumbnail-id>
        <div class="seed-testimonial-photo-field__preview" data-seed-testimonial-photo-preview>
            <?php echo $thumbnail; ?>
        </div>
        <p class="description" data-seed-testimonial-photo-empty <?php echo $thumbnail ? 'style="display:none;"' : ''; ?>>
            <?php esc_html_e('Aucune photo sélectionnée.', 'wp-seed-content-kit'); ?>
        </p>
        <p>
            <button type="button" class="button" data-seed-testimonial-photo-choose <?php echo $thumbnail ? 'style="display:none;"' : ''; ?>><?php esc_html_e('Choisir une photo', 'wp-seed-content-kit'); ?></button>
            <button type="button" class="button" data-seed-testimonial-photo-replace <?php echo $thumbnail ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Remplacer', 'wp-seed-content-kit'); ?></button>
            <button type="button" class="button" data-seed-testimonial-photo-remove <?php echo $thumbnail ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Supprimer', 'wp-seed-content-kit'); ?></button>
        </p>
    </div>
    <p>
        <label for="wp_seed_content_testimonial_date"><strong><?php esc_html_e('Date du témoignage', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="date" id="wp_seed_content_testimonial_date" name="_seed_testimonial_date" value="<?php echo esc_attr($testimonial_date); ?>">
    </p>
    <p class="description">
        <?php esc_html_e('Date à laquelle le témoignage a été donné. Elle est indépendante de la date d’ajout dans WordPress.', 'wp-seed-content-kit'); ?>
    </p>
    <?php if ($has_invalid_stored_date) : ?>
        <p class="notice notice-warning inline">
            <?php esc_html_e('La date historique enregistrée n’est pas valide. Choisissez une date valide pour la remplacer, ou laissez le champ vide puis enregistrez pour la supprimer.', 'wp-seed-content-kit'); ?>
        </p>
    <?php endif; ?>
    <p>
        <label for="wp_seed_content_testimonial_context"><strong><?php esc_html_e('Information complémentaire', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="text" id="wp_seed_content_testimonial_context" name="seed_testimonial_context" value="<?php echo esc_attr(wp_seed_content_get_testimonial_builder_meta($post->ID, 'seed_testimonial_context')); ?>" class="widefat">
    </p>
    <p class="description">
        <?php esc_html_e('Précision facultative affichée avec le témoignage, par exemple « En 3e année du parcours » ou « Après 2 ans de suivi ».', 'wp-seed-content-kit'); ?>
    </p>
    <fieldset>
        <legend><strong><?php esc_html_e('Publication du témoignage', 'wp-seed-content-kit'); ?></strong></legend>
        <p>
            <label>
                <input type="checkbox" name="_seed_testimonial_publication_consent" value="1" <?php checked($publication_authorized); ?>>
                <?php esc_html_e('Publication autorisée', 'wp-seed-content-kit'); ?>
            </label>
        </p>
        <p class="description">
            <?php esc_html_e('Décochez cette case pour retirer immédiatement ce témoignage de tous les affichages publics.', 'wp-seed-content-kit'); ?>
        </p>
    </fieldset>
    <p>
        <label>
            <input type="checkbox" name="_seed_testimonial_featured" value="1" <?php checked('1' === (string) wp_seed_content_get_meta($post->ID, '_seed_testimonial_featured')); ?>>
            <?php esc_html_e('Mis en avant', 'wp-seed-content-kit'); ?>
        </label>
    </p>
    <?php
}
