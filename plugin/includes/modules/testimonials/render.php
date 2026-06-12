<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_render_testimonial_card($post_id)
{
    $name = wp_seed_content_get_meta($post_id, '_seed_testimonial_name');
    $text = wp_seed_content_get_meta($post_id, '_seed_testimonial_text');
    $context = wp_seed_content_get_meta($post_id, '_seed_testimonial_context');
    $date = wp_seed_content_format_date(wp_seed_content_get_meta($post_id, '_seed_testimonial_date'));

    ob_start();
    ?>
    <article class="seed-card seed-card--testimonial">
        <?php if (has_post_thumbnail($post_id)) : ?>
            <div class="seed-testimonials__photo">
                <?php echo get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'seed-testimonials__photo-image', 'loading' => 'lazy')); ?>
            </div>
        <?php endif; ?>
        <div class="seed-card__body">
            <?php if ($text) : ?>
                <blockquote class="seed-card__quote"><?php echo esc_html($text); ?></blockquote>
            <?php endif; ?>
            <footer class="seed-card__testimonial-footer">
                <?php if ($name) : ?>
                    <span class="seed-card__testimonial-name"><?php echo esc_html($name); ?></span>
                <?php endif; ?>
                <?php if ($context) : ?>
                    <span class="seed-card__testimonial-context"><?php echo esc_html($context); ?></span>
                <?php endif; ?>
                <?php if ($date) : ?>
                    <time class="seed-card__date"><?php echo esc_html($date); ?></time>
                <?php endif; ?>
            </footer>
        </div>
    </article>
    <?php

    return ob_get_clean();
}
