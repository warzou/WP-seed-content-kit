<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_add_testimonial_meta_boxes()
{
    remove_meta_box('postimagediv', 'seed_testimonial', 'side');

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

function wp_seed_content_render_testimonial_meta_box($post)
{
    wp_nonce_field('wp_seed_content_save_testimonial_meta', 'wp_seed_content_testimonial_nonce');
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    $thumbnail = $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'thumbnail', false, array('class' => 'seed-testimonial-photo-field__image')) : '';
    ?>
    <p>
        <label for="wp_seed_content_testimonial_name"><strong><?php esc_html_e('Nom ou initiales', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="text" id="wp_seed_content_testimonial_name" name="_seed_testimonial_name" value="<?php echo esc_attr(wp_seed_content_get_meta($post->ID, '_seed_testimonial_name')); ?>" class="widefat">
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
        <label for="wp_seed_content_testimonial_text"><strong><?php esc_html_e('Témoignage', 'wp-seed-content-kit'); ?></strong></label><br>
        <textarea id="wp_seed_content_testimonial_text" name="_seed_testimonial_text" rows="8" class="widefat"><?php echo esc_textarea(wp_seed_content_get_meta($post->ID, '_seed_testimonial_text')); ?></textarea>
    </p>
    <p>
        <label>
            <input type="checkbox" name="_seed_featured" value="1" <?php checked(wp_seed_content_is_truthy_meta($post->ID, '_seed_featured')); ?>>
            <?php esc_html_e('Mis en avant', 'wp-seed-content-kit'); ?>
        </label>
    </p>
    <?php
}
