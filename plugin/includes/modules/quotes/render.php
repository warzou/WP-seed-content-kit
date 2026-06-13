<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_render_quote_card($post_id)
{
    $text = wp_seed_content_get_meta($post_id, '_seed_quote_text');
    $author = wp_seed_content_get_meta($post_id, '_seed_quote_author');
    $era = wp_seed_content_get_meta($post_id, '_seed_quote_era');
    $source = wp_seed_content_get_meta($post_id, '_seed_quote_source');

    ob_start();
    ?>
    <article class="seed-card seed-card--quote">
        <?php if ($text) : ?>
            <div class="seed-card__body">
                <blockquote class="seed-card__quote"><?php echo nl2br(esc_html($text)); ?></blockquote>
                <p class="seed-card__meta">
                    <?php if ($author) : ?>
                        <span class="seed-card__testimonial-name"><?php echo esc_html($author); ?></span>
                    <?php endif; ?>
                    <?php if ($era) : ?>
                        <span><?php echo esc_html($era); ?></span>
                    <?php endif; ?>
                    <?php if ($source) : ?>
                        <span><?php echo esc_html($source); ?></span>
                    <?php endif; ?>
                </p>
            </div>
        <?php else : ?>
            <div class="seed-card__body">
                <p class="seed-quotes__empty"><?php echo esc_html__('Citation vide.', 'wp-seed-content-kit'); ?></p>
            </div>
        <?php endif; ?>
    </article>
    <?php

    return ob_get_clean();
}
