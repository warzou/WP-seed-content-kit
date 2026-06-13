<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_add_quote_meta_boxes()
{
    add_meta_box(
        'wp_seed_content_quote_details',
        __('DÃ©tails de la citation', 'wp-seed-content-kit'),
        'wp_seed_content_render_quote_meta_box',
        'seed_quote',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'wp_seed_content_add_quote_meta_boxes');

function wp_seed_content_render_quote_meta_box($post)
{
    wp_nonce_field('wp_seed_content_save_quote_meta', 'wp_seed_content_quote_nonce');

    $quote = wp_seed_content_get_meta($post->ID, '_seed_quote_text');
    $author = wp_seed_content_get_meta($post->ID, '_seed_quote_author');
    $era = wp_seed_content_get_meta($post->ID, '_seed_quote_era');
    $source = wp_seed_content_get_meta($post->ID, '_seed_quote_source');
    ?>
    <p>
        <strong><?php esc_html_e('Champs obligatoires', 'wp-seed-content-kit'); ?></strong>
    </p>
    <p>
        <label for="wp_seed_content_quote_text"><strong><?php esc_html_e('Citation', 'wp-seed-content-kit'); ?></strong></label><br />
        <textarea
            id="wp_seed_content_quote_text"
            name="_seed_quote_text"
            rows="6"
            class="widefat"
            required
        ><?php echo esc_textarea($quote); ?></textarea>
    </p>
    <p>
        <strong><?php esc_html_e('Champs optionnels', 'wp-seed-content-kit'); ?></strong>
    </p>
    <p>
        <label for="wp_seed_content_quote_author"><strong><?php esc_html_e('Auteur', 'wp-seed-content-kit'); ?></strong></label> —
        <span class="description"><?php esc_html_e('optionnel', 'wp-seed-content-kit'); ?></span><br />
        <input
            type="text"
            id="wp_seed_content_quote_author"
            name="_seed_quote_author"
            value="<?php echo esc_attr($author); ?>"
            class="widefat"
        />
    </p>
    <p>
        <label for="wp_seed_content_quote_era"><strong><?php esc_html_e('Époque / date affichée', 'wp-seed-content-kit'); ?></strong></label> —
        <span class="description"><?php esc_html_e('optionnel', 'wp-seed-content-kit'); ?></span><br />
        <input
            type="text"
            id="wp_seed_content_quote_era"
            name="_seed_quote_era"
            value="<?php echo esc_attr($era); ?>"
            class="widefat"
        />
    </p>
    <p>
        <label for="wp_seed_content_quote_source"><strong><?php esc_html_e('Source / contexte', 'wp-seed-content-kit'); ?></strong></label> —
        <span class="description"><?php esc_html_e('optionnel', 'wp-seed-content-kit'); ?></span><br />
        <input
            type="text"
            id="wp_seed_content_quote_source"
            name="_seed_quote_source"
            value="<?php echo esc_attr($source); ?>"
            class="widefat"
        />
    </p>
    <p>
        <label>
            <span class="description"><?php esc_html_e('optionnel', 'wp-seed-content-kit'); ?></span>
            <input type="checkbox" name="_seed_quote_featured" value="1" <?php checked(wp_seed_content_is_truthy_meta($post->ID, '_seed_quote_featured')); ?> />
            <?php esc_html_e('Mis en avant', 'wp-seed-content-kit'); ?>
        </label>
    </p>
    <?php
}
