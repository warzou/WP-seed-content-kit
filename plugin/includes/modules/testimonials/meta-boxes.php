<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_add_testimonial_meta_boxes()
{
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

function wp_seed_content_render_testimonial_meta_box($post)
{
    wp_nonce_field('wp_seed_content_save_testimonial_meta', 'wp_seed_content_testimonial_nonce');
    ?>
    <p>
        <label for="wp_seed_content_testimonial_name"><strong><?php esc_html_e('Nom ou initiales', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="text" id="wp_seed_content_testimonial_name" name="_seed_testimonial_name" value="<?php echo esc_attr(wp_seed_content_get_meta($post->ID, '_seed_testimonial_name')); ?>" class="widefat">
    </p>
    <p>
        <label for="wp_seed_content_testimonial_text"><strong><?php esc_html_e('Texte', 'wp-seed-content-kit'); ?></strong></label><br>
        <textarea id="wp_seed_content_testimonial_text" name="_seed_testimonial_text" rows="8" class="widefat"><?php echo esc_textarea(wp_seed_content_get_meta($post->ID, '_seed_testimonial_text')); ?></textarea>
    </p>
    <p>
        <label for="wp_seed_content_testimonial_context"><strong><?php esc_html_e('Contexte', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="text" id="wp_seed_content_testimonial_context" name="_seed_testimonial_context" value="<?php echo esc_attr(wp_seed_content_get_meta($post->ID, '_seed_testimonial_context')); ?>" class="widefat">
    </p>
    <p>
        <label for="wp_seed_content_testimonial_date"><strong><?php esc_html_e('Date', 'wp-seed-content-kit'); ?></strong></label><br>
        <input type="date" id="wp_seed_content_testimonial_date" name="_seed_testimonial_date" value="<?php echo esc_attr(wp_seed_content_get_meta($post->ID, '_seed_testimonial_date')); ?>">
    </p>
    <p>
        <label>
            <input type="checkbox" name="_seed_testimonial_consent" value="1" <?php checked(wp_seed_content_is_truthy_meta($post->ID, '_seed_testimonial_consent')); ?>>
            <?php esc_html_e('Consentement de publication obtenu', 'wp-seed-content-kit'); ?>
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="_seed_featured" value="1" <?php checked(wp_seed_content_is_truthy_meta($post->ID, '_seed_featured')); ?>>
            <?php esc_html_e('Mis en avant', 'wp-seed-content-kit'); ?>
        </label>
    </p>
    <?php
}
