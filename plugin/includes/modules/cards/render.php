<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_render_post_card($post_id, $args = array())
{
    $defaults = array(
        'show_image' => true,
        'show_category' => true,
        'show_date' => true,
        'show_title' => true,
        'show_excerpt' => true,
        'show_button' => true,
        'button_label' => __('Lire', 'wp-seed-content-kit'),
    );
    $args = wp_parse_args($args, $defaults);

    $permalink = get_permalink($post_id);
    $category = wp_seed_content_get_primary_category($post_id);
    $category_slug = $category ? sanitize_html_class($category->slug) : 'post';
    $category_name = $category ? $category->name : __('Post', 'wp-seed-content-kit');

    ob_start();
    ?>
    <article class="seed-card seed-card--post seed-card--category-<?php echo esc_attr($category_slug); ?>">
        <?php if ($args['show_image']) : ?>
            <?php if (has_post_thumbnail($post_id)) : ?>
                <a class="seed-card__image-link" href="<?php echo esc_url($permalink); ?>">
                    <?php echo get_the_post_thumbnail($post_id, 'medium_large', array('class' => 'seed-card__image', 'loading' => 'lazy')); ?>
                </a>
            <?php else : ?>
                <div class="seed-card__image-placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="seed-card__body">
            <?php if ($args['show_category'] || $args['show_date']) : ?>
                <div class="seed-card__meta">
                    <?php if ($args['show_category']) : ?>
                        <span class="seed-card__badge seed-card__badge--<?php echo esc_attr($category_slug); ?>"><?php echo esc_html($category_name); ?></span>
                    <?php endif; ?>

                    <?php if ($args['show_date']) : ?>
                        <time class="seed-card__date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('', $post_id)); ?></time>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($args['show_title']) : ?>
                <h3 class="seed-card__title">
                    <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
                </h3>
            <?php endif; ?>

            <?php if ($args['show_excerpt']) : ?>
                <p class="seed-card__excerpt"><?php echo esc_html(wp_seed_content_get_post_excerpt($post_id)); ?></p>
            <?php endif; ?>

            <?php if ($args['show_button']) : ?>
                <div class="seed-card__footer">
                    <a class="seed-card__button" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($args['button_label']); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </article>
    <?php

    return ob_get_clean();
}

function wp_seed_content_get_primary_category($post_id)
{
    $categories = get_the_category($post_id);
    if (empty($categories) || is_wp_error($categories)) {
        return null;
    }

    return $categories[0];
}
